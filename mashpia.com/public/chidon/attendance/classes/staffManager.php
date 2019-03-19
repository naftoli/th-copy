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

  private function setAssignments() {
    if ( empty( $this->assignments ) ) {
      $stmt = $this->db->prepare("select * from th_chidon_staff_assignments where staff_id = :staff_id");
      $stmt->execute([ ':staff_id' => $this->staffID ]);
      $this->assignments = $stmt->fetchAll();
    }
  }

  public function getNumTypes() {
    if ( empty( $this->types ) ) {
      $stmt = $this->db->prepare("
        select st.type  
        from th_chidon_staff_assignments sa 
        join th_chidon_staff_types st on sa.type_id = st.th_chidon_staff_type_id 
        where sa.staff_id = :staff_id 
        group by st.type
      ");
      $stmt->execute([ ':staff_id' =>  $this->staffID ]);
      $rows = $stmt->fetchAll();
      foreach ( $rows as $row ) {
        $this->types[] = $row['type'];
      }
    } 
    return count( $this->types );
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

  public function getChildren() {

  }
}