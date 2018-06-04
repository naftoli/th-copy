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
    
    // CUSTOM TASKS //
	public $allow_parent_tasks;
    public $print_parent_tasks;
	
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
	public $no_label_labels = array();
		
	public $class_teacher;
	
	public $rank_name;
	public $rank_image_id;
	public $date_promoted;
	
	// USER SCHOOL CAMPAIGNS //
	public $subjects = array();
	
	public $has_won_big_prize;
	public $has_won;
	public $no_of_tickets;
    public $big_prizes_won;
    public $prizes_won = [];

	public $class_grade;
	public $class_sub;
	
	public $rank_marks = array();
	
	public $lang_id;

	// MEDALS //
	public $num_rows_medals;
	public $medals = array();
	
	// RANKS //
	public $num_rows_ranks;
	public $ranks = array();
	public $pic_mission_type;
	
	public $he_name;
	
	public $tehillim;
	
	public $mobile_pic;
	
	public $allowPersonalization;
    
    // REGISTRATION //
    public $registration_fee = false;
	
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
		$this->add_on_one = $row['add_on_one'];
		$this->add_on_two = $row['add_on_two'];
		$this->lang_id = $row['lang_id'];
		$this->pic_mission_type = $row['pic_mission_type'];
		$this->he_name = $row['he_name'];
		$this->mobile_pic = $row['mobile_pic'];
		$this->allowPersonalization = true;
		$this->allow_parent_tasks = $row['allow_parent_tasks'] == '1' ? true : false;
		$this->print_parent_tasks = $row['print_parent_tasks'] == '1' ? true : false;
	}
	
	public function get_school() {
		if ($this->school_id > 0) {
			require_once( dirname(__FILE__) . "/school.php" );
			$sql = "SELECT * FROM schools WHERE school_id=" . $this->school_id;
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);
			$school = new school($row);
			$this->school = $school;
		}
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
		if ($this->school_id > 0) {
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
	}
	
	function get_school_class() 
	{
		require_once 'school_class.php';	
		if ($this->class_id > 0)
		{
			$sql = "SELECT * FROM classes WHERE class_id=" . $this->class_id;
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);		
			$school_class = new school_class($row);
			$this->school_class = $school_class;
		}
    }

    /**
     * get_profile_picture
     * 
     * Returns a absoulte link to the users profile picture.
     * Note: for mashpia.com, link will begin with /
     *
     * @return string
     */
    public function get_profile_picture() {
        if ( $this->mobile_pic ) {
            return "/mobile/reg/" . $this->mobile_pic;
        } else if ( $this->user_photo_id ) {
            return "/file_view.php?id=" . $this->user_photo_id;
        } else {
            return "/mobile/img_new/boy-color-green-svg.svg";
        }
    }

    /**
     * get_address
     * 
     * Returns a formatted user address.
     *
     * @param string $newline newline character to break address on
     * @return string
     */
    public function get_address( $newline = "<br/>" )
    {
        $address  = $this->user_address1 . $newline;
        if ($this->user_address2) $address .= $this->user_address2 . $newline;
        $address .= $this->user_city . ", " . $this->user_state . " " . $this->user_postal;
        
        return $address;
    }
    /**
     * get_mission_type
     * 
     * Returns the mission type from the school_type_id
     *
     * @return string
     */
    public function get_mission_type()
    {
        if ( in_array( $this->school_type_id, [ 2, 3 ] ) )
            return "chabad";
        else if ( in_array( $this->school_type_id, [ 12, 13 ] ) )
            return "frum";
        else
            return "n/a";
    }

    /**
     * get_chayolei
     * 
     * Returns true if chidon and yan are both false
     *
     * @return string
     */
    public function is_chayolei()
    {
        $query = mysql_query(
            "SELECT chidon, yan FROM users WHERE user_id = " . $this->user_id . ";"
        );
        $row = mysql_fetch_assoc( $query );
        
        return $row['chidon'] == 0 && $row['yan'] == 0;
    }
    
    /**
     * get_chidon_info
     *
     * @return array
     */
    public function get_chidon_info( $year )
    {   // make sure the year is here
        if ( $year ) $year = mysql_real_escape_string( $year );
        else return false;
        // load all the chidon info...
        $query = mysql_query(
            "SELECT * FROM th_chidon WHERE year = '$year' AND user_id = '" . $this->user_id . "'"
        );
        return mysql_fetch_assoc( $query );
    }

    /**
     * get_prizes_won
     * 
     * Returns array of prizes won
     *
     * @return array
     */
    public function get_prizes_won()
    {
        $query = mysql_query(
             " SELECT rw.raffle_id, rw.prize_id, shipped, r.name as raffle_name, r.date_ran, r.type, "
            ." p.name as prize_name_weekly, p.thumbnail, pa.prize_name, pa.prize_image_id "
            ." FROM raffle_winners rw "
            ." JOIN raffles r using (raffle_id) "
            ." LEFT JOIN prizes p on rw.prize_id = p.prize_id and r.type='weekly' "
            ." LEFT JOIN prizes_auction pa on rw.prize_id = pa.prize_id and r.type='monthly' "
            ." WHERE user_id = ".$this->user_id.";"
        );
        while( $row = mysql_fetch_assoc( $query ) ){
            $prize_won = [
                "raffle_id" => $row['raffle_id'],   "prize_id" => $row['prize_id'],
                "shipped"   => $row['shipped'],     "raffle_name" => $row['raffle_name'],
            ];
            if ( $row['type'] == "weekly" ){
                $prize_won['prize_name'] = $row['prize_name_weekly'];
                $prize_won['picture']    = $row['thumbnail'];
            } else {
                $prize_won['prize_name'] = $row['prize_name'];
                $prize_won['picture']    = "/file_view.php?id=" . $row['prize_image_id'];
            }
            $this->prizes_won[] = $prize_won;
        }
        return $this->prizes_won;
    }
    
    /**
     * get_registration_fee
     *
     * Gets the registration fee for the user object using the registration_rate function
     * 
     * @return number
     */
    public function get_registration_fee(){
        require_once( dirname(__FILE__) . "/../functions/registration_rate.php" );
        return $this->registration_fee = registration_rate( $this->user_id );
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
		$sql = "SELECT r.rank_ord, r.rank_name, r.rank_image_id, date_promoted FROM rank_marks JOIN ranks AS r USING(rank_ord) WHERE user_id=" . $this->user_id . " ORDER BY rank_ord DESC LIMIT 1";
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		if ($row) {
			$this->rank_ord = $row["rank_ord"];
			$this->rank_name = $row["rank_name"];
			$this->rank_image_id = $row['rank_image_id'];
			$this->date_promoted = $row['date_promoted'];
		}
	}
	
	public function disablePersonalization() {
		$this->allowPersonalization = false;
	}
	
	function get_user_tracks($subject_id, $start_date, $end_date, $tasks = array(), $lang = 1, $printing_mode = false) {
		if ($subject_id == -1)
			$sql = "SELECT ut.* FROM user_tracks AS ut JOIN subjects AS s USING (subject_id) WHERE ut.user_id=" . $this->user_id . " and ut.enrolled = 1 ORDER BY s.subject_ord";
		else
			$sql = "SELECT ut.* FROM user_tracks AS ut JOIN subjects AS s USING (subject_id) WHERE ut.user_id=" . $this->user_id . " and ut.enrolled = 1 AND ut.subject_id=" . $subject_id . " ORDER BY s.subject_ord";
		
		$query = mysql_query($sql);
		
		if ( $printing_mode && !$this->print_parent_tasks ) {
			$print_custom_parent_tasks = false;
		} else {
			$print_custom_parent_tasks = true;
		}
		
		while ($row = mysql_fetch_assoc( $query ) ) {
			if ($row["level"] > 0 && $row["track_id"] > 0) {
				$user_track = new user_track($row);
				$user_track->get_subject_info();
				//if (!empty($tasks)) $user_track->get_date_tasks_missions($this->school_type_id, $start_date, $end_date, $tasks, $lang);
				$user_track->get_date_tasks_missions($this->school_type_id, $start_date, $end_date, $tasks, $lang, $this->allowPersonalization, $print_custom_parent_tasks);

				array_push($this->user_tracks, $user_track);
				
				if ($row['subject_id'] == 1) {
					$this->tehillim = array(
						'age' 	 => $row['level'], 
						'ladder' => $row['track_id']);
				}
			}
		}
		
		for ($utno = 0; $utno < count($this->user_tracks); $utno++) 
		{
			$user_track = $this->user_tracks[$utno];
			
			for ($dtno = 0; $dtno < count($user_track->daily_tasks); $dtno++) {
				//echo "<input type='hidden' name='DAILY TASKS INFO ONE' value='" . $user_track->daily_tasks[$dtno]->label_name . "->" . $user_track->daily_tasks[$dtno]->task_name . "'>\n";
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
		    
            //echo "<input type='hidden' name='daily_task' value='" . $this->daily_tasks[$dtno]->label_name . "' />";

			//if ($end_date == 2455640)
			//	echo "<input type='hidden' name='FOUND' value='FOUND'>\n";
			
			$label_name = $new_string = str_replace(":", " ", $this->daily_tasks[$dtno]->label_name);
			$start_date = $this->daily_tasks[$dtno]->start_date;
			$end_date = $this->daily_tasks[$dtno]->end_date;
			$frequency_id = $this->daily_tasks[$dtno]->frequency_id;
			$key = $label_name . ":" . $start_date . ":" . $end_date;

			if (!in_array($key, $this->daily_labels))
				$in_array = "FALSE";
			else
				$in_array = "TRUE";
			
			//echo "<input type='hidden' name='DAILY TASKS INFO TWO' value='" . $this->daily_tasks[$dtno]->task_name . "-" . $key . "-" . $in_array . "'>\n";
				
			if (!in_array($key, $this->daily_labels)) {
				//echo "<input type='hidden' name='DAILY LABEL PUSH' value='" . $this->daily_tasks[$dtno]->task_name . "-" . $key . "-" . $in_array . "'>\n";
				array_push($this->daily_labels, $key);
				$this->sorted_daily_labels[$frequency_id] = $key;
			}
		}
		
		ksort($this->sorted_daily_labels);        
		foreach ($this->sorted_daily_labels as $daily_label) 
		{
			//echo "<input type='hidden' name='SORTED DAILY LABEL INFO' value='" . $daily_label . "'>\n";
		}
		
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
			
			$start = $no_label_task->start_date;
			$end = $no_label_task->end_date;
			$key2 = $mission_name . ":" . $start . ":" . $end;
			if (!in_array($key2, $this->no_label_labels))
				array_push($this->no_label_labels, $key2);
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

	function get_subjects()
	{
		$sql = "SELECT ss.subject_id, s.subject_name ";
		$sql = $sql . "FROM school_subjects AS ss ";
		$sql = $sql . "JOIN subjects AS s USING (subject_id) ";
		$sql = $sql . "WHERE school_id=" . $this->school_id;
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) 
		{
			$subject = new subject($row);
			//$subject->get_subject_medals_not_awarded($this->user_id);
			//$subject->get_awarded_medals($this->user_id);
			array_push($this->subjects, $subject);
		}

	}
			
	public function set_school_name($school_name) 
	{
		$this->school_name = $school_name;
	}	

	public function set_no_of_tickets($no_of_tickets)
	{
		$this->no_of_tickets = $no_of_tickets;
	}
	
	public function set_has_won_big_prize($has_won_big_prize)
	{
		if ($has_won_big_prize == true)
			$this->has_won_big_prize = "*** HAS WON BIG PRIZE ***";
		else
			$this->has_won_big_prize = "";
	}
	
	public function set_has_won($has_won)
	{
		if ($has_won == true)
			$this->has_won = "*** HAS WON ***";
		else
			$this->has_won = "";
	}
	
	public function set_big_prizes_won($big_prizes_won)
	{
		$this->big_prizes_won = $big_prizes_won;
	}
	
	function get_all_subjects($subject_id)
	{
		$sql = "SELECT * FROM subjects WHERE subject_type NOT IN ('school_points', 'home_points', 'Tanya') ORDER BY subject_name ";
		if ($subject_id > 0)
			$sql = $sql . "AND subject_id=" . $subject_id;
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query))
		{
			$subject = new subject($row);
			array_push($this->subjects, $subject);
		}		
	}
	
	function get_class()
	{
		if ($this->class_id > 0) {
			$sql = "SELECT * FROM classes WHERE class_id=" . $this->class_id;
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);
			$this->class_grade = $row['class_grade'];
			$this->class_sub = $row['class_sub'];
		}
    }
    
    function get_grade()
    {
        $grade = $this->class_grade;
        if ( $this->class_sub ) $grade .= " - " . $this->class_sub;

        return $grade;
    }
	
	function set_class($row)
	{
		$this->class_grade = $row['class_grade'];
		$this->class_sub = $row['class_sub'];		
	}
	
	function get_rank_marks($ranks) {
	
		foreach ($ranks as $rank) {
			$sql = "SELECT date_promoted ";
			$sql = $sql . "FROM rank_marks ";
			$sql = $sql . "WHERE rank_ord=" . $rank->rank_ord . " ";
			$sql = $sql . "AND user_id=" . $this->user_id . " ";
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);
			$rank_mark = new rank_mark($row);
			array_push($this->rank_marks, $rank_mark);
		}
	}
	
	function get_medals($subject_id, $date_awarded_from, $date_awarded_to, $medals_filter) {
		$sql = "SELECT * ";
		$sql .= "FROM medal_marks AS mm ";
		$sql .= "JOIN subjects AS s USING (subject_id) ";
		$sql .= "JOIN medals AS m USING (medal_ord) ";
		$sql .= "WHERE user_id=" . $this->user_id . " ";
		$sql .= "AND date_awarded >=" . $date_awarded_from . " ";
		$sql .=" AND date_awarded <=" . $date_awarded_to . " ";

		if ($subject_id > 0)
			$sql .= "AND subject_id=" . $subject_id . " ";
		
		if ($medals_filter == 1)
			$sql .= "AND mm.date_received IS NULL ";
		elseif ($medals_filter == 2)
			$sql .= "AND mm.date_received IS NOT NULL ";

		$query = mysql_query($sql);
		$this->num_rows_medals = mysql_num_rows($query);
		
		while ($row = mysql_fetch_assoc($query)) {
			array_push($this->medals, $row);
		}	
	}
	
	function get_medals_two($subject_id, $date_awarded_from, $date_awarded_to, $medals_filter) {
		$sql = "SELECT * ";
		$sql .= "FROM medal_marks AS mm ";
		$sql .= "JOIN subjects AS s USING (subject_id) ";
		$sql .= "JOIN medals AS m USING (medal_ord) ";
		$sql .= "WHERE user_id=" . $this->user_id . " ";
		$sql .= "AND date_awarded >=" . $date_awarded_from . " ";
		$sql .=" AND date_awarded <=" . $date_awarded_to . " ";
		$sql .= "AND date_awarded not in (2455817,2455772) ";

		if ($subject_id > 0)
			$sql .= "AND subject_id=" . $subject_id . " ";
		
		if ($medals_filter == 1)
			$sql .= "AND mm.date_received IS NULL ";
		elseif ($medals_filter == 2)
			$sql .= "AND mm.date_received IS NOT NULL ";

		$query = mysql_query($sql);
		$this->num_rows_medals = mysql_num_rows($query);
		
		while ($row = mysql_fetch_assoc($query)) {
			array_push($this->medals, $row);
		}	
	}
	
	function get_ranks($from_promoted, $to_promoted, $ranks_card_filter, $ranks_book_filter) {
		$sql = "SELECT * ";
		$sql .= "FROM rank_marks AS rm ";
		$sql .= "JOIN ranks AS r USING (rank_ord) ";
		$sql .= "WHERE user_id=" . $this->user_id . " ";
		$sql .= "AND rm.date_promoted >=" . $from_promoted . " ";
		$sql .= "AND rm.date_promoted <=" . $to_promoted . " ";
		
		if ($ranks_card_filter == 1) 
			$sql .= "AND rm.date_card_received IS NOT NULL ";
		elseif ($ranks_card_filter == 2) 
			$sql .= "AND rm.date_card_received IS NULL ";
		
		if ($ranks_book_filter == 1) 
			$sql .= "AND rm.date_book_received IS NOT NULL ";
		elseif ($ranks_book_filter == 2) 
			$sql .= "AND rm.date_book_received IS NULL ";
		
		//echo "<input type='hidden' name='sql' value='" . $sql . "'>";
		$query = mysql_query($sql);
		$this->num_rows_ranks = mysql_num_rows($query);

		while ($row = mysql_fetch_assoc($query)) {
			array_push($this->ranks, $row);
		}
	}
	
	function get_ranks_two($from_promoted, $to_promoted, $ranks_card_filter, $ranks_book_filter) {
		$sql = "SELECT * ";
		$sql .= "FROM rank_marks AS rm ";
		$sql .= "JOIN ranks AS r USING (rank_ord) ";
		$sql .= "WHERE user_id=" . $this->user_id . " ";
		$sql .= "AND rm.date_promoted >=" . $from_promoted . " ";
		$sql .= "AND rm.date_promoted <=" . $to_promoted . " ";
		$sql .= "AND rm.date_promoted not in (2455817,2455772) ";
		
		if ($ranks_card_filter == 1) 
			$sql .= "AND rm.date_card_received IS NOT NULL ";
		elseif ($ranks_card_filter == 2) 
			$sql .= "AND rm.date_card_received IS NULL ";
		
		if ($ranks_book_filter == 1) 
			$sql .= "AND rm.date_book_received IS NOT NULL ";
		elseif ($ranks_book_filter == 2) 
			$sql .= "AND rm.date_book_received IS NULL ";
		
		//echo "<input type='hidden' name='sql' value='" . $sql . "'>";
		$query = mysql_query($sql);
		$this->num_rows_ranks = mysql_num_rows($query);

		while ($row = mysql_fetch_assoc($query)) {
			array_push($this->ranks, $row);
		}
	}
	
	function get_class_info() {
		$sql = "SELECT c.class_grade, c.class_sub FROM classes AS c WHERE class_id=" . $this->class_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->class_grade = $row['class_grade'];
		$this->class_sub = $row['class_sub'];				
	}
	
	function getRankInfo($plusMedals = false) {
		//find out which rank earned
		$sql = "select rank_ord from rank_marks where user_id = " . $this->user_id . " order by rank_ord desc limit 1";
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$rank = $row['rank_ord'];
		
		//find out how many medals needed for next rank
		$sql = "select * from ranks where rank_ord = " . ++$rank;
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$needed = $row['medals_required'];
		$name = $row['rank_name'];
		
		//find out how many total medals user earned and subtract from medals needed for next medal
		$sql = "select count(*) as total from medal_marks where user_id = " . $this->user_id;
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$total = $row['total'];
		
		if ($plusMedals) 
			$str = ($needed - $total) . " Medals to " . $name;
		else 
			$str = ($needed - $total) . " to " . $name;
		return $str; 
	}
}
?>