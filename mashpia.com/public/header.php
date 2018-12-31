<?
//ini_set('zlib.output_compression', '1');
ini_set('default_charset', 'UTF-8');
mb_internal_encoding("UTF-8");
error_reporting(E_ALL);
setlocale(LC_MONETARY, 'en_US');

// this feature seems to have been removed in php 5.4, what are we running? -hornbacher (http://php.net/manual/en/info.configuration.php#ini.magic-quotes-gpc)
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
// connect to the database
require_once( $_SERVER['DOCUMENT_ROOT'].'/db.php' );

// authentication imports
if (!isset($_GETPOST['bypass'])) {
	if (!isset($dual_auth)) // $dual_auth is set where before? not in db.php. - hornbacher
		$dual_auth = false;
	
	if ($dual_auth) {
		require_once( $_SERVER['DOCUMENT_ROOT'].'/admin_auth.php' );
		require_once( $_SERVER['DOCUMENT_ROOT'].'/auth.php' );
		
		if (empty($admin_user) && empty($user)) {
			include('login.php');
			exit;
		}
	} 
	elseif (isset($admin_auth)) {
		require_once( $_SERVER['DOCUMENT_ROOT'].'/admin_auth.php' );
	} else {
		require_once( $_SERVER['DOCUMENT_ROOT'].'/auth.php' );
	}
} else {
	$admin_user['admin_id'] = $_GETPOST['admin'];
}

/* all these funcitons are imported in public_html/header.php */
//this strange function fixes a bug in php
function cal_days_in_month2($cal, $month, $year) {
	if ($cal == CAL_JEWISH && $month == 6 && cal_days_in_month($cal, $month, $year) == 0)
		return cal_days_in_month($cal, $month+1, $year);
	else
		return cal_days_in_month($cal, $month, $year);
}

// escape string correctly
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
/*
//since mime_content_type is depreciated
if (!function_exists('mime_content_type')) {

	function mime_content_type($filename) {
		$finfo    = finfo_open(FILEINFO_MIME);
		$mimetype = finfo_file($finfo, $filename);
		finfo_close($finfo);
		return $mimetype;
	}
}
*/
if(!function_exists('mime_content_type')) {

	function mime_content_type($filename) {

		$mime_types = array(

			'txt' => 'text/plain',
			'htm' => 'text/html',
			'html' => 'text/html',
			'php' => 'text/html',
			'css' => 'text/css',
			'js' => 'application/javascript',
			'json' => 'application/json',
			'xml' => 'application/xml',
			'swf' => 'application/x-shockwave-flash',
			'flv' => 'video/x-flv',

			// images
			'png' => 'image/png',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'gif' => 'image/gif',
			'bmp' => 'image/bmp',
			'ico' => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'svg' => 'image/svg+xml',
			'svgz' => 'image/svg+xml',

			// archives
			'zip' => 'application/zip',
			'rar' => 'application/x-rar-compressed',
			'exe' => 'application/x-msdownload',
			'msi' => 'application/x-msdownload',
			'cab' => 'application/vnd.ms-cab-compressed',

			// audio/video
			'mp3' => 'audio/mpeg',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',

			// adobe
			'pdf' => 'application/pdf',
			'psd' => 'image/vnd.adobe.photoshop',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',

			// ms office
			'doc' => 'application/msword',
			'rtf' => 'application/rtf',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',

			// open office
			'odt' => 'application/vnd.oasis.opendocument.text',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
		);
		
		$arrFilename = explode('.',$filename);
		$ext = strtolower($arrFilename[count($arrFilename)-1]);
		if (array_key_exists($ext, $mime_types)) {
			return $mime_types[$ext];
		}
		elseif (function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME);
			$mimetype = finfo_file($finfo, $filename);
			finfo_close($finfo);
			return $mimetype;
		}
		else {
			return 'application/octet-stream';
		}
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
		case 'm':
		case 'k':
			$val *= 1024;
			break;
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

?>