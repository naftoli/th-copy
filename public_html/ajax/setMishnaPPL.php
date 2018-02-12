<?
require_once '../db.php';
$school = mysql_real_escape_string($_POST['school']);
$grade = (isset($_POST['grade']) ? mysql_real_escape_string($_POST['grade']) : 0);
$user = (isset($_POST['user']) ? mysql_real_escape_string($_POST['user']) : 0);
$points = mysql_real_escape_string($_POST['points']);
$type = mysql_real_escape_string($_POST['type']);

require_once '../class.mishnaPoints.php';
$result = MishnaPoints::setPoints( $school, $grade, $user, $points, $type );
echo $result;
?>