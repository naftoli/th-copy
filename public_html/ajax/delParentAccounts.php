<?
$deleted = false;
require '../db.php';
$sql = "delete a.* from admins a 
		left join admin_auths aa using (admin_id) 
		where aa.id is null";
if ( mysql_query( $sql ) ) {
	$deleted = true;
}
header("Location: ../admin.php?deletedParents=$deleted");
?>