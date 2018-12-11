<?php
require_once( __DIR__ . '/../vendor/autoload.php' );
date_default_timezone_set ( "UTC" ); // set the default timezone to avoid error warnings...

require dirname(__FILE__) . '/../includes/globals.php'; // import the global files...

$link = mysql_connect($global_db_host.":3306", $global_db_user, $global_db_pass) or trigger_error_server('Failed to connect to mysql', E_USER_ERROR);
mysql_query('SET NAMES utf8');
mysql_query('SET CHARACTER_SET utf8');
mysql_select_db('mashpiadb') or trigger_error_server('Failed to select db', E_USER_ERROR);
//mysql_query('set max_allowed_packet = ' . units2bytes('1G'));
//doesn't seem to work
define('FILE_DIR','files/');
// authorize.net is in demo mode in development
if ( $development && !defined('AUTHORIZE_NET_SANDBOX') ) {
	define('AUTHORIZE_NET_SANDBOX', true);
}

// Check if the users IP is in the hash defined above
function is_dev() {
	return 0;
}

define("dev", is_dev());
define("devel", TRUE); // is this used anywhere?
$_global_probe_iteraton = array();

function probe($strMsg="probe")
{
	if (!is_dev())
		return;
	global $_global_probe_iteraton;
	if (!isset($_global_probe_iteraton[$strMsg]))
		$_global_probe_iteraton[$strMsg] = 1;
	list($usec, $sec) = explode(" ", microtime());
    print $strMsg."[" . $_global_probe_iteraton[$strMsg] . "][" . number_format(msfloat()) . "]; <br>\n";
	$_global_probe_iteraton[$strMsg]++;
}

function kill($strMsg="kill")
{
	if (!is_dev())
		return;
	probe($strMsg);
	exit;
}

function dumper($mixedVars, $boolExit=0, $boolHTML=0, $boolBypass=0)
{
	if ($boolBypass || !is_dev())
		return;
	if ($boolHTML)
		print "<pre>";
	var_dump($mixedVars);
	probe("dumper");
	if ($boolHTML)
		print "</pre>";

	if ($boolExit)
		exit;
}

function msfloat($strMicrotime=FALSE)
{
	if ($strMicrotime)
		$strMicrotime = microtime();
	list($usec, $sec) = explode(" ", microtime());
	return (((float) $usec + (float) $sec)*10000);
}
function rdgq($strSql)
{
	$objQuery = mysql_query($strSql);
	if (!$objQuery)
		return array();
	$arrResults = array();
	while ($objRow = mysql_fetch_object($objQuery))
	{
		$arrResults[] = $objRow;
	}
	mysql_free_result($objQuery);
	return $arrResults;
}

/*
 * array_hash ( string $key, [mixed $array_or_string, ...], array $array)
 * Loop through an array of objects and reconstruct the array where the key is
 * now what is specified in the first parameter.
 */
function array_hash()
{
	$arrArgs = func_get_args();
	//var_dump($arrArgs);
	if (count($arrArgs) < 2) // why not just used named args?
		throw new Exception("Invalid number of arguments supplied to array_hash(...) in db.php.");
	$arrList = array_pop($arrArgs);
	$arrArgs = array_flatten($arrArgs);
	if (is_null($arrList))
		return array();
	if (!is_array($arrList))
		throw new Exception("Invalid list array supplied for array_hash in db.php.");
	if (!count($arrList))
		return array();
	$arrResult = array();
	foreach ($arrList as $mixedItem)
	{
		$arrResultRef = &$arrResult;
		foreach ($arrArgs as $strKey) {
			if (is_object($mixedItem))
			{
				if (!isset($mixedItem->$strKey))
					continue;
				$strKeyData = $mixedItem->$strKey;
			}
			else
			{
				if (!isset($mixedItem[$strKey]))
					continue;
				$strKeyData = $mixedItem[$strKey];
			}
			if (!count($arrResult)) {
				$arrResultRef = &$arrResult[$strKeyData];
			} else {
				$arrResultRef = &$arrResultRef[$strKeyData];
			}
		}
		$arrResultRef = $mixedItem;
	}
	return $arrResult;
}

/*
 * Remove dimentions from an array
 */
function array_flatten($arrInput)
{
	if (!is_array($arrInput))
		throw new Exception("Invalid argument supplied to array_flatten(...) in bootstrap.");
	$arrResult = array();
	foreach ($arrInput as $key => $value) {
		if (is_array($value)) {
			$arrResult = array_merge_real_recursive($arrResult, array_flatten($value));
		}
		else {
			$arrResult[$key] = $value;
		}
	}
	return $arrResult;
}

function array_merge_real_recursive() {
	$arrays = func_get_args();
	$base = array_shift($arrays);

	foreach ($arrays as $array) {
		reset($base); //important
		while (list($key, $value) = @each($array)) {
			if (is_array($value) && @is_array($base[$key])) {
				$base[$key] = array_merge_real_recursive($base[$key], $value);
			} else {
				$base[$key] = $value;
			}
		}
	}

	return $base;
}

// used in get_point_transactions.php twice and no where else from what I can tell
function array_stack()
{
	$arrArgs = func_get_args();
	//var_dump($arrArgs);
	if (count($arrArgs) < 2)
		throw new Exception("Invalid number of arguments supplied to array_stack(...) in bootstrap.");
	$arrList = array_pop($arrArgs);
	if (is_null($arrList))
		return array();
	if (!is_array($arrList))
		throw new Exception("Invalid list array supplied for array_stack in bootstrap.");
	$arrResult = array();
	foreach ($arrList as $strKeyData => $mixedItem)
	{
		$arrResultRef = &$arrResult;
		foreach ($arrArgs as $strKey) {
			if (is_object($mixedItem))
			{
				if (isset($mixedItem->$strKey))
					$strKeyData = $mixedItem->$strKey;
			}
			else
			{
				if (isset($mixedItem[$strKey]))
					$strKeyData = $mixedItem[$strKey];
			}
			if (!count($arrResult)) {
				$arrResultRef = &$arrResult[$strKeyData];
			} else {
				$arrResultRef = &$arrResultRef[$strKeyData];
			}
		}
		$arrResultRef = $strKeyData;
	}
	return $arrResult;
}

/*
 * Methods:
 * 1. Create lists of items grouped by the first key.
 * Usage key[,key...],value
 */
function array_bubble_hash()
{
	$arrArgs = func_get_args();
	//var_dump($arrArgs);
	if (count($arrArgs) < 2)
		throw new Exception("Invalid number of arguments supplied to array_bubble_hash(...) in bootstrap.");
	$arrList = array_pop($arrArgs);
	if (!is_array($arrList))
		throw new Exception("Invalid list array supplied for array_bubble_hash(...) in bootstrap.");
	$arrResult = array();
	foreach ($arrList as $mixedItem)
	{
		$arrResultRef = &$arrResult;
		foreach ($arrArgs as $strKey) {
			if (is_object($mixedItem))
			{
				if (!isset($mixedItem->$strKey))
					throw new Exception("Invalid key `" . $strKey . "` supplied array_bubble_hash(...) in bootstrap.");
				$strKeyData = $mixedItem->$strKey;
			}
			else
			{
				if (!isset($mixedItem[$strKey]))
					throw new Exception("Invalid key `" . $strKey . "` supplied for array_bubble_hash(...) in bootstrap.");
				$strKeyData = $mixedItem[$strKey];
			}
			if (!count($arrResult)) {
				$arrResultRef = &$arrResult[$strKeyData];
			} else {
				$arrResultRef = &$arrResultRef[$strKeyData];
			}
		}
		$arrResultRef[] = $mixedItem;
	}
	return $arrResult;
}

function trigger_error_server($error_msg, $error_type = E_USER_NOTICE) {
  if(!headers_sent()) header('HTTP/1.0 500 Internal Server Error');
  trigger_error($error_msg, $error_type);
}

function trigger_error_client($error_msg, $error_type = E_USER_NOTICE) {
  if(!headers_sent()) header('HTTP/1.0 400 Bad Request');
  trigger_error($error_msg, $error_type);
}

//escape a string for mysql
function ms($string) {
  return "'" . mysql_real_escape_string($string) . "'";
}

// get the error stack if the sql does not fire
function mq($sql) {
  $bt = debug_backtrace();

  $result = mysql_query($sql) or trigger_error_server('MySQL error: ('.mysql_errno().') '.mysql_error()." in <b>{$bt[0]['file']}</b> on line <b>{$bt[0]['line']}</b><br>\n<br><b>Query:</b>\n<pre>$sql</pre>\n\n<div style='display: none;'>", E_USER_ERROR);
  return $result;
}

function mqu($sql) {
  $bt = debug_backtrace();

  $result = mysql_unbuffered_query($sql) or trigger_error_server('MySQL error: ('.mysql_errno().') '.mysql_error()." in <b>{$bt[0]['file']}</b> on line <b>{$bt[0]['line']}</b><br>\n<br><b>Query:</b>\n<pre>$sql</pre>\n\n<div style='display: none;'>", E_USER_ERROR);
  return $result;
}

//don't actually use these - they are for documentation purposes. Hard code the conversion where it's needed.
function MysqlDateToJulian($date) {
  return "TO_DAYS($date)+1721060";
}

function JulianToMysqlDate($date) {
  return "FROM_DAYS($date-1721060)";
}

//from php.net user comment
function mysql_enum_values($tableName,$fieldName)
{
  $result = mq("DESCRIBE $tableName");

  //then loop:
  while($row = mysql_fetch_array($result))
  {
    //# row is mysql type, in format "int(11) unsigned zerofill"
    //# or "enum('cheese','salmon')" etc.

    ereg('^([^ (]+)(\((.+)\))?([ ](.+))?$',$row['Type'],$fieldTypeSplit);
    //# split type up into array
    $ret_fieldName = $row['Field'];
    $fieldType = $fieldTypeSplit[1];// eg 'int' for integer.
    $fieldFlags = $fieldTypeSplit[5]; // eg 'binary' or 'unsigned zerofill'.
    $fieldLen = $fieldTypeSplit[3]; // eg 11, or 'cheese','salmon' for enum.

    if (($fieldType=='enum' || $fieldType=='set') && ($ret_fieldName==$fieldName) )
    {
      $fieldOptions = split("','",substr($fieldLen,1,-1));
      return $fieldOptions;
    }
  }

  //if the function makes it this far, then it either
  //did not find an enum/set field type, or it
  //failed to find the the fieldname, so exit FALSE!
  return FALSE;

}

// 1 columns: ret[] = col1
// 2 columns: ret[col1] = col2
// 3 or more columns: ret[col1] = array(col2, col3, etc)
// col1 MUST be unique
function mysql_fetch_column($result) {
  $ret = array();
  if(mysql_num_fields($result) == 1) {
    for($i = 0; $i < mysql_num_rows($result); $i++) {
      $ret[] = mysql_result($result,$i);
    }
  } else {
    while($row = mysql_fetch_assoc($result)) {
      $key = array_shift($row);
      $ret[$key] = count($row) == 1 ? array_shift($row) : $row;
    }
  }
  return $ret;
}

// 2 columns: ret[col1][col2] = true;
// 3 columns: ret[col1][col2] = col3;
// 4 or more columns: ret[col1][col2] = array(col3, col4, etc)
// combination of col1 and col2 MUST be unique
function mysql_fetch_column_tuple($result) 
{
	if(mysql_num_fields($result) < 2) 
		return mysql_fetch_column($result);
		
	$ret = array();
	
	while($row = mysql_fetch_assoc($result)) 
	{
		$key1 = array_shift($row);
		$key2 = array_shift($row);
		$ret[$key1][$key2] = count($row) == 1 ? array_shift($row) : (count($row) ? $row : true);
	}
	
	return $ret;
}

function maxDBquery() {
  $row = mysql_fetch_assoc(mq("show variables like 'max_allowed_packet'"));
  return intval($row['Value']);
}

function nullif($value, $test = NULL) {
  return $value === $test ? 'NULL' : floatval($value);
}

function nullif_max($value, $max, $test = '') {
  return $value === $test ? 'NULL' : max(0, min(intval($value), $max));
}

function nullif_ms($value, $test = NULL) {
  return $value === $test ? 'NULL' : ms($value);
}

function rangeRows($start, $end) {
  $out = '';
  for($i = $start; $i <= $end; $i ++) {
    if($out) $out .= ' UNION ';
    $out .= "SELECT $i num";
  }
  return $out;
}

function authToName($auth, $id) {
  switch($auth) {
    case 'school':
      $result = mq("SELECT school_name name FROM schools WHERE school_id = $id");
      break;

    case 'class':
      $result = mq("SELECT CONCAT(class_grade, '-', class_sub) name FROM classes WHERE class_id = $id");
      break;

    case 'team':
      $result = mq("SELECT team_name name FROM teams WHERE team_id = $id");
      break;

    case 'user':
    case 'end_user':
      $result = mq("SELECT CONCAT(first, ' ', last) name FROM users WHERE user_id = $id");
      break;
  }

  $row = mysql_fetch_assoc($result);
  return $row['name'];
}

function authTypeToName($auth, $plural=true, $admin=true) {
  if($admin) $admin = ' ' . ($plural ? T_('Admins') : T_('Admin'));

  switch($auth) {
    case 'school':
      return $admin || !$plural ? T_('School') . $admin : T_('Schools');
      break;

    case 'class':
      return $admin || !$plural ? T_('Class') . $admin : T_('Classes');
      break;

    case 'team':
      return $admin || !$plural ? T_('Team') . $admin : T_('Teams');
      break;

    case 'user':
      return $admin || !$plural ? T_('Soldier') . $admin : T_('Soldiers');
      break;

    case 'end_user':
      return !$plural ? T_('Soldier') : T_('Soldiers');
      break;

    default:
      return $auth;
      break;
  }
}

function reportTypeURL_view($report_type, $auth, $id, $start_date, $end_date) {
  switch($report_type) {
    case '':
      $ret = "admin_report.php?start_date=$start_date&amp;end_date=$end_date";
      break;

    case 'mission_cover_sheet':
      $ret = "admin_print_pdf.php?type=mission_cover_sheet&amp;start_date=$start_date";
      break;

    default:
      $ret = 'admin_report_' . strtolower($report_type) . ".php?date=$start_date";
      break;
  }

  switch($auth) {
    case 'school':
      $ret .= "&amp;school_id=$id";
      break;

    case 'user':
      $ret .= "&amp;user_id=$id";
      break;
  }

  return $ret;
}

function reportTypeURL_mark($report_type, $auth, $id, $start_date, $end_date) {
  switch($report_type) {
    case '':
      $ret = "admin_marks.php?start_date=$start_date&amp;end_date=$end_date";
      break;

    case 'mission_cover_sheet':
      $ret = "admin_date_tasks_marks.php?start_date=$start_date&amp;end_date=$end_date";
      break;

    case 'Auction':
      $ret = "admin_user_auction.php?date=$start_date";
      break;

    default:
      $ret = 'admin_marks_' . strtolower($report_type) . ".php?date=$start_date";
      break;
  }

  switch($auth) {
    case 'school':
      $ret .= "&amp;school_id=$id";
      break;

    case 'user':
      $ret .= "&amp;user_id=$id";
      break;
  }

  return $ret;
}

function datesToQuery($name, $start_date = null, $end_date = null) {
  $date_query = $start_date ? "$name >= $start_date" : '';
  $date_query .= $end_date ? ($date_query ? ' AND ' : '') . "$name <= $end_date" : '';
  if(!$date_query) $date_query = '1 = 1';
  return $date_query;
}

function totalMarks($extra='', $group='', $boolExtract=true) { //$extra can be used for joins or WHERE
			
	$subject_join = (strpos($group, 'subject_id') !== FALSE || strpos($extra, 'subject_id') !== FALSE); //will be confused by column named subject_ids - but that's unlikely to be a problem
	$group_by = $group ? "GROUP BY $group" : '';
	if($group) $group .= ',';
	$intPointsFromIMS = 0;
	
	$bp = $extra;
	if ($pos = strpos(strtolower($extra), ' and') !== false)
		$bp = substr($extra, 0, $pos);
	/*
	return "
	SELECT $group IFNULL(SUM(mark_points) + " . intval($intPointsFromIMS) . ", 0) mark_points FROM
	(
	SELECT $group SUM(mark_points) mark_points FROM marks" . ($subject_join ? ' JOIN tasks USING (task_id)' : '') . " $extra $group_by
	UNION ALL
	SELECT $group SUM(mark_points) mark_points FROM date_tasks_marks JOIN ord ON (mark_inactive = 0 AND ord.num = 1)" . ($subject_join ? ' JOIN date_tasks USING (date_task_id) JOIN date_tasks_missions USING (date_tasks_mission_id)' : '') . " $extra $group_by
	UNION ALL
	SELECT $group SUM(mark_points) mark_points FROM chain_marks" . ($subject_join ? ' JOIN chain_items USING (chain_item_id)' : '') . " $extra $group_by
	UNION ALL
	SELECT $group SUM(award_points) mark_points FROM points " . str_replace(array('mark_date', 'mark_points'), array('award_date', 'award_points'), $extra) . " $group_by
	) marks $group_by
	";
	
	return "
	SELECT $group IFNULL(SUM(mark_points) + " . intval($intPointsFromIMS) . ", 0) mark_points FROM
	(
	SELECT $group SUM(mark_points) mark_points FROM date_tasks_marks " . ($subject_join ? ' JOIN date_tasks USING (date_task_id) JOIN date_tasks_missions USING (date_tasks_mission_id)' : '') . " $extra $group_by
	) marks $group_by
	";
	*/
	return "SELECT $group SUM(mark_points) marks FROM date_tasks_marks " . ($subject_join ? ' JOIN date_tasks USING (date_task_id) JOIN date_tasks_missions USING (date_tasks_mission_id)' : '') . " $extra $group_by";
}

function userStatement($user_id, $start_date=null, $end_date=null, $subject_ids='', $order='DESC') {
  if($subject_ids) $subject_ids = "AND subject_id IN ($subject_ids)";
	
  // $sql seems repetative and unused. - hornbacher
  $sql = "
  (SELECT 0 sort, subject_name, award_points points, award_date mark_date, CONCAT(award_left_circle, ' ', award_right_circle) name, award_description description, CONCAT(award_left_circle, ' ', award_right_circle) sort2 FROM points LEFT JOIN subjects USING (subject_id) WHERE user_id = $user_id $subject_ids AND " . datesToQuery('award_date', $start_date, $end_date) . ")

  UNION ALL

  (SELECT 0 sort, subject_name, mark_points points, mark_date, CONCAT(IFNULL(CONCAT(mission_number, ' '), ''), mission_name) name, mark_description description, ord sort2 FROM date_tasks_marks LEFT JOIN date_tasks USING (date_task_id) LEFT JOIN date_tasks_missions USING (date_tasks_mission_id) LEFT JOIN subjects USING (subject_id) WHERE mark_inactive = 0 AND user_id = $user_id $subject_ids AND " . datesToQuery('mark_date', $start_date, $end_date) . ")

  UNION ALL

  (SELECT 0 sort, subject_name, mark_points points, mark_date, name, mark_description description, floor*512+room sort2 FROM chain_marks LEFT JOIN chain_items USING (chain_item_id) LEFT JOIN subjects USING (subject_id) WHERE user_id = $user_id $subject_ids AND " . datesToQuery('mark_date', $start_date, $end_date) . ")

  UNION ALL

  (SELECT 0 sort, subject_name, mark_points points, mark_date, name, mark_description description, CONCAT(name, task_id) sort2 FROM marks LEFT JOIN tasks USING (task_id) LEFT JOIN subjects USING (subject_id) WHERE user_id = $user_id $subject_ids AND " . datesToQuery('mark_date', $start_date, $end_date) . ")

  UNION ALL

  (SELECT 1 sort, subject_name, 0 points, date_awarded mark_date, '' name, CONCAT(" . ms(T_('Medal Awarded') . ': ') . ", medal_name) description, medal_ord sort2 FROM medal_marks LEFT JOIN medals USING (medal_ord) LEFT JOIN subjects USING (subject_id) WHERE user_id = $user_id $subject_ids AND " . datesToQuery('date_awarded', $start_date, $end_date) . ")

  UNION ALL

  (SELECT 2 sort, '' subject_name, 0 points, date_promoted mark_date, '' name, CONCAT(" . ms(T_('Promoted to Rank') . ': ') . ", rank_name) description, rank_ord sort2 FROM rank_marks LEFT JOIN ranks USING (rank_ord) WHERE user_id = $user_id AND " . datesToQuery('date_promoted', $start_date, $end_date) . ")

  ORDER BY mark_date $order, sort $order, subject_name $order, name $order, sort2 $order";

  //echo $sql . "<br />";

  return mq("
  (SELECT 0 sort, subject_name, award_points points, award_date mark_date, CONCAT(award_left_circle, ' ', award_right_circle) name, award_description description, CONCAT(award_left_circle, ' ', award_right_circle) sort2 FROM points LEFT JOIN subjects USING (subject_id) WHERE user_id = $user_id $subject_ids AND " . datesToQuery('award_date', $start_date, $end_date) . ")

  UNION ALL

  (SELECT 0 sort, subject_name, mark_points points, mark_date, CONCAT(IFNULL(CONCAT(mission_number, ' '), ''), mission_name) name, mark_description description, ord sort2 FROM date_tasks_marks LEFT JOIN date_tasks USING (date_task_id) LEFT JOIN date_tasks_missions USING (date_tasks_mission_id) LEFT JOIN subjects USING (subject_id) WHERE mark_inactive = 0 AND user_id = $user_id $subject_ids AND " . datesToQuery('mark_date', $start_date, $end_date) . ")

  UNION ALL

  (SELECT 0 sort, subject_name, mark_points points, mark_date, name, mark_description description, floor*512+room sort2 FROM chain_marks LEFT JOIN chain_items USING (chain_item_id) LEFT JOIN subjects USING (subject_id) WHERE user_id = $user_id $subject_ids AND " . datesToQuery('mark_date', $start_date, $end_date) . ")

  UNION ALL

  (SELECT 0 sort, subject_name, mark_points points, mark_date, name, mark_description description, CONCAT(name, task_id) sort2 FROM marks LEFT JOIN tasks USING (task_id) LEFT JOIN subjects USING (subject_id) WHERE user_id = $user_id $subject_ids AND " . datesToQuery('mark_date', $start_date, $end_date) . ")

  UNION ALL

  (SELECT 1 sort, subject_name, 0 points, date_awarded mark_date, '' name, CONCAT(" . ms(T_('Medal Awarded') . ': ') . ", medal_name) description, medal_ord sort2 FROM medal_marks LEFT JOIN medals USING (medal_ord) LEFT JOIN subjects USING (subject_id) WHERE user_id = $user_id $subject_ids AND " . datesToQuery('date_awarded', $start_date, $end_date) . ")

  UNION ALL

  (SELECT 2 sort, '' subject_name, 0 points, date_promoted mark_date, '' name, CONCAT(" . ms(T_('Promoted to Rank') . ': ') . ", rank_name) description, rank_ord sort2 FROM rank_marks LEFT JOIN ranks USING (rank_ord) WHERE user_id = $user_id AND " . datesToQuery('date_promoted', $start_date, $end_date) . ")

  ORDER BY mark_date $order, sort $order, subject_name $order, name $order, sort2 $order");
}

function auctionCurrent() {
  return mysql_fetch_assoc(mq('SELECT * FROM auctions WHERE auction_ran = 0 AND (auction_run_date IS NULL OR auction_run_date > ' . unixtojd() . ') AND school_id IS NULL ORDER BY auction_date LIMIT 1'));
}

function auctionPoints($user_id, $auction_row = NULL, $checkIcorpa = true, $withCode = false)
{
	if ($checkIcorpa) { 
		//added to get points from new system
		$sql = "select user_code from users where user_id = " . $user_id;
		$result = mysql_query($sql);
		$row = mysql_fetch_row($result);		
		$arr['user_code'] = $row[0];
		$userCode = '3' . $row[0];
		$arr['jd_date'] = $auction_row['auction_points_trigger_date'];
		$arr['no_negs'] = 1;
		$arrPoints = header_icorpa_points($arr);
	}
		
	if (is_null($auction_row))
		$auction_row = auctionCurrent();

	if (!$auction_row)
		return null;
	
	//$cur_points = floatval(mysql_result(mq(totalMarks("WHERE user_id = $user_id AND mark_date <= {$auction_row['auction_date']}" . (is_null($auction_row['auction_points_start_date']) ? '' : " AND mark_date >= {$auction_row['auction_points_start_date']}"))), 0));
    //$cur_points = floatval(mysql_result(mq(totalMarks("WHERE user_id = $user_id AND mark_date <= {$auction_row['auction_date']}" . (is_null($auction_row['auction_points_start_date']) ? '' : " AND mark_date >= " . dateThisYear(13, 18) ))), 0));
    $cur_points = floatval(mysql_result(mq(totalMarks("WHERE user_id = $user_id AND mark_date <= {$auction_row['auction_date']}")), 0));
	if ($checkIcorpa) {
		if ($withCode) {
			if ($arrPoints[$userCode]["all_points"] > 0)
				$cur_points += $arrPoints[$userCode]["all_points"];
		} else {
			if ($arrPoints["all_points"] > 0)
				$cur_points += $arrPoints["all_points"];
		}
	}
	
	/* only show for last auction of year
	if ($cur_points >= 1200) {
		//$sql = totalMarks("WHERE user_id=" . $user_id . " AND mark_date <= " . $auction_row['auction_date'] . " ");
		//echo "<input type='hidden' name='SQL 1' value='" . $sql . "'>\n";
		$cur_points = floatval(mysql_result(mq(totalMarks("WHERE user_id = $user_id AND mark_date <= {$auction_row['auction_date']}")), 0));
		$cur_points += $arrPoints["all_points"];
	}
	*/
	//echo "<input type='hidden' name='CURRENT POINTS' value='" . $cur_points . "'>\n";
	
	if (!is_null($auction_row['auction_points_trigger_date']))
	{
		//$sql = totalMarks("WHERE user_id=" . $user_id . " AND mark_date <= " . $auction_row['auction_date'] . " AND mark_date > " . $auction_row['auction_points_trigger_date'] . " AND mark_date >= " . $auction_row['auction_points_start_date'] . " ");
		//echo "<input type='hidden' name='SQL 2' value='" . $sql . "'>\n";
		
		$trigger_points = floatval(mysql_result(mq(totalMarks("WHERE user_id = $user_id AND mark_date <= {$auction_row['auction_date']} AND mark_date > {$auction_row['auction_points_trigger_date']}" . (is_null($auction_row['auction_points_start_date']) ? '' : " AND mark_date >= {$auction_row['auction_points_start_date']}"))), 0));
		if ($checkIcorpa) {
			if ($withCode) {
				if ($arrPoints[$userCode]["jd_points"] > 0)
					$cur_points += $arrPoints[$userCode]["jd_points"];
			} else {
				if ($arrPoints["jd_points"] > 0)
					$cur_points += $arrPoints["jd_points"];
			}
		}
		
		//$sql = totalMarks("WHERE user_id = $user_id AND mark_date <= {$auction_row['auction_date']} AND mark_date > {$auction_row['auction_points_trigger_date']}" . (is_null($auction_row['auction_points_start_date']) ? '' : " AND mark_date >= {$auction_row['auction_points_start_date']}"));
		//echo "<input type='hidden' name='TRIGGER POINTS SQL' value='" . $arrPoints["jd_points"] . "'>";
		
		//if ($trigger_points >= 150)
		//{
		//	unset($trigger_points);
		//}
		
		if ($trigger_points < 1200) {
			$cur_points = $trigger_points;
		}
		unset($trigger_points);
		
		switch ($user_id) {
			case 15862:
				$cur_points = 2586;
				break;
			case 13286:
				$cur_points = 1003;
				break;
		}
	}
	//echo "<input type='hidden' name='TRIGGER POINTS' value='" . $trigger_points . "'>\n";
	
	return !isset($trigger_points) ? array('cur'=>$cur_points, 'prev'=>null, 'left'=>null) : array('cur'=>$trigger_points, 'prev'=>$cur_points-$trigger_points, 'left'=>150-$trigger_points);
}

//slow, non-optimized (non-indexed) query, use only when necessary
function auctionPointsQuery($auction_row = NULL) {
  if(is_null($auction_row)) $auction_row = auctionCurrent();

  return
  '(' .
    'SELECT user_id, IFNULL(mark_points, 0) mark_points FROM users LEFT JOIN (' . totalMarks(
      "WHERE mark_date <= {$auction_row['auction_date']}" . (is_null($auction_row['auction_points_start_date']) ? '' : " AND mark_date >= {$auction_row['auction_points_start_date']}"), 'user_id'
    ) . ') trigger_total USING (user_id)' .
  ') cur
  USING (user_id) LEFT JOIN (' . (
    !is_null($auction_row['auction_points_trigger_date']) ?
      'SELECT user_id, IFNULL(mark_points, 0) mark_points FROM users LEFT JOIN (' . totalMarks(
        "WHERE mark_date <= {$auction_row['auction_date']} AND mark_date > {$auction_row['auction_points_trigger_date']}" . (is_null($auction_row['auction_points_start_date']) ? '' : " AND mark_date >= {$auction_row['auction_points_start_date']}"), 'user_id'
      ) . ') trigger_inner USING (user_id)'
    :
      'SELECT null mark_points, user_id FROM users'
  ) . ') trigger_points';
}

function auctionPointsQueryExp() {
  return "IF(trigger_points.mark_points >= 150 OR trigger_points.mark_points IS NULL, cur.mark_points, trigger_points.mark_points)";
}

function tanyaSettings($user_id, $start_week = 0, $goal_halt = false) {
  $row = mysql_fetch_assoc(mq("SELECT track, year, lines_goal, lines_done, lines_offset, tanya_start_date, length_days, length_days_offset, medal_ord FROM tanya_users JOIN tanya_goals USING (track) WHERE user_id = $user_id"));

  if(!$row) return false;

  // 0 = today, -1 current week, -2 next week, else week number counting from 1
  if($start_week == 0) $start_date = unixtojd();
  elseif($start_week == -1) $start_date = unixtojd() - ((unixtojd() - $row['tanya_start_date']) % 7);
  elseif($start_week == -2) $start_date = unixtojd() - ((unixtojd() - $row['tanya_start_date']) % 7) + 7;
  else $start_date = $row['tanya_start_date'] + ($start_week-1)*7;

  $years_goal = round(($row['lines_goal'] - $row['lines_offset']) / 8 * $row['year']) + $row['lines_offset'];
  $days_left = $row['tanya_start_date'] + $row['length_days'] + $row['length_days_offset'] - $start_date;

  if($start_date < $row['tanya_start_date'] || $days_left <= 0) return false; //could do this in SQL but clearer here
  if($goal_halt && ($row['lines_done'] >= $row['lines_goal'] || $row['lines_done'] >= $years_goal)) return false;

  $years_goal_data = mysql_fetch_assoc(mq("SELECT page, perek FROM tanya_lines WHERE line = $years_goal"));
  $long_goal_data = mysql_fetch_assoc(mq("SELECT page, perek FROM tanya_lines WHERE line = {$row['lines_goal']}"));
  $medal = mysql_fetch_assoc(mq('SELECT medal_name, medal_on_image_id FROM medals WHERE medal_ord = ' . floor($row['medal_ord'])));
  $next_medal = mysql_fetch_assoc(mq('SELECT medal_name, medal_on_image_id FROM medals WHERE medal_ord = ' . floor($row['medal_ord']+1)));

  return array(
    'years_goal' => $years_goal,
    'years_goal_page' => $years_goal_data['page'],
    'years_goal_perek' => $years_goal_data['perek'],
    'long_goal' => $row['lines_goal'],
    'long_goal_page' => $long_goal_data['page'],
    'long_goal_perek' => $long_goal_data['perek'],
    'medal_ord' => $row['medal_ord'],
    'medal_name' => $medal['medal_name'],
    'medal_url' => $medal['medal_on_image_id'],
    'next_medal_name' => $next_medal['medal_name'],
    'next_medal_url' => $next_medal['medal_on_image_id'],
    'medal_progress' => $row['lines_goal'] <= $row['lines_offset'] ? 10 : ($row['lines_done']-$row['lines_offset']) / (($row['lines_goal']-$row['lines_offset']) / 10),
    'lines_to_next_medal' => $next_medal ? floor($row['medal_ord']+1) / 10 * ($row['lines_goal'] - $row['lines_offset']) + $row['lines_offset'] - $row['lines_done'] : NULL,
    'lines_done' => $row['lines_done'],
    'lines_offset' => $row['lines_offset'],
    'max_line' => max($row['lines_done'], $row['lines_offset'])+1,
    'days_left' => $days_left,
    'length_days' => $row['length_days'],
    'days_passed' => $start_date - $row['tanya_start_date'] + $row['length_days_offset'],
    'lines_per_week' => round(($years_goal - $row['lines_done']) / ($days_left/7), 1),
    'start_date' => $start_date,
    'test_limit' => ($row['medal_ord'] + .25) / 10 * ($row['lines_goal'] - $row['lines_offset']) + $row['lines_offset'],
  );
}

function hash2Code($text, $size = 14) {
  $chars = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

  $hash = unpack('C*', sha1("$text f1fc539ce546d700782b7993e96214ec", true));

  bcscale(0);
  $code = '0';
  while(($val = array_shift($hash)) !== NULL) {
    $code = bcmul($code, '256');
    $code = bcadd($code, $val);
  }

  $printable = '';
  while($code !== '0' && $size--) {
    $char = bcmod($code, '31');
    $code = bcdiv($code, '31');
    $printable = $chars[$char] . $printable;
  }
  return $printable;
}

/*
//caclulates all shabbos mevorchim dates for given year
function calculateSM( $year ) {
	$sm = array(); 
	$day = 30;
	for ( $i = 1; $i < 14; $i++ ) {
		$date = jewishtojd( $i, $day, $year );
		$date += 1; //fix issue with jdtounix showing a day off
		$time = jdtounix( $date );
		$dayOfWeek = date( "w", $time );
		$shabbosMevorchim = $date - ($dayOfWeek + 2); //really should be adding 1 but because of jdtounix fix need to add 2
		$sm[$i] = $shabbosMevorchim; //note: if value of index #5 == index #6 then that means that it is NOT a leap year
	}
	return $sm;
}
*/
//caclulates all shabbos mevorchim dates for given year
function calculateSM( $year ) {
    $sm = array(); 
    $day = 29; // last day of month before Rosh Chodesh
    //first get last sm for previous year
    $date = jewishtojd( 13, $day, ($year-1) );
    $date += 1; //fix issue with jdtounix showing a day off
    $time = jdtounix( $date );
    $dayOfWeek = date( "w", $time );
    $shabbosMevorchim = $date - ($dayOfWeek + 1); 
    $sm[0] = $shabbosMevorchim;
    for ( $i = 1; $i < 13; $i++ ) {
        $date = jewishtojd( $i, $day, $year );
        $date += 1; //fix issue with jdtounix showing a day off
        $time = jdtounix( $date );
        $dayOfWeek = date( "w", $time );
        $shabbosMevorchim = $date - ($dayOfWeek + 1); 
        $sm[$i] = $shabbosMevorchim; //note: if value of index #6 == index #7 then that means that it is NOT a leap year
    }
    return $sm;
}

function header_points($arrParams) {
	if (!isset($arrParams["user_code"]))
	{
		print "Sorry, there was an error: H-HIP101-SA87DS";
		exit;
	}
	$strUrl = 'http://mashpia.com/v2/legacy/userpoints';
	if (isset($arrParams["no_negs"])) {
		$strUrl .= "/no_negs/" . $arrParams["no_negs"];
	}
	if (isset($arrParams["auction_date"])) {
		$strUrl .= "/date/" . $arrParams["auction_date"];
	}
	if (isset($arrParams["start_date"])) {
		$strUrl .= "/start_date/" . $arrParams["start_date"];
	}
	$arrPost = array(
		"user_code" => serialize($arrParams['user_code'])
	);
	//echo $strUrl; exit;
	$objCurl = curl_init();
	curl_setopt($objCurl, CURLOPT_URL, $strUrl);
	curl_setopt($objCurl, CURLOPT_FRESH_CONNECT, 1);
	curl_setopt($objCurl, CURLOPT_FORBID_REUSE, 1); 
	curl_setopt($objCurl, CURLOPT_POST, 1);
	curl_setopt($objCurl, CURLOPT_POSTFIELDS, $arrPost);
	curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, 1);
	$strResult = curl_exec($objCurl);
	//echo $strResult; exit;
	
	if (0&&$_SERVER["REMOTE_ADDR"] == "173.176.48.60")
	{
		print $strResult;
		exit;
	}
	if (0&&preg_match("/Sorry, there was an error/", $strResult))
	{
		print "Sorry, there was an error: H-HIP102-23R3RR";
		exit;
	} 
	$arrResults = @unserialize($strResult);
	return $arrResults;
}

function header_total_points($arrParams) {
	$arrParams['no_negs'] = 1;
	return header_points($arrParams);
}

function header_store_points($arrParams) {
	return header_points($arrParams);
}

function header_auction_points($arrParams) {
	return header_points($arrParams);
}

function header_icorpa_points($arrParams)
{
	if (!isset($arrParams["user_code"]))
	{
		print "Sorry, there was an error: H-HIP101-SA87DS";
		exit;
	}
	$strUrl = 'http://v2dev1.mashpia.com/legacy/userpointsextract3';
	if (isset($arrParams["unix_epoch"]))
	{
		$strUrl .= "/unix_epoch/" . $arrParams["unix_epoch"];
	}
	if (isset($arrParams["jd_date"]))
	{
		$strUrl .= "/jd_date/" . $arrParams["jd_date"];
	}
	if (isset($arrParams["no_negs"]))
	{
		$strUrl .= "/no_negs/1";
	}
	$arrPost = array(
		"user_code" => serialize($arrParams['user_code'])
	);
	$objCurl = curl_init();
	curl_setopt($objCurl, CURLOPT_URL, $strUrl);
	curl_setopt($objCurl, CURLOPT_FRESH_CONNECT, 1);
	curl_setopt($objCurl, CURLOPT_FORBID_REUSE, 1); 
	curl_setopt($objCurl, CURLOPT_POST, 1);
	curl_setopt($objCurl, CURLOPT_POSTFIELDS, $arrPost);
	curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, 1); 
	$strResult = curl_exec($objCurl);
	
	$intHebrewPoints = 0;
	$intAllPoints = 0;
	if (0&&preg_match("/Sorry, there was an error/", $strResult))
	{
		print "Sorry, there was an error: H-HIP102-23R3RR";
		exit;
	}
	if (0&&$_SERVER["REMOTE_ADDR"] == "184.162.103.154")
	{
		print $strResult;
		exit;
	}
	$arrResults = unserialize($strResult);
	return $arrResults;
}
function header_update_icorpa_student() {}
?>
