<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/reports/inc/header.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/CustomerProfile.php';
use classes\authorize\CustomerProfile;

// parse the params...
$th_chidon_chap_id  = clean_post_param("chap_id");
//$action             = clean_post_param("action");
$school_id          = clean_post_param("school_id");

//***************** LOAD CURRENT YEAR **********************/
require_once($_SERVER['DOCUMENT_ROOT']."/class.globalSettings.php");
$year = GlobalSettings::getChidonYear();

$fields = ['school_id', 'first_name', 'last_name', 'phone', 'email', 'dob', 'chidon_type', 'acc_name', 'acc_address', 'acc_phone', 'vehicle', 's_size', 'chap_type'];

$sql_data = [];
// go through each feild and add it to the dataset...
foreach($fields as $key) {
    $value = clean_post_param($key);
    // if we are missing a paramater, return an error....
    if(!isset($_POST[$key]) && !in_array($key, ["s_size"])) { // if the key is blank and not excluded from the requirments...
        render_json_error("Error CH-CHP-010: Empty field. All fields are required.");
    } elseif (isset($_POST[$key]) && $value != "") { // do not add feilds that can be blank to the sql data if they are...
        $sql_data[] = "$key = '$value'";
    } elseif (isset($_POST[$key]) && in_array($key, ["s_size"]) && $value == "") { // if sweater is blank set sweater to null
        $sql_data[] = "$key = null";
    }
}

$update_query = mysql_query(
    "UPDATE th_chidon_chaps SET ".implode(", ", $sql_data)." WHERE th_chidon_chap_id='$th_chidon_chap_id'"
);

if(!$update_query){
    render_json_error("Error CH-CHP-011: Could not update chaperone.");
}

if ( isset($_POST['supervisor']) ) {
    include("createChap.php");
    $chap_id = createChap($_POST['supervisor'], $th_chidon_chap_id);
    if ( !$chap_id ) render_json_error("Error CH-CHP-011: Could not create walking supervisor.");
}

// charge card if necessary
if ( $_POST['toCharge'] ) {
    $amount = $_POST['toCharge'];
    // get school info 
    $sql = "select school_name, authorize_customer_profile_id, authorize_payment_profile_id from schools where school_id = " . mysql_real_escape_string( $_POST['school_id'] );
    $result = mysql_query( $sql );
    $row = mysql_fetch_assoc( $result );
    $school = $row['school_name'];
    $description = "$" . $amount . " charged to " . $school . " for creating a walking supervisor";
    if ( $amount > 20 ) $description .= " and buying a sweater";
    $description .= ".";
    $cs = new CustomerProfile( $row['authorize_customer_profile_id'] );
    $response = $cs->chargeCard( $amount, $row['authorize_payment_profile_id'], null, null, $description );

    if ( !is_array( $response ) ) {
        // there was an issue, notify HQ about it
        $to = "chidon@tzivoshashem.org";
        $subject = "Error charging " . $school . " for a walking supervisor";
        $message = "This is the error we received from Authorize: " . $response;
        $headers = [
            'From'      => 'cth@tzivoshashem.org',
            'Reply-To'  => 'cth@tzivoshashem.org'
        ];
        @mail( $to, $subject, $message, $headers );
    }
}

// let the user know that editing is done...
echo json_encode(["success" => true, "sql" => "UPDATE th_chidon_chaps SET ".implode(", ", $sql_data)." WHERE th_chidon_chap_id='$th_chidon_chap_id'"]); die();