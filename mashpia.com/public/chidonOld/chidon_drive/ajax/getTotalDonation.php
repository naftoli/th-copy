<?php
require __DIR__ . '/../../../api/header/db.php';
require __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$stmt = $MASHPIA_DB->prepare("
    SELECT 
        SUM(donation_amount) AS total 
    FROM
        chidon_donations
    WHERE
        chidon_year = :year
");
$res = $stmt->execute([
  ':year' =>  $year
]);

if ( $res ) {
  $row = $stmt->fetch();
  $totalDonation = $row['total'];

  $stmt = $MASHPIA_DB->prepare("
    SELECT 
        SUM(paid) AS registration
    FROM
        th_chidon
    WHERE
        year = :year
  ");
  $stmt->execute([':year' => $year]);
  $row = $stmt->fetch();
  $totalReg = $row['registration'];

  echo json_encode([
    'success' =>  true, 
    'total'   =>  intval( $totalDonation ) + intval( $totalReg ) + 350000
  ]);
} else {
  echo json_encode([
    'success' =>  false, 
    'error'   =>  'There was an error getting the total donation amount.'
  ]);
}