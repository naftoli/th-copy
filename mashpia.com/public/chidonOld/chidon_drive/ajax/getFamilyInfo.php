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
      u.gender, 
      tc.paid,
      tc.rohr_subsidy, 
      tc.fundraising_goal as goal, 
      tc.fundraising_minutes as minutes, 
      tc.show_pic   
    FROM
      admins a
          JOIN
      th_chidon tc ON tc.parent_id = a.admin_id 
          JOIN
      users u USING (user_id) 
    WHERE
        tc.parent_id = :admin AND tc.year = :year 
            AND tc.fundraising_goal > 0 
";
if ( $notYetPaid ) $qry .= " AND tc.date_paid is null ";
$qry .= "ORDER BY u.first";

$stmt = $MASHPIA_DB->prepare( $qry );
$res = $stmt->execute([
    ':admin'  =>  $admin_id,
    ':year'   =>  $year
]);

//echo "<pre>"; print_r( $stmt->debugDumpParams() ); echo "</pre>";
if ( $res ) {
    $rows = $stmt->fetchAll();
    if (!empty($rows)) $data['children'] = $rows;
    else {
        echo json_encode([
          'success' =>  false,
          'message' =>  "Could not find any children that have a fundraising goal setup."
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