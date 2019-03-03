<?php
require 'db.php';
$info = [];
$sql = "select user_id from users where school_id = 110";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $info[] = $row['user_id'];
}

$qrys = [];
foreach ( $info as $user_id ) {
  $qry = "insert into user_registration 
          set admin_id = 408, 
          year = 5779, 
          reg_date = now(), 
          paid = 45.00, 
          school_id = 110, 
          user_id = " . $user_id;
  $qrys[] = $qry;
}

mysql_query("set autocommit=0");
mysql_query("begin");
$success = true;
foreach ( $qrys as $qry ) {
  if ( !mysql_query( $qry ) ) {
    $success = false;
    break;
  }
}
if ( $success ) {
  mysql_query("commit");
  echo "done";
} else {
  echo mysql_error() . "<br />" . $qry . "<br />";
  mysql_query("rollback");
}
mysql_query("set autocommit=1");