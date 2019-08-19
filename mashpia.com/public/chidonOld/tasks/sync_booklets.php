<?php
require '../../db.php';

$info = [];
$sql = "select * from th_chidon where year = 5780 and reg_date < '2019-07-29 17:18:05' group by user_id";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $info[] = $row;
}

$qrys = [];
foreach ( $info as $row ) {
  $user_id = $row['user_id'];
  $admin_id = $row['parent_id'];
  $date = $row['reg_date'];
  $school_id = $row['school_id'];

  $amount = 8; // default amount
  if ( in_array( $school_id, [61, 269] ) && $date > '2019-06-20' ) {
    $amount = 12;
  }

  // see if we can find trans id
  $trans_id = 0;
  $sql = "select trans_id from transactions where trans_date >= '2019-06-01' and admin_id = " . $admin_id;
  //echo $sql . "<br />";
  $result = mysql_query( $sql );
  if ( mysql_num_rows( $result ) > 0 ) {
    $row = mysql_fetch_assoc( $result );
    $trans_id = $row['trans_id'];
  }

  $qrys[] = "insert into registration_charges 
            set user_id = " . $user_id . ", 
            school_id = " . $school_id . ", 
            type = 'chidon', 
            amount = " . $amount . ", 
            date = '" . $date . "', 
            trans_id = " . $trans_id . ", 
            year = 5780";
}

mysql_query('set autocommit=0');
mysql_query('begin');

$success = true;
foreach ( $qrys as $idx => $qry ) {
  if ( !mysql_query( $qry ) ) {
    $success = false;
    break;
  }
}

if ( $success ) {
  mysql_query('commit');
  echo "Done.";
} else {
  mysql_query('rollback');
  echo "Error.";
}
mysql_query('set autocommit=1');

