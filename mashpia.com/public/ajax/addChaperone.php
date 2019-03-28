<?php
//ini_set('display_errors',1);
chdir('../');
require 'db.php';

require_once 'class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$school = intval(mysql_real_escape_string($_POST['school']));
$amount = intval(mysql_real_escape_string($_POST['amount']));
$card_num = mysql_real_escape_string($_POST['ccnum']);
$exp_date = mysql_real_escape_string($_POST['ccexp']);
$zip = mysql_real_escape_string($_POST['cczip']);
$info = $_POST['info'];

// check if school is already registered
$sql = "select th_chidon_schools_id from th_chidon_schools
        where school_id = " . $school . "
        and year = " . $year . "
        and registered = 1";
$result = mysql_query($sql);
if (mysql_num_rows($result) == 0) {
    $sql = "insert into th_chidon_schools
            set school_id = " . $school . ",
            year = " . $year . ",
            registered = 1";
    mysql_query($sql);
}

if ($amount) {
    // run through authorize first
    $first_name ='';
    $last_name = '';
    $address = '';
    $state = '';
    $description = "Chaperone Registration for Chidon Shabbaton " . $year . " - School #:" . $school . "; Number of Chaperones paid for: " . count($info);
    
    if ($school != 82 || ($school == 82 && $card_num != 11111111)) {
        require 'authorize.php';
        if ($response_array[0] == 1) {
            // success
            $strResponse =  $response_array[3] . ':' . 
                            $response_array[4] . ':' . 
                            $response_array[6] . ':' . 
                            $response_array[9];
                
            $chaps = create_chaps($school, $info, $year);
            $description .= " Chap IDs: " . implode(',', $chaps);
            $sql = "insert into th_chidon_chap_payments
                    set school_id = " . $school . ", 
                    paid = " . $amount . ",
                    approval = '" . $strResponse . "',
                    description = '" . $description . "'";
            mysql_query($sql);
        } else {
            echo $response_array[3];
            exit;
        }
    } else {
        $chaps = create_chaps($school, $info, $year);
        $description .= " Chap IDs: " . implode(',', $chaps);
        $sql = "insert into th_chidon_chap_payments
                set school_id = " . $school . ", 
                paid = " . $amount . ",
                approval = '" . $strResponse . "',
                description = '" . $description . "'";
        mysql_query($sql);
    }
} else {
    create_chaps($school, $info, $year);
}

function create_chaps($school_id, $info, $year = false) {
    $chapIDs = array();
    foreach ($info as $chaperone) {
        $fname = mysql_real_escape_string($chaperone['fname']);
        $lname = mysql_real_escape_string($chaperone['lname']);
        $email = mysql_real_escape_string($chaperone['email']);
        $number = mysql_real_escape_string($chaperone['number']);
        $dob = mysql_real_escape_string($chaperone['yy']) . '-' . mysql_real_escape_string($chaperone['mm']) . '-' . mysql_real_escape_string($chaperone['dd']);
        $accName = mysql_real_escape_string($chaperone['accName']);
        $accAddress = mysql_real_escape_string($chaperone['accAddress']);
        $accCrossSt = mysql_real_escape_string($chaperone['accCrossSt']);
        $accPhone = mysql_real_escape_string($chaperone['addPhone']);
        $vehicle = intval(mysql_real_escape_string($chaperone['vehicle']));
        $full = intval(mysql_real_escape_string($chaperone['full']));
        $s_size = mysql_real_escape_string($chaperone['s_size']);
        
        $sql = "insert into th_chidon_chaps "
                ."set school_id = " . $school_id . ", "
                ."name = '" . $fname . ' ' . $lname . "', "
                ."first_name = '" . $fname . "', "
                ."last_name = '" . $lname . "', "
                ."year = '$year', "
                ."dob = '" . $dob . "', "
                ."acc_name = '" . $accName . "', "
                ."acc_address = \"" . $accAddress . "\", "
                ."acc_cross_st = \"" . $accCrossSt . "\", "
                ."acc_phone = '" . $accPhone . "', "
                ."vehicle = " . $vehicle . ", "
                ."phone = '" . $number . "', "
                ."email = '" . $email . "', "
                ."full_program = " . $full;
        if ($s_size) {
            $sql .= ", sweater = 1, sweater_size = '" . $s_size . "'";
        }
        if (mysql_query($sql)) {
            $chapIDs[] = mysql_insert_id();
            // send email to chaperone
            $to = $email;
            $subject = "Chidon Shabbaton Chaperone";
            $message = "Congratulations! You are now registered as a Chaperone for the Chidon Shabbaton " . $year . "! Please be in touch with your school's Chidon Coordinator for more information.";
            $headers = 'From: chidon@tzivoshashem.org';
            @mail($to, $subject, $message, $headers);
        }
    }
    return $chapIDs;
}

echo 0;

