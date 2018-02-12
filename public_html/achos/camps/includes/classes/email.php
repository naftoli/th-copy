<?php
class email {	
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
?>

