<?php

require 'vendor/autoload.php';
require_once("PaymentProfile.php");
require_once("AuthorizeAPIRequest.php");

use PHPUnit\Framework\TestCase;
use classes\authorize\PaymentProfile;
use includes\authorize\AuthorizeConstants as Constants;

class PaymentProfileTest extends TestCase {
    
    protected $api;
    protected $api_result_load;
    protected $customerProfileId = "1234";
    protected $customerPaymentProfileId = "5678";
    
    public function setUp() {
        // mock the api
        $this->api = $this->getMock('AuthorizeAPIRequest', ['setPostData', 'execute']);
        // demo result
        $this->api_result_load = ["paymentProfile" => ["customerProfileId" => $this->customerProfileId,"customerPaymentProfileId" => $this->customerPaymentProfileId,
            "payment" => ["creditCard"=> ["cardNumber" => "XXXX1111","expirationDate" => "XXXX","cardType" => "Visa"], "billTo" => []]
        ], "messages" => ["resultCode" => "Ok","message" => [["code"=>"I00001", "text" => "Successful"]]]];
    }
    
    public function testConstructor() {
        // set up the mock
        $this->api->expects($this->once())->method('execute')->will($this->returnValue($this->api_result_load)); //ecpect only one hit to the api
        // create the subject
        $subject = new PaymentProfile($this->customerProfileId, $this->customerPaymentProfileId, true, $this->api); // we do not call load explictly
        // assert that it hits the api and gets the data
        $this->assertEquals($subject->cardType, "Visa");
        $this->assertEquals($subject->expirationDate, "XXXX");
        $this->assertEquals($subject->cardNumber, "XXXX1111");
    }
    
    public function testCreate() {
        $api_result_create = ["customerProfileId" => $this->customerProfileId, "customerPaymentProfileId" => $this->customerPaymentProfileId,
                       "messages" => ["resultCode" => "Ok","message" => [["code"=>"I00001", "text" => "Successful"]]]];
        $this->api->expects($this->any())->method('execute')
            ->will($this->onConsecutiveCalls($api_result_create, $this->api_result_load)); //expect two hits to the api
        
        $subject = PaymentProfile::create("4111111111111111", "2020-06", "855", $this->customerProfileId, null, false, false, $this->api);
        
        $this->assertEquals($subject->cardType, "Visa");
        $this->assertEquals($subject->expirationDate, "XXXX");
        $this->assertEquals($subject->cardNumber, "XXXX1111");
    }
    
    public function testCreateBasicArray() {
        //variables
        $cc_number = "4111111111111111"; $cc_exp = "2020-06"; $cc_code = "885"; $bill_to = ["address" => "foobar"];
        // define the subject        
        $subject = PaymentProfile::createBasicArray($cc_number, $cc_exp, $cc_code, $bill_to, true);
        // just assert that all is good
        $this->assertInternalType('array', $subject, "#createBasicArray returns an array");
        $this->assertEquals($subject["billTo"], $bill_to);
        $this->assertEquals($subject["payment"]["creditCard"]["cardNumber"], $cc_number);
        $this->assertEquals($subject["payment"]["creditCard"]["expirationDate"], $cc_exp);
        $this->assertEquals($subject["payment"]["creditCard"]["cardCode"], $cc_code);
    }
    
    public function testLoad() {
        $this->api->expects($this->once())->method('execute')->will($this->returnValue($this->api_result_load)); //ecpect only one hit to the api
        
        $subject = new PaymentProfile($this->customerProfileId, $this->customerPaymentProfileId, false, $this->api);
        $subject->load(); // call the function explicitly
        // make the assertions
        $this->assertEquals($subject->cardType, "Visa");
        $this->assertEquals($subject->expirationDate, "XXXX");
        $this->assertEquals($subject->cardNumber, "XXXX1111");
    }
    
    public function testUpdate() {
        $cc_num = "4111111111111111"; $cc_exp = "2020-06"; $cc_code = "877";
        // set up requests and responses
        $api_request = ["updateCustomerPaymentProfileRequest" =>
                        ["merchantAuthentication" => ["name" => Constants::GetMerchantLoginID(), "transactionKey" => Constants::GetMerchantTransactionKey()],
                        "customerProfileId" => $this->customerProfileId, "paymentProfile"=>[
                            "payment"=>["creditCard"=>["cardNumber" => $cc_num, "expirationDate" => $cc_exp, "cardCode"=>$cc_code]],
                            "defaultPaymentProfile"=>true, "customerPaymentProfileId" => $this->customerPaymentProfileId
                        ], "validationMode" => "liveMode"]];
        $api_response_success = ["messages" => ["resultCode" => "Ok", "message" => [["code" => "I00001", "text" => "Successful."]]]];
        $api_response_fail = ["messages" => ["resultCode" => "Error", "message" => [["code" => "E00003", "text" => "The element 'creditCard' in namespace..."]]]];
        // configure the mocks
        $this->api->expects($this->any())->method('execute')
            ->will($this->onConsecutiveCalls($api_response_success, $api_response_fail)); //expect two hits to the api
        $this->api->expects($this->any())->method('setPostData')->with($this->equalTo($api_request)); // expect all post data to look the same
        
        // create the subject
        $subject = new PaymentProfile($this->customerPaymentProfileId, $this->customerProfileId, false, $this->api);
        // assign the variables
        $subject->cardNumber=$cc_num; $subject->expirationDate=$cc_exp; $subject->cardCode=$cc_code;
        $subject->default_card = true; $subject->live = true;
        // call the function
        $result = $subject->update();
        // assert the result
        $this->assertEquals($result, null);
        // call the function again
        $result = $subject->update();
        // and assert the failed result
        $this->assertEquals($result, $api_response_fail);
    }
}

?>