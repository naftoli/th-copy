<?
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

/***************** EXTERNAL DEPENDENCIES **********************/
require_once(dirname(__file__).'/../../functions/get_staff.php');
require_once(dirname(__file__).'/../../functions/get_students.php');
require_once(dirname(__file__).'/../../functions/get_shipped_marks.php');

/***************** GET THE POST PARAMS **********************/
if(!isset($_POST['school_id']) || !isset($_POST['start_date']) || !isset($_POST['end_date'])){ // if the school_id was not submitted
    echo json_encode(["error" => "invalid paramaters"]); die(); // show the error and kill the script
}
$school_id = $_POST['school_id']; // set the variable if it is set :-)
$filter = $_POST['filter'];

/***************** HANDLE DATES **********************/
$start_date = $_POST['start_date'];
if($start_date){ // if we have a start date
    $start_date = new DateTime($start_date);
    $start_date = $start_date->format("'Y-m-d'"); // formmat it for mysql
}
$end_date = $_POST['end_date'];
if($end_date){ // if we have an end date
    $end_date = new DateTime($end_date);
    $end_date = $end_date->format("'Y-m-d 24:59:59'"); // format it for mysql
}

/***************** GET LIST OF USERS MARKED AS SHIPPED **********************/
$shipped_marks = get_shipped_marks();
if($debug) print_r($shipped_marks);

/***************** GET THE LIST OF STAFF FROM SCHOOLS TABLE **********************/
$schools = []; // list of all schools names for rendering reasons
$school_sql = "SELECT school_id, school_name, yearly_prize_shipping_method FROM schools WHERE chayolei = 1 "; // only chayolei schools
if($school_id) $school_sql .= "AND school_id = $school_id "; // limit by school id
$school_query = mysql_query($school_sql); // limit the query
while($school = mysql_fetch_assoc($school_query)){ // get each row and add it to the array
    $schools[$school["school_id"]] = $school;
}
//if($debug) {echo "Schools: "; print_r($schools);}

$staff_full = get_staff($school_id);
$students_full = get_students($school_id, $start_date, $end_date, $debug);

$staff = [];
$students = [];
foreach($schools as $school_id => $school_name){
    // filter staff:
    if(count($staff_full[$school_id]) > 0){
        foreach($staff_full[$school_id] as $staff_member) {
            $shipped = isset($shipped_marks[$staff_member['type']][$staff_member['id']]);
            if($filter == "shipped" && !$shipped) continue;
            if($filter == "unshipped" && $shipped) continue;
            $staff[$school_id][] = $staff_member;;
        }
    }
    // filter students:
    if(count($students_full[$school_id]) > 0){
        foreach($students_full[$school_id] as $student) {
            $shipped = isset($shipped_marks["user"][$student['user_id']]);
            if($filter == "shipped" && !$shipped) continue;
            if($filter == "unshipped" && $shipped) continue;
            $students[$school_id][] = $student;
        }
    }
}
//if($debug) {echo "Staff: "; print_r($staff);}
//if($debug) {echo "Students: "; print_r($students);}
if($debug) echo "</pre>";
/***************** RENDER THE AJAX PAGE **********************/
?>
<div id="total_prizes_box">
<?php foreach($schools as $school_id => $school){?>
    <h2><?=$school["school_name"]?></h2>
    <div id="shipping_method">
        <h4>
            Shipping Method:
            <input type="radio" name="yearly_gift_shipping_<?=$school_id?>" id="yearly_gift_shipping"
                   data-school_id="<?=$school_id?>" value="pickup"
                   <?=$school["yearly_prize_shipping_method"] == "pickup" ? "checked" : ""?>/>Pickup
                   
            <input type="radio" name="yearly_gift_shipping_<?=$school_id?>" id="yearly_gift_shipping"
                   data-school_id="<?=$school_id?>" value="deliver"
                   <?=$school["yearly_prize_shipping_method"] == "deliver" ? "checked" : ""?>/>Delivery
        </h4>
    </div>
    <h3>Staff</h3>
    <p>Toggle All Staff
        <label class="slider-container">
            <input type="checkbox" class="toggle_all" data-type="staff" data-school_id="<?=$school_id?>" /> 
            <span class="slider-span"></span>
        </label>
    </p>
    <table id="table_staff_<?=$school_id?>">
        <thead>
            <th>Shipped</th><th>Name</th><th>Email</th><th>Phone</th><th>Position</th><th>Grade</th>
        </thead>
        <tbody>
            <?php foreach($staff[$school_id] as $staff_member){
                $shipped = isset($shipped_marks[$staff_member['type']][$staff_member['id']]);?>
                <tr>
                    <td>
                        <label class="fancy-check-container">
                            <input type="checkbox" class="shipping_mark" data-id="<?=$staff_member['id']?>" data-type="<?=$staff_member['type']?>"
                                <?=$shipped ? "checked" : ""?>/>
                            <span class="fancy-check"></span>
                        </label>
                    </td>
                    <td><?=$staff_member['name']?></td>
                    <td><?=$staff_member['email']?></td>
                    <td><?=$staff_member['phone']?></td>
                    <td><?=$staff_member['position']?></td>
                    <td><?= $staff_member['class_grade'] ? $staff_member['class_grade'].($staff_member['class_sub'] ? " - ".$staff_member['class_sub'] : "") : "N/A"?>
                    </td>
                </tr>
            <?} // end listing of staff?>
        </tbody>
    </table>
    <h4>Total Staff: <?=count($staff[$school_id])?></h4>
    <h3>Students</h3>
    <p>Toggle All Students
        <label class="slider-container">
            <input type="checkbox" class="toggle_all" data-type="students" data-school_id="<?=$school_id?>" /> 
            <span class="slider-span"></span>
        </label>
    </p>
    <table id="table_students_<?=$school_id?>">
        <thead>
            <th>Shipped</th><th>Serial Number</th><th>Name</th><th>Grade</th><th>Registered</th>
        </thead>
        <tbody>
            <?php foreach($students[$school_id] as $student){
                $registered = new DateTime($student['user_registered']);
                $shipped = isset($shipped_marks["user"][$student['user_id']]);?>
                <tr>
                    <td>
                        <label class="fancy-check-container">
                            <input type="checkbox" class="shipping_mark" data-id="<?=$student['user_id']?>" data-type="user"
                                <?=$shipped ? "checked" : ""?>/>
                            <span class="fancy-check"></span>
                        </label>
                    </td>
                    <td><?=$student['user_serial']?></td>
                    <td><?=$student['name']?></td>
                    <td><?=$student['class_grade'] . ($student['class_sub'] ? " - ".$student['class_sub'] : "");?></td>
                    <td><?=$registered->format("m/d/Y")?></td>
                </tr>
            <?} // end listing of staff?>
        </tbody>
    </table>
    <h4>Total Students: <?=count($students[$school_id])?></h4>
    <h4>Grand Total: <?=count($students[$school_id]) + count($staff[$school_id])?></h4>
<?} // end the loop for each school?>
</div>