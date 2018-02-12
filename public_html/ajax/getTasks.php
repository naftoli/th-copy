<?php $debug = false;
// enable debuging
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}

$id = $_GET['subject'];
$school = isset( $_GET['school'] ) ? $_GET['school'] : 0;
$class = isset( $_GET['grade'] ) ? $_GET['grade'] : 0;
$user = $_GET['user'];
$start = $_GET['start'];
$end = $_GET['end'];
$lang = isset($_GET['lang']) ? $_GET['lang'] : 1;

$version = isset($_GET['v']) ? $_GET['v'] : "1";

require_once '../db.php';
require_once '../class.tasksCustomizationNew.php';

$tc = new TasksCustomizationNew;
$tc->setStart( $start );
$tc->setEnd( $end );
$tc->setType( $user, $class, $school );
$tc->setLang( $lang );
$tasks = $tc->getTasks( $id, $debug );

if($version == "2") {
    $mandatory = [];
    $year = mysql_real_escape_string( isset($_GET['year']) ? $_GET['year'] : "5778" ); // get the year from the request
    $subject_id = mysql_real_escape_string( $_GET['subject'] ); // get the subject that they are asking for
    foreach($tasks as $cat => $details){ // get if each one is mandatory
        $sql = "SELECT * FROM mandatory_cats WHERE cat = \"" . $cat . "\" AND subject_id = " . $subject_id . " AND year = " . $year . " AND lang_id = " . $lang;
        $result = mysql_query( $sql );
        $mandatory[$cat] = mysql_num_rows($result) > 0;
    }
    echo json_encode(["tasks" => $tasks, "mandatory" => $mandatory]); die();
}

echo json_encode($tasks);
?>