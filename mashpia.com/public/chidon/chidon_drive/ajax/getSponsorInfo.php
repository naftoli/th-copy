<?php
require $_SERVER['DOCUMENT_ROOT'] . '/../../api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$data = [];
$success = true;
$stmt = $MASHPIA_DB->prepare("
  SELECT 
      sum(donation_amount) as total
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
  $data['totalAmount'] = $row['total'];
} else {
  $success = false;
}

$stmt = $MASHPIA_DB->prepare("
  SELECT 
      *
  FROM
      chidon_donations
  WHERE
      chidon_year = :year
  ORDER BY donation_date DESC
  LIMIT 5
");
$res = $stmt->execute([
  ':year' =>  $year
]);
if ( $res ) {
  $rows = $stmt->fetchAll();
  $data['sponsors'] = $rows;
} else {
  $success = false;
}

if ( $success ) {
  echo json_encode([
    'success'   =>  true,
    'info'      =>  $data
  ]);
} else {
  echo json_encode([
    'success'   =>  false,
    'message'   =>  "There was an error retrieving the most recent sponsors."
  ]);
}