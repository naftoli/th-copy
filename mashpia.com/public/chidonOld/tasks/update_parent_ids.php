<?php
ini_set('display_errors',1);
require __DIR__ . '/../../db.php';

// $info = [];
// $sql = "select user_id from th_chidon where parent_id = 174847";
// $result = mysql_query( $sql );
// while ( $row = mysql_fetch_assoc( $result ) ) {
//   $info[] = $row['user_id'];
// }

// $updates = [];
// foreach ( $info as $user_id ) {
//   $sql = "select admin_id from admin_auths where role_id = 1 and id = " . $user_id;
//   $result = mysql_query( $sql );
//   $row = mysql_fetch_assoc( $result );
//   if ( $row['admin_id'] && $row['admin_id'] != 174847 ) $updates[$user_id] = $row['admin_id'];
// }

// //echo "<pre>"; print_r( $updates ); echo "</pre>";
// $updated = 0;
// foreach ( $updates as $admin => $user ) {
//   $sql = "update th_chidon set parent_id = " . $admin . " where year = 5779 and user_id = " . $user;
//   if ( mysql_query( $sql ) ) $updated++;
// }
// echo "Updated: " . $updated;

// $info = [];
// $sql = "select * from admin_auths where admin_id = 174847";
// $result = mysql_query( $sql );
// while ( $row = mysql_fetch_assoc( $result ) ) {
//   $info[] = $row['id'];
// }

// $updates = [];
// foreach ( $info as $user ) {
//   $sql = "select parent_id from th_chidon where year = 5779 and user_id = " . $user;
//   $result = mysql_query( $sql );
//   $row = mysql_fetch_assoc( $result );
//   $updates[$user] = $row['parent_id'];
// }
// echo "<pre>"; print_r( $updates ); echo "</pre>";