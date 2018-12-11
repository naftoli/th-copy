<?
class WeeklyEmail {
	private $users;
	private $dates;
	private $userMissions;
	
	public function __construct( $schoolID, $start, $end ) {
		$sql = "select user_id from users where school_id = " . $schoolID;
		$result = mysql_query( $sql );
		while ( $row = mysql_fetch_assoc( $result ) ) {
			$this->users[] = $row['user_id'];
		}
		$this->dates['start'] = $start;
		$this->dates['end'] = $end;
	}
	
	public function setUserMissions() {
		require_once 'class.missionMarks.php';
		foreach ( $this->users as $userID ) {
			$mm = new MissionMarks( $userID, 0, $this->dates['start'], $this->dates['end'] );
			if ( $this->mm->isCompleted() ) {
				$msg = "completed";
			} else {
				$msg = $this->mm->getDebugInfo();
			}
			$this->userMissions[$userID] = $msg;
		}
	}
	
	public function sendEmail() {
		//get parsha info 
		require_once 'class.parshos.php';
		$p = new Parshos();
		$parsha = $p->getParshos( $this->dates['start'], $this->dates['end'] );
		if ( !$parsha ) {
			return "No parsha found that matches given dates."; 
		}
		
		foreach ( $this->userMissions as $userID => $msg ) {
			//get admin info for
			$sql = "select * from admins where admin_id = (select admin_id from admin_auths where id = $userID and auth = 'user')";
			$result = mysql_query($sql);
			$admin = mysql_fetch_assoc($result);
			
			$to = $admin['admin_email'];
			$subject = "Achos Hatemimim Weekly Progress";
			
			$name = $admin['first'] . ' ' . $admin['last'];
			
			if ( $msg == 'completed' ) {
				//send congratulatory email
				$emailMsg = "<b>Congratulations " . $name . "!</b><br /> You have finished all necessary tasks for the week of " . $parsha['name'] . ".<br />Keep up the great work!";
			} else {
				//send reminder
				$emailMsg = "<b>Hello " . $name . "</b><br />This is a friendly reminder that your mission for the week of " . $parsha['name'] . " has not been marked as completed because " . $msg;
			}
			
			// To send HTML mail, the Content-type header must be set
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			
			mail( $to, $subject, $msg, $headers );
		}
	}
}
?>