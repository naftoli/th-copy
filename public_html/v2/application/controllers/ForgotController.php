<?php

/**********************************************************
  @Author: Mohannad El-Barachi
  @Copyright:
  Forgot Controller - Takes a user's email and resets their password
  *********************************************************/

class ForgotController extends Zend_Controller_Action
{
		function init()
	    {}

	    function preDispatch()
	    {}

	    public function indexAction()
	    {
	    	// Check if the form has been posted
			if ($this->_request->isPost())
			{
				// Collect the data from the form & strip all tags
				Zend_Loader::loadClass('Zend_Filter_StripTags');
				$f = new Zend_Filter_StripTags();
				$email = $f->filter($this->_request->getPost('email'));
				$email = trim($email);
				$email = mysql_real_escape_string($email);

				// Send the email to the view controller
				$this->view->email = $email;

				// Validate
				if (empty($email))
				{
					$this->view->message = '<div class="error">Please enter your email</div>';
				}

				// Start the Users Object
				$User = new Users();
				$current_user = $User->get_user_from_email($email);

				if ($current_user)
				{
					// Reset the password & email it to them
					$new_password = $User->reset_password($current_user->user_id);

					// Email it to user
					if ($new_password)
					{
						// Email it to the user
	           			$email_text = "Hello, " . $User->get_user_full_name($current_user->user_id) . "\n\n";
						$email_text .= "So you forgot your password? No worries, we just reset it for you.\n\n";
						$email_text .= "Your new temporary password is: " . $new_password . "\n\n";
						$email_text .= "Please change it as soon as you log in.\n";
						$email_text .= "Thanks,\nThe Mashpia Support Team";

						$mail = new Zend_Mail();
						$mail->setBodyText($email_text);
						$mail->setFrom('donotreply@mashpia.com', 'Mashpia - Password Reset');
						$mail->addTo($email);
						$mail->setSubject('Mashpia Password Reset');

						if ($mail->send())
						{
							$this->view->message = "A new password has been emailed to your email address $email.<br />";
						}
					}
					else
					{
						// Here it failed
						$this->view->message = text("Sorry, there was an error") . ": FI101";
					}
				}
				else
				{
					$this->view->message = "<div class='error'>Sorry, this email doesn't exist</div>";
				}

			}

	    }

	    public function testAction()
	    {
	    	print $this->_request->isGet('host');
	    	exit;
	    }
}

?>