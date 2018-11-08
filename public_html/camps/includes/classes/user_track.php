<?php
class user_track {
	public $user_id;
	public $subject_id;
	public $track_id;
	public $level; 	
	public $enrolled;
	public $subject_name;
	public $subject_image_id;
	public $date_tasks_missions = array();
	
	public $daily_tasks = array();
	public $weekly_tasks = array();
	public $shabbos_tasks = array();
	public $no_label_tasks = array();
	
	public $show_user_track;
	public $medals = array();
	
	public $completed_missions;
	public $incomplete_missions;
	public $possible_missions;
	public $medal_awarded;
		
	function __construct($row){
		$this->user_id = $row["user_id"];
		$this->subject_id = $row["subject_id"];
		$this->track_id = $row["track_id"];
		$this->level = $row["level"]; 	
		$this->enrolled = $row["enrolled"];
		$this->show_user_track = false;
	}
	
	function get_subject_info() {
		$sql = "SELECT subject_name, subject_image_id FROM subjects WHERE subject_id=" . $this->subject_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->subject_name = $row["subject_name"];
		$this->subject_image_id = $row["subject_image_id"];
	}
	
	function get_date_tasks_missions($school_type_id, $start_date, $end_date) 
	{
		//echo "<input type='hidden' name='3) END DATE' value='" . $end_date . "'>\n";
		
		$sql = "SELECT * FROM date_tasks_missions WHERE lang_id = 1 and school_type_id=" . $school_type_id . " AND subject_id=" . $this->subject_id . " AND level=" . $this->level . " AND track_id=" . $this->track_id . " AND start_date >= " . $start_date . " AND end_date <= " . $end_date . " ORDER BY mission_number, start_date, mission_name";
		
		//////////if ($this->subject_id == 91)
		echo "<input type='hidden' name='SQL ONE' value='" . $sql . "'>\n";
                
                require_once 'class.defaults.php';
		$d = new Defaults($this->user_id);
                
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
		    
                    //find out if mission is new birthday mission and then see if it's for this child
                    if ( strpos( $row['mission_name'], 'Birthday!' ) ) {
                        $sqlB = "select * from birthdays where user_id = " . $this->user_id . " and date_tasks_mission_id = " . $row['date_tasks_mission_id'];
                        $resB = mysql_query( $sqlB );
                        if ( mysql_num_rows( $resB ) == 0 ) {
                            //don't add this mission to child's list of missions
                            continue;
                        }
                    }
            
			$date_tasks_mission = new date_tasks_mission($row);
			
			if ($date_tasks_mission->date_tasks_mission_id > 0) {
                            
                            //find out if default is off
                            //if it's off, find out if this mission should be shown for this user
                            if ($row['default_on'] || $d->isOn($date_tasks_mission->date_tasks_mission_id, 'mission')) {

                                // ***** Daily Tasks *****//
				$daily_tasks = $date_tasks_mission->get_daily_tasks($date_tasks_mission->start_date, $date_tasks_mission->end_date, $this->user_id, $this->subject_id, $this->subject_name, $this->track_id, $this->level, $this->subject_image_id);
				echo "<input type='hidden' name='4) END DATE' value='" . $date_tasks_mission->date_tasks_mission_id . "->" . $date_tasks_mission->end_date . "'>\n";
				for ($dtno = 0; $dtno < count($daily_tasks); $dtno++) {
					array_push($this->daily_tasks, $daily_tasks[$dtno]);
				}
                                /*
                                echo "<pre>";
                                echo "<input type='hidden' name='Daily Tasks' value='" . print_r( $this->daily_tasks ) . "'>";								
				echo "</pre>";
                                */
                
				// ***** Weekly Tasks *****//
				$weekly_tasks = $date_tasks_mission->get_weekly_tasks($date_tasks_mission->start_date, $date_tasks_mission->end_date, $this->user_id, $this->subject_id, $this->subject_name, $this->track_id, $this->level, $this->subject_image_id);
				for ($wtno = 0; $wtno < count($weekly_tasks); $wtno++) {
					array_push($this->weekly_tasks, $weekly_tasks[$wtno]);
				}								
				
				// ***** Shabbos Tasks *****//
				$shabbos_tasks = $date_tasks_mission->get_shabbos_tasks($date_tasks_mission->start_date, $date_tasks_mission->end_date, $this->user_id, $this->subject_id, $this->subject_name, $this->track_id, $this->level, $this->subject_image_id);
				//$str = "<pre>" . print_r($shabbos_tasks) . "</pre>";
				//echo "<input type='hidden' name='shabbos tasks' value='$str'>";
				for ($stno = 0; $stno < count($shabbos_tasks); $stno++) {
					array_push($this->shabbos_tasks, $shabbos_tasks[$stno]);
				}				
				
				// ***** No Label Tasks *****//
				$no_label_tasks = $date_tasks_mission->get_no_label_tasks($date_tasks_mission->start_date, $date_tasks_mission->end_date, $date_tasks_mission->mission_name, $date_tasks_mission->mission_number, $this->user_id, $this->subject_name, $this->subject_image_id);
				//echo "<input type='hidden' name='no label tasks' value='" . print_r($no_label_tasks) . "'";
				for ($nltno = 0; $nltno < count($no_label_tasks); $nltno++) {
					array_push($this->no_label_tasks, $no_label_tasks[$nltno]);
				}
                            }
			}		
		}
	}
	
	function get_september_date_tasks_missions($school_type_id, $start_date, $end_date) {
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
	
	function set_show_user_track($val) 
	{
		$this->show_user_track = $val;
	}
	
	function get_subject_medals()
	{
		$sql = "SELECT * ";
		$sql = $sql . "FROM medals_subjects AS ms ";
		$sql = $sql . "JOIN medals AS m USING (medal_ord) ";
		$sql = $sql . "WHERE ms.subject_id=" . $this->subject_id . " ";
		$sql = $sql . "ORDER BY ms.medal_ord";
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) 
		{
			$missions_required = $missions_required + $row['missions_required'];
			$medal = new medal($row);
			//$medal->set_missions_required($missions_required);
			array_push($this->medals, $medal);
		}
	}
	
	function get_completed_missions()
	{
		$sql = "SELECT SUM( mission_count ) AS completed_missions ";
		$sql = $sql . "FROM user_tracks AS ut ";
		$sql = $sql . "JOIN date_tasks_mission_marks AS dtmm ON (dtmm.user_id=" . $this->user_id . " AND dtmm.subject_id=" . $this->subject_id . ") ";
		$sql = $sql . "WHERE ut.user_id=" . $this->user_id . " ";
		$sql = $sql . "AND ut.subject_id=" . $this->subject_id . " ";
		$sql = $sql . "GROUP BY ut.user_id, ut.subject_id  ";
		
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		if ($row['completed_missions'] > 0)
			$this->completed_missions = $row['completed_missions'];
		else
			$this->completed_missions = 0;	
	}
	
	function get_incomplete_missions($subject_id, $start_date, $end_date)
	{
		$sql = "SELECT count(*) AS incomplete_missions ";
		$sql = $sql . "FROM user_tracks AS ut ";
		$sql = $sql . "JOIN date_tasks_missions AS dtm ON (dtm.school_type_id=" . $this->school_type_id . " AND dtm.subject_id=ut.subject_id AND dtm.track_id=ut.track_id AND dtm.level=ut.level) ";
		$sql = $sql . "LEFT JOIN date_tasks_mission_marks AS dtmm ON (dtmm.user_id=" . $this->user_id . " AND dtmm.date_tasks_mission_id=dtm.date_tasks_mission_id) ";
		$sql = $sql . "WHERE ut.user_id=" . $this->user_id . " ";
		$sql = $sql . "AND ut.subject_id=" . $subject_id . " ";
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
}
?>