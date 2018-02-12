<?php $debug = false;
// enable debuging
if (isset($_POST['debug']) && $_POST['debug'] == "true") {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

if ($admin_user['auth'] != 'super') {
    echo json_encode([
        "success"   => false,
        "error"     => "Invalid Permissions"
    ]);
    die();
}

$mishna_id      = mysql_real_escape_string($_POST['mishna']);
$perek_id       = mysql_real_escape_string($_POST['perek']);
$mesechto_id    = mysql_real_escape_string($_POST['mesechto']);
$lines          = mysql_real_escape_string($_POST['lines']);

if(!$mishna_id || !$perek_id || !$mesechto_id || !$lines){
    echo json_encode([
        "success"   => false,
        "error"     => "Invalid Paramaters"
    ]);
    die();
}

$sql = "UPDATE mishnos SET num_lines = '$lines' WHERE mesechto_id = '$mesechto_id' AND perek = '$perek_id' AND mishna = '$mishna_id'";

if($debug){
    echo json_encode(['post' => $_POST, 'info' => [$mishna_id, $perek_id, $mesechto_id, $lines], 'sql' => $sql]);
    die();
}

echo json_encode([
    "success"   => !!mysql_query($sql),
    "error"     => "Server Error: MS-LINE-SET: Please contact Support."
]);
die();