<?php
//ini_set('display_errors', 1);
require_once $_SERVER['DOCUMENT_ROOT'] . "/reports/inc/header.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/CustomerProfile.php';
use classes\authorize\CustomerProfile;

// $chaperones = isset($_POST["chaperones"]) ? $_POST["chaperones"] : false;
// $school_id  = clean_post_param("school_id"); // get the school id

// if(!$chaperones || !$school_id || count($chaperones) == 0) {
//     render_json_error("Error CH-CHP-020: Invalid request");
// }

//***************** LOAD CURRENT YEAR **********************/
require_once($_SERVER['DOCUMENT_ROOT']."/class.globalSettings.php");
$year = GlobalSettings::getChidonYear();

function createChap( $info ) {
    global $year;
    $full_program   = 1; // hardcoded as of 2019
    $school_id      = mysql_real_escape_string($info['school_id']);
    $first_name     = mysql_real_escape_string($info['first_name']);
    $last_name      = mysql_real_escape_string($info['last_name']);
    $email          = mysql_real_escape_string($info['email']);
    $phone          = mysql_real_escape_string($info['phone']);
    $dob            = mysql_real_escape_string($info['dob']);
    $chidon_type    = mysql_real_escape_string($info['chidon_type']);
    $vehicle        = intval(mysql_real_escape_string($info['vehicle']));
    //$full_program   = intval(mysql_real_escape_string($chaperone['full_program']));
    // accomidation info...
    $acc_name       = mysql_real_escape_string($info['acc_name']);
    $acc_address    = mysql_real_escape_string($info['acc_address']);
    $acc_phone      = mysql_real_escape_string($info['acc_phone']);
    $chap_type      = mysql_real_escape_string($info['chap_type']);
    
    $chaperone_sql = "INSERT INTO th_chidon_chaps "
            ." SET school_id = " . $school_id . ", "
            ." first_name = '" . $first_name . "', "
            ." last_name = '" . $last_name . "', "
            ." year = '$year', "
            ." dob = '" . $dob . "', "
            ." acc_name = '" . $acc_name . "', "
            ." acc_address = \"" . $acc_address . "\", "
            ." acc_phone = '" . $acc_phone . "', "
            ." vehicle = " . $vehicle . ", "
            ." phone = '" . $phone . "', "
            ." email = '" . $email . "', "
            ." chap_type = " . $chap_type . ", "
            ." chidon_type = '" . $chidon_type . "', "
            ." full_program = " . $full_program;
    // get the sweater size if needed...
    if( $info['s_size'] != '' ) {
        $sweater_size = mysql_real_escape_string($info['s_size']);
        $chaperone_sql .= ", sweater = 1, sweater_size = '" . $sweater_size . "'";
    } else {
        $chaperone_sql .= ", sweater = 0, sweater_size = null";
    }
    if ( isset( $info['purchases'] ) ) {
        $fixString = false;
        foreach ( $info['purchases'] as $field ) {
            if ( $field == 'extra_sweater' ) continue;
            else {
                $fixString = true;
                $chaperone_sql .= $field . " = 1,";
            }
        }
        if ( $fixString ) $chaperone_sql = substr($chaperone_sql, 0, strlen($chaperone_sql - 1));
    }

    if  (mysql_query($chaperone_sql) ) { // if we can create the chaperone...
        return mysql_insert_id();
    } 

    return false;
}

$success = true;
$chap_id = createChap( $_POST['info'] );
if ( !$chap_id ) $success = false;

if ( $success ) {
    // charge card if necessary 
    if ( $_POST['info']['toCharge'] ) {
        $amount = $_POST['info']['toCharge'];
        // get school info 
        $sql = "select school_name, authorize_customer_profile_id, authorize_payment_profile_id from schools where school_id = " . mysql_real_escape_string( $_POST['info']['school_id'] );
        $result = mysql_query( $sql );
        $row = mysql_fetch_assoc( $result );
        $school = $row['school_name'];
        $description = "$" . $amount . " charged to " . $school . " for chidon staff purchases";
        if ( $amount > 20 ) $description .= " and buying a sweater";
        $description .= ".";
        $cs = new CustomerProfile( $row['authorize_customer_profile_id'] );
        $response = $cs->chargeCard( $amount, $row['authorize_payment_profile_id'], null, null, $description );

        if ( !is_array( $response ) ) {
            // there was an issue, notify HQ about it
            $to = "chidon@tzivoshashem.org";
            $subject = "Error charging " . $school . " for chidon staff purchases";
            $message = "This is the error we received from Authorize: " . $response;
            $headers = [
                'From'      => 'cth@tzivoshashem.org',
                'Reply-To'  => 'cth@tzivoshashem.org'
            ];
            @mail( $to, $subject, $message, $headers );
        }
    }

    echo json_encode([
        "success" => true, 
        "message" => "Chaperone(s) successfully created."
    ]);
} else {
    echo json_encode([
        "success" => false, 
        "error"   => "Error creating chaperone(s)."
    ]);
}