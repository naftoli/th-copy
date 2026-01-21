<?php
// ini_set('display_errors',1);
// ini_set('error_reporting', E_ALL);

require_once __DIR__ . '/../../../api/header/db.php';
require_once __DIR__ . '/../../../class.globalSettings.php';

$year = GlobalSettings::getChidonYear();
$donation = $_POST['donation_info'];

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
    'country'         =>  'Billing Country',
    'phone'           =>  'Phone Number'
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
$display_name = $donation['display_name'];
$anonymous = $donation['anonymous'];
$email = $donation['email'];
$phone = $donation['phone'];
$notes = $donation['dedication'];

// prepare variables for donation table
$amount = $donation['amount'];

// prepare variables for payment
$cc_info = [];
$cc_info['number'] = $donation['cc']['num'];
$cc_info['exp'] = $donation['cc']['exp'];
$cc_info['cvc'] = $donation['cc']['cvv'];
$cc_info['skip'] = isset( $donation['skip'] ) ? $donation['skip'] : 0;
$cc_info['desc'] = "Chidon Drive " . $year;
$cc_info['last'] = $name;
$cc_info['first'] = '';

// prepare billing address
$billing = $donation['cc']['billing'];
$cc_info['address'] = $billing['address'] . ' ' . $billing['apt'];
$cc_info['city'] = $billing['city'];
$cc_info['state'] = $billing['state'];
$cc_info['zip'] = $billing['zip'];
$cc_info['country'] = $billing['country'];

// Start database transaction
$MASHPIA_DB->beginTransaction();

// Initialize variables
$trans_id = null;
$trans_info = null;
$msg = '';
$error_msg = '';
$donation_id = null;

try {
  // STEP 1: Insert donation into database first (with placeholder transaction_id)
  $stmt = $MASHPIA_DB->prepare("
    INSERT INTO chidon_donations 
    SET 
        name = :name, 
        display_name = :display_name, 
        anonymous = :anonymous,
        chidon_year = :year, 
        donation_amount = :amount, 
        transaction_id = :trans_id, 
        transaction_info = :trans_info, 
        email = :email, 
        phone = :phone , 
        notes = :notes
  ");
  
  // Use placeholder transaction_id initially
  $placeholder_trans_id = $cc_info['skip'] ? '11111' : '0';
  $placeholder_trans_info = $cc_info['skip'] ? 'testing by skipping authorize.net transaction.' : 'Pending credit card processing';
  
  $res = $stmt->execute([
    ':name'         =>  $name,
    ':display_name' =>  $display_name, 
    ':anonymous'    =>  $anonymous,
    ':year'         =>  $year, 
    ':amount'       =>  $amount, 
    ':trans_id'     =>  $placeholder_trans_id, 
    ':trans_info'   =>  $placeholder_trans_info, 
    ':email'        =>  $email,
    ':phone'        =>  $phone,
    ':notes'        =>  $notes
  ]);
  
  $donation_id = $MASHPIA_DB->lastInsertId();

  // STEP 2: Process credit card payment
  if ( $cc_info['skip'] ) {
    // don't process card at all
    $trans_id = 11111;
    $trans_info = "testing by skipping authorize.net transaction.";
    $response = null;
    $msg = "Success.";
  } else {
    require 'authorize.php';
    $response = chargeCreditCard( $amount, $cc_info );
    
    // STEP 3: Check payment response
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
              
              // Update donation record with actual transaction info
              $update_stmt = $MASHPIA_DB->prepare("
                UPDATE chidon_donations 
                SET transaction_id = :trans_id, transaction_info = :trans_info
                WHERE chidon_donation_id = :donation_id
              ");
              $update_stmt->execute([
                ':trans_id' => $trans_id,
                ':trans_info' => $trans_info,
                ':donation_id' => $donation_id
              ]);

              // send email
              include 'sendEmail.php';
              if (! sendEmail( $amount, $trans_id, $email, $name )) {
                $error_msg .= "There was an error sending the confirmation email";
              }
          } else {
            $error_msg .= "Transaction Failed \n";
            if ($tresponse->getErrors() != null) {
                $error_msg .= $tresponse->getErrors()[0]->getErrorText() . "\n";
            }
          }
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
    } else {
      $error_msg .= "Transaction Failed: No response from payment processor.\n";
    }
  }

  // STEP 4: Commit or rollback based on payment result
  if ( !empty( $error_msg ) && !$cc_info['skip'] ) {
    // Payment failed - rollback the database transaction
    $MASHPIA_DB->rollBack();
    echo json_encode([
      'success'     =>  false,
      'message'     =>  $error_msg
    ]);
  } else {
    // Payment succeeded (or skipped) - commit the transaction
    $MASHPIA_DB->commit();
    echo json_encode([
      'success'   =>  true,
      'message'   =>  $msg
    ]);
  }
  
} catch (Exception $e) {
  // Rollback on any exception
  $MASHPIA_DB->rollBack();
  echo json_encode([
    'success'   =>  false,
    'message'   =>  'An error occurred: ' . $e->getMessage()
  ]);
}