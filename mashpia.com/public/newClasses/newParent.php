<?
require 'baseParent.php';

class NewParent extends BaseParent {
	private $isShliach;
		
	public function __construct() {
		parent::__construct();
		$this->isShliach = false;
	}
	
	public function setAsShliach() {
		$this->isShliach = true;
	}
	
	public function action($data) {
		$this->data = $data;
		
		$sql = "INSERT INTO admins set ";
		foreach ($this->fields as $k) {
			if (isset($data[$k])) {
				if (is_numeric(($data[$k] ))) $sql .= $k . " = " . mysql_real_escape_string( $data[$k] ) . ", ";
				else $sql .= $k . " = \"" . mysql_real_escape_string( $data[$k] ) . "\", ";
			}
		}
		$sql .= "is_parent = 1";
		if ($this->isShliach) 
			$sql .= ", is_shliach = 1";
		if (@mysql_query($sql)) {
			$this->data['admin_id'] = mysql_insert_id();
			// create the helpdesk account
			$this->createHelpdeskAccount();
			
			return true;
		} else {
			return false;
		}
	}
	
	public function createHelpdeskAccount(){
		// log the user into the helpdesk system as well
		require_once($_SERVER["DOCUMENT_ROOT"].'/helpdesk/control/connect.php');
		require_once($_SERVER["DOCUMENT_ROOT"].'/helpdesk/control/functions.php');
		// load up the functions for adding users to the DBS
		require_once($_SERVER["DOCUMENT_ROOT"].'/tasks/forms/functions/helpdesk_account_migration.php');
		
		create_admin($this->data);
	}
	
	public function sendConfEmail() {
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= 'From: Tzivos Hashem <cth@tzivoshashem.org>' . "\r\n";
		$headers .= 'Reply-to: cth@tzivoshashem.org' . "\r\n";
		$to = $this->data['admin_email'];
		$subject = "Your new account with Chayolei Tzivos Hashem.";
		$html = "Hi!<br /><br />
A parent account has been created for your child (or children) in Chayolei Tzivos Hashem.<br /><br />
With it, you’ll able to mark your children’s missions daily, straight from any smartphone (or computer). You will also be able to check in on their progress reports, personalize their growth, and stay up-to-date on Tzivos Hashem news from bases around the world.  
<br /><br />
Darchei Hachassidus will come alive in your home as managing your kids’ Chayolei Tzivos Hashem accounts becomes easier than ever. Help your young soldier reach the greatest heights in Hashem’s army. 
<br /><br />
Your Username is: " . $this->data['username'] . " <br />
Your Password is: " . $this->data['password'] . " <br />
<br />
To change your username/password simply log into your account on tzivoshashem.com/mobile and click 'edit profile' on the top right hand corner. 
<br /><br />
For any questions, help, or feedback, contact your school's Base Commander.
<br /><br />
Wishing you much Yiddishe and Chassidishe Nachas,
<br /><br />
CTH Headquarters";
		$msg = "Welcome! You have a new account with Chayolei Tzivos Hashem. Your username is '<b>" . 
				$this->data['username'] . "</b>' and your password is '<b>" . $this->data['password'] . "</b>'. 
				You can use your account to manage the missions for your child(ren). 
				Just login to http://www.mashpia.com. Thank you!";
		if (@mail($to, $subject, $html, $headers)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function getAdminID() {
		return $this->data['admin_id'] ? $this->data['admin_id'] : 0;
	}
}
