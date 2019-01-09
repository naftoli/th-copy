<?php
ini_set('zlib.output_compression', '1');
ini_set('default_charset', 'UTF-8');
mb_internal_encoding("UTF-8");
error_reporting(E_ALL);
setlocale(LC_MONETARY, 'en_US');
	
if (in_array(strlen($_GET["card_id"]), array(20)))
{
	require('header.php');
	require_once('calendar.php');
	require_once('file_save.php');
	require_once('card_printer.php');
	include('code_processor.php');
	$scan_code = $_GET["card_id"];
	$strResult = process_code($_GET["card_id"]);
	if ($strResult != NULL && !is_array($strResult))
	{
		require_once("cardpop_template.php");
		card_pop_template($strResult);
		exit;
	}
	if (is_array($strResult))
	{
		require_once("cardpop_template.php");
		card_pop_template($strResult["points"] . " point" . ($strResult["points"] > 1 ? "s have" : " has") . " been awarded to you!");
		print '<script>window.parent.add_to_miles(' . $strResult["points"] . ');';
		print '</script>';
		exit;
	}
}

if (strlen($_GET["card_id"]) == 6) {
	require_once("cardpop_template.php");
	exit;
}

$intUser = intval($_COOKIE["user_id"]);
$objLink = mysql_connect('localhost', 'mashpia', 'ShJ1uWcT89Ek6E');
mysql_select_db('mashpiadb');
$strSql = "
	SELECT
		user_code, school_id  
	FROM
		users
	WHERE
		user_id = " . $intUser;
$objResult = mysql_query($strSql);
$strBarCode = "3" . mysql_result($objResult, 0);
$school_id = mysql_result($objResult, 1);
$objCurl = curl_init();
curl_setopt($objCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($objCurl, CURLOPT_COOKIESESSION, TRUE); 
curl_setopt($objCurl, CURLOPT_HEADER, 0);
curl_setopt($objCurl, CURLOPT_COOKIEFILE, "cookiefile");
curl_setopt($objCurl, CURLOPT_COOKIEJAR, "cookiefile");
curl_setopt($objCurl, CURLOPT_FOLLOWLOCATION, 1); 

//if (in_array($school_id, $australian)) $strURL = "https://v2.mashpia.com/v2/kiosk/auto-login/uc/" . $strBarCode;
//else $strURL = "/v2/kiosk/auto-login/uc/" . $strBarCode;
$strURL = "/v2/kiosk/auto-login/uc/" . $strBarCode . "/encrypted/0";
curl_setopt($objCurl, CURLOPT_URL, $strURL);
curl_exec($objCurl);

ob_start();
//if (in_array($school_id, $australian)) $strURL = "https://v2.mashpia.com/kiosk-main/cardpop/card_id/" . $_GET["card_id"];
//else $strURL = "/v2/kiosk-main/cardpop/card_id/" . $_GET["card_id"];
//echo $strURL; exit;
$strURL = "/v2/kiosk-main/cardpop/card_id/" . $_GET["card_id"];
curl_setopt($objCurl, CURLOPT_URL, $strURL);

if (isset($_POST["control"]))
{
	curl_setopt($objCurl, CURLOPT_POST, 1);
	curl_setopt($objCurl, CURLOPT_POSTFIELDS, $_POST);
}
print curl_exec($objCurl);

$strResult = ob_get_contents();
curl_close($objCurl);
ob_end_clean();
print $strResult;
mysql_close($objLink);
?> 