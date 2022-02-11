<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once '../../db.php';
$school_id = mysql_real_escape_string($_POST['school_id']);

if ($school_id) {
    $sql = "update schools set chidon_confirmed_5782 = 1 where school_id = " . $school_id;
    if (mysql_query($sql)) echo json_encode(['success' => true]);
    else echo json_encode(['success' => false]);
}
else echo json_encode(['success' => false]);
