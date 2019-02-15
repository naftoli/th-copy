<?php
//ini_set('display_errors',1);
require __DIR__ . '/../classes/ChidonDrive.php';
require __DIR__ . '/../classes/Communities.php';
require __DIR__ . '/../../../class.globalSettings.php';

$getSchools = isset( $_POST['getSchools'] ) && $_POST['getSchools'] == 0 ? 0 : 1; // flag to decide if we need the school info

$year = GlobalSettings::getChidonYear();
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
    if ( $goal > 0 ) { // don't show communities that have no goal / children
      $raised = floatVal( $cd->getAmountRaised() );
      $percent = number_format( floatval( ($raised / $goal) * 100 ), 2 );
      $numChildren = $cd->getNumChildren();
      $leaderboard[$percent][$community]['communityInfo'] = [
        'goal'        =>  $goal,
        'raised'      =>  $raised, 
        'percent'     =>  $percent,
        'numChildren' =>  $numChildren
      ];
      $communityPercent = $percent;

      if ( $getSchools ) {
        // get the breakdown of stats per school
        $schoolsStats = [];
        foreach ( $schools as $school ) {
          $cs = new ChidonDriveSchool( $year, $school );
          $cs->setAmounts( 350, 250, 100 );
          $cs->setGoal();
          $goal = $cs->getGoal();
          if ( $goal > 0 ) {
            $raised = floatVal( $cs->getAmountRaised() );
            $percent = number_format( floatval( ($raised / $goal) * 100 ), 2 );
            $numChildren = $cs->getNumChildren();
            $schoolInfo = $cs->getSchoolInfo();
            $schoolsStats[$percent][$schoolInfo['school_name']] = [
              'logo'    =>  $schoolInfo['logo'],
              'percent' =>  $percent, 
              'goal'    =>  $goal, 
              'raised'  =>  $raised, 
              'numChildren' =>  $numChildren
            ];
          }
        }
        foreach ( $schoolsStats as $percent => $more ) {
          ksort( $schoolsStats[$percent] );
        }
        ksort( $schoolsStats );
        $leaderboard[$communityPercent][$community]['schools'] = $schoolsStats;
      }      
    }
  }
} catch ( Exception $e ) {
  echo json_encode([
    'success' =>  false,
    'message' =>  $e->getMessage()
  ]);
}

// sort first by amount then by community name
foreach ( $leaderboard as $percent => $more ) {
  ksort( $leaderboard[$percent] );
}
asort( $leaderboard );

echo json_encode([
  'success' =>  true,
  'data'    =>  $leaderboard
]);