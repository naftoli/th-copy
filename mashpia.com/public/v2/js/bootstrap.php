<?php

// Index:
// j3h4j5h324 - Global variables and functions

// -----------------------------------------------------------------------------
// PHP & Apache config settings
// -----------------------------------------------------------------------------
date_default_timezone_set("America/Montreal");
error_reporting(E_ALL);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);

// -----------------------------------------------------------------------------
// Load Zend
// -----------------------------------------------------------------------------
set_include_path(
	'./library/'  .
	PATH_SEPARATOR . './application/views/scripts/includes/' .
	PATH_SEPARATOR . './application/views/scripts/includes/headers/'  .
	PATH_SEPARATOR . './application/views/scripts/includes/footers/'  .
	PATH_SEPARATOR . './application/models/' .
	PATH_SEPARATOR . './js/'  .
	PATH_SEPARATOR . './fonts/'  .
	PATH_SEPARATOR . './css/' .
	PATH_SEPARATOR .'./application/views' .
	PATH_SEPARATOR . get_include_path());

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();
Zend_Loader::loadClass('Zend_Db_Table');
Zend_Loader::loadClass('Zend_Debug');
Zend_Loader::loadClass('Zend_Auth');
Zend_Loader::loadClass('Users');
Zend_Loader::loadClass('Campaigns');
Zend_Loader::loadClass('Institutions');
Zend_Loader::loadClass('Utilities');
Zend_Loader::loadClass('Tasks');
Zend_Loader::loadClass('Missions');
Zend_Loader::loadClass('Kiosk');
Zend_Loader::loadClass('Store');
Zend_Loader::loadClass('Rules');
Zend_Loader::loadClass('Roles');
Zend_Loader::loadClass('Image');
Zend_Loader::loadClass('Classes');
Zend_Loader::loadClass('Points');
Zend_Loader::loadClass('Ladders');
Zend_Loader::loadClass('ToolsModels');
Zend_Loader::loadClass('Grades');
Zend_Loader::loadClass('Scheduler');
Zend_Loader::loadClass('Ranks');
Zend_Loader::loadClass('SchedulerProc');
Zend_Loader::loadClass('Medals');
Zend_Loader::loadClass('Books');
Zend_Loader::loadClass('Marking');
Zend_Loader::loadClass('Automation');
Zend_Loader::loadClass('AchievementCards');
Zend_Loader::loadClass('Incrementals');
Zend_Loader::loadClass('Imgs');
Zend_Loader::loadClass('Permissions');
Zend_Loader::loadClass('SimpleImage');
Zend_Loader::loadClass('Legacy');
Zend_Loader::loadClass('Registration');
Zend_Loader::loadClass('Orders');
Zend_Loader::loadClass('ToolsControllers');
Zend_Loader::loadClass('HebrewSchools');
Zend_Loader::loadClass('QueryGen');
Zend_Loader::loadClass('Config');
Zend_Loader::loadClass('Lists');
Zend_Loader::loadClass('Text');
Zend_Loader::loadClass('Kiosk01');
Zend_Loader::loadClass('AuthorizeNet');
Zend_Loader::loadClass('UPS');

//rename_function('reset', '_reset');
//runkit_function_redefine('reset', '$a', 'return first($a);');

$config = new Zend_Config_Ini('./application/config.ini', DEV_ENV);
$registry = Zend_Registry::getInstance();

$db = Zend_Db::factory($config->db);
Zend_Db_Table::setDefaultAdapter($db);
Zend_Registry::set('db', $db);

$frontController = Zend_Controller_Front::getInstance();
$frontController->throwExceptions(true);
$frontController->setControllerDirectory('./application/controllers');
$frontController->registerPlugin(new Zend_Controller_Plugin_ErrorHandler(array(
    'module'     => 'error',
    'controller' => 'error',
    'action'     => 'error'
)));

$query = new QueryGen();
if (1) //||is_dev()
{
	ob_start();
	$frontController->dispatch();
	$strWebAppPageContents = ob_get_contents();
	ob_end_clean();
	$objText = new Text();
	$strWebAppPageContents = $objText->text_process($strWebAppPageContents);
	print $strWebAppPageContents;
	exit;
}
else
{
	$frontController->dispatch();
}

$arrTextualContentData = array();
function itext($strCurrentText)
{
	return $strCurrentText;
}
function text($strCurrentText, $strResourceID=false)
{
	global $arrTextualContentData;
	$intRand = rand(1000000, 9999999);
	$strKey = "___:['textual_content;r:$intRand;i:" . count($arrTextualContentData) . ";']:___";
	$arrTextualContentData[] = array(
		"strCurrentText" => $strCurrentText,
		"strResourceID" => $strResourceID,
		"intOrderFound" => count($arrTextualContentData)
	);
	return $strKey;
}

// -----------------------------------------------------------------------------
// Global variables and functions j3h4j5h324
// -----------------------------------------------------------------------------

function dblencode($strURL)
{
	return urlencode(urlencode($strURL));
}

// make sql nice looking
function sql_format($sql){
	$arrStrData = array();
	$intItr = 0;
	// parsing
	$sql = preg_replace("/[\t\n\r]+/", " ", $sql);
	if (1) {
		// escaped
		while (preg_match('/\\[\\"\']/', $sql, $arrMatched))
		{
			$arrStrData[] = $arrMatched[0];
			$arrSql = explode($arrMatched[0], $sql);
			$arrSql[0] .= "[|__--str" . $intItr . ";" . rand(100000000,999999999) ."--__|]" . $arrSql[1];
			unset($arrSql[1]);
			$sql = join($arrMatched[0], $arrSql);
			$intItr++;
		}
		// str's
		while (preg_match('/[\'"`]([^\'"`]*)[\'"`]/', $sql, $arrMatched))
		{
			$arrStrData[] = $arrMatched[0];
			$arrSql = explode($arrMatched[0], $sql);
			$arrSql[0] .= "[|__--str" . $intItr . ";" . rand(100000000,999999999) ."--__|]" . $arrSql[1];
			unset($arrSql[1]);
			$sql = join($arrMatched[0], $arrSql);
			$intItr++;
		}
		// int's
		while (preg_match('/([ ,=><\(])([0-9]+)([ ,!=><\)])/', $sql, $arrMatched))
		{
			$arrStrData[] = $arrMatched[2];
			$arrSql = explode($arrMatched[0], $sql);
			$arrSql[0] .= $arrMatched[1] . "[|__--int" . $intItr . ";" . msfloat() ."--__|]" . $arrMatched[3] . $arrSql[1];
			unset($arrSql[1]);
			$sql = join($arrMatched[0], $arrSql);
			$intItr++;
		}
		$sql = preg_replace("/  +/", " ", $sql);
	}
	// formatting
	if (1) {
		$sql = preg_replace("/ +((?:update)|(?:select)|(?:insert)) +/i", strtoupper("$1") . "\n\t", $sql);
		$sql = preg_replace("/ +(from|where|group by|limit|having|order) +/i", "\n" . strtoupper("$1") . "\n\t", $sql);

		while (
			preg_match("/([^\t]\t?)([\t]+)([^\t\n]+)\)([^\n])/", $sql)
			|| preg_match("/([^\t])([\t]+)([^\t\n]*?)\(([^\n])/", $sql)
		) {
			$sql = preg_replace("/([^\t]\t?)([\t]+)([^\t\n]+)\)([^\n])/", "$1$2$3\n$2)\n$2$4", $sql,1);
			$sql = preg_replace("/([^\t]\t?)([\t]+)([^\t\n]+)\)$/", "$1$2$3\n$2)\n$2", $sql,1);
			$sql = preg_replace("/([^\t])([\t]+)([^\t\n]*?) *\(([^\n])/", "$1$2$3(\n$2\t$4", $sql, 1);
		}
		$sql = preg_replace("/\t +(and|or) +/i", "\t" . strtoupper("$1"), $sql);
		while (preg_match("/([^\t]?)([\t]*)([^\t\n]+)(and|or) +/i", $sql))
		{
			$sql = preg_replace("/([^\t]?)([\t]*)([^\t\n]+)(and|or) +/i", "$1$2$3\n$2" . strtoupper("$4") . " ", $sql);
		}
		while (preg_match("/([^\t]?)([\t]*)([^\t]+)(,)([^\n])/i", $sql))
		{
			$sql = preg_replace("/([^\t]?)([\t]*)([^\t]+)(,)([^\n])/i", "$1$2$3" . strtoupper("$4") . "\n$2$5", $sql);
		}
	}
	// put the data back
	if (1) {
		while (preg_match("/\[\|__\-\-(?:str|int)([0-9]+);[0-9]+\-\-__\|\]/", $sql, $arrMatched))
		{
			$sql = preg_replace("/" . preg_quote($arrMatched[0], "/") . "/", $arrStrData[$arrMatched[1]], $sql);
		}
	}
	$sql = preg_replace("/\t +/", "\t", $sql);
	return "<pre>" . $sql ."</pre>";
}

function quick_fraction($intVal)
{
	$intDecimal = $intVal - floor($intVal);
	$strResult = floor($intVal) > 0 ? floor($intVal) : "";
	if ($intDecimal == 0.25)
		$strResult .= " &frac14;";
	else if ($intDecimal == 0.50)
		$strResult .= " &frac12;";
	else if ($intDecimal == 0.75)
		$strResult .= " &frac34;";
	return $strResult;
}

function preg_replace_all($strMatch, $strReplace, $strData)
{
	while(preg_match($strMatch, $strData)) {
		$strData = preg_replace($strMatch, $strReplace, $strData);
	}
	return $strData;
}

/*
 * Remove layers of dimention from an array
 */
function array_strip($arrData, $intLevel=1)
{
	if ($intLevel < 0)
	{
		throw new Exception("Feature not yet supplied in array_strip([data],<0).");
	}
	if (
		!is_array($arrData)
		&& !is_object($arrData)
	)
		return $arrData;
	$arrReturn = array();
	foreach ($arrData as $intKey => $arrItem)
	{
		if (
			!is_array($arrItem)
			&& !is_object($arrItem)
		)
			$arrReturn[] = $arrItem;
		else
		{
			foreach ($arrItem as $intKey => $arrItem)
			{
				$arrReturn[] = $arrItem;
			}
		}
	}
	$intLevel--;
	if (!$intLevel)
		return $arrReturn;
	return array_strip($arrReturn, $intLevel);
}

/*
 * Create a string of random numbers.
 */
function rand_num_string($intLength=20)
{
	$strBarCode = "";
	while (strlen($strBarCode) < $intLength)
	{
		$strBarCode .= (string) rand(0, 999999999);
	}
	return substr($strBarCode, 0, $intLength);
}
function msfloat($strMicrotime=FALSE)
{

	if ($strMicrotime)
		$strMicrotime = microtime();
	list($usec, $sec) = explode(" ", microtime());
	return (((float) $usec + (float) $sec)*10000);
}

function nksort(&$arrData)
{
	uksort($arrData, 'strnatcasecmp');
}

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

function RIJNDAEL_encrypt($text, $key){

    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $text, MCRYPT_MODE_ECB, $iv));

}

function RIJNDAEL_decrypt($text, $key){

    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, base64_decode($text), MCRYPT_MODE_ECB, $iv));

}

/*
 * array_clean_slashes ( array $mixed )
 * Loop through an array or object recusivly and remove back slashes
 */
function array_clean_slashes($arrParams)
{
	foreach ($arrParams as $intKey => $Value) {
		if (is_array($Value) || is_object($Value))
		{
			if (is_array($arrParams))
				$arrParams[$intKey] = array_clean_slashes($Value); // Recursion
			else
				$arrParams->$intKey = array_clean_slashes($Value);
		}
		else
		{
			//$Value = preg_replace("/\\\\+(.)/", "$1", $Value);
			$Value = stripcslashes($Value);
			if (is_array($arrParams))
				$arrParams[$intKey] = $Value;
			else
				$arrParams->$intKey = $Value;
		}
	}
	return $arrParams;
}

/*
 * array_clean_sql ( array $mixed )
 * Run mysql_real_escape_string and trim recusivly on any single or multi dimentional
 * array.
 */
function array_clean_sql($arrParams)
{
	foreach ($arrParams as $intKey => $Value) {
		if (is_array($Value) || is_object($Value))
		{
			if (is_array($arrParams))
				$arrParams[$intKey] = array_clean_sql($Value); // Recursion
			else
				$arrParams->$intKey = array_clean_sql($Value);
		}
		else
		{
			if (is_string($Value))
				$Value = mysql_real_escape_string($Value);
			if (is_array($arrParams))
				$arrParams[$intKey] = $Value;
			else
				$arrParams->$intKey = $Value;
		}
	}
	return $arrParams;
}

/*
 * Remove dimentions from an array
 */
function array_flatten2($arrInput, $intMaxRecursion=0, $intCurrentRecursion=0)
{
	if (!is_array($arrInput))
		throw new Exception("Invalid argument supplied to array_flatten(...) in bootstrap.");
	$arrResult = array();
	foreach ($arrInput as $key => $value) {
		if (is_array($value)) {
			$arrResult = array_merge($arrResult, array_flatten($value, $intMaxRecursion, ++$intCurrentRecursion));
		}
		else {
			$arrResult[$key] = $value;
		}
	}
	return $arrResult;
}

/*
 * Retrieve a collection of values from an array
 * this is a legacy function. use array_extract2
 * this function was too much designed around a specific purpose
 * rather than as an independent tool
 */
function array_extract()
{
	$arrArgs = func_get_args();
	if (count($arrArgs) < 2)
		throw new Exception("Invalid number of arguments supplied to array_extract(...) in bootstrap.");
	$arrData = array_pop($arrArgs);
	if (is_object($arrData))
		$arrData = (array) $arrData;
	if (!is_array($arrData))
		throw new Exception("Invalid source data supplied to array_extract(...) in bootstrap.");
	if (!count($arrData))
		return array();
	$arrArgs = array_flatten($arrArgs);
	$arrResult = array();
	foreach ($arrArgs as $strValue)
	{
		if (isset($arrData[$strValue]))
			$arrResult[$strValue] = $arrData[$strValue];
		else
			$arrResult[$strValue] = "";
	}
	return $arrResult;
}

function array_extract2()
{
	$arrArgs = func_get_args();
	if (count($arrArgs) < 2)
		throw new Exception("Invalid number of arguments supplied to array_extract(...) in bootstrap.");
	$arrData = array_pop($arrArgs);
	if (!is_array($arrData) && !is_object($arrData))
		throw new Exception("Invalid source data supplied to array_extract(...) in bootstrap.");
	$arrArgs = array_fill_keys(array_flatten($arrArgs), 1);
	if (!count($arrArgs))
		return array();
	$arrResult = array();
	foreach ($arrArgs as $mixedKey => $intOne)
	{
		$arrResult[$mixedKey] = array();
	}
	if (!count($arrData))
		return $arrResult;
	foreach ($arrData as $mixedKey => $mixedData)
	{
		if (
			is_array($mixedData)
			|| is_object($mixedData)
		) {
			if (!count($mixedData))
				continue;
		}
		else if (!strlen($mixedData))
			continue;

		if (isset($arrArgs[$mixedKey]))
		{
			$arrResult[$mixedKey][$mixedData] = $mixedData;
		}
		else if (
			is_object($mixedData)
			|| is_array($mixedData)
		) {
			$arrNewResult = array_extract2(array_keys($arrArgs), $mixedData);
			if (count($arrNewResult))
			{
				array_merge_push($arrResult, $arrNewResult, NO_PUSH);
			}
		}
	}
	return $arrResult;
}


/*
 * Function: array_merge_push
 * Description: Recursively apply merge with associative arrays and
 * push to incremental arrays
 * Details:
 * - If keys of an array are integers it uses a push on
 * each item.
 * - If you add a string to an array it does array push
 * - If you provide keys of hash it does merge
 */

function array_merge_push(&$mixedOriginal, $mixedPush)
{
	if (
		!is_array($mixedOriginal)
		&& !is_object($mixedOriginal)
	)
		throw new Exception("The first parameter of function `array_merge_push` must either an array or object.");
	$arrPushList = func_get_args();
	array_shift($arrPushList);

	// parse and collect flags
	$arrFlags = array("FLAGS_KEY_267jxsv835s2jMDF6" => TRUE);
	foreach ($arrPushList as $intKey => $mixedParam)
	{
		if ($mixedParam == NO_PUSH)
		{
			$arrFlags[NO_PUSH] = TRUE;
			unset($arrPushList[$intKey]);
		}
		else if (
			is_array($mixedParam)
			&& isset($mixedParam["FLAGS_KEY_267jxsv835s2jMDF6"])
		) {
			foreach ($mixedParam as $mixedParam2)
			{
				if ($mixedParam2 == NO_PUSH)
					$arrFlags[NO_PUSH] = TRUE;
			}
			unset($arrPushList[$intKey]);
		}
	}

	foreach ($arrPushList as $mixedPush)
	{
		if (
			!is_array($mixedPush)
			&& !is_object($mixedPush)
		)
			$mixedPush = array($mixedPush);

		foreach ($mixedPush as $intKey => $mixedItem)
		{
			unset($mixedOriginalChild);
			if (
				is_array($mixedOriginal)
				&& isset($mixedOriginal[$intKey])
			)
				$mixedOriginalChild = &$mixedOriginal[$intKey];
			else if (
				is_object($mixedOriginal)
				&& isset($mixedOriginal->$intKey)
			)
				$mixedOriginalChild = &$mixedOriginal->$intKey;
			if (
				is_int($intKey)
				&& is_array($mixedOriginal)
				&& !isset($arrFlags[NO_PUSH])
			) {
				print "aaa";
				// push
				$mixedOriginal[] = $mixedItem;
			}
			else if (
				isset($mixedOriginalChild)
				&& !is_object($mixedOriginalChild)
				&& !is_array($mixedOriginalChild)
			) {
				// merge
				$mixedOriginalChild = $mixedItem;
			}
			else
			{
				if (!isset($mixedOriginalChild))
				{
					// merge
					if (is_array($mixedOriginal))
						$mixedOriginal[$intKey] = $mixedItem;
					if (is_object($mixedOriginal))
						$mixedOriginal->$intKey = $mixedItem;
				}
				else
				{
					// recurse
					array_merge_push($mixedOriginalChild, $mixedItem, $arrFlags);
				}
			}
		}
	}
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

function msort()
{
	$params = func_get_args();
	$array = array_pop($params);
	if (!is_array($array))
		return false;

	$multisort_params = array();
	foreach ($params as $i => $param)
	{
		if (is_string($param))
		{
			${"param_$i"} = array();
			foreach ($array as $index => $row)
			{
				if (gettype($row) == 'object')
					${"param_$i"}[$index] = strtolower($row->$param);
				else
					${"param_$i"}[$index] = strtolower($row[$param]);
			}
		}
		else
			${"param_$i"} = $params[$i];

		$multisort_params[] = &${"param_$i"};
	}
	$multisort_params[] = &$array;
	call_user_func_array("array_multisort", $multisort_params);
	return $array;
}


/*
 * array_hash ( string $key, mixed $array_or_string, [array $array])
 * Loop through an array of objects and reconstruct the array where the key is
 * now what is specified in the first parameter.
 */
function array_hash()
{
	$arrArgs = func_get_args();
	//var_dump($arrArgs);
	if (count($arrArgs) < 2)
		throw new Exception("Invalid number of arguments supplied to array_hash(...) in bootstrap.");
	$arrList = array_pop($arrArgs);
	$arrArgs = array_flatten($arrArgs);
	if (is_null($arrList))
		return array();
	if (!is_array($arrList))
		throw new Exception("Invalid list array supplied for array_hash in bootstrap.");
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

/*
 * in an array turn all objects in one of its values
 */
function object_extract($strValue, $mixData)
{
	array_walk_recursive($mixData, '_object_extract', $strValue);
	return $mixData;
}
function _object_extract(&$mixData, $strVar, $strValue)
{
	if (is_object($mixData))
		$mixData = $mixData->$strValue;
}

function array_fill_recursive($strValue, $mixData)
{
	array_walk_recursive($mixData, '_array_fill_recursive', $strValue);
	return $mixData;
}
function _array_fill_recursive(&$mixData, $strVar, $strValue)
{
	$mixData = $strValue;
}

function array_bubble_hash_old($strKey, $varListOrSortBy, $mixListOrKey=NULL, $arrList=NULL)
{
	if (is_null($arrList) && !is_string($varListOrSortBy))
		$arrList = $varListOrSortBy;
	else if (is_null($arrList) && !is_string($mixListOrKey))
		$arrList = $mixListOrKey;
	else if (!is_string($varListOrSortBy))
		throw new Exception("Invalid second argument supplied in array_bubble_hash_old in bootstrap.");
	if (!is_array($arrList))
		throw new Exception("Invalid list array supplied for array_bubble_hash_old in bootstrap.");
	if (!is_string($strKey))
		throw new Exception("Invalid argument supplied for array_bubble_hash_old in bootstrap.");

	$arrResult = array();
	foreach ($arrList as $varItem)
	{
		$arrItem = (array) $varItem;
		if (isset($arrItem[$strKey]))
		{
			$arrResult[$arrItem[$strKey]][] = $varItem;
		}
	}
	if (is_string($varListOrSortBy))
	{
		foreach ($arrResult as $strResultKey => $arrItems)
		{
			if (is_string($mixListOrKey))
				$arrResult[$strResultKey] = array_hash($varListOrSortBy, $mixListOrKey, $arrResult[$strResultKey]);
			else
				$arrResult[$strResultKey] = array_hash($varListOrSortBy, $arrResult[$strResultKey]);
			ksort($arrResult[$strResultKey]);
		}
	}
	return $arrResult;
}

function br2nl($string, $line_break=PHP_EOL) {
    $patterns = array(
                        "/(<br>|<br \/>|<br\/>)\s*/i",
                        "/(\r\n|\r|\n)/"
    );
    $replacements = array(
                            PHP_EOL,
                            $line_break
    );
    $string = preg_replace($patterns, $replacements, $string);
    return $string;
}

function array_in_array($arrNeedles, $arrHaystack)
{
	foreach ($arrNeedles as $strNeedle)
	{
		if (in_array($strNeedle, $arrHaystack))
			return TRUE;
	}
	return FALSE;
}



function fixspecialchars($strVal) {
	//return $strVal;
	return preg_replace("/[^\\\\]\\\\n/", "\n", $strVal);
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

function FrequencyTextToSingular ($strText)
{
	$arrText = array(
		"yearly" => "year",
		"monthly" => "month",
		"weekly" => "week",
		"daily" => "day"
	);
	return $arrText[strtolower($strText)];
}

function json($mixedData) {
	print json_encode($mixedData);
	exit;
}
function js($mixedData) {
	print json_encode($mixedData);
}
function first($arrItem) {
	foreach ($arrItem as $objResult) {
		return $objResult;
	}
	return FALSE;
}
?>