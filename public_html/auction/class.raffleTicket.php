<?
class RaffleTicket {
	private $users;
	private $start;
	private $end;
	private $reports;
	
	public function __construct( array $users, $start, $end ) {
		$this->users = $users;
		$this->start = $start;
		$this->end = $end;
		$this->setReports();
	}
	
	private function setReports() {
		$reports = array();
		$sql = "select report_id from reports where start_date >= " . $this->start . " and end_date <= " . $this->end;
		$result = mysql_query( $sql );
		while ( $row = mysql_fetch_assoc( $result ) ) {
			$this->reports[] = $row['report_id'];
		}
		//echo "<pre>"; print_r($this->reports); echo "</pre>"; exit;
	}
	
	public function calculateTickets() {
		$userTickets = array();
		//require_once '../class.missionSheet.php';
		//$m = new MissionSheet;
		foreach ( $this->users as $user ) {
			/*
			$sql = "select count(date_task_id) as total  
                    from date_tasks_marks 
                    join date_tasks dt using (date_task_id) 
                    join date_tasks_missions dtm using (date_tasks_mission_id) 
                    where user_id = " . $user . " 
                    and dtm.start_date >= " . $this->start . "  
                    and dtm.end_date <= " . $this->end;
			*/
			/*
			$sql = "select date_task_id   
                    from date_tasks_marks 
                    where user_id = " . $user . " 
                    and mark_date >= " . $this->start . "  
                    and mark_date <= " . $this->end . " limit 1";
			$result = mysql_query( $sql );
			if ( mysql_num_rows( $result ) ) {
				//$row = mysql_fetch_assoc( $result );
				//$total = (int) ceil( $row['total'] / 7 ) ;
				//$total = $row['total'];
				//if ( $total > 5 ) $total = 5; //limit child to 5 tickets
				//for ( $i = 0; $i < $total; $i++ ) {
					$userTickets[] = $user;
				//}
			}
			*/
			/*
			foreach ( $this->reports as $report ) {
				if ( $m->marked( $user, $report ) ) {
					$userTickets[] = $user;
				}
			}
			 * 
			 */
			$sql = "select count(*) as total from date_tasks_mission_marks
					where mark_date >= " . $this->start . "
					and mark_date <= " . $this->end . "
					and user_id = " . $user;
			$result = mysql_query($sql);
			if (mysql_num_rows($result) > 0) {
				$row = mysql_fetch_assoc($result);
				$total = $row['total'];
				for ($i = 0; $i < $total; $i++) {
					$userTickets[] = $user;
				}
			} else {
				$userTickets = array();
			}
		}
		return $userTickets;
	}
}
?>