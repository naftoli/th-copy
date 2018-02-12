<?php
class admin {
	
	public $admin_id;
	public $username;
	public $auth;
	public $password;
	public $title; 
	public $first; 
	public $last;
	public $lang; 	
	public $admin_address1; 	
	public $admin_address2; 	
	public $admin_city; 	
	public $admin_state; 	
	public $admin_postal; 	
	public $admin_country; 	
	public $admin_phone_work;
	public $admin_phone_home;
	public $admin_phone_mobile;
	public $admin_email;
	public $camp_id;
	public $staff_type_id; 
	public $staff_photo_id;
	
	public $school_id;
	
	public $school;
	
	public $schools = array();
	public $children = array();
	
	public function __construct($row = NULL, $admin_id = NULL) {
	
		if (is_null($row)) {
			$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_id;
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);
		}
		
		$this->admin_id = $row['admin_id'];
		$this->username = $row['username'];
		$this->auth = $row['auth'];
		$this->password = $row['password'];
		$this->title = $row['title']; 
		$this->first = $row['first']; 
		$this->last = $row['last'];
		$this->lang = $row['lang']; 	
		$this->admin_address1 = $row['admin_address1']; 	
		$this->admin_address2 = $row['admin_address2']; 	
		$this->admin_city = $row['admin_city']; 	
		$this->admin_state = $row['admin_state']; 	
		$this->admin_postal = $row['admin_postal']; 	
		$this->admin_country = $row['admin_country']; 	
		$this->admin_phone_work = $row['admin_phone_work'];
		$this->admin_phone_home = $row['admin_phone_home'];
		$this->admin_phone_mobile = $row['admin_phone_mobile'];
		$this->admin_email = $row['admin_email'];
		$this->camp_id = $row['camp_id'];
		$this->staff_type_id = $row['staff_type_id']; 
		$this->staff_photo_id = $row['staff_photo_id'];
	}
	
	public function get_school_id() {
		$sql = "SELECT id FROM admin_auths WHERE admin_id=" . $this->admin_id . " AND auth='school'";
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->school_id = $row["id"];
	}
	
	function get_schools() {
		if ($this->auth == 'super')
			$sql = "SELECT * FROM schools ORDER BY school_name";
		else
			$sql = "SELECT s.* FROM admin_auths AS aa JOIN schools AS s ON (aa.id=s.school_id) WHERE aa.auth='school' AND aa.admin_id=" . $this->admin_id . " ORDER BY school_name";
		$query = mysql_query($sql);
		$row_num = 0;
		while ($row = mysql_fetch_assoc($query)) {
			$row_num++;
			if ($row_num == 1)
				$this->school_id = $row["school_id"];
			$school = new school($row);
			array_push($this->schools, $school);
		}
	}
	
	function get_school($school_id) {
		$sql = "SELECT * FROM schools WHERE school_id=" . $school_id . " ORDER BY school_name";
		$query = mysql_query($sql);

		while ($row = mysql_fetch_assoc($query)) {
			$this->school = new school($row);
			$this->school->get_subjects();
			$this->school->get_classes();
			$this->school->get_users();
		}	
	}
	
	public function get_children() {
		$sql = "SELECT u.* ";
		$sql = $sql . "FROM admin_auths AS aa ";
		$sql = $sql . "JOIN users AS u ON (aa.id=u.user_id) ";
		$sql = $sql . "WHERE admin_id=" . $this->admin_id . " ";
		$sql = $sql . "AND auth='user'";
		
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			$user = new user($row);
			$user->get_school();	
			$user->get_user_add_ons();										
			array_push($this->children, $user);
		}
	}	
		
}
?>