<?php
ini_set('display_errors',1);
require __DIR__ . '/../../../api/header/db.php';
require __DIR__ . '/../../../class.globalSettings.php';
require __DIR__ . '/../encrypt.php';

$year = GlobalSettings::getChidonYear();
$admin = mysql_real_escape_string( $_POST['admin'] );
$admin_id = encrypt_decrypt('decrypt', $admin);

if ( $admin_id ) {
  $stmt = $MASHPIA_DB->prepare("
     SELECT 
        u.user_id,
        u.first,
        u.last,
        tc.fundraising_goal AS goal,
        tc.show_pic AS pic, 
        tc.fundraising_type,
        tc.fundraising_goal,
        tc.fundraising_minutes
    FROM
        users u
            JOIN
        th_chidon tc USING (user_id)
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
    foreach ( $children as $idx => $child ) {
      $stmt2 = $MASHPIA_DB->prepare("
      SELECT 
          IFNULL(SUM(subsidy_amount), 0) AS raised
      FROM
          chidon_user_subsidies
      WHERE
          user_id = :user AND chidon_year = :year
      ");
      $stmt2->execute([
        ':user' => $child['user_id'], 
        ':year' => $year
      ]);
      $row = $stmt2->fetch();
      $children[$idx]['raised'] = $row['raised'];
      $children[$idx]['track'] = [
          'id'      => $child['fundraising_type'],
          'hours'   => $child['fundraising_minutes'],
          'goal'    => $child['fundraising_goal']
      ];
    }

    echo json_encode([
      'success'   =>  true,
      'info'      =>  [ 
        'children'=>  $children, 
        'admin'   =>  $admin_id 
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