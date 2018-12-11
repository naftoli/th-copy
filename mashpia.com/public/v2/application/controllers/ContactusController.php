<?php
/**********************************************************
  @Author: MMC
  @Copyright:
  Contact Us Controller
  add/edit/delete message
  *********************************************************/

class ContactusController extends Zend_Controller_Action
{
	protected $contact;
	protected $email;
	protected $message;

    function preDispatch(){
	}

	public function indexAction(){

		$request = $this->getRequest();
		if($request->isPost())
		{
				//collect the data from the user
				Zend_Loader::loadClass('Zend_Filter_StripTags');
				$f = new Zend_Filter_StripTags();
				$captcha_code = $f->filter($this->_request->getPost('captcha_code'));
				$recaptcha_challenge_field = $f->filter($this->_request->getPost('recaptcha_challenge_field'));
				$recaptcha_response_field = $f->filter($this->_request->getPost('recaptcha_response_field'));
				$this->contact = $f->filter($this->_request->getPost('contact_form_contact'));
				$this->email = $f->filter($this->_request->getPost('contact_form_email'));
				$this->message = $f->filter($this->_request->getPost('contact_form_message'));

				// captcha fails on blank input, so add a value
				if ($recaptcha_response_field=='') $recaptcha_response_field="x"; else $recaptcha_response_field;

				// get captcha challenge result
				// Get the recaptcha keys from the configuration file
				$config = new Zend_Config_Ini('./application/config.ini','DEV_ENV');
				$recaptcha_public_key = $config->recaptcha_public;
				$recaptcha_private_key = $config->recaptcha_private;

		    	// Send the recaptcha to the view file for the contact us form
		    	$captcha = new Zend_Service_ReCaptcha($recaptcha_public_key,$recaptcha_private_key);
				$result = $captcha->verify($recaptcha_challenge_field,$recaptcha_response_field);

				if ($result->isValid()) {
					$arr = array ('the_result'=> "true");
				}
				else{
					$arr = array ('the_result'=> "false");
				}

				// send JSON reply
				header('Content-Type: text/javascript; charset=utf8');
				header('Access-Control-Max-Age: 3628800');
				header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
				echo json_encode($arr);
				
				// if OK, then send email
				if ($result->isValid()) {
					$Contactus = new Contactus();
					$this->sendEmail();
					$array = array(  'contact'=>$this->contact,
									'email'=>$this->email,
									'message'=>$this->message);
					$Contactus->insert_new_contact_us($array);
					//$this->view->message = "Thank you for your message " . $contact . ". Your message has been emailed to Webmaster.";
					//$this->_redirect('contactus/confirm');
				}

				 die();


				// push data back out to form
				// $this->view->contact = $this->contact;
				// $this->view->email = $this->email;
				// $this->view->message = $this->message;


				// email validation class
				// $validator = new Zend_Validate_EmailAddress();

				// error messages
				// if (empty($this->contact)) {
					// $this->view->error_message = 'Please provide your name.';
				// }
				// elseif (empty($this->email)) {
					// $this->view->error_message = 'Please provide your email.';
				// }
				// elseif (!$validator->isValid($this->email)) {
					 // $this->view->error_message = 'Invalid email address.';
				// }
				// elseif (empty($this->message))				{
					// $this->view->error_message = 'Please provide your comment.';
				// }
				// elseif ($result->isValid() == false ) {
					// $this->view->error_message = 'Invalid Captcha value.';
				// }

				// on success:
				// else{
					// $Contactus = new Contactus();
					// $Contactus->insert_new_contact_us($array);
					// $this->view->message = "Thank you for your message " . $contact . ". Your message has been emailed to Webmaster.";
					// $this->sendEmail();
					// $this->_redirect('contactus/confirm');
				// }
			}
		 }

	public function sendEmail()
	{
		// Email it to the user
		$email_text = "";
		$email_text .= "The following message was sent by " . $this->contact . ": " . $this->message ;
		$mail = new Zend_Mail();
		$mail->setBodyText($email_text);
		$mail->setFrom('donotreply@mashpia.com', 'Mashpia.com');
		$mail->addTo($this->email);
		$mail->addCC("info@mashpia.com");
		$mail->setSubject('Mashpia Contact Us Request');
		if ($mail->send())
		{
		//	$this->view->message = "A new password has been emailed to your email address $email.<br />";
		}
	}


	// confirmation of contact us
	public function confirmAction()
	{
	}

	public function deleteAction()
	{
		echo "<br>deleting";
	}

	public function listAction()
	{
		// $Contacts = new Contact();
		// $array = $Contacts->get_all_contact_us();
		// $this->view->array = $array;
	}

	public function editAction(){
		echo "<br>editing";
	}
}
?>
