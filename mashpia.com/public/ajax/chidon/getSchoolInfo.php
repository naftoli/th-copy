<?php
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

$school_id = mysql_real_escape_string( $_POST['school'] );
$sql = "select * from schools where school_id = " . $school_id;
$result = mysql_query( $sql );
$school = mysql_fetch_assoc( $result );

if ( $school['authorize_customer_profile_id'] && $school['authorize_payment_profile_id'] ) {
  // find out what the cc info is (last 4 digits);

}

echo json_encode( $school );
?>