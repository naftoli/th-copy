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
      between_streets1 = :acc_cross1,
      between_streets2 = :acc_cross2,
      host_number = :acc_phone,
      allergies = :allergies,
      sandwich = :sandwich,
      walking = :walking,
      shoe_size = :shoe,
      test_lang = :test_lang, 
      notes = :notes, 
      answers = :answer, 
      host_street = :street, 
      host_street_num = :street_num, 
      host_street_num_suffix = :street_num_suffix, 
      host_street_apt = :street_apt, 
      walking_zone = :zone
  WHERE
      year = :year AND user_id = :user_id
");
$res = $stmt->execute([
  ':history'      =>  $childInfo['history'], 
  ':sweater'      =>  $childInfo['sweater'], 
  ':grade'        =>  $childInfo['grade'], 
  ':book'         =>  $childInfo['book'], 
  ':acc_family'   =>  $childInfo['acc_family'], 
  ':acc_cross1'   =>  $childInfo['acc_cross1'], 
  ':acc_cross2'   =>  $childInfo['acc_cross2'], 
  ':acc_phone'    =>  $childInfo['acc_phone'], 
  ':allergies'    =>  $childInfo['allergies'], 
  ':sandwich'     =>  $childInfo['sandwich'], 
  ':walking'      =>  $childInfo['walking'], 
  ':shoe'         =>  $childInfo['shoe'],
  ':test_lang'    =>  $childInfo['test_lang'],
  ':year'         =>  $year, 
  ':user_id'      =>  $childInfo['user_id'],
  ':notes'        =>  $childInfo['notes'], 
  ':answer'       =>  $childInfo['answer'], 
  ':street'       =>  $childInfo['acc_street'], 
  ':street_num'   =>  $childInfo['acc_street_num'], 
  ':street_num_suffix'  =>  $childInfo['acc_street_num_suffix'], 
  ':street_apt'   =>  $childInfo['acc_street_apt'], 
  ':zone'         =>  $childInfo['walking_zone']
]);
//echo "<pre>"; echo $stmt->debugDumpParams(); echo "</pre>"; exit;
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
