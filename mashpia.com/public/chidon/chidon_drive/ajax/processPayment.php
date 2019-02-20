<?php
ini_set('display_errors',1);
require_once __DIR__ . '/../../../api/header/db.php';
require_once __DIR__ . '/../../../class.globalSettings.php';
require __DIR__ . '/../encrypt.php';

$admin = $_COOKIE['chidon_admin'];
$admin_id = encrypt_decrypt('decrypt', $admin);

$year = GlobalSettings::getChidonYear();
$donation = $_POST['info'];

//*************** LOAD AUTHORIZE FUNCTIONS *********************/
require_once __DIR__ . '/../../../classes/authorize/AuthorizeAPIRequest.php';
require_once __DIR__ . '/../../../classes/authorize/CustomerProfile.php';
require_once __DIR__ . '/../../../classes/authorize/PaymentProfile.php';

use classes\authorize\AuthorizeAPIRequest;
use classes\authorize\CustomerProfile;
use classes\authorize\PaymentProfile;

function checkMandatory( $values ) {
  $missing = [];

  // map of needed fields with their more user friendly description
  $mandatory = [
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
    'error'     =>  $message
  ]);
  exit;
}

//echo "<pre>"; print_r( $donation ); echo "</pre>";

// prepare variables for payment
$amount = $donation['amount'];
$name = $donation['name'];
$cc_info = [];
$cc_info['number'] = $donation['cc']['num'];
$cc_info['exp'] = $donation['cc']['exp'];
$cc_info['cvc'] = $donation['cc']['cvv'];
$cc_info['skip'] = isset( $donation['skip'] ) ? $donation['skip'] : 0;
$cc_info['desc'] = "Chidon Shabbaton Registration " . $year . " for family (admin_id): " . $admin_id;
$cc_info['last'] = $name;
$cc_info['first'] = '';

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
  $response = null;
} else {
  if ( $amount > 0 ) {
    require 'authorize.php';
    $response = chargeCreditCard( $amount, $cc_info );
  } else {
    $response = null;
    $trans_info = "bypassing credit card processing";
  }
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

          // send email confirmation
          $subject = "Chidon Shabbaton Registration Payment";
          $email_message = "Thank you for your payment of $" . $amount . ". Your transaction id is: " . $trans_id . ". Your child(ren) are now registered for the Shabbaton.";
          $headers = 'From: chidon@tzivoshashem.com' . "\r\n" .
                    'Reply-To: chidon@tzivoshashem.com' . "\r\n";
          @mail( $emil, $subject, $email_message, $headers );
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
} else if ( $cc_info['skip'] || $amount == 0 ) {
  $msg = "Success.";
}

if ( !empty( $msg ) ) {
  // register children
  $stmt = $MASHPIA_DB->prepare("
    UPDATE th_chidon 
    SET 
        paid = :amount,
        date_paid = NOW(),
        paid_by = :admin, 
        approval = :approval
    WHERE
        user_id = :user AND year = :year
  ");
  $MASHPIA_DB->beginTransaction();
  $success = true;
  foreach ( $donation['regDetails'] as $child ) {
    $res = $stmt->execute([
      ':amount'   =>  $child['amount'], 
      ':admin'    =>  $admin_id, 
      ':user'     =>  $child['id'], 
      ':year'     =>  $year, 
      ':approval' =>  $trans_info
    ]);
    if ( !$res ) {
      //echo "<pre>"; print_r( $stmt->debugDumpParams() ); echo "</pre>";
      $success = false;
      break;
    }
  }

  if ( $success ) {
    $MASHPIA_DB->commit();
    $msg .= "Your child(ren) have been successfully registered for the Chidon Shabbaton " . $year;
    echo json_encode([
      'success'   =>  true,
      'message'   =>  $msg
    ]);
  } else {
    $MASHPIA_DB->rollBack();
    $error_msg .= "Your transaction has been processed but there was an error saving your child(ren)'s registration to our system. Please contact Tzivos Hashem HQ ASAP.";
    echo json_encode([
      'success'     =>  false,
      'error'       =>  $error_msg
    ]);
  } 
} else {
  echo json_encode([
    'success'     =>  false,
    'error'       =>  $error_msg
  ]);
}