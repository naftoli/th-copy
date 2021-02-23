<?
$admin_auth = array('school'); 
require('../../header.php');

if( isset($_GET['debug'])){
	//error_reporting(E_ALL);
    ini_set("display_errors", 1);
}

$id = isset($_GET['id']) ? mysql_real_escape_string($_GET['id']) : false;
if (!id){
    http_response_code(302);
    header('Location: ./index.php');
    exit;
}
$sql = "SELECT * FROM chidon_prizes WHERE prize_id = '$id'";
$query = mysql_query($sql);
$prize = mysql_fetch_assoc($query);

if (!$prize){
    http_response_code(302);
    header('Location: ./index.php');
    exit;
}

$sql = "DELETE FROM chidon_prizes WHERE prize_id = '$id'";
$query = mysql_query($sql);
$prize = mysql_fetch_assoc($query);

http_response_code(302);
header('Location: ./index.php');
