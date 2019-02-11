<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$year = GlobalSettings::getChidonYear();
$year = 5778;
$donation = $_POST['donation_info'];

//*************** LOAD AUTHORIZE FUNCTIONS *********************/
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/AuthorizeAPIRequest.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/CustomerProfile.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/PaymentProfile.php';

use classes\authorize\AuthorizeAPIRequest;
use classes\authorize\CustomerProfile;
use classes\authorize\PaymentProfile;

function checkMandatory( $values ) {
  $missing = [];

  // map of needed fields with their more user friendly description
  $mandatory = [
    'email'           =>  'Email Address', 
    'name'            =>  'Donor Name',
    'display_name'    =>  'Display Name',
    'num'             =>  'Credit Card Number',
    'exp'             =>  'Credit Card Expiry',
    'cvv'             =>  'Security Code',
    'address'         =>  'Billing Address',
    'city'            =>  'Billing City',
    'state'           =>  'Billing State',
    'zip'             =>  'Billing Zip',
    'country'         =>  'Billing Country'
  ];

  foreach ( $values as $k => $v ) {
    if ( is_array( $v ) ) $missing += checkMandatory( $v );
    else {
      if ( in_array( $k, array_keys( $mandatory ) ) ) {
        $val = trim( $v );
        if ( empty( $val ) ) {
          $missing[] = $mandatory[$k];
        }
      }
    }
  }
  return $missing;
}

$missing = checkMandatory( $donation );

// in order to have the alert box show with line breaks, needs to be in this weird format
if ( !empty( $missing ) ) {
  $message = 
'You have not filled out the following mandatory field(s).';
foreach ( $missing as $field ) {
  $message .= 
"
$field";
}

  echo json_encode([
    'success'   =>  false, 
    'message'   =>  $message
  ]);
  exit;
}

// prepare variables for donor table
$name = $donation['name'];
$display_name = $donation['anonymous'] ? 'Anonymous' : $donation['display_name'];
$email = $donation['email'];
$phone = $donation['phone'];

// prepare variables for donation table
$amount = $donation['amount'];

// prepare variables for payment
$cc_info = [];
$cc_info['number'] = $donation['cc']['num'];
$cc_info['exp'] = $donation['cc']['exp'];
$cc_info['cvc'] = $donation['cc']['cvv'];
$cc_info['skip'] = isset( $donation['skip'] ) ? $donation['skip'] : 0;

// prepare billing address
$billing = $donation['cc']['billing'];
$cc_info['address'] = $billing['address'] . ' ' . $billing['apt'];
$cc_info['city'] = $billing['city'];
$cc_info['state'] = $billing['state'];
$cc_info['zip'] = $billing['zip'];
$cc_info['country'] = $billing['country'];

// first process donation
if ( $cc_info['skip'] ) {
  // don't process card at all
  $trans_id = 11111;
  $trans_info = "testing by skipping authorize.net transaction.";
  $response = null;
} else {
  require 'authorize.php';
  $response = chargeCreditCard( $amount, $cc_info );
}

// check response
$msg = '';
$error_msg = '';
if ($response != null) {
  // Check to see if the API request was successfully received and acted upon
  if ($response->getMessages()->getResultCode() == "Ok") {
      // Since the API request was successful, look for a transaction response
      // and parse it to display the results of authorizing the card
      $tresponse = $response->getTransactionResponse();
  
      if ($tresponse != null && $tresponse->getMessages() != null) {
          $msg .= " Successfully created transaction with Transaction ID: " . $tresponse->getTransId() . "\n";
          $msg .= " Transaction Response Code: " . $tresponse->getResponseCode() . "\n";
          $msg .= " Message Code: " . $tresponse->getMessages()[0]->getCode() . "\n";
          $msg .= " Auth Code: " . $tresponse->getAuthCode() . "\n";
          $msg .= " Description: " . $tresponse->getMessages()[0]->getDescription() . "\n";

          $trans_id = $tresponse->getTransId();
          $trans_info = $trans_id . ":" . $tresponse->getResponseCode() . ":" . $tresponse->getMessages()[0]->getCode() . ":". $tresponse->getAuthCode() . ":" . $tresponse->getMessages()[0]->getDescription();          
      } else {
          $error_msg .= "Transaction Failed \n";
          if ($tresponse->getErrors() != null) {
              $error_msg .= " Error Code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
              $error_msg .= " Error Message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";
          }
      }
      // Or, print errors if the API request wasn't successful
  } else {
      $error_msg .= "Transaction Failed \n";
      $tresponse = $response->getTransactionResponse();
  
      if ($tresponse != null && $tresponse->getErrors() != null) {
          $error_msg .= " Error Code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
          $error_msg .= " Error Message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";
      } else {
          $error_msg .= " Error Code  : " . $response->getMessages()->getMessage()[0]->getCode() . "\n";
          $error_msg .= " Error Message : " . $response->getMessages()->getMessage()[0]->getText() . "\n";
      }
  }
} else if ( $cc_info['skip'] ) {
  $msg = "Success.";
}

if ( !empty( $msg ) ) {
  // add to donations table 
  $stmt = $MASHPIA_DB->prepare("
    INSERT INTO chidon_donations 
    SET 
        chidon_year = :year, 
        donation_amount = :amount, 
        transaction_id = :trans_id, 
        transaction_info = :trans_info 
  ");
  $res = $stmt->execute([
    ':year'         =>  $year, 
    ':amount'       =>  $amount, 
    ':trans_id'     =>  $trans_id, 
    ':trans_info'   =>  $trans_info
  ]);

  echo json_encode([
    'success'   =>  true,
    'message'   =>  $msg
  ]);
} else {
  echo json_encode([
    'success'     =>  false,
    'message'     =>  $error_msg
  ]);
}
exit;
// // try to make a profile and save it
// $stmt = $MASHPIA_DB->prepare("
//     SELECT 
//         * 
//     FROM
//         chidon_donors
//     WHERE
//         email = :email
// ");
// $res = $stmt->execute([
//   ':email'  =>  $donation['email']
// ]);
// if ( !$res ) $error_msg[] = "Error checking for donor.";
// else {
//   $row = $stmt->fetch();
//   if ( empty( $row ) ) {
//     // create donor
//     $qry = "
//       INSERT INTO chidon_donors 
//       SET 
//           name = :name,
//           display_name = :display_name,
//           phone = :phone, 
//           email = :email
//     ";
//     $stmt2 = $MASHPIA_DB->prepare( $qry );
//     $res2 = $stmt2->execute([
//       ':name'         =>  $name,
//       ':display_name' =>  $display_name,
//       ':phone'        =>  $phone,
//       ':email'        =>  $email
//     ]);
//     if ( $res2 ) {
//         $donor_id = $MASHPIA_DB->lastInsertId();
//     }
//   } else {
//     $donor_id = $row['chidon_donor_id'];
//     $customer_id = $row['authorize_customer_profile_id'];

//     // update donor
//     $qry = "
//       UPDATE chidon_donors 
//       SET 
//           name = :name,
//           display_name = :display_name,
//           phone = :phone
//       WHERE
//           chidon_donor_id = :id
//     ";
//     $stmt2 = $MASHPIA_DB->prepare( $qry );
//     $res2 = $stmt2->execute([
//       ':name'         =>  $name,
//       ':display_name' =>  $display_name,
//       ':phone'        =>  $phone,
//       ':id'           =>  $donor_id
//     ]);
//   }
// }

// // create payment profile (and customer profile if needed)
// $error_msg = [];
// if ( !$customer_id ) {
//   $description = "Customer profile for " . $name;
//   $paymentProfile = PaymentProfile::createBasicArray( $cc_num, $cc_exp, $cc_security );
//   $customerProfile = CustomerProfile::create( 'Chidon_Drive_Donor_' . $donor_id, $email, $description, $paymentProfile );
//   if ( $customerProfile instanceof CustomerProfile ) {
//       $customer_id = $customerProfile->customerProfileId;
//       $payment_id = $customerProfile->paymentProfiles[0]['customerPaymentProfileId'];
//   } else {
//       $error_msg[] = $customerProfile['message'];
//   }
// } else {
//   // create a new payment profile
//   $paymentProfile = PaymentProfile::create( $cc_num, $cc_exp, $cc_security, $customer_id );
//   if ( $paymentProfile instanceof PaymentProfile ) {
//       $payment_id = $paymentProfile->customerPaymentProfileId;
//   } else {
//       // get error
//       $error_msg[] = $paymentProfile['messages']['message'][0]['text'];
//   }
// }

// if ( !empty( $error_msg ) && $donor_id && $customer_id && $payment_id ) {
//   // update tables

// }