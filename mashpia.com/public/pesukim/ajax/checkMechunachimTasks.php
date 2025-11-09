<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/pesukim/class.pesukim.php';

$p = new Pesukim($_POST['user_id']);
$tasks = $_POST['mechunachim_tasks'];
$mechunachim = json_decode($_POST['mechunachim'], true);
$start = $_POST['start'];
$end = $_POST['end'];

try {
    $res = [];
    foreach ($tasks as $task_id) {
        foreach ($mechunachim as $mechunach) {
            if (intval($mechunach['verified']) == 1) {
                $res[$task_id][$mechunach['mechunach_id']] = $p->checkMechunachTask($task_id, $mechunach['mechunach_id'], $start, $end);
            }
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

echo json_encode(['success' => true, 'data' => $res]);
?>