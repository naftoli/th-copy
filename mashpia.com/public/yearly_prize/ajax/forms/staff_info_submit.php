<?
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

/***************** IMPORTS **********************/
require_once(dirname(__FILE__)."/../../classes/Staff.php");
// get the type of staff
$staff_type = $_POST['staff_type'];

$name = mysql_real_escape_string($_POST['staff_name']);
$email = mysql_real_escape_string($_POST['staff_email']);
$cell_number = mysql_real_escape_string($_POST['staff_number']);
$work_number = mysql_real_escape_string($_POST['staff_work_number']);
$position = mysql_real_escape_string($_POST['staff_position']);

/***************** UPDATE SCHOOLS TABLE **********************/
// principals are tied to the school directly, edit there
if($staff_type == "school"){
    $school_id = $_POST['staff_id']; // get the admin_id
    // update the table
    $sql = "UPDATE schools SET principal=\"".$name . "\", principal_email='$email', principal_number='$cell_number', principal_position = '$position', ".
            " principal_number_work='$work_number' WHERE school_id='$school_id';";
    if (mysql_query($sql)) {
        echo json_encode(["success" => true]); die();
    }
    
    echo json_encode(["success" => false, "error" => "Could not update staff due to server error. Please contact support", "details" => $sql, "POST" => $_POST]); die();
}

/***************** UPDATE ADMINS TABLE **********************/
// admin's that are tied to the school (not teachers) - update the admins table
if($staff_type == "admin"){
    $admin_id = $_POST['staff_id']; // get the admin_id
    // split the name into the correct parts
    $name = explode(" ", $name);
    $last_name = array_pop($name); // get the last name (and remove it from the name)
    $first_name = join(" ", $name); // join the rest of the name back together
    
    $sql = "UPDATE admins SET first=\"".$first_name."\", last=\"".$last_name."\", admin_email='$email', ".
            "admin_phone_mobile='$cell_number', admin_phone_work='$work_number' WHERE admin_id='$admin_id';";
    
    $sql_pos = "UPDATE admin_auths SET position='$position' WHERE admin_id='$admin_id' AND auth='school';";
    
    if (mysql_query($sql) && mysql_query($sql_pos)) { // if the query runs well
        echo json_encode(["success" => true]); die();
    }
    
    echo json_encode(["success" => false, "error" => "Could not update staff due to server error. Please contact support", "POST" => $_POST]); die();
}

/***************** UPDATE ADMINS AND CLASSES TABLE **********************/
// teachers are tied to the school and are in the admin and classes table
if($staff_type == "teacher"){
    $admin_id = $_POST['staff_id']; // get the admin_id
    $cell = $number; // recast since email template was copy-pasted from updateTeacher.php in /ajax/
    // get the class id
    $sql = "SELECT id FROM admin_auths WHERE auth = 'class' AND admin_id = " . $admin_id;
    $row = mysql_fetch_assoc(mysql_query($sql)); // get the row
    $class_id = $row['id']; // and get the class ID from there
    
    // $sql = "UPDATE classes SET class_teacher = \"" . $name . "\", email = '" . $email . "', cell = '" . $cell_number . "' WHERE class_id = " . $class_id;
    // @mysql_query($sql);
    
    $sql = "UPDATE admins SET last = \"" . $name . "\", admin_email = '" . $email . "', admin_phone_work = '" . $work_number .
            "', admin_phone_mobile = '" . $cell_number . "' WHERE admin_id = " . $admin_id;

    if (mysql_query($sql)) {
        // send email to hq about change
        $sql = "SELECT class_grade, class_sub, school_name FROM classes c JOIN schools s USING (school_id) WHERE c.class_id = " . $class_id;
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        $school = $row['school_name'];
        $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';
        $headers[] = 'From: Mashpia Admin <admin@mashpia.com>';
        $to = "cth@mashpia.com";
        $subject = "Teacher Info Changed";
        $msg = "The teacher info from <b>" . $school . " Grade: " . $grade . "</b> has just been changed.<br />
Username: " . $username . "<br />
Password: " . $password . "<br />
Name: " . $name . "<br />
Email: " . $email . "<br />
Phone: " . $cell_number . "<br />";
        mail($to, $subject, $msg, implode("\r\n", $headers));
        
        echo json_encode(["success" => true, "POST" => $_POST]); die(); // send the response and kill the script
    } else {
        echo json_encode(["success" => false, "error" => "Could not update staff due to server error(#SU100-SQL). Please contact support", "details" => [mysql_error(), $sql]]); die();
    }
    
    echo json_encode(["success" => false, "error" => "Could not update staff. Please check your data", "POST" => $_POST, "class_id" => $class_id]); die();
}

/***************** UPDATE STAFF_INFO TABLE **********************/
// staff info
if($staff_type == "staff"){
    if($_POST['staff_id']) {
        $staff = Staff::load($_POST['staff_id']);
        $staff->update($_POST);
    } else {
        unset($_POST['staff_id']); // remove the blank staff id
        $staff = Staff::create($_POST);
    }
    
    if($staff) {
        echo json_encode(["success" => true, "staff" => $staff]);
    } else {
        echo json_encode(["success" => false, "error" => "Could not update staff. Please check inputs"]);
    }
    die(); // end the scirpt
}

