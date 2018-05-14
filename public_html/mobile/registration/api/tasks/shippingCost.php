<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once( dirname(__FILE__) . "/../header.php" );
include_once( dirname(__FILE__) . "/../functions/getZone.php" );

if ( $_SERVER['REQUEST_METHOD'] != "POST" )
    render_json_error( "Invalid Request", "Invalid Request Type. Expecting POST" );

$school_ids = $_POST['school_ids'];
$schools_with_shipping = [
    '269' // Anash Kinder
    // add school ID's here to enable shipping charges for that school
];

// get the region of the admin
$zone_query = mysql_query(
    "SELECT admin_country AS country FROM admins WHERE admin_id = '$admin_id';"
);
$zone_info = mysql_fetch_assoc( $zone_query )['country'];
$zone = getZone( $zone_info );

$child_count = 0;
foreach( $school_ids as $school_id ){
    if ( in_array( $school_id, $schools_with_shipping ) )
        $child_count += 1;
}

$rate_query = mysql_query(
    "SELECT type, rate FROM shipping_rates WHERE zone='$zone' AND child_count='$child_count';"
);
$response = fetch_results_assoc( $rate_query );

render_json_response( $response );