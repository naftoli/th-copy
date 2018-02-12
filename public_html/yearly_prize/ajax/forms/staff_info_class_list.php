<?php
/***************** DEBUGGING SETTINGS **********************/
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
    //echo "<h2>Debug log:</h2>";
    //echo "<pre>";
}
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

$school_id = $_POST['school_id'];

if(!$school_id) {
   echo "Invalid Paramaters"; die(); // return the json and kill the script
}

$school_id = mysql_real_escape_string($school_id); // prevent SQL injection
// create and run the query
$class_sql = "SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub;";
$class_query = mysql_query($class_sql);

if(!$class_query){
    echo "Invalid Query"; die(); // return the json and kill the script
}
// create a array to hold the classes
$classes = [];
// add all the rows to the array
while ($row = mysql_fetch_assoc($class_query)){
    $classes[] = $row;
}
// print the array as html
?>
<select id="class_id">
    <? foreach($classes as $class){?>
        <option value="<?=$class['class_id']?>">
            <?=$class['class_grade'].($class['class_sub'] ? " - ".$class['class_sub'] : "")?>
        </option>
    <?}?>
</select>
