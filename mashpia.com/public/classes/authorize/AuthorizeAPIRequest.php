<?php
namespace classes\authorize;

use classes\authorize;

require_once( dirname(__FILE__) . "/../../../includes/authorize_constants.php" );

use includes\authorize\AuthorizeConstants as Constants;

/*
 * \classes\authorize\HttpRequest interface
 *
 * A wrapper for our custom Curl Wrapper that will allow for better unit tests
 *
 * setOption($name, $value) => set the option on internal curl request.
 * setPostData($postData) => set post data on curl connection to array. Parsed to JSON
 * execute() => make the api call
 * getInfo($name) => get the info from the curl connection
 * close() => close the curl connection
 */

interface HttpRequest {
    public function setOption($name, $value);
    public function setPostData($postData);
    public function execute();
    public function getInfo($name);
    public function close();
}

/*
 * \classes\authorize\AuthorizeAPIRequest class.
 *
 * A HttpRequest class designed to handle calls to the Authoirze API
 *
 * Extends the HttpRequest interface defined above
 *
 * Additional Functions:
 *      __construct($method, $postDataArray, $url)
 *          $methoud => GET or POST, optional
 *          $postDataArray => The data to POST
 *          $url => The url to make the request to (gets it from Constants.php by default)
 */

class AuthorizeAPIRequest implements HttpRequest{
    
    private $handle = null; // internal CURL handle
    public $postData = null; // The latest data to be posted to the api
    public $responseData = null; // The latest response recived (json decoded)
    
    // create the API request object
    public function __construct($method="POST", $postDataArray=null, $url=null) {
        // default to the correct url for the api endpoint
        if (!$url) {$url = Constants::GetApiEndpoint();}
        // set the handle to a new curl instance
        $this->handle = curl_init($url);
        // Set the default options for the API call
        curl_setopt_array($this->handle, [
            CURLOPT_RETURNTRANSFER=>TRUE,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POST=>TRUE
        ]);
        // reset the method to GET if the user requests it.
        if($method == "GET") {
            curl_setopt($this->handle, CURLOPT_HTTPGET, TRUE);
        } else if ($postDataArray) { // no GET now the POST data can be set
            $this->setPostData($postDataArray);
        } else {
        }
    }
    
    // set the data for post
    public function setPostData($postData) {
        $this->postData = json_encode($postData); // encode the data into json
        // TODO only set this if the methoud is post
        curl_setopt($this->handle, CURLOPT_POSTFIELDS, $this->postData); // set the request to send the data
    }
    
    // set a curl option
    public function setOption($name, $value) {
        curl_setopt($this->handle, $name, $value);
    }
    
    // execute the API request
    public function execute() {
        // hit the api and get the response
        $response = curl_exec($this->handle);
        //if the request failed
        if($response === FALSE) {
            // return the error that caused the failure
            return curl_error($ch);
        } else {// the response was sucessfull
            $response = str_replace("\xEF\xBB\xBF",'',$response); // remove BOM from the begining of the json
        
            $this->responseData = json_decode($response, true); // decode the json and set it to the public instance variable of responseData
            return $this->responseData; // return the decoded data
        }
    }
    
    // get info from the connection handle
    public function getInfo($name) {
        return curl_getinfo($this->handle, $name);
    }
    
    //close the connection
    public function close(){
        curl_close($this->handle);
    }
    
    // close the connection when the object is destroyed
    public function __destruct() {
        $this->close();
    }
    
    
}

?>