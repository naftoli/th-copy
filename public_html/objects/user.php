<?php
class user {
	public $user_id;
	public $user_code;
	public $username;
	public $email;
	public $password;
	public $first;
	public $last;
	public $first_he;
	public $last_he;
	public $lang;
	public $school_type_id;
	public $school_id; 
	public $class_id;
	public $team_id;
	public $user_serial;
	public $fee_id;
	public $user_address1;
	public $user_address2;
	public $user_city;
	public $user_state;
	public $user_postal;
	public $user_country;
	public $user_phone;
	public $gender;
	public $user_start_date;
	public $user_registered;
	public $camp_registered;
	public $user_registration_fee;
	public $user_notes;
	public $dob;
	public $dob_he_offset;
	public $dob_he; 
	public $user_photo_id;
	public $kiosk_edit;
	public $camp_id;
	public $total_points;
	public $task_points;
	public $school_name;
	public $add_on_one;
	public $add_on_two;
	public $shirt_size;
	
	public $rank_name;
	public $rank_color;
	public $rank_image_id;
	public $class_grade;
	public $class_sub;

	public $user_add_ons = array();
	public $medals = array();
	
	function __construct($row = NULL, $user_id = NULL) {
	
		if (is_null($row)) {
			$sql = "SELECT * FROM users WHERE user_id=" . $user_id;
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);
		}
	
		$this->user_id = $row['user_id'];
		$this->user_code = $row['user_code'];
		$this->username = $row['username'];
		$this->email = $row['email'];
		$this->password = $row['password'];
		$this->first = $row['first'];
		$this->last = $row['last'];
		$this->first_he = $row['first_he'];
		$this->last_he = $row['last_he'];
		$this->lang = $row['lang'];
		$this->school_type_id = $row['school_type_id'];
		$this->school_id = $row['school_id']; 
		$this->class_id = $row['class_id'];
		$this->team_id = $row['team_id'];
		$this->user_serial = $row['user_serial'];
		$this->fee_id = $row['fee_id'];
		$this->user_address1 = $row['user_address1'];
		$this->user_address2 = $row['user_address2'];
		$this->user_city = $row['user_city'];
		$this->user_state = $row['user_state'];
		$this->user_postal = $row['user_postal'];
		$this->user_country = $row['user_country'];
		$this->user_phone = $row['user_phone'];
		$this->gender = $row['gender'];
		$this->user_start_date = $row['user_start_date'];
		$this->user_registered = $row['user_registered'];
		$this->camp_registered = $row['camp_registered'];
		$this->user_registration_fee = $row['user_registration_fee'];
		$this->user_notes = $row['user_notes'];
		$this->dob = $row['dob'];
		$this->dob_he_offset = $row['dob_he_offset'];
		$this->dob_he = $row['dob_he'];
		$this->user_photo_id = $row['user_photo_id'];
		$this->kiosk_edit = $row['kiosk_edit'];
		$this->camp_id = $row['camp_id'];	
		$this->add_on_one = $row['add_on_one'];
		$this->add_on_two = $row['add_on_two'];
		$this->shirt_size = $row['shirt_size'];
	}
	
	public function get_school() {
		$sql = "SELECT * FROM schools WHERE school_id=" . $this->school_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$school = new school($row);
		$this->school = $school;
	}
	
	function get_rank() {
		$sql = "SELECT r.rank_name, r.rank_image_id, r.rank_color FROM rank_marks JOIN ranks AS r USING(rank_ord) WHERE user_id=" . $this->user_id . " ORDER BY rank_ord DESC LIMIT 1";
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->rank_name = $row["rank_name"];
		$this->rank_color = $row["rank_color"];
		$this->rank_image_id = $row['rank_image_id'];
	}
		
	function get_class() {
		$sql = "SELECT * FROM classes WHERE class_id=" . $this->class_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->class_grade = $row['class_grade'];
		$this->class_sub = $row['class_sub'];
	}
	
	function get_user_add_ons() {
		$sql = "SELECT * FROM user_add_ons WHERE user_id=" . $this->user_id;
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			array_push($this->user_add_ons, $row);
		}
	}	
	
	function get_medals ($subject_id) {
		$sql = "SELECT * FROM medal_marks WHERE user_id=" . $this->user_id . " JOIN medals AS m USING (medal_ord) ";
		if ($subject_id > 0)
			$sql .= "AND subject_id=" . $subject_id;
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			array_push($this->medals, $row);
		}			
	}
	
}
?>