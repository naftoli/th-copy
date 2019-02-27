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

$stmt2 = $MASHPIA_DB->prepare("
    SELECT 
        SUM(paid) AS totalReg, SUM(rohr_subsidy) AS totalRohr
    FROM
        th_chidon
    WHERE
        year = :year
");
$res2 = $stmt2->execute([
  ':year' =>  $year
]);

if ( $res && $res2 ) {
  $row = $stmt->fetch();
  $row2 = $stmt2->fetch();
  echo json_encode([
    'success' =>  true, 
    'total'   =>  $row['total'] + $row2['totalReg'] + ($row2['totalRohr'] * 100)
  ]);
} else {
  echo json_encode([
    'success' =>  false, 
    'error'   =>  'There was an error getting the total donation amount.'
  ]);
}