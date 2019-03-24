<?php
require_once __DIR__ . "/../../../api/header/db.php";
require_once __DIR__ . "/../../../class.globalSettings.php";

class StaffManager
{
  private $db;
  private $year;
  private $staffID;
  private $personalInfo;
  private $assignments;
  private $types;
  private $bunks;
  private $walkingGroups;

  public function __construct() {
    global $MASHPIA_DB;
    $this->db = $MASHPIA_DB;
    $this->staffID = 0;
    $this->personalInfo = [];
    $this->assignments = [];
    $this->types = [];
    $this->bunks = [];
    $this->walkingGroups = [];
    $this->year = GlobalSettings::getChidonYear();
  }

  public function checkLogin( $username, $password ) {
    $stmt = $this->db->prepare("select * from th_chidon_staff where username = :username and password = :password and year = :year");
    $res = $stmt->execute([
      ':username' =>  $username, 
      ':password' =>  $password, 
      ':year'     =>  $this->year
    ]);
    if ( $res ) {
      $rows = $stmt->fetchAll();
      foreach ( $rows as $row ) {
        $this->staffID = intval( $row['staff_id'] );
        $this->personalInfo = $row;
      }
    }
    if ( $this->staffID ) return true;
    else return false;
  }

  public function setStaffByID( $id ) {
    $stmt = $this->db->prepare("select * from th_chidon_staff where staff_id = :id");
    $res = $stmt->execute([ ':id' =>  $id ]);
    if ( $res ) {
      $rows = $stmt->fetchAll();
      foreach ( $rows as $row ) {
        $this->staffID = intval( $row['staff_id'] );
        $this->personalInfo = $row;
        return true;
      }
    }
    return false;
  }

  public function getID() {
    return $this->staffID;
  }

  public function getPersonalInfo() {
    return $this->personalInfo;
  }

  public function getInfo() {
    $this->setAssignments();
    $this->setTypes();
    return json_encode([
      'assignments' =>  $this->assignments, 
      'types'       =>  $this->types
    ]);
  }

  private function setAssignments() {
    if ( empty( $this->assignments ) ) {
      $stmt = $this->db->prepare("
        SELECT 
            *
        FROM
            th_chidon_staff_types st
                JOIN
            th_chidon_types t ON t.th_chidon_type_id = st.type_id
                LEFT JOIN
            th_chidon_staff_assignments s ON s.staff_type_id = st.th_chidon_staff_type_id
        WHERE
            st.staff_id = :staff_id
      ");
      $stmt->execute([ ':staff_id' => $this->staffID ]);
      $rows = $stmt->fetchAll();
      foreach ( $rows as $row ) {
        $this->assignments[$row['type']][$row['role']][] = $row['group_number'];
      }
    }
  }

  private function setTypes() {
    if ( empty( $this->types ) ) {
      $stmt = $this->db->prepare("
        SELECT 
            t.type
        FROM
            th_chidon_staff_types st
                JOIN
            th_chidon_types t ON t.th_chidon_type_id = st.type_id
        WHERE
            st.staff_id = :staff_id
        GROUP BY t.type
      ");
      $stmt->execute([ ':staff_id' =>  $this->staffID ]);
      $rows = $stmt->fetchAll();
      foreach ( $rows as $row ) {
        $this->types[] = $row['type'];
      }
    } 
  }

  public function getBunks() {
    foreach ( $this->assignments as $row ) {
      if ( $row['bunk'] ) $this->bunks[] = $row['bunk'];
    }
    return $this->bunks;
  }

  public function getWalkingGroups() {
    foreach ( $this->assignments as $row ) {
      if ( $row['walking_group'] ) $this->walkingGroups[] = $row['walking_group'];
    }
    return $this->walkingGroups;
  }

  public function getTimes( array $groups ) {
    $times = [];
    $listOfGroups = implode(',', $groups);
    $stmt = $this->db->prepare("
      SELECT 
          t.*
      FROM
          th_chidon_attendance_times t
              JOIN
          th_chidon_staff_assignments sa ON sa.group_number = t.att_type_id
              JOIN
          th_chidon_staff_types st ON st.th_chidon_staff_type_id = sa.staff_type_id
      WHERE
          st.staff_id = :staff_id
              AND t.att_type_id IN ($listOfGroups)
    ");
    $res = $stmt->execute([ ':staff_id' => $this->staffID ]);
    if ( $res ) {
      $times = $stmt->fetchAll();
    }
    return $times;
  }

  public function getChildren( $time_id, $type, array $groups ) {
    $field = null;
    switch ( $type ) {
      case 'bunk':
        $field = "bunk_number";
        break;
      case 'walk':
        $field = "walking_group";
        break;
    } 
    $groupsList = implode(',', $groups);
    $stmt = $this->db->prepare("
      SELECT 
          tc.*,
          s.school_name,
          u.first,
          u.last,
          u.user_serial,
          u.user_id,
          m.marked
      FROM
          th_chidon tc
              JOIN
          schools USING (school_id)
              JOIN
          users USING (user_id)
              LEFT JOIN
          th_chidon_attendance_marks m ON m.th_chidon_id = tc.th_chidon_id
              AND m.att_time_id = :time_id
      WHERE
          year = :year AND $field IN ($groupsList)
      ORDER BY $field , between_streets1 , between_streets2 , host_street , host_street_num , host_street_num_suffix , host_street_apt , first , last
    ");
    $res = $stmt->execute([ 
      ':year'     =>  $this->year, 
      ':time_id'  =>  $time_id 
    ]);
    if ( $res ) return $stmt->fetchAll();
  }
}