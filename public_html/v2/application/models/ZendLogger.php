<?php

class Zendlogger extends Zend_Controller_Plugin_Abstract 
{ 
    public function preDispatch(Zend_Controller_Request_Abstract $request) 
    { 
        	$logLevel = 7;
			$db = Zend_Registry::get('db');	     
	        $columnMapping = array( 
	            'timestamp' => 'timestamp', 
	            'level' => 'priorityName', 
	            'message' => 'message' 
	        );          
	        $writer = new Zend_Log_Writer_Db($db, 'zend_log', $columnMapping);          
	        $writer->addFilter(new Zend_Log_Filter_Priority($logLevel)); 
	        Zend_Registry::set('zend_logger', new Zend_Log($writer)); 
    	
    } 
} 

?>