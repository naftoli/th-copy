<?
$admin_auth = array('school','user');
require_once 'header.php';

$params = array( 'end_date' => strtotime( "now" ), 
                 array( 'user_id'  => 
                    array( 8273, 13159 ) ) );
echo "<pre>";
print_r( header_v2_campaign_details( $params ) );
echo "</pre>";
?>