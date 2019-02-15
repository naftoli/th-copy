<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/../../api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/../../../includes/authorize_constants.php';

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use includes\authorize\AuthorizeConstants as Constants;

define("AUTHORIZENET_LOG_FILE", "phplog");

function chargeCreditCard( $amount, $cc_info )
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
    if ( isset( $cc_info['zip'] ) ) $customerAddress->setZip( $cc_info['zip'] );
    if ( isset( $cc_info['country'] ) ) $customerAddress->setCountry( $cc_info['country'] );

    // Create a TransactionRequestType object and add the previous objects to it
    $transactionRequestType = new AnetAPI\TransactionRequestType();
    $transactionRequestType->setTransactionType("authCaptureTransaction");
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