<?php
require __DIR__ . '/../../../api/header/db.php';
require __DIR__ . '/../../../class.globalSettings.php';
require __DIR__ . '/../encrypt.php';

$admin = $_COOKIE['chidon_admin'];
$admin_id = encrypt_decrypt('decrypt', $admin);
$year = GlobalSettings::getChidonYear();

$ids = $_POST['ids'];

$stmt = $MASHPIA_DB->prepare("
  UPDATE th_chidon 
  SET 
      paid = 0,
      date_paid = NOW(),
      paid_by = :admin, 
      approval = :message
  WHERE
      year = :year AND user_id = :user_id
");

$success = true;
$MASHPIA_DB->beginTransaction();
foreach ( $ids as $id ) {
  $res = $stmt->execute([
    ':admin'    =>  $admin_id, 
    ':year'     =>  $year, 
    ':user_id'  =>  $id, 
    ':message'  =>  "Child doesn't need to pay, he/she raised enough money to cover the shabbaton fee."
  ]);
  if ( !$res ) {
    $success = false;
    break;
  }
}

if ( $success ) {
  $MASHPIA_DB->commit();
  echo json_encode([
    'success'   =>  true,
    'message'   =>  "You have successfully registered your child(ren) to the Shabbaton."
  ]);
} else {
  $MASHPIA_DB->rollBack();
  echo json_encode([
    'success' =>  false,
    'error'   =>  "There was an error registering your child(ren)."
  ]);
}