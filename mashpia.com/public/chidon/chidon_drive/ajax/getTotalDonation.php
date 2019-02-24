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
$res = $stmt->execute([':year' => $year]);
if ( $res ) {
  $row = $stmt->fetch();
  echo json_encode([
    'success'   =>  true, 
    'total'     =>  $row['total']
  ]);
} else {
  echo json_encode([
    'success'   =>  false, 
    'error'     =>  "There was an error getting the total amount donated to date."
  ]);
}