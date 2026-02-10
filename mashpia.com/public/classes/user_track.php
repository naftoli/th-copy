<?php
class user_track 
{
	public $user_id;
	public $subject_id;
	public $track_id;
	public $level; 	
	public $enrolled;
	public $subject_name;
	public $subject_image_id;
	public $date_tasks_missions = array();
	
	public $school_type_id;
	
	public $daily_tasks = array();
	public $weekly_tasks = array();
	public $shabbos_tasks = array();
	public $no_label_tasks = array();
	public $pesukim_tasks = array();
	
	public $show_user_track;
	public $medals = array();
	public $noMedals;
	
	public $completed_missions;
	public $incomplete_missions;
	public $possible_missions;
	public $medal_awarded;
	
	public $missions = array();
	
	public $first;
	public $last;
	
	public $start_date;
	public $end_date;
	
	function __construct($row)
	{
		$this->user_id = $row["user_id"];
		$this->subject_id = $row["subject_id"];
		$this->track_id = $row["track_id"];
		$this->level = $row["level"]; 	
		$this->enrolled = $row["enrolled"];
		$this->show_user_track = false;
		$this->noMedals = false;
	}
	
	function set_user_information($row)
	{
		$this->first = $row["first"];
		$this->last = $row["last"];	
	}
	
	function set_school_type_id()
	{
		$sql = "SELECT school_type_id FROM users WHERE user_id=" . $this->user_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->school_type_id = $row["school_type_id"];
	}
	
	function update_mission()
	{
		$updated = false;
	
		$today = gregoriantojd(date("n"), date("j"), date("Y"));
		
		$sql  = "SELECT dtm.* ";
		$sql .= "FROM date_tasks_missions AS dtm ";
		$sql .= "LEFT JOIN date_tasks_mission_marks as dtmm ON (dtm.date_tasks_mission_id = dtmm.date_tasks_mission_id and dtmm.user_id = " . $this->user_id . ") ";
		//$sql .= "join date_tasks as dt on (dt.date_tasks_mission_id = dtm.date_tasks_mission_id) "; //added in order not to get missions that only have bonus tasks
		$sql .= "WHERE dtm.school_type_id=" . $this->school_type_id . " ";
		$sql .= "AND dtm.subject_id=" . $this->subject_id . " ";
		$sql .= "AND dtm.track_id=" . $this->track_id . " ";
		$sql .= "AND dtm.level=" . $this->level . " ";
		$sql .= "AND dtm.start_date < " . $today . " ";
		$sql .= "AND dtmm.date_tasks_mission_id is null ";
		//$sql .= "AND dt.mandatory_qty = 1 "; //added in order not to get missions that only have bonus tasks
		$sql .= "ORDER BY dtm.mission_number";
		//echo "<input type='hidden' name='updater' value='" . $sql . "'>";
		//exit;
		$query = mysql_query($sql);		
		
		while ($row = mysql_fetch_assoc($query)) 
		{
			$date_tasks_mission = new date_tasks_mission($row);			
			$date_tasks_mission->set_mission_completed($this->user_id);
			
			if ($date_tasks_mission->mission_completed == true)
			{
				$updated = true;		
				//$mark_date = date("Y-m-d H:i:s", time());
				
				$insert_sql = "INSERT INTO date_tasks_mission_marks SET user_id=" . $this->user_id . ", ";
				$insert_sql = $insert_sql . "date_tasks_mission_id=" . $date_tasks_mission->date_tasks_mission_id . ", ";
				$insert_sql = $insert_sql . "subject_id=" . $this->subject_id . ", ";
				$insert_sql = $insert_sql . "mission_value=" . $date_tasks_mission->mission_value . ", ";
				$insert_sql = $insert_sql . "mission_name='" . mysql_real_escape_string($date_tasks_mission->mission_name) . "', ";
				$insert_sql = $insert_sql . "mark_date=" . $date_tasks_mission->end_date . ", ";
				$insert_sql = $insert_sql . "mark_override=0";	
				$insert_query = mysql_query($insert_sql);
			}
			
		}
		
		return $updated;
	}
	
	function get_subject_info() 
	{
		$sql = "SELECT subject_name, subject_image_id FROM subjects WHERE subject_id=" . $this->subject_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->subject_name = $row["subject_name"];
		$this->subject_image_id = $row["subject_image_id"];
	}
	
	function get_date_tasks_missions($school_type_id, $start_date, $end_date, $tasks = array(), $lang = 1, $allowPersonalization = true,
                                     $print_parent_tasks = true, $chidonLimmud = false)
	{
		//echo "<input type='hidden' name='3) END DATE' value='" . $end_date . "'>\n";
		$this->start_date = $start_date;
		$this->end_date = $end_date;
		
		$sql = "SELECT * FROM date_tasks_missions WHERE lang_id = $lang and school_type_id=" . $school_type_id // limit the language and school id
			. " AND subject_id=" . $this->subject_id . " AND level=" . $this->level . " AND track_id=" . $this->track_id // limit the task
			. " AND start_date >= " . $start_date . " AND end_date <= " . $end_date; // limit the dates
		if(!$print_parent_tasks) $sql .= " AND created_by_parent IS NULL";
        if ($this->subject_id == 21 && !$chidonLimmud) $sql .= " AND mission_name NOT LIKE '%Chidon Limmud%' ";
		$sql .= " ORDER BY created_by_parent IS NULL DESC, mission_number, start_date, mission_name"; // place custom parent tasks at the bottom...
    //    if ($this->subject_id == 40) echo $sql . "<br />";
		// if ($this->subject_id == 136) {
		// 	echo $sql . "<br />"; 
		// }

        include_once dirname(__FILE__) . '/../class.defaults.php';
		$d = new Defaults($this->user_id);
                
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
		    
            //find out if mission is new birthday mission and then see if it's for this child
            if ( strpos( $row['mission_name'], 'Birthday!' ) !== false || strpos( $row['mission_description'], 'יום הולדת' ) !== false ) {
                $sqlB = "select * from birthdays where user_id = " . $this->user_id . " and date_tasks_mission_id = " . $row['date_tasks_mission_id'];
				//if ($this->user_id == 15025) echo "<input type='hidden' name='birthdayMission' value='" . $sqlB . "' />";
				
                $resB = mysql_query( $sqlB );
                if ( mysql_num_rows( $resB ) == 0 || !$allowPersonalization ) {
                    //don't add this mission to child's list of missions
                    continue;
                } 
            }

			$date_tasks_mission = new date_tasks_mission($row, $tasks, $allowPersonalization);
			
			if ($date_tasks_mission->date_tasks_mission_id > 0) {
                            
                //find out if default is off
                //if it's off, find out if this mission should be shown for this user
                if ($row['default_on'] || $d->isOn($date_tasks_mission->date_tasks_mission_id, 'mission')) {
					
                    // ***** Daily Tasks *****//
        			$daily_tasks = $date_tasks_mission->get_daily_tasks($date_tasks_mission->start_date, $date_tasks_mission->end_date, $this->user_id, $this->subject_id, $this->subject_name, $this->track_id, $this->level, $this->subject_image_id);
        			for ($dtno = 0; $dtno < count($daily_tasks); $dtno++) {
        				array_push($this->daily_tasks, $daily_tasks[$dtno]);
        			}
                    
        			// ***** Weekly Tasks *****//
        			$weekly_tasks = $date_tasks_mission->get_weekly_tasks($date_tasks_mission->start_date, $date_tasks_mission->end_date, $this->user_id, $this->subject_id, $this->subject_name, $this->track_id, $this->level, $this->subject_image_id);
					for ($wtno = 0; $wtno < count($weekly_tasks); $wtno++) {
        				array_push($this->weekly_tasks, $weekly_tasks[$wtno]);
        			}								
        			
        			// ***** Shabbos Tasks *****//
        			$shabbos_tasks = $date_tasks_mission->get_shabbos_tasks($date_tasks_mission->start_date, $date_tasks_mission->end_date, $this->user_id, $this->subject_id, $this->subject_name, $this->track_id, $this->level, $this->subject_image_id);
        			for ($stno = 0; $stno < count($shabbos_tasks); $stno++) {
        				array_push($this->shabbos_tasks, $shabbos_tasks[$stno]);
        			}				
        			
        			// ***** No Label Tasks *****//
        			$no_label_tasks = $date_tasks_mission->get_no_label_tasks($date_tasks_mission->start_date, $date_tasks_mission->end_date, $date_tasks_mission->mission_name, $date_tasks_mission->mission_number, $this->user_id, $this->subject_name, $this->subject_image_id, $this->subject_id);
        			for ($nltno = 0; $nltno < count($no_label_tasks); $nltno++) {
        				array_push($this->no_label_tasks, $no_label_tasks[$nltno]);
        			}

					// ***** Pesukim Tasks *****//
					if ($this->subject_id == 136) {
        				$pesukim_tasks = $date_tasks_mission->get_pesukim_tasks($date_tasks_mission->start_date, $date_tasks_mission->end_date, $this->user_id, $this->subject_id, $this->subject_name, $this->track_id, $this->level, $this->subject_image_id);
        				for ($ptno = 0; $ptno < count($pesukim_tasks); $ptno++) {
        					array_push($this->pesukim_tasks, $pesukim_tasks[$ptno]);
        				}
					}
                } 
			}		
		}
//		echo "<pre>"; print_r($this->daily_tasks); echo "</pre>"; exit;
	}

	function get_date_tasks_missions_for_duch($school_type_id, $start_date, $end_date, $tasks = array(), $lang = 1, $allowPersonalization = true,
                                     $print_parent_tasks = true, $chidonLimmud = false)
	{
		global $all_date_tasks_missions;
		//echo "<input type='hidden' name='3) END DATE' value='" . $end_date . "'>\n";
		$this->start_date = $start_date;
		$this->end_date = $end_date;

		include_once dirname(__FILE__) . '/../class.defaults.php';
		$d = new Defaults($this->user_id);

		if ( ! isset( $all_date_tasks_missions[ $this->subject_id ][ $school_type_id ][ $lang ][ $this->level ][ $this->track_id ] ) ) {
			$all_date_tasks_missions[ $this->subject_id ][ $school_type_id ][ $lang ][ $this->level ][ $this->track_id ] = [];

			$sql = "SELECT * FROM date_tasks_missions WHERE lang_id = $lang and school_type_id=" . $school_type_id // limit the language and school id
				. " AND subject_id=" . $this->subject_id . " AND level=" . $this->level . " AND track_id=" . $this->track_id // limit the task
				. " AND start_date >= " . $start_date . " AND end_date <= " . $end_date; // limit the dates
			if(!$print_parent_tasks) $sql .= " AND created_by_parent IS NULL";
			if ($this->subject_id == 21 && !$chidonLimmud) $sql .= " AND mission_name NOT LIKE '%Chidon Limmud%' ";
			$sql .= " ORDER BY created_by_parent IS NULL DESC, mission_number, start_date, mission_name"; // place custom parent tasks at the bottom...

			$query = mysql_query($sql);
			while ($row = mysql_fetch_assoc($query)) {
				$all_date_tasks_missions[ $this->subject_id ][ $school_type_id ][ $lang ][ $this->level ][ $this->track_id ][] = $row;				
			}
		}

		// Use cached mission list; tasks are still computed per user (defaults/exceptions are user-specific)
		foreach ( $all_date_tasks_missions[ $this->subject_id ][ $school_type_id ][ $lang ][ $this->level ][ $this->track_id ] as $row ) {
			//find out if mission is new birthday mission and then see if it's for this child
			// if ( strpos( $row['mission_name'], 'Birthday!' ) !== false || strpos( $row['mission_description'], 'יום הולדת' ) !== false ) {
			// 	$sqlB = "select * from birthdays where user_id = " . $this->user_id . " and date_tasks_mission_id = " . $row['date_tasks_mission_id'];
			// 	//if ($this->user_id == 15025) echo "<input type='hidden' name='birthdayMission' value='" . $sqlB . "' />";
				
			// 	$resB = mysql_query( $sqlB );
			// 	if ( mysql_num_rows( $resB ) == 0 || !$allowPersonalization ) {
			// 		//don't add this mission to child's list of missions
			// 		continue;
			// 	} 
			// }
			$date_tasks_mission = new date_tasks_mission($row, $tasks, $allowPersonalization);

			if ( ! $date_tasks_mission->default_on && ! $d->isOn( $date_tasks_mission->date_tasks_mission_id, 'mission' ) ) {
				continue;
			}

			$daily_tasks = $date_tasks_mission->get_daily_tasks($date_tasks_mission->start_date, $date_tasks_mission->end_date, $this->user_id, $this->subject_id, $this->subject_name, $this->track_id, $this->level, $this->subject_image_id);
			for ($dtno = 0; $dtno < count($daily_tasks); $dtno++) {
				array_push($this->daily_tasks, $daily_tasks[$dtno]);
			}
			$weekly_tasks = $date_tasks_mission->get_weekly_tasks($date_tasks_mission->start_date, $date_tasks_mission->end_date, $this->user_id, $this->subject_id, $this->subject_name, $this->track_id, $this->level, $this->subject_image_id);
			for ($wtno = 0; $wtno < count($weekly_tasks); $wtno++) {
				array_push($this->weekly_tasks, $weekly_tasks[$wtno]);
			}
			$shabbos_tasks = $date_tasks_mission->get_shabbos_tasks($date_tasks_mission->start_date, $date_tasks_mission->end_date, $this->user_id, $this->subject_id, $this->subject_name, $this->track_id, $this->level, $this->subject_image_id);
			for ($stno = 0; $stno < count($shabbos_tasks); $stno++) {
				array_push($this->shabbos_tasks, $shabbos_tasks[$stno]);
			}
			$no_label_tasks = $date_tasks_mission->get_no_label_tasks($date_tasks_mission->start_date, $date_tasks_mission->end_date, $date_tasks_mission->mission_name, $date_tasks_mission->mission_number, $this->user_id, $this->subject_name, $this->subject_image_id, $this->subject_id);
			for ($nltno = 0; $nltno < count($no_label_tasks); $nltno++) {
				array_push($this->no_label_tasks, $no_label_tasks[$nltno]);
			}
			if ($this->subject_id == 136) {
				$pesukim_tasks = $date_tasks_mission->get_pesukim_tasks($date_tasks_mission->start_date, $date_tasks_mission->end_date, $this->user_id, $this->subject_id, $this->subject_name, $this->track_id, $this->level, $this->subject_image_id);
				for ($ptno = 0; $ptno < count($pesukim_tasks); $ptno++) {
					array_push($this->pesukim_tasks, $pesukim_tasks[$ptno]);
				}
			}
		}
	}

	function get_date_tasks_missions_by_date_range($school_type_id, $start_date, $end_date, $tasks = array(), $lang = 1, $allowPersonalization = true,
		$print_parent_tasks = true, $chidonLimmud = false)
	{
		$this->start_date = $start_date;
		$this->end_date = $end_date;
		
		$sql = "SELECT * FROM date_tasks_missions WHERE lang_id = $lang and school_type_id=" . $school_type_id // limit the language and school id
			. " AND subject_id=" . $this->subject_id . " AND level=" . $this->level . " AND track_id=" . $this->track_id // limit the task
			. " AND start_date >= " . $start_date . " AND end_date <= " . $end_date; // limit the dates
		if(!$print_parent_tasks) $sql .= " AND created_by_parent IS NULL";
        if ($this->subject_id == 21 && !$chidonLimmud) $sql .= " AND mission_name NOT LIKE '%Chidon Limmud%' ";
		$sql .= " ORDER BY created_by_parent IS NULL DESC, mission_number, start_date, mission_name"; // place custom parent tasks at the bottom...
    //    if ($this->subject_id == 40) echo $sql . "<br />";
		// if ($this->subject_id == 136) {
		// 	echo $sql . "<br />"; 
		// }

        include_once dirname(__FILE__) . '/../class.defaults.php';
		$d = new Defaults($this->user_id);
                
		$date_tasks_missions = [];
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
		    
            //find out if mission is new birthday mission and then see if it's for this child
            if ( strpos( $row['mission_name'], 'Birthday!' ) !== false || strpos( $row['mission_description'], 'יום הולדת' ) !== false ) {
                $sqlB = "select * from birthdays where user_id = " . $this->user_id . " and date_tasks_mission_id = " . $row['date_tasks_mission_id'];
				//if ($this->user_id == 15025) echo "<input type='hidden' name='birthdayMission' value='" . $sqlB . "' />";
				
                $resB = mysql_query( $sqlB );
                if ( mysql_num_rows( $resB ) == 0 || !$allowPersonalization ) {
                    //don't add this mission to child's list of missions
                    continue;
                } 
            }

			$date_tasks_mission = new date_tasks_mission($row, $tasks, $allowPersonalization);
			
			if ($date_tasks_mission->date_tasks_mission_id > 0) {
                            
                //find out if default is off
                //if it's off, find out if this mission should be shown for this user
                if ($row['default_on'] || $d->isOn($date_tasks_mission->date_tasks_mission_id, 'mission')) {
					array_push($date_tasks_missions, $date_tasks_mission);
				}
			}
		}

		// get the task mission ids from the date tasks missions
		$task_mission_ids = array_map(function ($date_tasks_mission) {
			return $date_tasks_mission->date_tasks_mission_id;
		}, $date_tasks_missions);

		foreach ($date_tasks_missions as $date_tasks_mission) {
			// ***** Daily Tasks *****//
			$daily_tasks = $date_tasks_mission->get_daily_tasks($date_tasks_mission->start_date, $date_tasks_mission->end_date, $this->user_id, $this->subject_id, $this->subject_name, $this->track_id, $this->level, $this->subject_image_id, $task_mission_ids);
			for ($dtno = 0; $dtno < count($daily_tasks); $dtno++) {
				array_push($this->daily_tasks, $daily_tasks[$dtno]);
			}
			
			// ***** Weekly Tasks *****//
			$weekly_tasks = $date_tasks_mission->get_weekly_tasks($date_tasks_mission->start_date, $date_tasks_mission->end_date, $this->user_id, $this->subject_id, $this->subject_name, $this->track_id, $this->level, $this->subject_image_id, $task_mission_ids);
			for ($wtno = 0; $wtno < count($weekly_tasks); $wtno++) {
				array_push($this->weekly_tasks, $weekly_tasks[$wtno]);
			}								
			
			// ***** Shabbos Tasks *****//
			$shabbos_tasks = $date_tasks_mission->get_shabbos_tasks($date_tasks_mission->start_date, $date_tasks_mission->end_date, $this->user_id, $this->subject_id, $this->subject_name, $this->track_id, $this->level, $this->subject_image_id, $task_mission_ids);
			for ($stno = 0; $stno < count($shabbos_tasks); $stno++) {
				array_push($this->shabbos_tasks, $shabbos_tasks[$stno]);
			}				
			
			// ***** No Label Tasks *****//
			$no_label_tasks = $date_tasks_mission->get_no_label_tasks($date_tasks_mission->start_date, $date_tasks_mission->end_date, $date_tasks_mission->mission_name, $date_tasks_mission->mission_number, $this->user_id, $this->subject_name, $this->subject_image_id, $this->subject_id, $task_mission_ids);
			for ($nltno = 0; $nltno < count($no_label_tasks); $nltno++) {
				array_push($this->no_label_tasks, $no_label_tasks[$nltno]);
			}
			break; // only one loop to get all the tasks
		}
	}
	
	function get_september_date_tasks_missions($school_type_id, $start_date, $end_date) 
	{
		$sql = "SELECT * FROM date_tasks_missions WHERE school_type_id=" . $school_type_id . " AND subject_id=" . $this->subject_id . " AND level=" . $this->level . " AND track_id=" . $this->track_id . " AND start_date >= " . $start_date . " AND end_date <= " . $end_date . " ORDER BY mission_number";
		$query = mysql_query($sql);		
		while ($row = mysql_fetch_assoc($query)) {
			$date_tasks_mission = new date_tasks_mission($row);
			
			if ($date_tasks_mission->date_tasks_mission_id > 0) {
				// ***** No Label Tasks *****//
				$no_label_tasks = $date_tasks_mission->get_no_label_tasks($date_tasks_mission->start_date, $date_tasks_mission->end_date, $date_tasks_mission->mission_name, $date_tasks_mission->mission_number, $this->user_id, $this->subject_name, $this->subject_image_id);
				for ($nltno = 0; $nltno < count($no_label_tasks); $nltno++) {
					array_push($this->no_label_tasks, $no_label_tasks[$nltno]);
				}				
			}
			
		}
	}
	
	function set_show_user_track($user_track) 
	{
		$this->show_user_track = $user_track;
	}
	
	function get_subject_medals()
	{
		//if ($this->subject_id == 40)
		//{
		//	echo "*** POSSIBLE MISSIONS:" . $this->possible_missions . "<br />";
		//}
	
		$missions_required = 0;
		
		$sql = "SELECT m.*, ms.* ";
		$sql = $sql . "FROM medals_subjects AS ms ";
		$sql = $sql . "JOIN medals AS m USING (medal_ord) ";
		$sql = $sql . "LEFT JOIN medal_marks AS mm ON (mm.user_id=" . $this->user_id . " AND mm.subject_id=" . $this->subject_id . " AND mm.medal_ord=m.medal_ord) ";
		$sql = $sql . "WHERE ms.subject_id=" . $this->subject_id . " ";
		$sql = $sql . "AND mm.user_id IS NULL ";
		$sql = $sql . "ORDER BY ms.medal_ord";
				
		$i = 0; //counter for first iteration
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) 
		{
			$missions_required += $row['missions_required'];
			
			if ($this->possible_missions >= $missions_required)
			{
				$medal = new medal($row);
				$medal->set_missions_required($missions_required);
				array_push($this->medals, $medal);
				$i++;
			}
			else
			{
				//if we don't even have first medal, set noMedals variable
				if ($i == 0) {
					$this->noMedals = true;
				} 
				break;
			}
		}
	}
	
	function get_completed_missions()
	{
		$sql  = "SELECT SUM( mission_count ) AS completed_missions ";
		$sql .= "FROM user_tracks AS ut ";
		$sql .= "JOIN date_tasks_mission_marks AS dtmm ON (dtmm.user_id=" . $this->user_id . " AND dtmm.subject_id=" . $this->subject_id . ") ";
		$sql .= "WHERE ut.user_id=" . $this->user_id . " ";
		$sql .= "AND ut.subject_id=" . $this->subject_id . " ";
		$sql .= "GROUP BY ut.user_id, ut.subject_id  ";
		
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		if ($row['completed_missions'] > 0)
			$this->completed_missions = $row['completed_missions'];
		else
			$this->completed_missions = 0;	
			
	}
	
	function get_incomplete_missions($school_type_id, $start_date, $end_date)
	{
		$sql = "SELECT count(*) AS incomplete_missions ";
		$sql = $sql . "FROM user_tracks AS ut ";
		$sql = $sql . "JOIN date_tasks_missions AS dtm ON (dtm.school_type_id=" . $school_type_id . " AND dtm.subject_id=ut.subject_id AND dtm.track_id=ut.track_id AND dtm.level=ut.level) ";
		$sql = $sql . "LEFT JOIN date_tasks_mission_marks AS dtmm ON (dtmm.user_id=" . $this->user_id . " AND dtmm.date_tasks_mission_id=dtm.date_tasks_mission_id) ";
		$sql = $sql . "WHERE ut.user_id=" . $this->user_id . " ";
		$sql = $sql . "AND ut.subject_id=" . $this->subject_id . " ";
		$sql = $sql . "AND dtm.start_date < " . $end_date . " ";
		$sql = $sql . "AND dtm.end_date > " . $start_date . " ";
		$sql = $sql . "AND dtmm.user_id IS NULL ";
		$sql = $sql . "GROUP BY ut.user_id, ut.subject_id  ";
		
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		
		if ($row['incomplete_missions'] > 0)
			$this->incomplete_missions = $row['incomplete_missions'];	
		else
			$this->incomplete_missions = 0;			
	}
	
	function set_possible_missions()
	{
		$this->possible_missions = $this->completed_missions + $this->incomplete_missions;
	}
	
	function set_medal_awarded($subject_id, $user_id, $medal_ord)
	{
		$sql = "SELECT count(*) AS awarded FROM medal_marks WHERE user_id=" . $user_id . " AND subject_id=" . $subject_id . " AND medal_ord=" . $medal_ord;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		
		if ($row['awarded'] > 0) 
			$this->medal_awarded = true;
		else
			$this->medal_awarded = false;
	}
	
	function get_missions($school_type_id, $start_date, $end_date)
	{
		$sql = "SELECT * ";
		$sql = $sql . "FROM date_tasks_missions AS dtm ";
		$sql = $sql . "WHERE dtm.school_type_id=" . $school_type_id . " AND ";
		$sql = $sql . "dtm.subject_id=" . $this->subject_id . " AND ";
		$sql = $sql . "dtm.track_id=" . $this->track_id . " AND ";
		$sql = $sql . "dtm.level=" . $this->level . " AND ";
		$sql = $sql . "dtm.start_date >= " . $start_date . " AND ";
		$sql = $sql . "dtm.end_date <= " . $end_date . " ";
		
		$query = mysql_query($sql);
		$num_rows = mysql_num_rows($query);
		if ($num_rows > 0)
			$this->show_user_track = true;
		while ($row = mysql_fetch_assoc($query))
		{
			$date_task_mission = new date_tasks_mission($row);
			
			$date_task_mission->set_week_string();
			$date_task_mission->get_tasks();
			array_push($this->missions, $date_task_mission);
		}
	}
	
	function fetch_date_tasks_missions($school_type_id, $start_date) 
	{
		$sql = "SELECT * ";
		$sql = $sql . "FROM date_tasks_missions ";
		$sql = $sql . "WHERE school_type_id=" . $school_type_id . " ";
		$sql = $sql . "AND subject_id=" . $this->subject_id . " ";
		$sql = $sql . "AND track_id=" . $this->track_id . " ";
		$sql = $sql . "AND level=" . $this->level . " ";
		$sql = $sql . "AND start_date >= " . $start_date . " ";
		$sql = $sql . "ORDER BY start_date ASC ";
		
		//echo "<input type='hidden' name='SQL1' value='" . $sql . "'>\n";
		
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query))
		{
			$date_task_mission = new date_tasks_mission($row);
			$date_task_mission->set_mission_completed($this->user_id);
			$date_task_mission->set_future_mission($this->user_id);
			array_push($this->date_tasks_missions, $date_task_mission);
		}
	}
	
	function check_task($task_id, $output = false) {
		//get marks
		$mark_sql = "select * from date_tasks_marks as dtmark 
					join date_tasks as dt using (date_task_id) 
					left join date_tasks_missions as dtm using (date_tasks_mission_id) 
					where dtmark.date_task_id = " . $task_id . "
					and dtmark.mark_date > 2456536 
					and dtmark.user_id = " . $this->user_id;
		//echo $mark_sql . "<br />";
		$mark_result = mysql_query($mark_sql);
		$num = mysql_num_rows($mark_result);
		
		$completed = false;
		if ($num > 0) { 
			$row_mark = mysql_fetch_assoc($mark_result);
			$needed = $row_mark['needed'] ? $row_mark['needed'] : 0;
			$qty = $row_mark['quantity'] ? $row_mark['quantity'] : 0;

			//find out if it's a daily task or a task that needs a quantity
			if ($qty) {
				if ($row_mark['done_qty'] >= $qty) {
					$completed = true;
				}
			} else if ($needed) {
				if ($num >= $needed) {
					$completed = true;
				}  
			} else {
				$completed = true;
			}
		}
		if ($output)
			echo "Task: " . $task_id . ($completed ? " completed" : " not completed") . "<br />";
		return $completed;
	}
	
	function check_missions($output = false, $delete = false, $start = 0) {
		$deleted = 0;
		$inserted = 0;
		// get all missions from $start until now
		$sql = "SELECT *
                FROM date_tasks_missions
                JOIN date_tasks
                USING ( date_tasks_mission_id )
                JOIN date_tasks_marks AS dtm
                USING ( date_task_id )
                WHERE user_id = " . $this->user_id . " 
                AND subject_id = " . $this->subject_id;
		if ($start) $sql .= " AND start_date >= " . $start;
		$sql .= " GROUP BY date_tasks_mission_id
                ORDER BY end_date";

		//echo $sql; exit;
		$total_completed = 0;
		$total_not_completed = 0;
		$result = mysql_query($sql);
		
		if ($output)
			echo "User: " . $this->user_id . "<br />";
		
		while ($row = mysql_fetch_assoc($result)) {
			//for each mission get the mission task(s)
			$task_sql = "select * from date_tasks 
                        where date_tasks_mission_id = " . $row['date_tasks_mission_id'] . " 
                        and mandatory_qty = 1 
                        order by date_task_id";
			//echo $task_sql; exit;
			
			$task_result = mysql_query($task_sql);
			if (mysql_num_rows($task_result) == 0) continue; // task must have been deleted at some point
			//get number of mission tasks
			$num_mission_tasks = mysql_num_rows($task_result);
			if ($output)
				echo "Mission: " . $row['date_tasks_mission_id']. "<br />";

			//if number is more than 1 make sure user has all tasks marked
			$marked = 0;
			if ($num_mission_tasks > 0) {
				while ($row_task = mysql_fetch_assoc($task_result)) {
					//for each mission task check it it's completed
					if ($this->check_task($row_task['date_task_id'], $output)) {
						$marked++;
					}
				}
			}
						
			//set flag
			$done = false;
			if ($marked > 0 && $marked == $num_mission_tasks) {
				$done = true;
				$total_completed++;
				if ($output)
					echo "Mission completed<br />";
			} else {
				$total_not_completed++;
				if ($output)
					echo "Mission not completed<br />";
			}
			
			//find out if mission was set to marked
			$mm_sql = "select * from date_tasks_mission_marks 
                        where date_tasks_mission_id = " . $row['date_tasks_mission_id'] . " 
                        and subject_id = " . $this->subject_id . " 
                        and user_id = " . $this->user_id;
			//echo $mm_sql . "<br />";
			$mm_result = mysql_query($mm_sql);
			$mm_rows = mysql_num_rows($mm_result);
			
			if ($mm_rows > 0 ) {
				if ($output)
					echo "Mission is marked<br />";	
				//if not completed delete from table
				if (!$done && $delete) {
					$del = "delete from date_tasks_mission_marks 
                            where date_tasks_mission_id = " . $row['date_tasks_mission_id'] . "  
                            and subject_id = " . $this->subject_id . " 
                            and user_id = " . $this->user_id;
					if ($output)
						echo $del . "<br />";
					/*
					if (mysql_query($del)) {
						echo "deleted<br />";
						$deleted++;
					}
					*/
				}
			} else { 
				if ($output)
					echo "Mission is not marked<br />";
				//if completed add to table
				if ($done) {
					$ins = "insert into date_tasks_mission_marks ".
							" set user_id = " . $this->user_id . 
							", date_tasks_mission_id = " . $row['date_tasks_mission_id'] .
							", subject_id = " . $this->subject_id .
							", mission_value = " . $row['mission_value'] .
							", mission_name = ''" .
							", mark_date = " . $row['end_date'] .
							", mark_override = 0" .
							", missions_updated = 0";
					if ($output) 
						echo $ins . "<br />";
					if (mysql_query($ins)) {
						if ($output) 
							echo "inserted<br />";
						$inserted++;
					} else
						echo mysql_error() . "<br />";
				}
			}
			//if ($output)
				echo "<br />";
		}
		if ($output) {
			echo "Subject: " . $this->subject_id . "<br />";
			echo "Total inserted: " . $inserted . "<br />";
			echo "Total deleted: " . $deleted . "<br />";
			echo "Total completed: " . $total_completed . "<br />";
			echo "Total not completed: " . $total_not_completed . "<br /><br />";
		}
	}	
}
?>