<?php
require '../classes/ChidonDrive.php';
require '../classes/Communities.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$year = GlobalSettings::getChidonYear();
$year = 5778; // override for testing

$c = new Communities;
$communities = $c->getCommunities();

$schools = [];
try {
  // find out the stats for each school in community
  foreach ( $schools as $school ) {
    $cs = new ChidonDriveSchool( $year, $school );
    $cs->setAmounts( 350, 250, 100 );
    $cs->setGoal();
    $goal = $cs->getGoal();
    $raised = floatVal( $cs->getAmountRaised() );
    $numChildren = $cs->getNumChildren();
    $schoolInfo = $cs->getSchoolInfo();
    $schools[$school][$percent][$community] = [
      'name'    =>  $schoolInfo['school_name'], 
      'logo'    =>  $schoolInfo['logo'],
      'percent' =>  $percent, 
      'goal'    =>  $goal, 
      'raised'  =>  $raised, 
      'numChildren' =>  $numChildren
    ];
  }
} catch ( Exception $e ) {
  echo json_encode([
    'success' =>  false,
    'message' =>  $e->getMessage()
  ]);
}

// sort first by amount then by community name
foreach ( $leaderboard as $percent => $info ) {
  ksort( $leaderboard[$percent] );
}
asort( $leaderboard );

echo json_encode([
  'success' =>  true,
  'data'    =>  $leaderboard
]);