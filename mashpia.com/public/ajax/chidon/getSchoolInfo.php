<?php
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/AuthorizeAPIRequest.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/CustomerProfile.php';

use classes\authorize\AuthorizeAPIRequest;
use classes\authorize\CustomerProfile;

$school_id = mysql_real_escape_string( $_POST['school'] );
$sql = "select * from schools where school_id = " . $school_id;
$result = mysql_query( $sql );
$school = mysql_fetch_assoc( $result );

if ( $school['authorize_customer_profile_id'] ) {
  // find out what the cc info is (last 4 digits);
  $customer_profile = new CustomerProfile( $school['authorize_customer_profile_id'] );
  echo "<pre>"; print_r( $customer_profile ); echo "</pre>";
}

echo json_encode( $school );
?>