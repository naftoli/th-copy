<?php
/***************** DEBUGGING SETTINGS **********************/
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
    //echo "<h2>Debug log:</h2>";
}
if($debug) echo "<pre>";

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
require_once(dirname(__file__).'/../../functions/get_shipped_marks.php');

/***************** HANDLE DATES **********************/
$start_date = $_POST['start_date'];
if($start_date){ // if we have a start date
    $start_date = new DateTime($start_date);
    $start_date = $start_date->format("'Y-m-d'"); // formmat it for mysql
}
$end_date = $_POST['end_date'];
if($end_date){ // if we have an end date
    $end_date = new DateTime($end_date);
    $end_date = $end_date->format("'Y-m-d'"); // format it for mysql
}

if(!$start_date || !$end_date) {
    echo "Error: Start date and end date must be provided"; die();
}

$sql = "SELECT user_id FROM users WHERE user_registered >= $start_date AND user_registered <= $end_date;";
$marked = get_shipped_marks();

$marked = $marked['user'];

if($debug){
    echo "<pre>";
    print_r($marked);
    echo "</pre>";
}


$query = mysql_query($sql);

$total_created = 0;
$total_failed = 0;

while ($row = mysql_fetch_assoc($query)){
    $user_id = $row['user_id'];
    if(!$marked[$user_id]){
        $insert_sql = "INSERT INTO yearly_prize_shipping (id, type, year, shipped) VALUES ($user_id, 'user', 5778, 1);";
        //echo "$user_id - $insert_sql<br/>";
        $result = mysql_query($insert_sql);
        $result ? $total_created++ : $total_failed++; // check if the query worked and add up the results accordingly
    }
}

$total = $total_created + $total_failed;

echo "$total Students To be marked<br/>";
echo "$total_created/$total Successfull.<br/>$total_failed/$total Failed";
