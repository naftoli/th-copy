<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

class ChidonDriveGlobal {
  private $year;
  private $grant;

  public function __construct( $year ) {
    $this->year = $year;
  }

  public function setGoal( $goal ) {
    $this->goal = $goal;
  }

  public function setGrant( $grant ) {
    $this->grant = $grant;
  }

  // find out how much was raised so far
  // overrrides parent method
  public function getAmountRaised() {
    global $MASHPIA_DB;

    $raised = $this->grant;
    // find out sum of donations
    $stmt = $MASHPIA_DB->prepare("
      SELECT 
          IFNULL( SUM(donation_amount), 0 ) AS total 
      FROM
          chidon_donations
      WHERE
          year = :year
    ");
    $res = $stmt->execute([
      ':year' =>  $this->year
    ]);
    if ( $res ) {
      $row = $stmt->fetch();
      $raised += $row['total'];
    } else {
      throw new Exception("Error calculating amount raised.");
    }
    return $raised;
  }
}

abstract class ChidonDrive {
  protected $year;
  protected $goal;
  protected $children = [];
  protected $costPerChild;
  protected $regPerChild;
  protected $grantPerChild;
  
  public function __construct( $year ) {
    $this->year = $year;
  }

  public function setAmounts( $costPerChild, $regPerChild, $grantPerChild ) {
    $this->costPerChild = $costPerChild;
    $this->regPerChild = $regPerChild;
    $this->grantPerChild = $grantPerChild;
  }

  abstract public function setChildren();

  public function setGoal() {
    if ( !$this->costPerChild ) {
      throw new Exception("You have not entered the cost per child.");
    }
    
    $this->setChildren();
    $this->goal = count( $this->children ) * $this->costPerChild;
  }

  public function getGoal() {
    return $this->goal; 
  }

  public function getNumChildren() {
    return count( $this->children );
  }

  // throws error if there's a problem getting info from db
  public function getAmountRaised() {
    global $MASHPIA_DB;

    $users = implode(',', $this->children);
    $stmt = $MASHPIA_DB->prepare("
      SELECT 
          IFNULL( SUM(subsidy_amount), 0 ) AS total 
      FROM
          chidon_user_subsidies
      WHERE
          chidon_year = :year 
              AND user_id IN ($users)
    ");
    $res = $stmt->execute([
      ':year'   =>  $this->year 
    ]);
    if ( $res ) {
      $row = $stmt->fetch();
      return $row['total'];
    } else {
      throw new Exception("Error calculating amount raised.");
    }
  }
}

class ChidonDriveFamily extends ChidonDrive {
  private $parent_id;

  public function __construct( $year, $admin_id ) {
    parent::__construct( $year );
    $this->parent_id = $admin_id;
  }

  // finds all children connected to this parent that is a contestant in the chidon
  // throws error if we have issues retrieving the children from the db
  public function setChildren() {
    global $MASHPIA_DB;

    $stmt = $MASHPIA_DB->prepare("
      SELECT 
          aa.id
      FROM
          admin_auths aa
              JOIN
          th_chidon tc ON aa.id = tc.user_id
      WHERE
          aa.admin_id = :admin_id AND aa.role_id = 1
              AND tc.year = :year
              AND tc.contestant = 1
    ");
    $res = $stmt->execute([
      ':admin_id' =>  $this->parent_id, 
      ':year'     =>  $this->year
    ]);
    //echo "<pre>"; $stmt->debugDumpParams(); echo "</pre>";
    if ( $res ) {
      $rows = $stmt->fetchAll();
      foreach ( $rows as $row ) {
        $this->children[] = $row['id'];
      }
    } else {
      throw new Exception("Error setting children.");
    }
  }
}

class ChidonDriveSchool extends ChidonDrive {
  private $school;

  public function __construct( $year, $school_id ) {
    parent::__construct( $year );
    $this->school = $school_id;
  }

  public function setChildren() {
    global $MASHPIA_DB;

    $stmt = $MASHPIA_DB->prepare("
      SELECT 
          user_id  
      FROM
          th_chidon 
      WHERE
          year = :year AND contestant = 1
              AND school_id = :school
    ");
    $res = $stmt->execute([
      ':year'     =>  $this->year, 
      ':school'   =>  $this->school 
    ]);
    if ( $res ) {
      $rows = $stmt->fetchAll();
      foreach ( $rows as $row ) {
        $this->children[] = $row['user_id'];
      }
    } else {
      throw new Exception("Error setting children.");
    }
  }
}

class ChidonDriveCommunity extends ChidonDrive {
  private $community;
  private $schools;

  public function __construct( $year ) {
    parent::__construct( $year );
  }

  public function setCommunity( $community, array $schools ) {
    $this->community = $community;
    $this->schools = $schools;
  }

  // figures out children based on community schools
  public function setChildren() {
    global $MASHPIA_DB;

    $school_ids = implode(",", $this->schools);
    $stmt = $MASHPIA_DB->prepare("
      SELECT 
          user_id 
      FROM
          th_chidon 
      WHERE
          year = :year AND contestant = 1
              AND school_id IN ($school_ids)
    ");
    $res = $stmt->execute([
      ':year'     =>  $this->year
    ]);
    if ( $res ) {
      $rows = $stmt->fetchAll();
      foreach ( $rows as $row ) {
        $this->children[] = $row['user_id'];
      }
    } else {
      throw new Exception("Error setting children.");
    }
  }
}