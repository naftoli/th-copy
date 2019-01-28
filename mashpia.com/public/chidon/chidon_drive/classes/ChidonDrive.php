<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

// interface will be used for family / school / community / global goals, and amounts raised, to have uniform functions
interface iChidonDrive {
  public function setGoal();
  public function getGoal();
  public function getAmountRaised();
}

class ChidonDrive {
  protected $year;
  protected $goalPerChild;
  protected $grantPerChild;
  protected $costPerChild;
  protected $regPerChild;
  
  public function __construct( $year ) {
    $this->year = $year;
  }

  public function setAmounts( $goalPerChild, $grantPerChild, $costPerChild, $regPerChild ) {
    $this->goalPerChild = $goalPerChild;
    $this->grantPerChild = $perChildAmount;
    $this->costPerChild = $costPerChild;
    $this->regPerChild = $regPerChild;
  }
}

class ChidonDriveGlobal extends ChidonDrive implements iChidonDrive {
  private $goal;
  private $grant;

  public function __construct( $year ) {
    parent::__construct( $year );
  }

  public function setGoal( $goal ) {
    $this->goal = $goal;
  }

  public function setGrant( $grant ) {
    $this->grant = $grant;
  }

  public function getGoal() {
    return $this->goal;
  }

  // find out how much was raised so far
  // throws error if there's an issue getting info from db
  public function getAmountRaised() {
    $raised = $this->grant;
    // find out sum of donations
    $MASHPIA_DB->prepare("
      SELECT 
          SUM(donation_amount) AS total 
      FROM
          chidon_donations
      WHERE
          year = :year
    ");
    $res = $MASHPIA_DB->execute([
      ':year' =>  $this->year
    ]);
    if ( $res ) {
      $row = $res->fetch();
      if ( $row ) {
        $raised += $row['total'];
      }
    }
    if ( !$res || !$row ) throw new \Error("Error fetching info from db.");
    return $raised;
  }

}

class ChidonDriveFamily extends ChidonDrive implements iChidonDrive {
  private $parent_id;
  private $children = [];
  private $goal;

  public function __construct( $year ) {
    parent::__construct( $year );
  }

  public function setParent( $admin_id ) {
    $this->parent_id = $admin_id;
  }

  // finds all children connected to this parent that is a contestant in the chidon
  // throws error if we have issues retrieving the children from the db
  public function setGoal() {
    $MASHPIA_DB->prepare("
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
    $res = $MASHPIA_DB->execute([
      ':admin_id' =>  $admin_id, 
      ':year'     =>  $this->year
    ]);
    if ( $res ) {
      $rows = $res->fetchAll();
      foreach ( $rows as $row ) {
        $this->children[] = $row['id'];
      }
      $this->goal = count( $rows ) * $this->costPerChild;
    } else {
      throw new \Error("Error fetching children.");
    }
  }

  public function getGoal() {
    return $this->goal; 
  }

  public function getAmountRaised() {

  }
}

class ChidonDriveSchool extends ChidonDrive implements iChidonDrive {
  private $school;
  private $goal;

  public function __construct( $year ) {
    parent::__construct( $year );
  }

  public function setSchool( $school_id ) {
    $this->school = $school_id;
  }

  // figures out community goal based on number of children eligible in school
  public function setGoal() {
    $MASHPIA_DB->prepare("
      SELECT 
          COUNT(*) AS total 
      FROM
          th_chidon 
      WHERE
          year = :year AND contestant = 1
              AND school_id = :school
    ");
    $res = $MASHPIA_DB->execute([
      ':year'     =>  $this->year, 
      ':school'   =>  $this->school 
    ]);
    if ( $res ) {
      $row = $res->fetch();
      $this->goal = $row['total'] * $this->costPerChild;
    } else {
      throw new \Error("Error computing school goal.");
    }
  }

  public function getGoal() {
    return $this->goal;
  }

  public function getAmountRaised() {

  }
}

class ChidonDriveCommunity extends ChidonDrive implements iChidonDrive {
  private $community;
  private $schools;
  private $goal;

  public function __construct( $year ) {
    parent::__construct( $year );
  }

  public function setCommunity( $community, $schools ) {
    $this->community = $community;
    $this->schools = $schools;
  }

  // figures out community goal based on number of children eligible in schools
  public function setGoal() {
    $school_ids = implode(',', $this->schools);
    $MASHPIA_DB->prepare("
      SELECT 
          COUNT(*) AS total 
      FROM
          th_chidon 
      WHERE
          year = :year AND contestant = 1
              AND school_id IN (:schools)
    ");
    $res = $MASHPIA_DB->execute([
      ':year'     =>  $this->year, 
      ':schools'  =>  $school_ids
    ]);
    if ( $res ) {
      $row = $res->fetch();
      $this->goal = $row['total'] * $this->costPerChild;
    } else {
      throw new \Error("Error computing community goal.");
    }
  }

  public function getGoal() {
    return $this->goal;
  }

  public function getAmountRaised() {
    
  }
}