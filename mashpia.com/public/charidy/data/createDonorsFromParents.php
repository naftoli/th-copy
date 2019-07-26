<?php
require_once '../../db.php';

$parents = [];
$sql = "select a.* from admins a 
        join admin_auths aa using (admin_id) 
        left join mashpia_charidy.donors d on d.parent_admin_id = a.admin_id 
        where d.parent_admin_id is null 
        group by a.admin_id";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $parents[] = $row;
}

$qrys = [];
foreach ( $parents as $row ) {
  // create donor
  $phone = empty( $row['admin_phone_mobile'] ) ? empty( $row['admin_phone_mobile2'] ) ? empty( $row['admin_phone_home'] ) ? empty( $row['admin_phone_work'] ) ? '' : 
    $row['admin_phone_work'] : $row['admin_phone_home'] : $row['admin_phone_mobile2'] : $row['admin_phone_mobile'];
  if ( 
    filter_var($row['admin_email'], FILTER_VALIDATE_EMAIL) || !empty( $phone ) && $row['admin_id'] > 172438 
   ) { 
    $sql = "insert into mashpia_charidy.donors 
            set parent_admin_id = " . $row['admin_id'] . ", 
            first_name = \"" . $row['first'] . "\", 
            last_name = \"" . addslashes( $row['last'] ) . "\", 
            address = \"" . $row['admin_address1'] . "\", 
            city = '" . $row['admin_city'] . "', 
            state = '" . $row['admin_state'] . "', 
            zip = '" . $row['admin_postal'] . "', 
            country = '" . $row['country'] . "', 
            email = '" . $row['admin_email'] . "', 
            phone = '" . $phone . "'";
    $qrys[] = $sql;
  }
}

mysql_query('set autocommit=0');
mysql_query('begin');
$success = true;
foreach ( $qrys as $qry ) {
  echo $qry . "<br />";
  if ( !mysql_query( $qry ) ) {
    $success = false;
    echo mysql_error() . "<br />" . $qry . "<br />";
    break;
  }
}
if ( $success ) {
  mysql_query('commit');
  echo "done.";
} else {
  mysql_query('rollback');
}
mysql_query('set autocommit=1');