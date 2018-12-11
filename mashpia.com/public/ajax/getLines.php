<?
require_once '../db.php';

$campaign = $_POST['id'];
$type = $_POST['type'];
$school_id = isset( $_POST['school'] ) ? $_POST['school'] : 0;
$class_id = isset( $_POST['grade'] ) ? $_POST['grade'] : 0;
$user_id = isset( $_POST['user'] ) ? $_POST['user'] : 0;

require_once '../class.balPehCampaign.php';
$bp = BalPehCampaign::getInstance( $campaign );

$function = 'getTotal' . ucfirst( $type ); //pledged or learned
if ($user_id > 0) {
	$total = $bp->$function( 'user', $user_id );
} else if ($class_id > 0) {
	$total = $bp->$function( 'class', $class_id );
} else if ($school_id > 0) {
	$total = $bp->$function( 'school', $school_id );
}
echo $total;
?>