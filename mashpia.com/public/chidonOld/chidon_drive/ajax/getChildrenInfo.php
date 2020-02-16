<?php
//ini_set('display_errors',1);
require __DIR__ . '/../../../api/header/db.php';
require __DIR__ . '/../../../class.globalSettings.php';
require __DIR__ . '/../encrypt.php';

$year = GlobalSettings::getChidonYear();
$admin_id = mysql_real_escape_string( $_POST['admin'] );
$admin_id = encrypt_decrypt('decrypt', $admin_id);
$data = [];

$qry = "
  SELECT 
      a.first as A_first,
      a.last as A_last,
      a.father,
      a.mother,
      u.user_id,
      u.school_id,
      u.first,
      u.last,
      u.mobile_pic,
      u.user_photo_id,
      u.gender, 
      tc.paid,
      tc.rohr_subsidy, 
      tc.fundraising_goal as goal, 
      tc.show_pic   
  FROM
      admins a
          JOIN
      th_chidon tc ON tc.parent_id = a.admin_id 
          JOIN
      users u USING (user_id) 
  WHERE
      tc.parent_id = :admin AND tc.year = :year 
        AND tc.can_enroll = 1 
        AND (tc.khk = 1 or tc.school_rep = 1 or tc.trophy_contestant = 1 or tc.contestant = 1)
    ORDER BY u.first
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
    // make sure school has chap and enough walking supervisors
    $stmt_chap = $MASHPIA_DB->prepare("
        SELECT * FROM th_chidon_chaps WHERE year = :year AND school_id = :school AND chap_type = 1
    ");
    $stmt_walking = $MASHPIA_DB->prepare("
        SELECT * FROM th_chidon_chaps WHERE year = :year AND school_id = :school AND is_walking = 1
    ");
    $walking_needed = $MASHPIA_DB->prepare("
        SELECT * FROM th_chidon_chaps_needed WHERE year = :year AND school_id = :school 
    ");
    
    foreach ( $rows as $idx => $row ) {
        // check chaps 
        $school_id = $row['school_id'];
        $stmt_chap->execute([
            ':year' => $year, 
            ':school' => $school_id
        ]);
        $rows_chap = $stmt_chap->fetchAll(); 
        if ( count( $rows_chap ) == 0 ) {
            unset( $rows[$idx] );
            continue;
        } else {
            $stmt_walking->execute([
                ':year' => $year, 
                ':school' => $school_id
            ]);
            $rows_walking = $stmt_walking->fetchAll();
            $walking_needed->execute([
                ':year' => $year, 
                ':school' => $school_id
            ]);
            $walking = $walking_needed->fetch();
            $needed = $walking['needed'];
            if ( $needed > count( $rows_walking ) ) {
                unset( $rows[$idx] );
                continue;
            }
        }

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
    if ( !empty( $rows ) ) $data['children'] = $rows;
    else {
        echo json_encode([
            'success' =>  false,
            'message' =>  "Could not find any children that have a school with enough staff setup. Please contact your child(ren)'s school."
        ]);
        exit;
    }
  } else {
    echo json_encode([
      'success' =>  false,
      'message' =>  "Could not find any children that are eligible."
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