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
        u.user_id, u.first, u.last, u.first_he, u.last_he, u.mobile_pic, u.user_photo_id, u.gender, tc.* 
    FROM
        users u
            JOIN
        th_chidon tc USING (user_id)
    WHERE
        tc.year = :year AND tc.parent_id = :admin
            AND (tc.contestant = 1 or tc.school_rep = 1) 
            AND tc.can_enroll = 1 
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
        'error'     =>  "Could not find any children eligible for the chidon that haven't been paid yet and have been activated by the school."
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