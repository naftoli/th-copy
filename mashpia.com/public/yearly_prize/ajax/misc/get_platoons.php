<?
/***************** DEBUGGING SETTINGS **********************/
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
    echo "<h2>Debug Log: </h2>";
}
if($debug) echo "<pre>";

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

$school_id = $_POST['school_id'];

if(!$school_id){
    // get the list from the schema and format it
    $platoon_sql = "SELECT SUBSTRING(COLUMN_TYPE,5) as 'options' FROM information_schema.COLUMNS "
        ."WHERE TABLE_SCHEMA='mashpiadb' AND TABLE_NAME='classes' AND COLUMN_NAME='class_grade';";
    if($debug) echo $platoon_sql;
    $row = mysql_fetch_assoc(mysql_query($platoon_sql));
    $row = $row['options']; // get the options column
    $row = substr($row, 1, -1); // get rid of the enclosing ()
    $platoon_options_tmp = str_getcsv($row, ',', "'"); // split it as a csv into an array
    $platoon_options = []; // the array to return
    foreach($platoon_options_tmp as $option){ 
        $platoon_options[$option] = $option; // set the keys to the values for later
    }
} else {
    $platoon_sql = "SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = '$school_id';";
    $query = mysql_query($platoon_sql);
    
    $platoon_options = [];
    while($row = mysql_fetch_assoc($query)){
        $platoon_options[$row['class_id']] = $row['class_grade'] . ( empty( $row['class_sub']) ? '' : "-" . $row['class_sub'] );
    }
}

if($debug) print_r($platoon_options);

if($debug) echo "</pre>";
?>

<select id="class_grade">
    <option value="">All</option>
    <? foreach($platoon_options as $value => $option){ ?>
        <option value="<?=$value?>"><?=$option?></option>
    <?}?>
</select>