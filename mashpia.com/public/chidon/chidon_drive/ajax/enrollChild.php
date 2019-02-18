<?php
require __DIR__ . '/../../../api/header/db.php';
require __DIR__ . '/../../../class.globalSettings.php';

$year = GlobalSettings::getChidonYear();
$childInfo = $_POST['child'];

$stmt = $MASHPIA_DB->prepare("
  UPDATE th_chidon 
  SET 
      history = :history,
      size = :sweater,
      grade = :grade,
      book = :book,
      host = :acc_family,
      host_address1 = :acc_address1,
      host_address2 = :acc_address2,
      between_streets1 = :acc_cross1,
      between_streets2 = :acc_cross2,
      host_number = :acc_phone,
      allergies = :allergies,
      sandwich = :sandwich,
      walking = :walking,
      shoe_size = :shoe,
      test_lang = :test_lang
  WHERE
      year = :year AND user_id = :user_id
");
$res = $stmt->execute([
  ':history'      =>  $childInfo['history'], 
  ':sweater'      =>  $childInfo['sweater'], 
  ':grade'        =>  $childInfo['grade'], 
  ':book'         =>  $childInfo['book'], 
  ':acc_family'   =>  $childInfo['acc_family'], 
  ':acc_address1' =>  $childInfo['acc_address1'],
  ':acc_address2' =>  $childInfo['acc_address2'],
  ':acc_cross1'   =>  $childInfo['acc_cross1'], 
  ':acc_cross2'   =>  $childInfo['acc_cross2'], 
  ':acc_phone'    =>  $childInfo['acc_phone'], 
  ':allergies'    =>  $childInfo['allergies'], 
  ':sandwich'     =>  $childInfo['sandwich'], 
  ':walking'      =>  $childInfo['walking'], 
  ':shoe'         =>  $childInfo['shoe'],
  ':test_lang'    =>  $childInfo['test_lang'],
  ':year'         =>  $year, 
  ':user_id'      =>  $childInfo['user_id']
]);
if ( $res ) {
  echo json_encode([
    'success'   =>  true,
    'message'   =>  'Saved.'
  ]);
} else {
  echo json_encode([
    'success'   =>  false,
    'error'     =>  'Error saving info.'
  ]);
}
