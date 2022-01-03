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
        u.user_id,
        u.first,
        u.last,
        u.first_he,
        u.last_he,
        u.mobile_pic,
        u.user_photo_id,
        u.gender,
        tc.*, 
        tcs.option
    FROM
        users u
            JOIN
        th_chidon tc USING (user_id)
            LEFT JOIN
        th_chidon_schools tcs ON (tcs.school_id = tc.school_id
            AND tcs.year = tc.year)
    WHERE
        tc.year = :year AND tc.parent_id = :admin
            #AND tc.can_enroll = 1
            #AND (tc.shabbaton_maven = 1
            #OR tc.shabbaton_pro = 1
            #OR tc.shabbaton_expert = 1
            #OR tc.shabbaton_trophy = 1)
    GROUP BY u.user_id
  ");
  $res = $stmt->execute([
    ':year'   =>  $year, 
    ':admin'  =>  $admin_id
  ]);
  //echo $stmt->debugDumpParams(); exit;
  if ( $res ) {
    $children = $stmt->fetchAll();
    // make each child's school has already registered and has enough chaps / walking supervisors
//    $stmt = $MASHPIA_DB->prepare("
//      SELECT * FROM th_chidon_schools WHERE year = :year AND school_id = :school
//    ");
//    $stmt_chap = $MASHPIA_DB->prepare("
//      SELECT * FROM th_chidon_chaps WHERE year = :year AND school_id = :school AND chap_type = 1
//    ");
//    $stmt_walking = $MASHPIA_DB->prepare("
//      SELECT * FROM th_chidon_chaps WHERE year = :year AND school_id = :school AND is_walking = 1
//    ");
//    $walking_needed = $MASHPIA_DB->prepare("
//      SELECT * FROM th_chidon_chaps_needed WHERE year = :year AND school_id = :school
//    ");
//    $schoolNotReady = 0;
//    $numChildren = count( $children );
//    foreach ( $children as $idx => $child ) {
//      $school_id = $child['school_id'];
//      $stmt->execute([
//        ':year' => $year,
//        ':school' => $school_id
//      ]);
//      $rows = $stmt->fetchAll();
//      if ( count( $rows ) > 0 ) {
//        // check chaps
//        $stmt_chap->execute([
//          ':year' => $year,
//          ':school' => $school_id
//        ]);
//        $rows_chap = $stmt_chap->fetchAll();
//        if ( count( $rows_chap ) == 0 )  {
//          $schoolNotReady++;
//          unset( $children[$idx] );
//        } else {
//          $stmt_walking->execute([
//            ':year' => $year,
//            ':school' => $school_id
//          ]);
//          $rows_walking = $stmt_walking->fetchAll();
//          $walking_needed->execute([
//            ':year' => $year,
//            ':school' => $school_id
//          ]);
//          $walking = $walking_needed->fetch();
//          $needed = $walking['needed'];
//          if ( $needed > count( $rows_walking ) ) {
//            $schoolNotReady++;
//            unset( $children[$idx] );
//          }
//        }
//      } else {
//        $schoolNotReady++;
////        unset( $children[$idx] );
//      }
//    }
    if ( $children ) {
//      if ( $schoolNotReady > 0 ) {
//        echo json_encode([
//          'success'   =>  true,
//          'children'  =>  $children,
//          'error'     =>  "Not all of your children's schools have their chaperones and walking supervisors setup yet. Therefore, not all eligible children will show up on enrollment form."
//        ]);
//      } else {
        echo json_encode([
          'success'   =>  true,
          'children'  =>  $children
        ]);
//      }
    } else {
//      if ( $schoolNotReady > 0 ) {
//        echo json_encode([
//          'success'   =>  false,
//          'error'     =>  "Your child(ren)'s school(s) do not have their chaperones and walking supervisors setup yet. You can only enroll once they set it up."
//        ]);
//      } else {
        echo json_encode([
          'success'   =>  false,
          'error'     =>  "You don't have any children that are elligible for Shabbaton Enrollment."
        ]);
//      }
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