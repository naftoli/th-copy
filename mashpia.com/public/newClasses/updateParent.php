<?
require 'baseParent.php';

class UpdateParent extends BaseParent {
	
	public function __construct() {
		parent::__construct();
	}
	
	public function action($data) {
		$this->data = $data;
		
		$sql = "update admins set ";
		foreach ($this->fields as $k) {
			if (isset($data[$k])) {
				$sql .= $k . " = '" . $data[$k] . "', ";
			}
		}
		$sql .= "is_parent = 1  
				where admin_id = " . $this->data['admin_id'];
		if (@mysql_query($sql)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function sendConfEmail() {
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= 'From: Tzivos Hashem <cth@tzivoshashem.org>' . "\r\n";
		$headers .= 'CC: cth@mashpia.com, shimmy@jcm.museum' . "\r\n";
		$to = $this->data['admin_email'];
		$subject = "Your Chayolei Tzivos Hashem account.";
		$msg = "Thank you for updating your account with us. Your changes have been applied.";
		if (@mail($to, $subject, $msg, $headers)) {
			return true;
		} else {
			return false;
		}
	}
}
?>