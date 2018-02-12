<?
require '../../../db.php';
$user_id = mysql_real_escape_string( $_POST['user_id'] );
$subject = mysql_real_escape_string( $_POST['subject'] );

$sql = "select max(medal_ord) as total from medal_marks where user_id = " . $user_id . " and subject_id = " . $subject;
$result = mysql_query( $sql );
$row = mysql_fetch_assoc( $result );
echo $row['total'];
?>