<?
//ini_set('zlib.output_compression', '1');
ini_set('default_charset', 'UTF-8');
mb_internal_encoding("UTF-8");
error_reporting(E_ALL);
setlocale(LC_MONETARY, 'en_US');

if (get_magic_quotes_gpc()) {
	function stripslashes2(&$val, $key) {
		$val = stripslashes($val);
	}
	
	array_walk_recursive($_GET, 'stripslashes2');
	array_walk_recursive($_POST, 'stripslashes2');
	array_walk_recursive($_COOKIE, 'stripslashes2');
	array_walk_recursive($_REQUEST, 'stripslashes2');
}

$_GETPOST = $_POST + $_GET;
require_once('db.php');

if (!isset($dual_auth)) 
	$dual_auth = false;

if ($dual_auth) {
	require('admin_auth.php');
	require('auth.php');
	
	if (empty($admin_user) && empty($user)) {
		include('login.php');
		exit;
	}
} 
elseif (isset($admin_auth)) {
	require('admin_auth.php');
}
else
	require('auth.php');
	
	//this strange function fixes a bug in php
	function cal_days_in_month2($cal, $month, $year) {
		if ($cal == CAL_JEWISH && $month == 6 && cal_days_in_month($cal, $month, $year) == 0)
			return cal_days_in_month($cal, $month+1, $year);
		else
			return cal_days_in_month($cal, $month, $year);
	}

	function es($string) {
		return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
	}

	//escape quotes, but not backslashes
	function esq($string) {
		return str_replace(array('"', "'"), array('\"', "\'"), $string);
	}

	//set a request variable
	function sgr($name, $value) {
		global $_GETPOST;
		
		if (is_null($value))
			unset($_GETPOST[$name]);
		else
			$_GETPOST[$name] = $value;
	}

	//get a request variable, string
	function gr($name, $empty = '') {
		global $_GETPOST;
		
		return agr($_GETPOST, $name, $empty);
	}

	function agr(&$in, $name, $empty = '') {
		if (isset($in[$name])) {
			return trim($in[$name]);
		} 
		else {
			return $empty;
		}
	}

	//get a request variable, signed int, machine word size max (usually 32 bits)
	function gri($name, $empty = NULL) {
		global $_GETPOST;
		
		return agri($_GETPOST, $name, $empty);
	}

	function agri(&$in, $name, $empty = NULL) {
		if (isset($in[$name])) {
			return intval($in[$name]);
		} 
		else {
			return $empty;
		}
	}

	//get a long int, max size 53 bits, even on a 32 bit machine
	function grl($name, $empty = NULL) {
		return floor(grf($name, $empty));
	}

	//get a request variable, int with NULL
	function grni($name, $empty = 'NULL') {
		global $_GETPOST;
		
		return agrni($_GETPOST, $name, $empty);
	}

	function agrni(&$in, $name, $empty = 'NULL') {
		if (isset($in[$name])) {
			if ($in[$name] === '' || $in[$name] === 'NULL') {
				return 'NULL';
			} 
			else {
				return intval($in[$name]);
			}
		} 
		else {
			return $empty;
		}
	}

	//get a request variable, float
	function grf($name, $empty = NULL) {
		global $_GETPOST;
		
		return agrf($_GETPOST, $name, $empty);
	}

	function agrf(&$in, $name, $empty = NULL) {
		if (isset($in[$name])) {
			return floatval($in[$name]);
		} 
		else {
			return $empty;
		}
	}

	function gra($name, $empty = array()) {
		global $_GETPOST;
		
		if (isset($_GETPOST[$name]) && is_array($_GETPOST[$name])) {
			return $_GETPOST[$name];
		} 
		else {
			return $empty;
		}
	}

	if (!function_exists('sys_get_temp_dir')) {
		function sys_get_temp_dir() {
			return '/tmp';
		}
	}

	function array_prepend($array,$prepend) {
		$return = array();
		
		foreach ($array as $val) {
			$return[] = $prepend . '[' . $val . ']';
		}
		return $return;
	}

	function array_search_key($val, $array) {
		return $array[$val];
	}

	//returns hidden input fields to recreate an array as a form
	//modeled after http_build_query
	//you can pass it: array('array_name' => $array_data) rather then
	//the array_data directly if you need the name in all the variables
	function form_build_input($array) {
		$ret = '';
		
		foreach ($array as $key => $val) {
		
			if (is_array($val)) {
			
				if (!empty($val)) {
					$ret .= form_build_input(
					array_combine(array_prepend(array_keys($val), $key), array_values($val)));
				}
			} 
			else {
				$ret .= '<input type="hidden" name="' . es($key) . '" value="' . es($val) . '">' . "\n";
			}
		}
		
		return $ret;
	}

	//since mime_content_type is depreciated
	if (!function_exists('mime_content_type')) {
	
		function mime_content_type($filename) {
			$finfo    = finfo_open(FILEINFO_MIME);
			$mimetype = finfo_file($finfo, $filename);
			finfo_close($finfo);
			return $mimetype;
		}
	}

	function maxUploadSize() {
		return min(units2bytes(ini_get('post_max_size')), units2bytes(ini_get('upload_max_filesize')));
	}

	function units2bytes($val) {
		$val = trim($val);
		
		switch(strtolower($val{strlen($val)-1})) {
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}
		
		return $val;
	}

	function bytes2units($size) {
		$bytes = array('','K','M','G','T');
		foreach($bytes as $val) {
			if($size > 1024) {
				$size /= 1024;
			} 
			else {
				break;
			}
		}
		return round($size, 2).$val;
	}

	function firstInitial($str) {
		$split = mb_split('[ \t\r\n\v\f]+', $str);
		$ret = array_shift($split);
		foreach($split as $word) {
			$ret .= ' ' . mb_substr($word, 0, 1) . (mb_strlen($word) > 1 ? '.' : '');
		}
		return $ret;
	}

	define('REPORT_DIR', sys_get_temp_dir() . '/mashpia_reports');
	function sendReport($input, $name='report.pdf', $fmt='xml', $disposition='inline') {
		//   header('Content-type: text/xml'); echo $input; exit;
		//   header('Content-type: text/comma-separated-values'); echo $input; exit;
		if (!file_exists(REPORT_DIR)) 
			mkdir(REPORT_DIR, 0311);
		
		$file = tempnam(REPORT_DIR, "{$fmt}_");
	
		if (dirname($file) != REPORT_DIR) {
			unlink($file);
			trigger_error_server('failed to make temp file for report', E_USER_ERROR);
		}
	
		chmod($file, 0644);
	
		if (!rename($file, $file . ".{$fmt}")) 
			trigger_error_server('failed to rename temp file for report', E_USER_ERROR);
		
		$file .= ".{$fmt}";
	
		file_put_contents($file, $input);
	
		header('Location: http://reports.'.implode('.',array_slice(explode('.', current(explode(':', $_SERVER['HTTP_HOST']))),-2)). '/generate/report.php?name=' . urlencode($name) . '&disposition=' . urlencode($disposition). "&fmt={$fmt}&pullurl=http" . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '') . "://{$_SERVER['HTTP_HOST']}" . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/reports/' . urlencode(basename($file)));
	
		exit;
	}

	function csvEscape($str) {
		return '"' . str_replace('"', '""', $str) . '"';
	}

	function beginning_of_hebrew_year() 
	{
		return dateThisYear(13, 18);
	}
	
	function chaiElul() {
		//return dateThisYear(13, 18);			// mmc  -> OUT   - use last year's date
		//$test_date = dateThisYear(13, 18,0, -1);
		//echo "TEST DATE:$test_date<br />";
		return dateThisYear(13, 18,0, -1);
	}
	
	function chaiElulYear() {
		$date = cal_from_jd(dateThisYear(13, 18), CAL_JEWISH);
		return $date['year']+1;
	}
	
	function currentYear() {
		//return dateThisYear(13, 18);			// mmc  -> OUT   - use last year's date
		$curYear = explode("/", jdtogregorian(dateCurrentYear(13,18)));
		//this will return the currect year. this is a small bypass
		return $curYear[2];
	}

	//returns the previous time this date occured before $starting + $year_offset years
	function dateCurrentYear($month=0, $day=0, $starting = 0, $year_offset = 0) {
		if(!$starting) 
			$starting = unixtojd();
			
		$today = cal_from_jd($starting, CAL_JEWISH);
		
		return cal_to_jd(CAL_JEWISH, $month, $day, $today['year']+$year_offset-(cal_to_jd(CAL_JEWISH, $month, $day, $today['year']) >= $starting ? 1 : 0));
	}
	
	function julian_today() {
		// month = date("n")
		// day = date("j")
		// year = date("Y")
		// gregoriantojd ( int $month , int $day , int $year )
		
		return gregoriantojd(date("n"), date("j"), date("Y"));
	}
	
	function dateThisYear($month, $day, $starting = 0, $year_offset = 0) {
		if(!$starting) 
			$starting = unixtojd();
			
		$today = cal_from_jd($starting, CAL_JEWISH);
		
		return cal_to_jd(CAL_JEWISH, $month, $day, $today['year']+$year_offset-(cal_to_jd(CAL_JEWISH, $month, $day, $today['year']) >= $starting ? 1 : 0));
	}

	function rangeToDate($range) {
		return $range == 0 ? unixtojd() : unixtojd() - $range * 7 + 7 - jddayofweek(unixtojd());
	}
	
	/* below are functions used to interact with the v2 system */
	
	function header_update_icorpa_student($arrParams)
	{
		if (!isset($arrParams["legacy_user_id"]))
		{
			print "Sorry, there was an error: H-HUIS101-D8S8DS";
			exit;
		}
		ob_start();
		$strUrl = "http://v2.mashpia.com/legacy/updatestudent/legacy_user_id/" . $arrParams["legacy_user_id"];
		$objCurl = curl_init();
		curl_setopt($objCurl, CURLOPT_URL, $strUrl);
		curl_exec($objCurl);
		$strResult = ob_get_contents();
		curl_close($objCurl);
		ob_end_clean();
		return 1;
	}
	
	/*
	 * Required: arrUserCodes
	 * Optional: intStartDate
	 * Optional: intEndDate
	 */
	function header_v2_missions($arrParams)
	{
		if (!isset($arrParams['arrUserCodes']))
		{
			print "Sorry, there was an error: H-HVM101-F2S2AA";
			exit;
		}
		$strUrl = 'http://v2dev1.mashpia.com/legacy/headerv2missions';
		$objCurl = curl_init();
		curl_setopt($objCurl, CURLOPT_URL, $strUrl);
		curl_setopt($objCurl, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($objCurl, CURLOPT_FORBID_REUSE, 1); 
		curl_setopt($objCurl, CURLOPT_POST, 1);
		curl_setopt($objCurl, CURLOPT_POSTFIELDS, 'arrParams=' . urlencode(serialize($arrParams)));
		curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, 1); 
		$strResult = curl_exec($objCurl);
		if (0&&$_SERVER["REMOTE_ADDR"] == "173.178.178.66")
		{
			print $strResult;
			exit;
		}
		$arrResults = unserialize($strResult);
		return $arrResults;
	}
	/*
	 * Required: user_code
	 * Optional: jd_date, unix_epoch(N/A)
     * Optional: no_negs - returns 'earned' points as oppose to 'available' points
	 */
	function header_icorpa_points($arrParams)
	{
		$arrResult = reset(header_icorpa_points_multi($arrParams));
		if (count($arrResult))
			return $arrResult;
		return array(
			"jd_points" => 0,
			"hebrew_points" => 0,
			"all_points" => 0
		);
	}
	function header_icorpa_points_multi($arrParams)
	{
		if (!isset($arrParams["user_code"]))
		{
			print "Sorry, there was an error: H-HIP101-SA87DS";
			exit;
		}
		$strUrl = 'http://v2.mashpia.com/legacy/userpointsextract2';
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
	function header_v2_campaign_details($arrParams)
	{
		$arrPost = array(
			"params" => serialize($arrParams)
		);
		$objCurl = curl_init();
		$strUrl = "http://v2dev1.mashpia.com/legacy/outputcustom1";
		curl_setopt($objCurl, CURLOPT_URL, $strUrl);
		curl_setopt($objCurl, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($objCurl, CURLOPT_FORBID_REUSE, 1); 
		curl_setopt($objCurl, CURLOPT_POST, 1);
		curl_setopt($objCurl, CURLOPT_POSTFIELDS, $arrPost);
		curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, 1); 
		$strResult = curl_exec($objCurl);
		
		if (0&&preg_match("/Sorry, there was an error/", $strResult))
		{
			print "Sorry, there was an error: H-HIP102-23R3RR";
			exit;
		}
		if (0&&$_SERVER["REMOTE_ADDR"] == "24.202.85.95")
		{
			print $strResult;
			exit;
		}
		$arrResults = @unserialize($strResult);
		if (@$arrResults['success'] == 'true')
		{
			return $arrResults['data'];
		} else {
			return array('success' => 'false');
		}
	}

//caclulates all shabbos mevorchim dates for given year
function calculateSM( $year ) {
    $sm = array(); 
    $day = 29;
    for ( $i = 1; $i < 14; $i++ ) {
        $date = jewishtojd( $i, $day, $year );
        $date += 1; //fix issue with jdtounix showing a day off
        $time = jdtounix( $date );
        $dayOfWeek = date( "w", $time );
        $shabbosMevorchim = $date - ($dayOfWeek + 2); //really should be adding 1 but because of jdtounix fix need to add 2
        $sm[$i] = $shabbosMevorchim; //note: if value of index #6 == index #7 then that means that it is NOT a leap year
    }
    return $sm;
}
?>
