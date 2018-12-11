<?
class UserRegister {
	private $data;
	private $users;
	
	public function __construct($users, $year, $admin) {
		$this->data['year'] = $year;
		$this->data['admin'] = $admin;
		if (is_array($users)) {
			$this->users = $users;
		} else {
			$this->users = array($users);
		}
	}
	
	public function register() {
		foreach ($this->users as $user) {
			$sql = "insert ignore into user_registration 
					set user_id = $user, 
					year = " . $this->data['year'] . ", 
					admin_id = " . $this->data['admin'];
			if (! @mysql_query($sql)) {
				return false;
			}
		}
		$this->updateStart();
		return true;
	}
	
	private function updateStart() {
		foreach ($this->users as $user) {
			$sql = "update users set user_registered = now() where user_registered is null and user_id = " . $user;
			@mysql_query($sql);
			
			$sql = "update users set user_start_date = " . unixtojd() . " where user_id = " . $user . " and user_start_date is null";
			@mysql_query($sql);
		}
	}
} 
?>