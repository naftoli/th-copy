<?php
//ini_set('display_errors',1);
require '../classes/ChidonDrive.php';
require '../classes/Communities.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$year = GlobalSettings::getChidonYear();
$year = 5778; // override for testing

$c = new Communities;
$communities = $c->getCommunities();

$leaderboard = [];
try {
  foreach ( $communities as $community => $schools ) {
    $cd = new ChidonDriveCommunity( $year );
    $cd->setAmounts( 350, 250, 100 );
    $cd->setCommunity( $community, $schools );
    $cd->setGoal();
    $goal = $cd->getGoal();
    $raised = floatVal( $cd->getAmountRaised() );
    $numChildren = $cd->getNumChildren();
    if ( $goal > 0 ) { // don't show communities that have no goal / children
      $percent = number_format( floatval( ($raised / $goal) * 100 ), 2 );
      $leaderboard[$percent][$community] = [
        'goal'        =>  $goal,
        'raised'      =>  $raised, 
        'percent'     =>  $percent,
        'numChildren' =>  $numChildren
      ];
    }
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