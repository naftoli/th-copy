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

include("createChap.php");

$success = true;
if ( isset( $_POST['info']['supervisor'] ) ) {
    // we need to make sure to insert both or nothing
    mysql_query("set autocommit = 0");
    mysql_query("begin");
    $chap_id = createChap( $_POST['info'] );
    $supervisor = createChap( $_POST['info']['supervisor'], $chap_id );
    if ( $chap_id && $supervisor ) {
        mysql_query("commit");
        mysql_query("set autocommit = 1");
    } else {
        $success = false;
        mysql_query("rollback");
        mysql_query("set autocommit = 1");
    }
} else {
    $chap_id = createChap( $_POST['info'] );
    if ( !$chap_id ) $success = false;
}

if ( $success ) {
    // charge card if necessary 
    if ( $_POST['info']['toCharge'] ) {
        $amount = $_POST['info']['toCharge'];
        // get school info 
        $sql = "select school_name, authorize_customer_profile_id, authorize_payment_profile_id from schools where school_id = " . mysql_real_escape_string( $_POST['info']['school_id'] );
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

// if credit card info was provided, charge the card....
//if($cc_info){
    // split out the data as authorize.php wants it...
    // $amount     = mysql_real_escape_string($cc_info['amount']);
    // $card_num   = mysql_real_escape_string($cc_info['ccnum']);
    // $exp_date   = mysql_real_escape_string($cc_info['ccexp']);
    // $zip        = mysql_real_escape_string($cc_info['cczip']);
    
    // calculate total amount to charge
    // $amount = 0;
    // foreach ( $chaperones as $chaperone ) {
    //     $amount += $chaperone['total'];
    // }

    // data needed for authorize.php
    //$first_name =''; $last_name = ''; $address = ''; $state = '';
    // $description = "Chaperone Registration for Chidon Shabbaton " . $year . " - School #:" . $school_id . "; Number of Chaperones paid for: " . count($chaperones);
    
    // //if ($school_id != 82 || ($school_id == 82 && $card_num != 4111111111111111)) { // if this is not 4111 1111 1111 1111 for A Academy only....
    // if ( $amount > 0 ) { // if this is not Avrohom Academy and there's an amount to charge
    //     // find out what the customer profile id and payment profile id is
    //     $school_sql = "select tcs.*, s.authorize_customer_profile_id, s.authorize_payment_profile_id from th_chidon_schools tcs 
    //                     join schools s using (school_id) 
    //                     where year = " . $year . " and school_id = " . $school_id;
    //     $school_result = mysql_query( $school_sql );
    //     if ( !mysql_num_rows( $school_result ) ) {
    //         render_json_error("School has not been registered yet and does not have a payment profile on file.");
    //     } else {
    //         $school = mysql_fetch_assoc( $school_result );
    //         $customer_profile = $school['authorize_customer_profile_id'];
    //         // if we have a specific profile for chidon, choose that, else use schools existing payment profile
    //         $payment_profile = $school['payment_profile_id'] ? $school['payment_profile_id'] : $school['authorize_payment_profile_id']; 
    //     }
        
    //     $customerProfile = new CustomerProfile( $customer_profile, false );
    //     $payment_result = $customerProfile->chargeCard( $amount, $payment_profile, null, null, $description );
    //     if ( !is_array( $payment_result ) ) {
    //         render_json_error( $payment_result ); 
    //     } else {

    //     // require($_SERVER['DOCUMENT_ROOT'].'/authorize.php');
    //     //if ($response_array[0] == 1) {
    //         // success
    //         // $strResponse =  $response_array[3] . ':' . $response_array[4] . ':' . 
    //         //                 $response_array[6] . ':' . $response_array[9];
    //         $strResponse = $payment_result['transactionResponse']['transId'] . ":" . $payment_result['transactionResponse']['messages'][0]['code'] . ":" . 
    //                         $payment_result['transactionResponse']['messages'][0]['description'];

    //         // create the chaperones...
    //         $created = false;
    //         $inserted = false;
    //         $chaps = createChpaerones($chaperones, $year);
    //         if ( $chaps ) $created = true;
    //         $description .= " Chap IDs: " . implode(',', $chaps);
    //         $sql = "INSERT INTO th_chidon_chap_payments "
    //             ." set school_id = " . $school_id . ", "
    //             ." paid = " . $amount . ", "
    //             ." approval = \"" . $strResponse . "\", "
    //             ." description = '" . $description . "'";
    //         if (@mysql_query($sql)) $inserted = true;

    //         if ( $strResponse && $created && $inserted ) {
    //             $finalMessage = "Chaperone(s) have been successfully created.\nHere is the reponse from the credit card processor:\n" . $strResponse;
    //         } else if ( $strResponse && $inserted && !$created ) {
    //             $finalMessage = "Your payment has gone through, however there was an error creating the chaperones on our system. Please contact HQ ASAP.\nHere is the response from the 
    //                 credit card processor:\n" . $strResponse;
    //         } else if ( $strResponse && $created && !$inserted ) {
    //             $finalMessage = "Chaperone(s) have been successfully created. However, we could not save the record of the Credit Card transaction.\n
    //                 Here is the reponse from the credit card processor:\n" . $strResponse;
    //         } else if ( $strResponse && !$created && !$inserted ) {
    //             $finalMessage = "Your credit card has been charged. However there was an error creating the chaperone(s), as well as saving the transaction to our database.
    //                 Please contact HQ ASAP.\nHere is the reponse from the credit card processor:\n" . $strResponse;
    //         }

    //         echo json_encode([
    //             "success" => true, 
    //             "message" => $finalMessage
    //         ]);
    //     } 
    //     // else {
    //     //     render_json_error("Credit Card Error: ".$response_array[3]);
    //     // }
    // } else { // if this is the test account pretend that the transaction was a success...
        // $chaps = createChpaerones($chaperones, $year);
        // $description .= " Chap IDs: " . implode(',', $chaps);
        // if ( $chaps ) {
        //     echo json_encode([
        //         "success" => true, 
        //         "message" => "Chaperone(s) successfully created."
        //     ]);
        // } else {
        //     echo json_encode([
        //         "success" => false, 
        //         "error"   => "Error creating chaperone(s)."
        //     ]);
        // }
    // }
// } else {
//     createChpaerones($chaperones, $year);
//     echo json_encode(["success" => true]);
// }