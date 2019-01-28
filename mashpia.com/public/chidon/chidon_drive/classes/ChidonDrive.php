<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

class ChidonDrive {
  protected $year;
  protected $goal;
  protected $goalPerChild;
  protected $grant;
  protected $grantPerChild;
  protected $costPerChild;
  protected $regPerChild;
  
  public function __construct( $year ) {
    $this->year = $year;
  }

  public function setGoals( $goal, $goalPerChild ) {
    $this->goal = $goal;
    $this->goalPerChild = $goalPerChild;
  }

  public function setGrants( $grant, $grantPerChild ) {
    $this->grant = $grant;
    $this->grantPerChild = $perChildAmount;
  }

  public function setCostRegPerChild( $costPerChild, $regPerChild ) {
    $this->costPerChild = $costPerChild;
    $this->regPerChild = $regPerChild;
  }

  public function getGoal() {
    return $this->goal;
  }

  // find out how much was raised so far
  // throws error if there's an issue getting info from db
  public function getAmountRaised() {
    $raised = $grant;
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

class ChidonDriveFamily extends ChidonDrive {
  private $parent_id;
  private $children = [];

  public function __construct( $year ) {
    parent::__construct( $year );
  }

  public function setParent( $admin_id ) {
    $this->parent_id = $admin_id;
  }

  // finds all children connected to this parent that is a contestant in the chidon
  // throws error if we have issues retrieving the children from the db
  public function setChildren() {
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
    } else {
      throw new \Error("Error fetching children.");
    }
  }

  public function getFamilyGoal() {
    return count( $this->children ) * $this->costPerChild; 
  }
}

class ChidonDriveSchool extends ChidonDrive {
  private $school;

  public function __construct( $year ) {
    parent::__construct( $year );
  }

  public function setSchool( $school_id ) {
    $this->school = $school_id;
  }

  // figures out community goal based on number of children eligible in school
  public function getSchoolGoal() {
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
      return $row['total'] * $this->costPerChild;
    } else {
      throw new \Error("Error computing school goal.");
    }
  }
}

class ChidonDriveCommunity extends ChidonDrive {
  private $community;
  private $schools;

  public function __construct( $year ) {
    parent::__construct( $year );
  }

  public function setCommunity( $community, $schools ) {
    $this->community = $community;
    $this->schools = $schools;
  }

  // figures out community goal based on number of children eligible in school
  public function getCommunityGoal() {
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
      return $row['total'] * $this->costPerChild;
    } else {
      throw new \Error("Error computing community goal.");
    }
  }
}