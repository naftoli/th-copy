<?php

define("AUTHORIZE_NET_SANDBOX", true);

require 'vendor/autoload.php';
require_once("CustomerProfile.php");
require_once("AuthorizeAPIRequest.php");

use PHPUnit\Framework\TestCase;
use classes\authorize\CustomerProfile;

class CustomerProfileTest extends TestCase {
    
    private $profile_id = "1502089040"; // set the desired profile id for the tests
    protected $apiResult; // default get request result
    protected $api; // the api mock object
    
    public function setUp() {
        // mock out the http request
        $this->api = $this->getMock('AuthorizeAPIRequest', ['setPostData', 'execute']);
        // a copied example of an API return as of 9/11/2017
        $this->apiResult = [
            "profile" => ["paymentProfiles" => [],"shipToList" => [],"customerProfileId" => $this->profile_id,"description" => "Some Description","email" => "test@test.com"],
            "messages" => ["resultCode" => "Ok","message" => ["code" => "I00001", "text" => "Successful."]]
        ];
    }
    
    public function testConstructor() {
        // set up expected result
        $this->api->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($this->apiResult));

        $customerProfile = new CustomerProfile($this->profile_id, true, $this->api);
        
        $this->assertEquals($this->profile_id, $customerProfile->customerProfileId);
        $this->assertEquals($this->apiResult['profile']['paymentProfiles'], $customerProfile->paymentProfiles);
        $this->assertEquals($this->apiResult['profile']['description'], $customerProfile->description);
        $this->assertEquals($this->apiResult['profile']['email'], $customerProfile->email);
    }
    
    public function testLoad() {
        $this->api->expects($this->once())
            ->method('setPostData');
            
        $this->api->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($this->apiResult));
        
        $customerProfile = new CustomerProfile($this->profile_id, true, $this->api);
        
        $this->assertEquals($this->profile_id, $customerProfile->customerProfileId);
        $this->assertEquals($this->apiResult['profile']['paymentProfiles'], $customerProfile->paymentProfiles);
        $this->assertEquals($this->apiResult['profile']['description'], $customerProfile->description);
        $this->assertEquals($this->apiResult['profile']['email'], $customerProfile->email);
    }
    
    public function testObjectAfterCallingLoad(){
        
        $this->api->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($this->apiResult));
            
        $subject = new CustomerProfile("1502089040", true, $this->api);
        
        $this->assertEquals($subject->customerProfileId, $this->apiResult['profile']['customerProfileId']);
        $this->assertEquals($subject->email, $this->apiResult['profile']['email']);
        $this->assertEquals($subject->description, $this->apiResult['profile']['description']);
        $this->assertEquals($subject->paymentProfiles, $this->apiResult['profile']['paymentProfiles']);
        $this->assertEquals($subject->shipToList, $this->apiResult['profile']['shipToList']);
    }
    
    // test a successfull call to ::create
    public function testCreateSuccess() {
        // the expected result for a sucess
        $apiResult = [
            "customerProfileId" => "157497",
            "customerPaymentProfileIdList" => ["157497"],
            "messages" => [
                "resultCode" => "Ok",
                "message" => [["code"=> "I00001", "text" => "Successful."]]
            ]
        ];
        // some fake payment profile
        $paymentProfile = ["payment" => ["creditCard" => ["cardNumber" => "4111111111111111", "expirationDate" => "2020-12"]]];
        $apiResult_load = [
            "profile" => ["paymentProfiles" => [array_merge(["customerPaymentProfileId" => "1234"], $paymentProfile)],"shipToList" => [],"customerProfileId" => "157497",
                          "description" => "desc","email" => "test@test.com", "merchantCustomerId" => "TH_007"],
            "messages" => ["resultCode" => "Ok","message" => ["code" => "I00001", "text" => "Successful."]]
        ];
        
        // mock the api response
        $this->api->expects($this->any()) // updated behavior, calls load to standardize payment profile output
            ->method('execute')
            ->will($this->onConsecutiveCalls($apiResult, $apiResult_load));
        // this is the line we are testing
        $subject = CustomerProfile::create("TH_007", "test@test.com", "desc", $paymentProfile, false, $this->api);
        
        // now lets see the results
        $this->assertEquals($subject->customerProfileId,$apiResult_load["profile"]["customerProfileId"], "::create sets the instance profileId to the result from the load api call");
        $this->assertEquals($subject->paymentProfiles, $apiResult_load["profile"]["paymentProfiles"], "::create sets the instances payment profile array to the list returned by the load call");
        // test that it manually sets it to what was passed in
        $this->assertEquals($subject->merchantCustomerId, "TH_007", "::create does sets the merchantCustomerId to what was sent in");
        $this->assertEquals($subject->email, "test@test.com", "::create does sets the email to what was sent in");
        $this->assertEquals($subject->description, "desc", "::create does sets the description to what was sent in");
    }
    
    // test a failed call to ::create
    public function testCreateFail() {
        // standard failure example
        $apiResult = [
            "customerPaymentProfileIdList" => [],
            "messages" => [
                "resultCode" => "Error",
                "message" => [["code"=> "E00040", "text" => "The record cannot be found."]]
            ]
        ];
        // some fake payment profile
        $paymentProfile = ["payment" => ["creditCard" => ["cardNumber" => "4111111111111111", "expirationDate" => "2020-12"]]];
        
        $this->api->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($apiResult));
            
        $subject = CustomerProfile::create("TH_007", "test@test.com", "desc", $paymentProfile, false, $this->api);
        
        $expected_error_message = "Error(E00040): The record cannot be found."; // formatted with data from above apiResult.
        
        // now lets test the results
        $this->assertInternalType("array", $subject, "::create returns an array when it fails");
        $this->assertEquals($subject["result"], $apiResult, "returns array containing the api result");
        $this->assertEquals($subject["message"], $expected_error_message, "returns array containing a user readable error message");
    }
    
    // test that it loads the info for duplicate information (Error Code E00039)
    public function testCreateFailCodeE00039() {
        $apiResult = [
            "customerPaymentProfileIdList" => [],
            "messages" => [
                "resultCode" => "Error",
                "message" => [["code"=> "E00039", "text" => "A duplicate record with ID " . $this->profile_id ." already exists."]]
            ]
        ];
        // some fake payment profile
        $paymentProfile = ["payment" => ["creditCard" => ["cardNumber" => "4111111111111111", "expirationDate" => "2020-12"]]];
        
        $this->api->expects($this->any())
            ->method('execute')
            ->will($this->onConsecutiveCalls($apiResult, $this->apiResult)); // first return the fail and then return the success
            
        $subject = CustomerProfile::create("TH_007", "test@test.com", "desc", $paymentProfile, false, $this->api);
        
        $this->assertEquals($this->profile_id, $subject->customerProfileId);
        $this->assertEquals($this->apiResult['profile']['paymentProfiles'], $subject->paymentProfiles);
        $this->assertEquals($this->apiResult['profile']['description'], $subject->description);
        $this->assertEquals($this->apiResult['profile']['email'], $subject->email);
    }
    
    public function testUpdate() {
        // mock api data
        $api_request = ["updateCustomerProfileRequest" => ["merchantAuthentication" => [ "name" => "6ZvKUVx425pQ", "transactionKey" => "7kfzP7LuQA358N5y"],
            "profile" => ["email" => "newaddress@example.com","customerProfileId" => "10000"]
        ]];
        $api_result = ["messages" => ["resultCode" => "Ok", "message" => [["code" => "I00001", "text" => "Successful."]]]];
        // wire up the mocs
        $this->api->expects($this->once())->method('setPostData')->with($this->equalTo($api_request));
        $this->api->expects($this->once())->method('execute')->will($this->returnValue($api_result));
        // create the customer profile object
        $subject = new CustomerProfile("10000", false, $this->api);
        // update some data
        $subject->email = "newaddress@example.com";
        // call the update to the server
        $subject = $subject->update();
        // assert the result is as expected
        $this->assertEquals($subject, "Successful.", "returns the text from the api result");
    }
    
    public function testChargeCard(){
        // paramaters
        $refId = "ABCD"; $amount = "50"; $paymentProfileId = "1234"; $customerProfileId = "5678";
        // the expected api request
        $api_request = ["createTransactionRequest" => ["merchantAuthentication"=>[ "name" => "6ZvKUVx425pQ", "transactionKey" => "7kfzP7LuQA358N5y"],
                "refId" => $refId,"transactionRequest" => ["transactionType" => "authCaptureTransaction","amount" => $amount,
                "profile" => ["customerProfileId" => $customerProfileId,"paymentProfile" => ["paymentProfileId" => $paymentProfileId]]]
        ]];
        // a sample api result wihtout the transacion response
        $api_result = ["refId"=> $refId, "transactionResponse" => ["responseCode" => "1"], "messages" => ["resultCode" => "Ok", "message" => [["code" => "I00001", "text" => "Successful."]]]];
        // set up the mocks
        $this->api->expects($this->once())->method('setPostData')->with($this->equalTo($api_request));
        $this->api->expects($this->once())->method('execute')->will($this->returnValue($api_result));
        // create the customer profile instance
        $customerProfile = new CustomerProfile($customerProfileId, false, $this->api);
        // run the fncution and store the result
        $subject = $customerProfile->chargeCard($amount, $paymentProfileId, $refId);
        // for now it just returns the api result 
        $this->assertEquals($subject, $api_result);
    }
    
    public function testChargeCardFails(){
        // paramaters
        $refId = "ABCD"; $amount = "50"; $paymentProfileId = "1234"; $customerProfileId = "5678";
        $api_result = ["refId"=> $refId, "transactionResponse" => ["responseCode" => "2", "errors" => [["errorCode" => "2", "errorText" => "This transaction has been Declined"]]],
                       "messages" => ["resultCode" => "Ok", "message" => [["code" => "I00001", "text" => "Successful."]]]];
        // set up the api mock object
        $this->api->expects($this->once())->method('execute')->will($this->returnValue($api_result));
        // create the test object
        $customerProfile = new CustomerProfile($customerProfileId, false, $this->api);
        // get the subject for the test (return value of chargeCard() function)
        $subject = $customerProfile->chargeCard($amount, $paymentProfileId, $refId);
        //
        $this->assertEquals($subject, "Error(2): This transaction has been Declined");
    }
}

?>

