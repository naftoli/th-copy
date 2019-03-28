<?php
require __DIR__ . '/../../../api/header/db.php';
require __DIR__ . '/../encrypt.php';

$username = trim( $_POST['username'] );
$password = trim( $_POST['password'] );

$stmt = $MASHPIA_DB->prepare("
  SELECT 
      admin_id
  FROM
      admins
  WHERE
      username = :username
          AND password = :password
");
$res = $stmt->execute([
  ':username'   =>  $username, 
  ':password'   =>  $password
]);
if ( $res ) {
  $admin = $stmt->fetch();
  if ( $admin['admin_id'] ) {
    $admin_id = encrypt_decrypt('encrypt', $admin['admin_id']);
    echo json_encode([
      'success'   =>  true, 
      'admin'     =>  $admin_id
    ]);
    exit;
  }
}
// if we get here there were errors
echo json_encode([
  'success'   =>  false,
  'error'     =>  'Invalid username or password.'
]);