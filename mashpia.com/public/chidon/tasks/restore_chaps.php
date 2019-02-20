<?php
require '../../db.php';

$chaps = [];
$sql = "select * from mashpia_th_chidon_chapsrestore.th_chidon_chaps";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $chaps[] = $row;
}
//echo "<pre>"; print_r( $chaps ); echo "</pre>";

$qrys = [];
foreach ( $chaps as $chap ) {
  $qry = "insert into th_chidon_chaps 
          set school_id = " . $chap['school_id'] . ", 
          name = \"" . $chap['name']. "\", 
          phone = '" . $chap['phone'] . "', 
          email = '" . $chap['email'] . "', 
          full_program = " . $chap['full_program'] . ", 
          sweater = " . $chap['sweater'] . ", 
          sweater_size = '" . $chap['sweater_size'] . "', 
          ticket = " . $chap['ticket'] . ", 
          year = " . $chap['year'] . ", 
          show_id_cards = " . $chap['show_id_cards'] . ", 
          first_name = '" . $chap['first_name'] . "', 
          last_name = '" . $chap['last_name'] . "', 
          dob = '" . $chap['dob'] . "', 
          acc_name = \"" . $chap['acc_name'] . "\", 
          acc_phone = '" . $chap['acc_phone'] . "', 
          vehicle = " . $chap['vehicle'] . ", 
          acc_cross_st = '" . $chap['acc_cross_st'] . "', 
          chidon_type = '" . $chap['chidon_type'] . "', 
          walking_zone = " . ($chap['walking_zone'] ? $chap['walking_zone'] : 0) . ", 
          chap_type = " . $chap['chap_type'];
  $qrys[] = $qry;
}

mysql_query('set autocommit=0');
mysql_query('begin');
$success = true;
foreach ( $qrys as $qry ) {
  if ( !mysql_query( $qry ) ) {
    echo $qry . "<br />" . mysql_error();
    $success = false;
    break;
  }
}
if ( !$success ) {
  mysql_query('rollback');
} else {
  mysql_query('commit');
}
mysql_query('set autocommit=1');