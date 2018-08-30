<?
class Missions {
	protected $school;
	protected $grade;
	protected $user;
	protected $start;
	protected $end;
	protected $missions;
	
	public function __construct( $start, $end, $user = 0, $school = 0, $grade = 0, $allowPersonalization = true, $printing_mode = false ) {
		$this->school = $school;
		$this->grade = $grade;
		$this->user = $user;
		$this->start = $start;
		$this->end = $end;
		$this->missions = array();
		$this->createMissions( $allowPersonalization, $printing_mode );
	}
	
	private function createMissions( $allowPersonalization, $printing_mode = false ) {
		chdir("../"); // what if this class is called not from ../mission_report ? this will not work as expected.
		include_once("classes/user.php");
		include_once("classes/user_track.php");
		include_once("classes/school_class.php");
		include_once("class.taskExceptions.php");
		include_once("classes/date_tasks_mission.php");
		include_once("classes/daily_task.php");
		include_once("classes/weekly_task.php");
		include_once("classes/shabbos_task.php");
		include_once("classes/no_label_task.php");
		include_once("classes/task.php");
		include_once("classes/date_tasks_mark.php");
		
		$sql = "SELECT u.* FROM users u"
				." JOIN classes c ON u.class_id = c.class_id"
				." WHERE u.user_registered > 0";
		if ( $this->school ) {
			$sql .= " AND u.school_id = " . $this->school;
		}
		if ( $this->grade ) {
			$sql .= " AND u.class_id = " . $this->grade;
		}
		if ( $this->user ) {
			$sql .= " AND u.user_id = " . $this->user;
		}
		$sql .= " ORDER BY c.class_grade, c.class_sub, u.last, u.first";
		
		$query = mysql_query( $sql );
		while ( $row = mysql_fetch_assoc($query) ) {
		    $user = new user( $row );
		    $user->get_rank();
			$user->get_school_class();
			// the idea was to disable personalization for OT so that there's the same number of pages that get printed for every child in each class, 
			// but since it doesn't help anyway (b/c ages could be different in same class), the if statment will not return true but false
			// if ( !$allowPersonalization && $row['school_id'] == 255 ) $user->disablePersonalization(); 
		    $user->get_user_tracks( -1, $this->start, $this->end, array(), $user->lang_id, $printing_mode );
		    array_push( $this->missions, $user );
		}
		chdir("mission_report");
	}
	
	public function getMissions() {
		return $this->missions;
	}
}
?>