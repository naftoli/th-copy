<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once 'classes/ChidonDrive.php';

$year = GlobalSettings::getChidonYear();
$year = 5778;
$c = new ChidonDriveFamily( $year, 175618 );
$c->setAmounts( 350, 250, 100 );
$c->setGoal();
echo "Goal of Family 175618: " . $c->getGoal() . "<br />";
echo "Number of children: " . $c->getNumChildren() . "<br />";
echo "Amount Raised: " . $c->getAmountRaised() . "<br /><br />";

$c = new ChidonDriveSchool( $year, 61 );
$c->setAmounts( 350, 250, 100 );
$c->setGoal();
echo "Goal of school 61: " . $c->getGoal() . "<br />";
echo "Number of children: " . $c->getNumChildren() . "<br />";
echo "Amount Raised: " . $c->getAmountRaised() . "<br /><br />";

$c = new ChidonDriveCommunity( $year );
$c->setAmounts( 350, 250, 100 );
$schools = [ 61, 255 ];
$community = 'ny';
$c->setCommunity( $community, $schools );
$c->setGoal();
echo "Goal of " . $community . " community: " . $c->getGoal() . "<br />";
echo "Number of children: " . $c->getNumChildren() . "<br />";
echo "Amount Raised: " . $c->getAmountRaised() . "<br /><br />";