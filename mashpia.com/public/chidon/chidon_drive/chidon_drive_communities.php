<?php
ini_set('display_errors', 1);
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once 'classes/ChidonDrive.php';
require_once 'classes/Communities.php';

$c = new Communities();
$communities = $c->getCommunities();
//echo "<pre>"; print_r( $communities ); echo "</pre>"; exit;

$year = 5778;
foreach ( $communities as $community => $schools ) {
  $cd = new ChidonDriveCommunity( $year );
  $cd->setAmounts( 350, 250, 100 );
  $cd->setCommunity( $community, $schools );
  $cd->setGoal();
  echo "Goal of " . $community . " community: " . $cd->getGoal() . "<br />";
  echo "Number of children: " . $cd->getNumChildren() . "<br />";
  echo "Amount Raised: " . $cd->getAmountRaised() . "<br /><br />";
}