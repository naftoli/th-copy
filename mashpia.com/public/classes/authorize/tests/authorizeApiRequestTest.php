<?php

require 'vendor/autoload.php';
require_once("AuthorizeAPIRequest.php");

use PHPUnit\Framework\TestCase;
use includes\authorize\AuthorizeConstants as Constants;
use classes\authorize\AuthorizeAPIRequest;

class authorizeApiRequestTest extends TestCase {
    public function testConstructor() {
        // postData defaults to null
        $subject = new AuthorizeApiRequest();
        $this->assertEquals($subject->postData, NULL);
        $this->assertEquals($subject->responseData, NULL);
        // but is set if the methoud is post
        $subject = new AuthorizeAPIRequest("POST", ["foo" => "bar"]);
        $this->assertEquals($subject->postData, json_encode(["foo" => "bar"]));
        // not if it is GET
        $subject = new AuthorizeAPIRequest("GET", ["foo" => "bar"]);
        $this->assertEquals($subject->postData, NULL);
    }
    
    public function testSetPostData() {
        // calling setPostData sets the post data
        $subject = new AuthorizeApiRequest();
        $subject->setPostData(["foo" => "bar"]);
        $this->assertEquals($subject->postData, json_encode(["foo" => "bar"]));
    }
    
    public function testGetInfo() {
        // calling this function should give us insight into the state of the handle
        $subject = new AuthorizeApiRequest();
        
        $this->assertEquals($subject->getInfo(CURLINFO_EFFECTIVE_URL), Constants::GetApiEndpoint());
    }
    
    public function testSetOption() {
        // calling this function should set curl info that can be retrived with #setOption.
        $subject = new AuthorizeApiRequest();
        // set an option
        $subject->setOption(CURLOPT_PRIVATE , true);
        // validate that it was set
        $this->assertEquals($subject->getInfo(CURLINFO_PRIVATE), true);
    }
}
