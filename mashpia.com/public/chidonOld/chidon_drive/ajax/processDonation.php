<?php
// ini_set('display_errors',1);
// ini_set('error_reporting', E_ALL);

// Set custom error log file for donation processing
$donation_log_file = __DIR__ . '/donation_errors.log';
ini_set('error_log', $donation_log_file);

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
  $placeholder_trans_id = $cc_info['skip'] ? 11111 : 0;
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
              try {
                $update_stmt = $MASHPIA_DB->prepare("
                  UPDATE chidon_donations 
                  SET transaction_id = :trans_id, transaction_info = :trans_info
                  WHERE id = :donation_id
                ");
                $update_stmt->execute([
                  ':trans_id' => $trans_id,
                  ':trans_info' => $trans_info,
                  ':donation_id' => $donation_id
                ]);
              } catch (Exception $e) {
                // Log error but don't fail the transaction
                error_log("Failed to update donation transaction info: " . $e->getMessage());
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
    try {
      $MASHPIA_DB->rollBack();
    } catch (Exception $e) {
      error_log("Failed to rollback transaction: " . $e->getMessage());
    }
    echo json_encode([
      'success'     =>  false,
      'message'     =>  $error_msg
    ]);
    exit;
  } else {
    // Payment succeeded (or skipped) - commit the transaction
    try {
      $MASHPIA_DB->commit();
    } catch (Exception $e) {
      error_log("Failed to commit transaction: " . $e->getMessage());
      // Even if commit fails, try to return success since payment went through
    }
    echo json_encode([
      'success'   =>  true,
      'message'   =>  $msg
    ]);
    
    // Flush output immediately so response is sent to client (PHP-FPM compatible)
    if (function_exists('fastcgi_finish_request')) {
      // This sends the response to the client and closes the connection
      // The script continues running but client already received the response
      fastcgi_finish_request();
    } else {
      // For non-FastCGI, flush all output buffers
      while (ob_get_level() > 0) {
        ob_end_flush();
      }
      flush();
    }
    
    // Send email AFTER response is sent (non-blocking - client already got response)
    if (!empty($trans_id) && !$cc_info['skip']) {
      // Include email function before trying to use it
      if (file_exists(__DIR__ . '/sendEmail.php')) {
        include_once __DIR__ . '/sendEmail.php';
        if (function_exists('sendEmail')) {
          // Don't let email errors affect the response (already sent)
          // Use error suppression and set a timeout
          set_time_limit(10); // Give email 10 seconds max
          if (!sendEmail($amount, $trans_id, $email, $name)) {
            error_log("Failed to send donation email: " . $e->getMessage());
          }
        }
      }
    }
    
    exit;
  }
  
} catch (Exception $e) {
  // Rollback on any exception
  try {
    $MASHPIA_DB->rollBack();
  } catch (Exception $rollbackEx) {
    error_log("Failed to rollback on exception: " . $rollbackEx->getMessage());
  }
  echo json_encode([
    'success'   =>  false,
    'message'   =>  'An error occurred: ' . $e->getMessage()
  ]);
  exit;
}