<?php
//ini_set('display_errors',1);
require __DIR__ . '/../../../api/header/db.php';
require __DIR__ . '/../../../class.globalSettings.php';
require __DIR__ . '/../encrypt.php';

$year = GlobalSettings::getChidonYear();
$admin_id = mysql_real_escape_string( $_POST['admin'] );
$encrypted = isset( $_POST['encrypted'] ) ? intval( $_POST['encrypted'] ) : 0;
if ( $encrypted ) $admin_id = encrypt_decrypt('decrypt', $admin_id);
$notYetPaid = isset( $_POST['notYetPaid'] ) ? intval( $_POST['notYetPaid'] ) : 0;
$data = [];

$qry = "
  SELECT 
      a.first as A_first,
      a.last as A_last,
      a.father,
      a.mother,
      u.user_id,
      u.first,
      u.last,
      u.mobile_pic,
      u.user_photo_id,
      tc.paid,
      tc.rohr_subsidy, 
      cug.goal  
  FROM
      admins a
          JOIN
      admin_auths aa USING (admin_id)
          JOIN
      users u ON u.user_id = aa.id
          JOIN
      chidon_user_goals cug USING (user_id) 
          JOIN
      th_chidon tc USING (user_id) 
  WHERE
      aa.admin_id = :admin
          AND aa.role_id = 1  
          AND cug.year = :year 
          AND tc.year = :year
";
if ( $notYetPaid ) $qry .= " AND tc.date_paid is null ";
$qry .= "
  GROUP BY u.user_id
";
$stmt = $MASHPIA_DB->prepare( $qry );
$res = $stmt->execute([
  ':admin'  =>  $admin_id, 
  ':year'   =>  $year
]);
//echo "<pre>"; print_r( $stmt->debugDumpParams() ); echo "</pre>";
if ( $res ) {
  $rows = $stmt->fetchAll();
  if ( $rows ) {
    foreach ( $rows as $idx => $row ) {
      // find out how much was raised so far
      $stmt2 = $MASHPIA_DB->prepare("
        SELECT IFNULL(SUM(subsidy_amount), 0) as total_donation 
        FROM chidon_user_subsidies 
        WHERE chidon_year = :year AND user_id = :user");
      $res2 = $stmt2->execute([
        ':year' =>  $year, 
        ':user' =>  $row['user_id']
      ]);
      if ( $res2 ) {
        $row2 = $stmt2->fetch();
        $rows[$idx]['total_donation'] = $row2['total_donation'];
      }
    }
    $data['children'] = $rows;
  } else {
    echo json_encode([
      'success' =>  false,
      'message' =>  "Could not find any children eligible for the chidon that haven't been paid yet and have been activated by the school."
    ]);
    exit;
  }
} else {
  echo json_encode([
    'success' =>  false,
    'message' =>  'Error getting family info from database.'
  ]);
  exit;
}

// get all sponsors for this family
$stmt = $MASHPIA_DB->prepare("
    SELECT 
        *
    FROM
        chidon_donations
    WHERE
        for_family_id = :admin
        AND chidon_year = :year
    ORDER BY donation_date DESC
    LIMIT 5
");
$res = $stmt->execute([
  ':admin'  =>  $admin_id, 
  ':year'   =>  $year
]);
if ( $res ) {
  $rows = $stmt->fetchAll();
  $data['sponsors'] = $rows;
} else {
  echo json_encode([
    'success' =>  false,
    'message' =>  'Error getting sponsors info from database.'
  ]);
  exit;
}

// if we get here all is good
echo json_encode([
  'success' =>  true,
  'data'    =>  $data
]);