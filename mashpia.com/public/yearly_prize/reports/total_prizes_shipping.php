<?
/***************** DEBUGGING SETTINGS **********************/
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
    echo "<h2>Debug log:</h2>";
}
if($debug) echo "<pre>";

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

/***************** EXTERNAL DEPENDENCIES **********************/
require_once (dirname(__FILE__)."/../functions/get_staff.php");
require_once (dirname(__FILE__)."/../functions/get_students.php");
require_once (dirname(__FILE__)."/../functions/get_shipped_marks.php");

// debugging information
//if($debug) print_r($schools);

$school_id = $_GET['school_id']; // set the variable if it is set :-)
$filter = $_GET['filter'];

/***************** GET THE LIST OF STAFF FROM SCHOOLS TABLE **********************/
$schools = []; // list of all schools names for rendering reasons
$school_sql = "SELECT school_id, school_name, yearly_prize_shipping_method FROM schools WHERE chayolei = 1 "; // only chayolei schools
if($school_id) $school_sql .= "AND school_id = $school_id "; // limit by school id
$school_query = mysql_query($school_sql); // limit the query
while($school = mysql_fetch_assoc($school_query)){ // get each row and add it to the array
    $schools[$school["school_id"]] = $school;
}

/***************** HANDLE DATES **********************/
$start_date = $_GET['start_date'];
if($start_date){ // if we have a start date
    $start_date = new DateTime($start_date);
    $start_date = $start_date->format("'Y-m-d'"); // formmat it for mysql
}
$end_date = $_GET['end_date'];
if($end_date){ // if we have an end date
    $end_date = new DateTime($end_date);
    $end_date = $end_date->format("'Y-m-d'"); // format it for mysql
}

$shipped_marks = get_shipped_marks();

$staff_full = get_staff($school_id);
$students_full = get_students($school_id, $start_date, $end_date, $debug);

$staff = [];
$students = [];
foreach($schools as $school_id => $school){
    // filter staff:
    foreach($staff_full[$school_id] as $staff_member) {
        $shipped = $shipped_marks[$staff_member['type']][$staff_member['id']];
        if($filter == "shipped" && !$shipped) continue;
        if($filter == "unshipped" && $shipped) continue;
        $staff[$school_id][] = $staff_member;;
    }
    // filter students:
    foreach($students_full[$school_id] as $student) {
        $shipped = $shipped_marks["user"][$student['user_id']];
        if($filter == "shipped" && !$shipped) continue;
        if($filter == "unshipped" && $shipped) continue;
        $students[$school_id][] = $student;
    }
}

//if($debug) print_r($staff);
//if($debug) print_r($students);

// end debugging
if($debug) echo "</pre>";

function get_school_info($school_id){
    $sql = "SELECT school_name, shipping_address1, shipping_city, shipping_state, ".
        "shipping_postal, yearly_prize_shipping_method as 'yp_shipping' FROM schools WHERE school_id='$school_id';";
    $query = mysql_query($sql);
    if($query){
        return mysql_fetch_assoc($query);
    }
    return false;
}

function get_school_admins($school_id){
    $admins = [];
    $sql = "SELECT CONCAT(first, ' ', last) as 'name' from admins join admin_auths using (admin_id) where id='$school_id' and first != ''";
    $query = mysql_query($sql);
    while($row= mysql_fetch_assoc($query)){
        $admins[] = $row['name'];
    }
    return $admins;
}

?>
<!DOCTYPE html">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Yearly Prize Totals Report Printout</title>
        <link href="../css/printout.css?v=1.1" rel="stylesheet" type="text/css">
    </head>
    <body onload='window.print();'>
        <? foreach($schools as $school_id => $school){
            $school_info = get_school_info($school_id);
            $school_admins = get_school_admins($school_id);
            $total = count($staff[$school_id]) + count($students[$school_id]);?>
            
            
            <h1><?=$school['school_name']?> - <?=$total?> Tehillims</h1>
            <h3>Shipping Method: <?=$school_info['yp_shipping'] == "deliver" ? "Delivery" : "Pickup" ;?></h3>
            <h3>Address:<br/>
            <? if ($school_info['shipping_address1']) {?>
                <?=$school_info['shipping_address1']?>, <?=$school_info['shipping_city']?>
                <?=$school_info['shipping_state']?> <?=$school_info['shipping_postal']?>
            <?} else {?>
                N/A
            <?}?>
            </h3>
            <h3>Attn: <?=join(", ", $school_admins);?></h3>
            
            <h2>Staff - <?=count($staff[$school_id]);?> Tehillims:</h2>
            <? foreach($staff[$school_id] as $staff_member) {?>
                
                <div class="shipping_box">
                    <div class="shipping_inner_box">
                        <span class="checkbox"></span>
                        <h3><?=$staff_member['name']?></h3>
                    </div>
                </div>
                
            <?}?>
            <h2>Students - <?=count($students[$school_id]);?> Tehillims:</h2>
            <? foreach($students[$school_id] as $student){$registered = new DateTime($student['user_registered']);?>
                
                <div class="shipping_box">
                    <div class="shipping_inner_box">
                        <span class="checkbox"></span>
                        <h3><?=$student['name']?><br/><?=$student['class_grade'] . ($student['class_sub'] ? "-".$student['class_sub'] : "");?></h3>
                    </div>
                </div>
                
            <?}?>
            
            <div class="page_break"></div>
        <?}?>
    </body>
</html>