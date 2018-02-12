<?
require_once('db.php');
$boolDebug = @$_GET["debug"];

$intUserSerial = mysql_escape_string($_GET["s"]);
$intUserSerial = preg_replace("/^3/", "", $intUserSerial);

$strSql = "
	SELECT
		user_id, school_id 
	FROM
		users
	WHERE
		user_code = " . $intUserSerial;

$user_row = mysql_fetch_assoc(mq($strSql));
if ($boolDebug)
	var_dump($user_row);

//$cur_points = floatval(mysql_result(mq(totalMarks("WHERE user_id = {$user_row['user_id']} AND mark_date >= " . dateThisYear(13, 18))), 0));
$strDate = "";
if (!empty($_GET['start_date'])) {
	list ($intMonth, $intDay, $intYear) = explode(' ', date('n j Y', $_GET['start_date']));
	$strDate .= ' AND mark_date > ' . gregoriantojd($intMonth, $intDay, $intYear);
}
$cur_points = floatval(mysql_result(mq(totalMarks("WHERE user_id = {$user_row['user_id']}" . $strDate)), 0));
/*
function dateThisYear($month, $day, $starting = 0, $year_offset = 0) {
		if(!$starting) 
			$starting = unixtojd();
			
		$today = cal_from_jd($starting, CAL_JEWISH);
		$strDate = cal_to_jd(CAL_JEWISH, $month, $day, $today['year']+$year_offset-(cal_to_jd(CAL_JEWISH, $month, $day, $today['year']) >= $starting ? 1 : 0));
		return $strDate;
}
*/
print $cur_points;
exit;
/*
if (in_array($user_row["school_id"], array(55, 64, 66, 112, 110)))
{
	$intMonth = 13;
	$intDay = 18;
	$year_offset = -1;
	$starting = 0;
	$_GET['p'] = 'australia';
}
else
{

	$intMonth = 13;
	$intDay = 18;
	$year_offset = 0;
	$starting = 0;
// }

$p = $_GET['p'];
if ($_GET['d'] != "") $d = unixtojd($_GET['d']);
switch ($p) {
	case 'australia':
		print mysql_result(mq(totalMarks("WHERE user_id = {$user_row['user_id']}")), 0); exit;
	case 'army':
		$sql = "SELECT SUM(mark_points) mark_points 
			FROM date_tasks_marks JOIN ord ON (mark_inactive = 0 AND ord.num = 1) 
			WHERE user_id = {$user_row['user_id']} AND mark_date >= " . dateThisYear($intMonth, $intDay, $starting, $year_offset);
		break;
	case 'base':
		/*
		$school_id = $user_row['school_id'];
		$sql = "SELECT SUM(award_points) mark_points 
			FROM points WHERE user_id = " . $user_row['user_id'];
		if ($school_id == 91)
			$sql .= " AND award_date >= 2455686";
		else
			$sql .= " AND award_date >= 2455621";
		$cur_points = floatval(mysql_result(mq(totalMarks("WHERE user_id = {$user_row['user_id']} AND mark_date >= " . dateThisYear(13, 18))), 0));
		break;
	case 'both':
	case 'none':
		$sql = "SELECT IFNULL(SUM(mark_points), 0) mark_points 
			FROM ( 
			SELECT SUM(mark_points) mark_points 
			FROM date_tasks_marks 
			JOIN ord ON (mark_inactive = 0 AND ord.num = 1) WHERE user_id = {$user_row['user_id']} AND mark_date >= " . dateThisYear($intMonth, $intDay, $starting, $year_offset) . " 
			UNION ALL SELECT SUM(award_points) mark_points 
			FROM points WHERE user_id = " . $user_row['user_id'];
			$sql .= " AND award_date >= 2455621";
		$sql .= ") marks";
		//totalMarks("WHERE user_id = {$user_row['user_id']} AND mark_date >= " . dateThisYear(13, 18));
		break;			
	default:
		break;
}
if (isset($cur_points))
{
	print $cur_points;
}
else
{
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	print ceil($row['mark_points']);
}

//$sql = totalMarks("WHERE user_id = {$user_row['user_id']}");
//echo $sql;


 * 
 */
?>
