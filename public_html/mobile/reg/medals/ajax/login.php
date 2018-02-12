<?
require '../../../../db.php';
$user = mysql_real_escape_string( $_POST['user_id'] );
$points = mysql_real_escape_string( $_POST['points'] );

$oldPoints = 0;
$sql = "select cur_points from mobile_logins where user_id = " . $user . " order by date desc limit 1";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$oldPoints = $row['cur_points'];

$update = "insert into mobile_logins set user_id = " . $user . ", cur_points = " . $points;
mysql_query($update);

$diff = 0;
if ($points > 0) {
	$diff = $points - $oldPoints;
}
echo $diff;
?>