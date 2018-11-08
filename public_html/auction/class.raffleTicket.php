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
		foreach ( $this->users as $user ) {
			$sql = "select SUM( mission_count ) as total from date_tasks_mission_marks
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