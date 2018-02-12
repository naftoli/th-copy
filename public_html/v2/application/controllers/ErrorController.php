<?php   

/**********************************************************
  @Author: Mohannad El-Barachi
  @Copyright: 
  Error Controller
  *********************************************************/
    
class ErrorController extends Zend_Controller_Action   
{   
		function init()
	    {}
	    
	    function preDispatch() 
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
	    
	    public function errorAction()
	    {
	    	// Get the error handler
	     	$errors = $this->_getParam('error_handler');

	     	// Display the error message depending on what it is
		    switch ($errors->type) 
		    {
	            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
	            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
	            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
	                $this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
	                break;
	            default:
	                break;
	        }
	        
	        // Log the errors			
	        $exception = $errors->exception;
	        Zend_Registry::get('zend_logger')->debug($exception->getMessage()); 
	        
	        // Enable the tracing when debugging only!
			//Zend_Registry::get('zend_logger')->info($exception->getTraceAsString()); 
	    }
			
			public function indexAction()
			{
				
			}
}

?>