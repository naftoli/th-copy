<?php
require_once '../../db.php';

$info = [];
$sql = "select * from th_chidon where year = 5780";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $info[] = $row;
}

$transactions = [];
$sql = "select * from transactions where amount = 8 and trans_date > '2019-06-01' order by trans_id desc";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $transactions[$row['users_registered']] = $row['trans_id'];
}

foreach ( $info as $row ) {
  $user_id = $row['user_id'];
  $school_id = $row['school_id'];
  $date = $row['reg_date'];
  $trans_id = $transactions[$user_id];
  $sql = "insert into registration_charges 
          set user_id = " . $user_id . ", 
          school_id = " . $school_id . ", 
          trans_id = " . $trans_id . ", 
          date = '" . $date . "', 
          year = 5780, 
          type = 'chidon', 
          amount = 8.00";
  mysql_query( $sql );
}
echo "done.";