<?php
require_once __DIR__ . "/../../../api/header/db.php";
require_once __DIR__ . "/../../../class.globalSettings.php";

class StaffManager
{
  private $db;
  private $year;
  private $staffID;
  private $superAdmin;
  private $chaperone;
  private $chapID;
  private $personalInfo;
  private $assignments;
  private $roles;
  private $types;
  private $bunks;
  private $walkingGroups;

  public function __construct() {
    global $MASHPIA_DB;
    $this->db = $MASHPIA_DB;
    $this->staffID = 0;
    $this->superAdmin = false;
    $this->chaperone = false;
    $this->chapID = 0;
    $this->personalInfo = [];
    $this->assignments = [];
    $this->roles = [];
    $this->types = [];
    $this->bunks = [];
    $this->walkingGroups = [];
    $this->year = GlobalSettings::getChidonYear();
    // $this->year = 5779;
  }

  public function getYear() {
    return $this->year;
  }

  private function ensureAssignmentsLoaded() {
    if ( empty( $this->assignments ) ) $this->setAssignments();
    if ( empty( $this->types ) ) $this->setTypes();
  }

  public function assertGroupsAllowed( array $groups ) {
    $this->ensureAssignmentsLoaded();
    $allowed = [];
    if ( isset( $this->assignments['bunk'] ) && is_array( $this->assignments['bunk'] ) ) {
      foreach ( $this->assignments['bunk'] as $b ) $allowed[(string)$b] = true;
    }
    if ( isset( $this->assignments['walking_group'] ) && is_array( $this->assignments['walking_group'] ) ) {
      foreach ( $this->assignments['walking_group'] as $g ) $allowed[(string)$g] = true;
    }
    foreach ( $groups as $g ) {
      if (!isset($allowed[(string)$g])) return false;
    }
    return true;
  }

  private function buildInPlaceholders( array $values ) {
    return implode(',', array_fill(0, count($values), '?'));
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
        if ( intval( $row['super_admin'] ) ) $this->superAdmin = true;
        if ( intval( $row['chap_id'] ) ) {
          $this->chaperone = true;
          $this->chapID = intval( $row['chap_id'] );
        }
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
        if ( intval( $row['super_admin'] ) ) $this->superAdmin = true;
        if ( intval( $row['chap_id'] ) ) {
          $this->chaperone = true;
          $this->chapID = intval( $row['chap_id'] );
        }
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
    $this->ensureAssignmentsLoaded();
    return [
      'assignments' =>  $this->assignments, 
      'roles'       =>  $this->roles,
      'types'       =>  $this->types
    ];
  }

  private function setAssignments() {
    if ( $this->superAdmin ) {
      $this->roles = ['super admin'];
      // all bunks
      for ( $j = 1; $j <= 120; $j++ ) {
        $this->assignments['bunk'][] = $j;
      }
      // all walking groups
      for ( $j = 1; $j <= 32; $j++ ) {
        for ( $a = 'A'; $a <= 'B'; $a++ ) {
          $this->assignments['walking_group'][] = $j . $a;
        }
      }
      for ( $j = 1; $j <= 15; $j++ ) {
        for ( $a = 'A'; $a <= 'B'; $a++ ) {
          $this->assignments['walking_group'][] = '78-' . $j . $a;
        }
      }
      for ( $j = 1; $j <= 4; $j++ ) {
        $this->assignments['walking_group'][] = $j . 'EF';
      }
    } else {
      $stmt = $this->db->prepare("
        SELECT 
            *
        FROM
            th_chidon_staff_assignments sa
                JOIN
            th_chidon_types t ON sa.staff_type_id = t.th_chidon_type_id
        WHERE
            sa.staff_id = :staff_id 
      ");
      $stmt->execute([ ':staff_id' => $this->staffID ]);
      $rows = $stmt->fetchAll();
      foreach ( $rows as $row ) {
        if ( !isset($this->assignments[$row['type']]) ) $this->assignments[$row['type']] = [];
        if ( !in_array( $row['group_number'], $this->assignments[$row['type']] ) ) $this->assignments[$row['type']][] = $row['group_number'];
        if ( !in_array( $row['role'], $this->roles ) ) $this->roles[] = $row['role'];
      }
      // if chaperone add all other bunks
      // if ( $this->chaperone ) {
      //   $this->roles[] = 'chaperone';
      //   $stmt = $this->db->prepare("
      //     SELECT 
      //         bunk_number
      //     FROM
      //         th_chidon
      //     WHERE
      //         school_id = (SELECT 
      //                 school_id
      //             FROM
      //                 th_chidon_chaps
      //             WHERE
      //                 th_chidon_chap_id = :chap_id)
      //     GROUP BY bunk_number
      //   ");
      //   $res = $stmt->execute([ ':chap_id'  =>  $this->chapID ]);
      //   if ( $res ) {
      //     $rows = $stmt->fetchAll();
      //     foreach ( $rows as $row ) {
      //       if ( $row['bunk_number'] ) $this->assignments['bunk'][] = $row['bunk_number'];
      //     }
      //   }
      //   $stmt = $this->db->prepare("
      //     SELECT 
      //         walking_group
      //     FROM
      //         th_chidon
      //     WHERE
      //         school_id = (SELECT 
      //                 school_id
      //             FROM
      //                 th_chidon_chaps
      //             WHERE
      //                 th_chidon_chap_id = :chap_id)
      //     GROUP BY walking_group
      //   ");
      //   $res = $stmt->execute([ ':chap_id'  =>  $this->chapID ]);
      //   if ( $res ) {
      //     $rows = $stmt->fetch();
      //     foreach ( $rows as $row ) {
      //       if ( $row['walking_group'] ) $this->assignments['walking_group'][] = $row['walking_group'];
      //     }
      //   }
      // }
    }
    if (!isset($this->assignments['bunk'])) $this->assignments['bunk'] = [];
    if (!isset($this->assignments['walking_group'])) $this->assignments['walking_group'] = [];
    sort( $this->assignments['bunk'] );
    // find out the counselors for each bunk in array
    $counselors = [];
    foreach ( $this->assignments['bunk'] as $bunk ) {
      $stmt = $this->db->prepare("
        SELECT 
            first_name, last_name
        FROM
            th_chidon_staff_assignments sa
                JOIN
            th_chidon_staff s USING (staff_id)
        WHERE
            staff_type_id = 1 AND group_number = :bunk 
              AND s.gender = 'boys'
      ");
      $res = $stmt->execute([ ':bunk' => intval( $bunk ) ]);
      if ( $res ) {
        $rows = $stmt->fetchAll();
        foreach ( $rows as $row ) $counselors[$bunk][] = $row['first_name'] . ' ' . $row['last_name'];
      }
    }
    $this->assignments['counselors'] = $counselors;
    $walking_counselors = [];
    foreach ( $this->assignments['walking_group'] as $group ) {
      $stmt = $this->db->prepare("
        SELECT 
            first_name, last_name
        FROM
            th_chidon_staff_assignments sa
                JOIN
            th_chidon_staff s USING (staff_id)
        WHERE
            staff_type_id = 8 AND group_number = :group 
              AND s.gender = 'boys'
      ");
      $res = $stmt->execute([ ':group' => (string)$group ]);
      if ( $res ) {
        $rows = $stmt->fetchAll();
        foreach ( $rows as $row )
          $walking_counselors[$group][] = $row['first_name'] . ' ' . $row['last_name'];
      }
    }
    $this->assignments['walking_counselors'] = $walking_counselors;
  }

  private function setTypes() {
    if ( $this->superAdmin ) {
      $this->types = ['bunk', 'walking_group'];
    } else {
      $stmt = $this->db->prepare("
        SELECT 
            t.type
        FROM
            th_chidon_types t
                JOIN
            th_chidon_staff_assignments sa ON t.th_chidon_type_id = sa.staff_type_id
        WHERE
            sa.staff_id = :staff_id
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
    $this->ensureAssignmentsLoaded();
    return $this->assignments['bunk'] ?? [];
  }

  public function getWalkingGroups() {
    $this->ensureAssignmentsLoaded();
    return $this->assignments['walking_group'] ?? [];
  }

  public function getTimes( array $groups ) {
    $times = [];
    if (empty($groups)) return [];
    $placeholders = $this->buildInPlaceholders($groups);
    $stmt = $this->db->prepare("
      SELECT 
          t.*
      FROM
          th_chidon_attendance_times t
      WHERE
          t.year = ?
            AND t.att_type_number IN ($placeholders) 
            AND t.chidon_type = 'boys' 
      GROUP BY att_type, att_time
    ");
    $params = array_merge([ $this->year ], array_values($groups));
    $res = $stmt->execute($params);
    //$debug = $stmt->debugDumpParams();
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
    if (!$field || empty($groups)) return [];
    $placeholders = $this->buildInPlaceholders($groups);
    $sql = "
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
          schools s USING (school_id)
              JOIN
          users u USING (user_id)
              LEFT JOIN
          th_chidon_attendance_marks m ON m.th_chidon_id = tc.th_chidon_id
              AND m.att_time_id = :time_id
      WHERE
          tc.year = :year AND tc.$field IN ($placeholders) 
            AND u.gender = 'M' 
    ";
    if ( $type == 'walk' ) {
      $sql .= "ORDER BY $field, dropoff_number";
    } else {
      $sql .= "ORDER BY $field, last, first";
    }

    $stmt = $this->db->prepare( $sql );
    $stmt->bindValue(':year', $this->year);
    $stmt->bindValue(':time_id', $time_id);
    $i = 1;
    foreach ( array_values($groups) as $g ) {
      $stmt->bindValue($i++, (string)$g);
    }
    $res = $stmt->execute();
    //return $stmt->debugDumpParams(); 
    $children = [];
    if ( $res ) {
      $rows = $stmt->fetchAll();
      foreach ( $rows as $row ) {
        $children[$row[$field]][] = $row;
      }
    }
    return $children;
  }
}