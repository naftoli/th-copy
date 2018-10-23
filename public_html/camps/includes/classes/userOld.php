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
	//public $add_on_one_grades = array();
	public $add_on_two;
	//public $add_on_two_grades= array();
	public $school;	
	public $school_class;	
	public $member_points = array();
	public $groups = array();
	public $parent_id;
	public $childs_parent;
	
	public $user_tracks = array();
		
	public $daily_tasks = array();
	public $weekly_tasks = array();
	public $shabbos_tasks = array();
	public $no_label_tasks = array();
	
	// DAILY TASKS //
	public $daily_labels = array();
	public $sorted_daily_labels = array();
	
	// WEEKLY TASKS //
	public $weekly_labels = array();
	public $sorted_weekly_labels = array();
	
	// SHABBOS TASKS //
	public $shabbos_labels = array();
	public $sorted_shabbos_labels = array();
	
	// NO LABEL TASKS //
	public $no_label_subjects = array();
		
	public $user_add_ons = array();
	
	public $class_teacher;
	
	public $rank_name;
	public $rank_image_id;
	
	function __construct($row){
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
	}
	
	public function get_school() {
		require_once("school.php");
		
		$sql = "SELECT * FROM schools WHERE school_id=" . $this->school_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$school = new \classes\school($row);
		$this->school = $school;
	}
	
	public function get_user_task_points() {
		$this->task_points = 0;
		$sql = "SELECT SUM(ct.points) AS total_points ";
		$sql = $sql . "FROM member_tasks AS mt ";
		$sql = $sql . "JOIN camp_tasks AS ct USING (camp_task_id) ";
		$sql = $sql . "WHERE mt.user_id=" . $this->user_id . " AND mt.completed=1";
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		if ($row['total_points'] > 0)
			$this->task_points =  $row['total_points'];
	}
	
	public function get_user_total_points() {
		$sql = "SELECT SUM(points) AS total_points ";
		$sql = $sql . "FROM member_points AS mp ";
		$sql = $sql . "WHERE mp.user_id=" . $this->user_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->total_points = $row['total_points'];
		
		$sql = "SELECT SUM(ct.points) AS total_points ";
		$sql = $sql . "FROM member_tasks AS mt ";
		$sql = $sql . "JOIN camp_tasks AS ct USING (camp_task_id) ";
		$sql = $sql . "WHERE mt.user_id=" . $this->user_id . " AND mt.completed=1";
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->total_points = $this->total_points + $row['total_points'];

		$total_spent = 0;
		$sql = "SELECT prize_points, prize_quantity FROM store_purchases WHERE user_id=" . $this->user_id;
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			$total_spent = $total_spent+ ($row['prize_points'] * $row['prize_quantity']);
		}	
		$this->total_points = $this->total_points - $total_spent;
	}
	
	public function get_user_groups() {
		$sql = "SELECT g.* ";
		$sql = $sql . "FROM member_groups AS mg ";
		$sql = $sql . "JOIN groups AS g USING (group_id) ";
		$sql = $sql . "WHERE mg.user_id=" . $this->user_id . " AND mg.end_date=0";
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			$group = new group($row);
			$group->get_division();
			$group->division->get_group_type();
			array_push($this->groups, $group);
		}
	}
	
	public function update_item($user_id, $item_name, $value) {
		$sql = "UPDATE users SET " . $item_name . "='" . mysql_real_escape_string($value) . "' WHERE user_id=" . $user_id;
		$query = mysql_query($sql);
		if ($query)
			return true;
		else
			return false;
	}
	
	function get_school_info() {
		$sql = "SELECT school_name, add_on_one, add_on_two FROM schools WHERE school_id=" . $this->school_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->school_name = $row["school_name"];
		
		if ($row["add_on_one"] == 1) {
			$this->add_on_one = 0;
		}
		elseif ($row["add_on_one"] == 2 || $row["add_on_one"] == 4) {
			$this->add_on_one = $row["add_on_one"];
		}
		elseif ($row["add_on_one"] == 3) {
			$sql = "SELECT grade FROM school_add_on_grades WHERE school_id=" . $this->school_id . " AND grade=" . $this->school_class->class_grade . " AND add_on_number=1";
			$query = mysql_query($sql);
			$num_rows = mysql_num_rows($query);
			if ($num_rows > 0) 
				$this->add_on_one = 3;
			else
				$this->add_on_one = 0;
		}
		
		if ($row["add_on_two"] == 1) {
			$this->add_on_two = 0;
		}
		elseif ($row["add_on_two"] == 2 || $row["add_on_two"] == 4) {
			$this->add_on_two = $row["add_on_two"];
		}
		elseif ($row["add_on_two"] == 3) {
			if ($this->school_class->class_grade != "") {
				$sql = "SELECT grade FROM school_add_on_grades WHERE school_id=" . $this->school_id . " AND grade=" . $this->school_class->class_grade . " AND add_on_number=2";
				$query = mysql_query($sql);
				$num_rows = mysql_num_rows($query);
				if ($num_rows > 0) 
					$this->add_on_two = 3;
				else
					$this->add_on_two = 0;
			}
		}
		
	}
	
	function get_school_class() {
		include_once("school_class.php"); 
		
		if ($this->class_id > 0)
		{
			$sql = "SELECT * FROM classes WHERE class_id=" . $this->class_id;
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);		
			$school_class = new school_class($row);
			$this->school_class = $school_class;
		}
	}
	
	public function get_parent() {
		$this->parent_id = 0;
		
		$sql = "SELECT admin_id FROM admin_auths WHERE auth='user' AND role_id=1 AND id=" . $this->user_id;		
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$num_rows = mysql_num_rows($query);
		
		if ($num_rows > 0)
			$this->parent_id = $row["admin_id"];
			
		return $this->parent_id;
	}	
	
	public function get_childs_parent() {
		$this->parent_id = 0;
		
		$sql = "SELECT a.* ";
		$sql = $sql . "FROM admin_auths AS aa ";
		$sql = $sql . "JOIN admins AS a USING (admin_id) ";
		$sql = $sql . "WHERE aa.auth='user' AND aa.id=" . $this->user_id;	
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->childs_parent = new admin($row);
	}
	
	function get_rank() {
		$sql = "SELECT r.rank_name, r.rank_image_id FROM rank_marks JOIN ranks AS r USING(rank_ord) WHERE user_id=" . $this->user_id . " ORDER BY rank_ord DESC LIMIT 1";
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		if ($row) {
			$this->rank_name = $row["rank_name"];
			$this->rank_image_id = $row['rank_image_id'];
		}
	}

	function get_user_add_ons() {
		$sql = "SELECT * FROM user_add_ons WHERE user_id=" . $this->user_id;
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			array_push($this->user_add_ons, $row);
		}
	}
	
	function get_user_tracks($subject_id, $start_date, $end_date) {		
		if ($subject_id == -1)
			$sql = "SELECT ut.* FROM user_tracks AS ut JOIN subjects AS s USING (subject_id) WHERE ut.user_id=" . $this->user_id . " and ut.enrolled = 1 ORDER BY s.subject_ord";
		else
			$sql = "SELECT ut.* FROM user_tracks AS ut JOIN subjects AS s USING (subject_id) WHERE ut.user_id=" . $this->user_id . " and ut.enrolled = 1 AND ut.subject_id=" . $subject_id . " ORDER BY s.subject_ord";
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			if ($row["level"] > 0 && $row["track_id"] > 0) {
				$user_track = new user_track($row);
				$user_track->get_subject_info();
				$user_track->get_date_tasks_missions($this->school_type_id, $start_date, $end_date);
				array_push($this->user_tracks, $user_track);
			}
		}
		
		for ($utno = 0; $utno < count($this->user_tracks); $utno++) {
			$user_track = $this->user_tracks[$utno];

			for ($dtno = 0; $dtno < count($user_track->daily_tasks); $dtno++) {
				array_push($this->daily_tasks, $user_track->daily_tasks[$dtno]);
			}
			for ($wtno = 0; $wtno < count($user_track->weekly_tasks); $wtno++) {
				array_push($this->weekly_tasks, $user_track->weekly_tasks[$wtno]);
			}
			for ($stno = 0; $stno < count($user_track->shabbos_tasks); $stno++) {
				array_push($this->shabbos_tasks, $user_track->shabbos_tasks[$stno]);				
			}
			for ($nltno = 0; $nltno < count($user_track->no_label_tasks); $nltno++) {
				array_push($this->no_label_tasks, $user_track->no_label_tasks[$nltno]);
			}
		}
		
		// ********** DAILY TASKS ********** //
		for ($dtno = 0; $dtno < count($this->daily_tasks); $dtno++) {	
			$label_name = $new_string = str_replace(":", " ", $this->daily_tasks[$dtno]->label_name);
			$start_date = $this->daily_tasks[$dtno]->start_date;
			$end_date = $this->daily_tasks[$dtno]->end_date;
			$frequency_id = $this->daily_tasks[$dtno]->frequency_id;
			$key = $label_name . ":" . $start_date . ":" . $end_date;
			
			if (!in_array($key, $this->daily_labels)) {
				array_push($this->daily_labels, $key);
				$this->sorted_daily_labels[$frequency_id] = $key;
			}
		}		
		ksort($this->sorted_daily_labels);
		// ********** DAILY TASKS ********** //
		
		// ********** WEEKLY TASKS ********** //
		for ($wtno = 0; $wtno < count($this->weekly_tasks); $wtno++) {		
			$label_name = $this->weekly_tasks[$wtno]->label_name;			
			$frequency_id = $this->weekly_tasks[$wtno]->frequency_id;
			
			if (!in_array($label_name, $this->weekly_labels)) {
				array_push($this->weekly_labels, $label_name);
				$this->sorted_weekly_labels[$frequency_id] = $label_name;
			}
		}
		ksort($this->sorted_weekly_labels);
		// ********** WEEKLY TASKS ********** //
		
		// ********** SHABBOS TASKS ********** //
		for ($stno = 0; $stno < count($this->shabbos_tasks); $stno++) {				
			$label_name = $this->shabbos_tasks[$stno]->label_name;
			$frequency_id = $this->shabbos_tasks[$stno]->frequency_id;
			
			if (!in_array($label_name, $this->shabbos_labels)) {
				array_push($this->shabbos_labels, $label_name);
				$this->sorted_shabbos_labels[$frequency_id] = $label_name;
			}
		}
		ksort($this->sorted_shabbos_labels);
		// ********** SHABBOS TASKS ********** //
		
		// ********** NO LABEL TASKS ********** //
		for ($nltno = 0; $nltno < count($this->no_label_tasks); $nltno++) {	
			$no_label_task = $this->no_label_tasks[$nltno];			
			$subject_name = $no_label_task->subject_name;
			$mission_name = $no_label_task->mission_name;
			$key = $subject_name . ":" . $mission_name;
			
			if (!in_array($key, $this->no_label_subjects))
				array_push($this->no_label_subjects, $key);
		}
		// ********** NO LABEL TASKS ********** //
	}	
	
	function get_september_user_tracks($subject_id, $start_date, $end_date) {		
		$sql = "SELECT ut.* FROM user_tracks AS ut JOIN subjects AS s USING (subject_id) WHERE ut.user_id=" . $this->user_id . " AND (ut.subject_id=40 OR ut.subject_id=1) ORDER BY s.subject_ord";
		
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			if ($row["level"] > 0 && $row["track_id"] > 0) {
				$user_track = new user_track($row);
				$user_track->get_subject_info();
				$user_track->get_september_date_tasks_missions($this->school_type_id, $start_date, $end_date);
				array_push($this->user_tracks, $user_track);
			}
		}
		
		for ($utno = 0; $utno < count($this->user_tracks); $utno++) {
			$user_track = $this->user_tracks[$utno];

			for ($nltno = 0; $nltno < count($user_track->no_label_tasks); $nltno++) {
				array_push($this->no_label_tasks, $user_track->no_label_tasks[$nltno]);
			}
		}
		
		
		// ********** NO LABEL TASKS ********** //
		for ($nltno = 0; $nltno < count($this->no_label_tasks); $nltno++) {	
			$no_label_task = $this->no_label_tasks[$nltno];			
			$subject_name = $no_label_task->subject_name;
			$mission_name = $no_label_task->mission_name;
			$key = $subject_name . ":" . $mission_name;
			
			if (!in_array($key, $this->no_label_subjects))
				array_push($this->no_label_subjects, $key);
		}
		// ********** NO LABEL TASKS ********** //
	}	
			
}
?>