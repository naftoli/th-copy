<?php
$admin_auth = ['school'];
define( "MASHPIA_AUTH_REQUIRED", true );
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';

$year = $_POST['year'];
$donation = $_POST['donation'];
$user_donations = $_POST['user_donations'];
$cc_info = $_POST['cc_info'];
$cc_info['desc'] = "Chidon Drive " . $year;

// extract first and last name
$cc_info['first'] = '';
$cc_info['last'] = trim( $cc_info['name'] );
if ( strpos( $cc_info['last'], ' ' ) !== false ) {
  $full_name = explode(' ', $cc_info['last']);
  $lastPos = count( $full_name ) - 1;
  $cc_info['last'] = $full_name[$lastPos];
  $cc_info['first'] = $full_name[0];
  if ( $lastPos > 1 ) {
    for ( $i = 1; $i < $lastPos; $i++ ) {
      $cc_info['first'] += $full_name[$i];
    }
  }
}

// make sure cc info is valid
$msg = '';
$card_num = preg_replace('/\s+/', '', $cc_info['number']);
$length = strlen( $card_num );
if ( $length < 15 || $length > 16 ) {
  $msg .= "Credit Card Number must be 15 or 16 digits.\n"; 
} else {
  $cc_info['number'] = $card_num;
}

if ( strpos( $cc_info['exp'], '/' ) === false ) {
  $msg .= "Expiry Date must be in the format MM / YYYY.\n";
} else {
  // strip spaces and divide mm and yyyy
  $expiry = explode('/', preg_replace('/\s+/', '', $cc_info['exp']));
  $mm = $expiry[0];
  $yy = $expiry[1];
  if ( strlen( $mm ) != 2 || strlen( $yy ) != 4 ) {
    $msg .= "Expiry Date must be in the format MM / YYYY.\n";
  } else if ( intval( $mm ) > 12 || intval( $yy ) < 2019 ) {
    if ( intval( $mm ) > 12 ) $msg .= "Expiry Month cannot be greater than 12.\n";
    if ( intval( $yy ) < 2019 ) $msg .= "Expiry Year cannot be less than 2019.\n";
  } else {
    $exp = $expiry[1] . '-' . $expiry[0];
    $cc_info['exp'] = $exp;
  }
}

$cvc = $cc_info['cvc'];
if ( !is_numeric( $cvc ) ) {
  $msg .= "CVC can only have numbers.\n";
}

if ( !empty( $msg ) ) {
  echo $msg;
  exit;
}

// process donation amount through authorize
require 'authorize.php';
$response = chargeCreditCard( $donation, $cc_info );

if ($response != null) {
  // Check to see if the API request was successfully received and acted upon
  if ($response->getMessages()->getResultCode() == "Ok") {
      // Since the API request was successful, look for a transaction response
      // and parse it to display the results of authorizing the card
      $tresponse = $response->getTransactionResponse();
  
      if ($tresponse != null && $tresponse->getMessages() != null) {
          echo " Successfully created transaction with Transaction ID: " . $tresponse->getTransId() . "\n";
          echo " Transaction Response Code: " . $tresponse->getResponseCode() . "\n";
          echo " Message Code: " . $tresponse->getMessages()[0]->getCode() . "\n";
          echo " Auth Code: " . $tresponse->getAuthCode() . "\n";
          echo " Description: " . $tresponse->getMessages()[0]->getDescription() . "\n";

          $trans_id = $tresponse->getTransId();
          $trans_info = $trans_id . ":" . $tresponse->getResponseCode() . ":" . $tresponse->getMessages()[0]->getCode() . ":". $tresponse->getAuthCode() . ":" . $tresponse->getMessages()[0]->getDescription();

          // save to transactions table.
          $transaction_query = $MASHPIA_DB->prepare(
              "INSERT INTO transactions (trans_date, description, amount, response) "
              ."VALUES (NOW(), ?, ?, ?)"
          );
          $statusTransaction = $transaction_query->execute([
              $cc_info['desc'], $donation, json_encode( $response )
          ]);
          $trans_db_id = $MASHPIA_DB->lastInsertId();

          // add donation and user donations to db
          $MASHPIA_DB->beginTransaction();

          $success = true;
          $stmt = $MASHPIA_DB->prepare("
            INSERT INTO chidon_donations 
            SET admin_id = :admin, 
            chidon_year = :year, 
            donation_amount = :donation,
            transaction_id = :trans_id, 
            transaction_info = :trans_info
          ");
          $res = $stmt->execute([
            ':admin'    =>  $admin_user['admin_id'], 
            ':year'     =>  $year, 
            ':donation' =>  $donation, 
            ':trans_id' =>  $trans_id, 
            ':trans_info' => $trans_info
          ]);
          if ( $res ) {
            $stmt = $MASHPIA_DB->prepare("
              INSERT INTO chidon_user_subsidies 
              SET chidon_donation_id = :donation_id, 
              chidon_year = :year,
              user_id = :user,
              subsidy_amount = :amount
            ");
            foreach ( $user_donations as $donation ) {
              $res2 = $stmt->execute([
                ':donation_id'  =>  $MASHPIA_DB->lastInsertId(), 
                ':year'         =>  $year, 
                ':user'         =>  $donation['user_id'], 
                ':amount'       =>  $donation['amount']
              ]);
              if ( !$res2 ) {
                $success = false;
                break;
              }
            }
          } else {
            $success = false;
          }

          if ( $success ) {
            $MASHPIA_DB->commit();
          } else {
            $MASHPIA_DB->rollBack();
            echo "There was an error saving the donation to the database. Please contact HQ.";

            // send email to self with problem
            $to      = 'naftoli@tzivoshashem.org';
            $subject = 'Chidon Drive Error';
            $message = 'Error in inserting donations into chidon drive db tables. Ref: ' . $trans_db_id;
            $headers = array(
                'From'     => 'cth@mashpia.com',
                'Reply-To' => 'cth@mashpia.com'
            );
            @mail($to, $subject, $message, $headers);
          }
      } else {
          echo "Transaction Failed \n";
          if ($tresponse->getErrors() != null) {
              echo " Error Code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
              echo " Error Message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";
          }
      }
      // Or, print errors if the API request wasn't successful
  } else {
      echo "Transaction Failed \n";
      $tresponse = $response->getTransactionResponse();
  
      if ($tresponse != null && $tresponse->getErrors() != null) {
          echo " Error Code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
          echo " Error Message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";
      } else {
          echo " Error Code  : " . $response->getMessages()->getMessage()[0]->getCode() . "\n";
          echo " Error Message : " . $response->getMessages()->getMessage()[0]->getText() . "\n";
      }
  }
} else {
  echo  "No response returned \n";
}
?>