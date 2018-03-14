<?php
//ini_set('display_errors', 1);
include($_SERVER['DOCUMENT_ROOT']."/reports/inc/header.php");

$chaperones = isset($_POST["chaperones"]) ? $_POST["chaperones"] : false;
$cc_info    = isset($_POST["cc_info"]) ? $_POST["cc_info"] : false;
$school_id  = clean_post_param("school_id"); // get the school id

if(!$chaperones || !$school_id || count($chaperones) == 0) {
    render_json_error("Error CH-CHP-020: Invalid request");
}

//***************** LOAD CURRENT YEAR **********************/
require_once($_SERVER['DOCUMENT_ROOT']."/class.globalSettings.php");
$year = GlobalSettings::getChidonYear();

//***************** REGISTER SCHOOL **********************/
$school_exists = mysql_query(
     " SELECT th_chidon_schools_id FROM th_chidon_schools "
    ." WHERE school_id = " . $school_id . " "
    ." AND year = " . $year . " "
    ." AND registered = 1 "
);
if (mysql_num_rows($school_exists) == 0) {
    mysql_query(
         " INSERT INTO th_chidon_schools "
        ." SET school_id = " . $school_id . ", "
        ." year = " . $year . ", "
        ." registered = 1"
    );
}

// if credit card info was provided, charge the card....
if($cc_info){
    // split out the data as authorize.php wants it...
    $amount     = mysql_real_escape_string($cc_info['amount']);
    $card_num   = mysql_real_escape_string($cc_info['ccnum']);
    $exp_date   = mysql_real_escape_string($cc_info['ccexp']);
    $zip        = mysql_real_escape_string($cc_info['cczip']);
    // data needed for authorize.php
    $first_name =''; $last_name = ''; $address = ''; $state = '';
    $description = "Chaperone Registration for Chidon Shabbaton " . $year . " - School #:" . $school_id . "; Number of Chaperones paid for: " . count($chaperones);
    
    if ($school_id != 82 || ($school_id == 82 && $card_num != 4111111111111111)) { // if this is not 4111 1111 1111 1111 for A Academy only....
        require($_SERVER['DOCUMENT_ROOT'].'/authorize.php');
        if ($response_array[0] == 1) {
            // success
            $strResponse =  $response_array[3] . ':' . $response_array[4] . ':' . 
                            $response_array[6] . ':' . $response_array[9];
            // create the chaperones...
            $chaps = createChpaerones($chaperones, $year);
            $description .= " Chap IDs: " . implode(',', $chaps);
            $sql = "INSERT INTO th_chidon_chap_payments "
                ." set school_id = " . $school_id . ", "
                ." paid = " . $amount . ", "
                ." approval = '" . $strResponse . "', "
                ." description = '" . $description . "'";
            if (mysql_query($sql)) echo json_encode(["success" => true]);
            else render_json_error("Could not save record of Credit Card transaction. Please email chidon@tzivoshashem.org");
        } else {
            render_json_error("Credit Card Error: ".$response_array[3]);
        }
    } else { // if this is the test account pretend that the transaction was a success...
        $chaps = createChpaerones($chaperones, $year);
        $description .= " Chap IDs: " . implode(',', $chaps);
        $strResponse = "TEST TRANSACTION"; // fake response....
        $sql = "INSERT INTO th_chidon_chap_payments "
            ." set school_id = " . $school_id . ", "
            ." paid = " . $amount . ", "
            ." approval = '" . $strResponse . "', "
            ." description = '" . $description . "'";
        if (mysql_query($sql)) echo json_encode(["success" => true]);
        else render_json_error("Could not save record of Credit Card transaction. Please email chidon@tzivoshashem.org");
    }
} else {
    createChpaerones($chaperones, $year);
    echo json_encode(["success" => true]);
}

//***************** CREATE CHAPERONES **********************/
function createChpaerones($chaperones, $year) {
    $chaperone_ids = [];
    // go through each chaperone
    foreach($chaperones as $chaperone) {
        $school_id      = mysql_real_escape_string($chaperone['school_id']);
        $first_name     = mysql_real_escape_string($chaperone['first_name']);
        $last_name      = mysql_real_escape_string($chaperone['last_name']);
        $email          = mysql_real_escape_string($chaperone['email']);
        $phone          = mysql_real_escape_string($chaperone['phone']);
        $dob            = mysql_real_escape_string($chaperone['dob']);
        $chidon_type    = mysql_real_escape_string($chaperone['chidon_type']);
        $vehicle        = intval(mysql_real_escape_string($chaperone['vehicle']));
        $sweater        = intval(mysql_real_escape_string($chaperone['sweater']));
        $full_program   = intval(mysql_real_escape_string($chaperone['full_program']));
        // accomidation info...
        $acc_name       = mysql_real_escape_string($chaperone['acc_name']);
        $acc_address    = mysql_real_escape_string($chaperone['acc_address']);
        $acc_cross_st   = mysql_real_escape_string($chaperone['acc_cross_st']);
        $acc_phone      = mysql_real_escape_string($chaperone['acc_phone']);
        
        $chaperone_sql = "INSERT INTO th_chidon_chaps "
                ." SET school_id = " . $school_id . ", "
                ." name = '" . $first_name . ' ' . $last_name . "', "
                ." first_name = '" . $first_name . "', "
                ." last_name = '" . $last_name . "', "
                ." year = '$year', "
                ." dob = '" . $dob . "', "
                ." acc_name = '" . $acc_name . "', "
                ." acc_address = \"" . $acc_address . "\", "
                ." acc_cross_st = \"" . $acc_cross_st . "\", "
                ." acc_phone = '" . $acc_phone . "', "
                ." vehicle = " . $vehicle . ", "
                ." phone = '" . $phone . "', "
                ." email = '" . $email . "', "
                ." full_program = " . $full_program;
        // get the sweater size if needed...
        if($sweater == 1){
            $sweater_size = mysql_real_escape_string($chaperone['sweater_size']);
            $chaperone_sql .= ", sweater = 1, sweater_size = '" . $sweater_size . "'";
        }
        
        if(($full_program || $sweater) && !isset($_POST['cc_info'])) { // if they are getting a sweater or are in the program and did not pay....
            render_json_error("Error CH-CHP-021: Payment info required but not provided.");
        }
        
        if (mysql_query($chaperone_sql)) { // if we can create the chaperone...
            $chaperone_ids[] = mysql_insert_id(); // insert the ID into the array...
            // send email to chaperone
            $to = $email;
            $subject = "Chidon Shabbaton Chaperone";
            $message = "Congratulations! You are now registered as a Chaperone for the Chidon Shabbaton " . $year . "! Please be in touch with your school's Chidon Coordinator for more information.";
            $headers = 'From: chidon@tzivoshashem.org';
            @mail($to, $subject, $message, $headers);
        }
    }
    
    return $chaperone_ids;
}
