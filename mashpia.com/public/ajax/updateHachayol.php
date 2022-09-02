<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

define( "MASHPIA_AUTH_REQUIRED", true );
include_once("../api/header/header.php");

$data = json_decode(file_get_contents("php://input"));
$user_ids = $data->info;
$success = true;
foreach ($user_ids as $id => $val) {
    $sql = "update users set hachayol = " . myslql_real_escape_string($val) . " where user_id = " . mysql_real_escape_string($id);
    if (!mysql_query($sql)) {
        $success = false;
        break;
    }
}
if ($success) echo json_encode(['success' => $success]);
else echo json_encode(['success' => $success, 'error' => "Error in sql: $sql"]);