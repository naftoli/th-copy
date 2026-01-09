<?php $debug = false;
// enable debuging
if (isset($_GET['debug'])) {
    //error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}

$id = $_GET['subject'];
$start = $_GET['start'];
$end = $_GET['end'];
$school = isset( $_GET['school'] ) ? $_GET['school'] : 0;
$class = isset( $_GET['grade'] ) ? $_GET['grade'] : 0;
$user = isset( $_GET['user'] ) ? $_GET['user'] : 0;
$lang = isset($_GET['lang']) ? $_GET['lang'] : 1;

// convert dates to julian if needed
if (strpos($start, '-') !== false) {
    $dateInfo = explode('-', $start);
    $start = gregoriantojd($dateInfo[1], $dateInfo[2], $dateInfo[0]);
    $dateInfo = explode('-', $end);
    $end = gregoriantojd($dateInfo[1], $dateInfo[2], $dateInfo[0]);
}

$version = isset($_GET['v']) ? $_GET['v'] : "1";

require_once '../db.php';
require_once '../class.tasksCustomizationNew.php';

$tc = new TasksCustomizationNew;
$tc->setStart( $start );
$tc->setEnd( $end );
$tc->setType( $user, $class, $school );
$tc->setLang( $lang );

// check if the parent is set and if so update the parent_id of the tc object
if (isset($_GET['parent'])) {
    $tc->setParentID($_GET['parent']);
};

if (isset($_GET['forStreak'])) {
    $tasks = $tc->getTasksForStreak( $id, $user );
    echo json_encode($tasks); 
    exit;
}

$tasks = $tc->getTasks( $id, $debug );
if ($version == "2") {
    $mandatory = $tc->mandatory;
    echo json_encode(["tasks" => $tasks, "mandatory" => $mandatory, "parent" => $_GET['parent']]); die();
} else {
    echo json_encode($tasks);
}
?>