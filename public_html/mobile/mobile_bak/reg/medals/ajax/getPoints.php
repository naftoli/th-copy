<?
chdir('../../../../');
require 'db.php';

$user_id = mysql_real_escape_string( $_POST['user_id'] );
require 'class.points.php';
$p = new Points( $user_id );
$points = $p->getTotalPoints();

echo $points;
/*
$points = floor(mysql_result(mq(totalMarks("WHERE user_id = $user_id")), 0));
$sql = "select user_code from users where user_id = " . $user_id;
$result = mysql_query($sql);
$row = mysql_fetch_row($result);		
$arrParams['user_code'] = $row[0];
$arrPoints = header_total_points($arrParams);
$points += $arrPoints[$arrParams['user_code']];
  
//echo $points; exit;

//$sql = "select user_code from users where user_id = " . $user_id;
//$result = mysql_query($sql);
//$row = mysql_fetch_assoc($result);
//$points += @reset(header_store_points(array("user_code" => $row['user_code'])));
*/
?>