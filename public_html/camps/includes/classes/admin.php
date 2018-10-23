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
	public $is_parent;
	public $reminders;
	public $child_first;
	public $child_last;
	public $user_code;
	public $groups = array();
	public $children = array();
	public $registered_children = array();
	public $sponsors = array();
	public $auths = array();
	public $schools = array();
    public $h_school;
	
	public function __construct($row){
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
		$this->reminders = $row["reminders"];
		$this->is_parent = $row["is_parent"];
        $this->h_school = false;		
	}

    public function setHschool() {
        $this->h_school = true;
    }
	
	public function get_admin_groups() {
		$sql = "SELECT g.* ";
		$sql = $sql . "FROM staff_groups AS sg ";
		$sql = $sql . "JOIN groups AS g USING (group_id) ";
		$sql = $sql . "WHERE admin_id=" . $this->admin_id;	
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			$group = new group($row);
			$group->get_division();
			$group->division->get_group_type();
			array_push($this->groups, $group);
		}		
	}
	
	public function update_item($admin_id, $field_name, $value) {
		$sql = "UPDATE admins SET " . $field_name . "='" . mysql_real_escape_string($value) . "' WHERE admin_id=" . $admin_id;
		$query = mysql_query($sql);
		if ($query)
			return true;
		else
			return false;	
	}
	
	public function get_children() {
		include_once("user.php");
		include_once("school.php");
		
		$sql = "SELECT u.* FROM admin_auths AS aa JOIN users AS u ON (aa.id=u.user_id) WHERE admin_id=" . $this->admin_id . " AND auth='user'";
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			$user = new user($row);
			$user->get_school();			
			if ($user->school->school_settings != "home_school") {
				$user->get_school_class();
				$user->get_school_info();
			}
			array_push($this->children, $user);
		}
	}
	
	public function get_registered_children() {
		include_once("user.php");
		include_once("school.php");
		
		$sql = "SELECT u.* FROM admin_auths AS aa JOIN users AS u ON (aa.id=u.user_id) WHERE admin_id=" . $this->admin_id . " AND auth='user' and u.user_registered > 0";
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			$user = new user($row);
			$user->get_school();			
			if ($user->school->school_settings != "home_school") {
				$user->get_school_class();
				$user->get_school_info();			
			}
			array_push($this->registered_children, $user);
		}
	}
	
	function get_markable_children() {
		include_once("user.php");
		
		$sql  = "SELECT u.* ";
		$sql .= "FROM admin_auths AS aa ";
		$sql .= "JOIN users AS u ON (aa.id=u.user_id) ";
		$sql .= "WHERE admin_id = " . $this->admin_id . " ";
		$sql .= "AND aa.auth = 'user' ";
		$sql .= "AND u.parent_marking = 1 ";
		$sql .= "AND u.user_registered > 0";
		//$sql .= "and u.school_id is not null and u.class_id is not null";
		//echo $sql;
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			$user = new user($row);
			$user->get_school();
			if ($user->school_id && $user->school->school_settings != "home_school") {
				$user->get_school_class();
				$user->get_school_info();
			}
			array_push($this->children, $user);
		}
	
	}
	
	public function set_children($row) {
		$this->child_first = $row["child_first"];
		$this->child_last = $row["child_last"];
		$this->user_code = $row["user_code"];
	}
	
	function get_unregistered_children() {
		$sql = "SELECT u.* ";
		$sql .= "FROM admin_auths AS aa ";
		$sql .= "JOIN users AS u ON (aa.id=u.user_id) ";
		//$sql .= "LEFT JOIN user_add_ons AS uao ON (u.user_id=uao.user_id) ";
		$sql .= "WHERE admin_id=" . $this->admin_id . " ";
		$sql .= "AND auth='user' 
		          AND u.user_registered IS NULL 
		          and u.school_id is not null 
		          and u.class_id is not null";
		
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			$user = new user($row);
			$user->get_school_class();
			$user->get_school_info();	
			$user->get_user_add_ons();
			array_push($this->children, $user);
		}
	}
	
	public function get_sponsors() {
		$sql = "SELECT * FROM admin_sponsors WHERE admin_id=" . $this->admin_id;
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			$name = $row["name"];
			$is_regular  = $row["is_regular "];
			$sponsor = compact('name', 'is_regular');
			array_push($this->sponsors, $sponsor);
		}
	}
	
	public function get_school_id() {
		$sql = "SELECT id FROM admin_auths WHERE admin_id=" . $this->admin_id . " AND auth='school'";
		$query = mysql_query($sql);
		if ( $row = mysql_fetch_assoc($query) ) {
		  $this->school_id = $row["id"];
        } else {
            $this->school_id = 0;
        }
	}
	
	public function get_schools() {
		$sql = "SELECT school_id, s.school_name ";
		$sql .= "FROM admin_auths AS aa ";
		$sql .= "JOIN schools AS s ON (aa.id=s.school_id) ";
		$sql .= "WHERE admin_id=" . $this->admin_id . " ";
		$sql .= "AND auth='school'";
		$query = mysql_query($sql);
		
		while ($row = mysql_fetch_assoc($query)) {
			$school_id = $row['school_id'];
			$school_name = $row['school_name'];
			$school = compact('school_id', 'school_name');
			
			array_push($this->schools, $school);
		}
	}
	
	
	public function get_auths() {
		$sql = "SELECT auth FROM admin_auths WHERE admin_id=" . $this->admin_id;
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			array_push($this->auths, $row["auth"]);
		}		
	}	
}
?>