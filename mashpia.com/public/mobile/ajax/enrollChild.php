<?php /********** SUPPORT DEBUGGING ***********/
$debug = false;
if (isset($_GET['debug']) || isset($_POST['debug'])){
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
}
/************************ LOAD UP ADMIN OBJECT ************************/
require_once $_SERVER['DOCUMENT_ROOT']."/db.php"; // connect to the database....

$subject_id = mysql_real_escape_string($_POST['subject_id']);
$user_id    = mysql_real_escape_string($_POST['user_id']);

echo json_encode([
    "success"   => !!mysql_query("UPDATE user_tracks SET enrolled = 1 WHERE subject_id = $subject_id AND user_id = $user_id")
]);