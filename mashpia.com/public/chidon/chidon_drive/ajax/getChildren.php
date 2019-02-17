<?php
require __DIR__ . '/../../../api/header/db.php';
require __DIR__ . '/../../../class.globalSettings.php';
require __DIR__ . '/../encrypt.php';

$year = GlobalSettings::getChidonYear();
$admin = mysql_real_escape_string( $_POST['admin'] );
$admin_id = encrypt_decrypt('decrypt', $admin);

if ( $admin_id ) {
  $stmt = $MASHPIA_DB->prepare("
    SELECT 
        u.user_id, u.first, u.last, u.mobile_pic, u.user_photo_id 
    FROM
        users u
            JOIN
        th_chidon tc USING (user_id)
    WHERE
        tc.year = :year AND tc.parent_id = :admin
  ");
  $res = $stmt->execute([
    ':year'   =>  $year, 
    ':admin'  =>  $admin_id
  ]);
  if ( $res ) {
    $children = $stmt->fetchAll();
    if ( $children ) {
      echo json_encode([
        'success'   =>  true,
        'children'  =>  $children
      ]);
    } else {
      echo json_encode([
        'success'   =>  false,
        'error'     =>  "No children found."
      ]);
    }
  } else {
    echo json_encode([
      'success'   =>  false,
      'error'     =>  'There was an error retreiving your children information.'
    ]);
  }
} else {
  echo json_encode([
    'success'   =>  false,
    'error'     =>  'Invalid Admin ID.'
  ]);
}