<?
require_once('db.php');
$boolDebug = @$_GET["debug"];

$intStartDate = $intEndDate = false;
if (!empty($_POST['start_date']))
	$intStartDate = unixtojd($_POST['start_date']);
if (!empty($_POST['end_date']))
	$intEndDate = unixtojd($_POST['end_date']);
$arrUserIds = unserialize($_POST['serialized_user_ids']);
$arrResults = array();
foreach ($arrUserIds as $intUser) {
	$strSql = "WHERE user_id = {$intUser}";
	if ($intStartDate)
		$strSql .= " AND mark_date >= " . $intStartDate;
	if ($intEndDate)
		$strSql .= " AND mark_date <= " . $intEndDate;
	//print $strSql;exit;
	$arrResults[$intUser] = floatval(mysql_result(mq(totalMarks($strSql)), 0));
}
print serialize($arrResults);  
exit;
/*
function dateThisYear($month, $day, $starting = 0, $year_offset = 0) {
		if(!$starting) 
			$starting = unixtojd();
			
		$today = cal_from_jd($starting, CAL_JEWISH);
		$strDate = cal_to_jd(CAL_JEWISH, $month, $day, $today['year']+$year_offset-(cal_to_jd(CAL_JEWISH, $month, $day, $today['year']) >= $starting ? 1 : 0));
		return $strDate;
}
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
