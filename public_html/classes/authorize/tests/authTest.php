<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

require 'vendor/autoload.php';
require_once 'Auth.php';

use PHPUnit\Framework\TestCase;
use classes\authorize\Auth;
use includes\authorize\AuthorizeConstants as Constants;

class AuthTest extends TestCase {
    
    protected $auth;
    
    public function setUp() {
        $this->auth = new Auth;
    }
    
    // Test the constructor
    public function testConstructor() {
        // assert that the constructor creates the internal merchantAuthentication array
        $this->assertInternalType('array', $this->auth->merchantAuthentication);
        
        // assert that the top level of the array is "merchantAuthentication"
        $this->assertArrayHasKey('merchantAuthentication', $this->auth->merchantAuthentication);
        
        // assert that it has the correct children and that their values are correct
        $this->assertArrayHasKey('name', $this->auth->merchantAuthentication['merchantAuthentication']);
        $this->assertEquals(Constants::GetMerchantLoginID(), $this->auth->merchantAuthentication['merchantAuthentication']['name']);
        $this->assertArrayHasKey('transactionKey', $this->auth->merchantAuthentication['merchantAuthentication']);
        $this->assertEquals(Constants::GetMerchantTransactionKey(), $this->auth->merchantAuthentication['merchantAuthentication']['transactionKey']);
    }
    
    /*
     * #createApiCall($command, $array)
     *
     * This test ensures the following:
     *  1. It returns an array 
     */
    public function testCreateApiCall() {
        $command = "FakeAPICall";
        $array = ["fakedata"=>"fake"];
        $subject = $this->auth->createApiCall($command, $array);
        //assert that it returns an array
        $this->assertInternalType('array', $subject, "#createApiCall -> returns an array");
        
        // assert that it contains the command
        $this->assertArrayHasKey($command, $subject, "#createApiCall -> result has the command as a key");
        
        // assert that it also contains the merchantAuthentication
        $this->assertArrayHasKey('merchantAuthentication', $subject[$command], "#createApiCall -> result has authentication nested under command");
        
        // assert that the array is nested inside as well
        $this->assertEquals($array, array_intersect_assoc($array, $subject[$command]), "#createApiCall -> result includes array passed in nested under command");
    }
    
    // test the determination of the validation mode
    public function testLiveValidationMode(){
        // passing true returns liveMode
        $this->assertEquals($this->auth->getValidationMode(true), "liveMode", "Setting validation mode to true retunrs 'liveMode'");
        // passing false returns testMode
        $this->assertEquals($this->auth->getValidationMode(false), "testMode", "Setting validation mode to true retunrs 'testMode'");
    }
}