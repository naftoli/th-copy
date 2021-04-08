<?php
require_once __DIR__ . '/../../../api/header/header.php';
require_once __DIR__ . '/../../../../includes/authorize_constants.php';

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use includes\authorize\AuthorizeConstants as Constants;

define("AUTHORIZENET_LOG_FILE", "phplog");

function chargeCreditCard( $amount, $cc_info, $transType = "authCaptureTransaction" )
{
    /* Create a merchantAuthenticationType object with authentication details
       retrieved from the constants file */
    $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
    $merchantAuthentication->setName( Constants::GetMerchantLoginID() );
    $merchantAuthentication->setTransactionKey( Constants::GetMerchantTransactionKey() );
    
    // Set the transaction's refId
    $refId = 'ref' . time();

    // Create the payment data for a credit card
    $creditCard = new AnetAPI\CreditCardType();
    $creditCard->setCardNumber( $cc_info['number'] );
    $creditCard->setExpirationDate( $cc_info['exp'] );
    $creditCard->setCardCode( $cc_info['cvc'] );

    // Add the payment data to a paymentType object
    $paymentOne = new AnetAPI\PaymentType();
    $paymentOne->setCreditCard($creditCard);

    // Create order information
    $order = new AnetAPI\OrderType();
    $order->setDescription( $cc_info['desc'] );

    // Set the customer's Bill To address
    $customerAddress = new AnetAPI\CustomerAddressType();
    $customerAddress->setFirstName( $cc_info['first'] );
    $customerAddress->setLastName( $cc_info['last'] );
    if ( isset( $cc_info['address'] ) ) $customerAddress->setAddress( $cc_info['address'] );
    if ( isset( $cc_info['city'] ) ) $customerAddress->setCity( $cc_info['city'] );
    if ( isset( $cc_info['state'] ) ) $customerAddress->setState( $cc_info['state'] );
    if ( isset( $cc_info['country'] ) ) $customerAddress->setCountry( $cc_info['country'] );
    $customerAddress->setZip( $cc_info['zip'] );

    // Create a TransactionRequestType object and add the previous objects to it
    $transactionRequestType = new AnetAPI\TransactionRequestType();
    $transactionRequestType->setTransactionType($transType);
    $transactionRequestType->setAmount($amount);
    $transactionRequestType->setOrder($order);
    $transactionRequestType->setPayment($paymentOne);
    $transactionRequestType->setBillTo($customerAddress);

    // Assemble the complete transaction request
    $request = new AnetAPI\CreateTransactionRequest();
    $request->setMerchantAuthentication($merchantAuthentication);
    $request->setRefId($refId);
    $request->setTransactionRequest($transactionRequestType);

    // Create the controller and get the response
    $controller = new AnetController\CreateTransactionController($request);
    //$response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
    //$response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
    $response = $controller->executeWithApiResponse( Constants::GetApiEndpoint() );

    return $response;
}

function chargeHold($amount, $transID, $test = false) {
    /* Create a merchantAuthenticationType object with authentication details
       retrieved from the constants file */
    if ($test) {
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName(Constants::MERCHANT_SANDBOX_LOGIN_ID);
        $merchantAuthentication->setTransactionKey(Constants::MERCHANT_SANDBOX_TRANSACTION_KEY);
    } else {
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName(Constants::GetMerchantLoginID());
        $merchantAuthentication->setTransactionKey(Constants::GetMerchantTransactionKey());
    }

    // Set the transaction's refId
    $refId = 'ref' . time();

    // Now capture the previously authorized  amount
    $transactionRequestType = new AnetAPI\TransactionRequestType();
    $transactionRequestType->setTransactionType("priorAuthCaptureTransaction");
    $transactionRequestType->setAmount($amount);
    $transactionRequestType->setRefTransId($transID);

    // Assemble the complete transaction request
    $request = new AnetAPI\CreateTransactionRequest();
    $request->setRefId($refId);
    $request->setMerchantAuthentication($merchantAuthentication);
    $request->setTransactionRequest($transactionRequestType);

    // Create the controller and get the response
    $controller = new AnetController\CreateTransactionController($request);
    if ($test) {
        $response = $controller->executeWithApiResponse( Constants::SANDBOX_API_URL );
    } else {
        $response = $controller->executeWithApiResponse( Constants::GetApiEndpoint() );
    }

    return $response;
}

function releaseHold($transID, $test = false) {
    /* Create a merchantAuthenticationType object with authentication details
       retrieved from the constants file */
    if ($test) {
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName(Constants::MERCHANT_SANDBOX_LOGIN_ID);
        $merchantAuthentication->setTransactionKey(Constants::MERCHANT_SANDBOX_TRANSACTION_KEY);
    } else {
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName(Constants::GetMerchantLoginID());
        $merchantAuthentication->setTransactionKey(Constants::GetMerchantTransactionKey());
    }

    // Set the transaction's refId
    $refId = 'ref' . time();

    // Now capture the previously authorized  amount
    $transactionRequestType = new AnetAPI\TransactionRequestType();
    $transactionRequestType->setTransactionType("voidTransaction");
    $transactionRequestType->setRefTransId($transID);

    // Assemble the complete transaction request
    $request = new AnetAPI\CreateTransactionRequest();
    $request->setRefId($refId);
    $request->setMerchantAuthentication($merchantAuthentication);
    $request->setTransactionRequest($transactionRequestType);

    // Create the controller and get the response
    $controller = new AnetController\CreateTransactionController($request);
    if ($test) {
        $response = $controller->executeWithApiResponse( Constants::SANDBOX_API_URL );
    } else {
        $response = $controller->executeWithApiResponse( Constants::GetApiEndpoint() );
    }

    return $response;
}

function parseResponse( $response ) {
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
            if (!is_array($response)) {
                $error_msg .= $response;
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
        }
    } else {
        $error_msg .= 'There was a problem processing your credit card.';
    }

    return [
        'success'   => $msg,
        'error'     => $error_msg
    ];
}