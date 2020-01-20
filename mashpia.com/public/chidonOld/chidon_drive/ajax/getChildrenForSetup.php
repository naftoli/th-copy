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
        u.user_id, u.first, u.last, tc.rohr_subsidy as rohr, tc.fundraising_goal as goal, tc.show_pic as pic   
    FROM
        users u
            JOIN
        th_chidon tc using (user_id) 
    WHERE
        tc.year = :year AND tc.parent_id = :admin 
    ORDER BY u.first
  ");
  $res = $stmt->execute([
    ':year'   =>  $year, 
    ':admin'  =>  $admin_id
  ]);
  //echo $stmt->debugDumpParams(); exit;
  if ( $res ) {
    $children = $stmt->fetchAll();
    echo json_encode([
      'success'   =>  true,
      'info'      =>  [ 
        'children' => $children, 
        'admin'   => $admin_id
      ]
    ]);    
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