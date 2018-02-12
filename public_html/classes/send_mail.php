<?php
class MailClass
{	
	private $to;
	private $subject;
	private $message;
	private $headers;

	function __construct() {
	}

	function send_mail($mail_parms){		
		$this->to = $mail_parms['to'];		
		$this->subject = $mail_parms['subject'];
		$this->message = $mail_parms['message'];
		$this->headers = $mail_parms['headers'];
		
		$mail_sent = @mail($this->to, $this->subject, $this->message, $this->headers);
		if ($mail_sent) 
			$message = true;
		else
			$message = false;
		return $message;
	}
}

// sample usage:
// -------------
// $mail_parms = array();
// $mail_parms['to'] = 'itps@ftml.net';
// $mail_parms['subject'] = "Program Director Invitation";
// $mail_parms['message'] = "You have been invited to be Program Director.";		
// $mail_parms['headers'] = "From: itps@ftml.net\r\nReply-To: itps@ftml.net";

// $myMailClass = new MailClass();
// $success = $myMailClass->send_mail($mail_parms);