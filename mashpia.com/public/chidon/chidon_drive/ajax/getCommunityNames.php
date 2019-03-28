<?php
require __DIR__ . '/../classes/ChidonDrive.php';
require __DIR__ . '/../classes/Communities.php';
require __DIR__ . '/../../../class.globalSettings.php';

$year = GlobalSettings::getChidonYear();
$c = new Communities;
$communities = $c->getCommunities();

$names = [];
foreach ( $communities as $community => $schools ) {
  $names[] = $community;
}
echo json_encode( $names );