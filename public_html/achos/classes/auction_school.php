<?
class auction_school
{
	public $school_id;
	public $school_name;
	
	public $school_tickets;
	public $ticket_percentage;
	
	public $prizes_won;
	public $prize_percentage;
	
	public $users;
	public $user_percentage;
	
	function __construct($row)
	{
		$this->school_id = $row['school_id'];
		$this->school_name = $row['school_name'];
	
		$this->get_total_tickets();
		$this->get_tickets_won();
		
		$this->get_registered_users();
	}
	
	function get_total_tickets()
	{
		global $auction_id;
		global $total_tickets;
		
		$sql = "SELECT SUM(aup.quantity) AS total_tickets ";
		$sql = $sql . "FROM auction_user_prizes AS aup ";
		$sql = $sql . "JOIN users AS u USING (user_id) ";
		$sql = $sql . "WHERE aup.auction_id=" . $auction_id . " ";
		$sql = $sql . "AND school_id=" . $this->school_id;
		$query = mysql_query($sql);	
		$row = mysql_fetch_assoc($query);
		$this->school_tickets = $row['total_tickets'];
		$this->ticket_percentage = round( (($this->school_tickets / $total_tickets) * 100), 2);
	}	
	
	function get_tickets_won()
	{
		global $auction_id;
		global $total_prizes;
		
		$sql = "SELECT SUM(quantity) AS prizes_won ";
		$sql = $sql . "FROM auction_winners AS aw ";
		$sql = $sql . "JOIN users AS u USING (user_id) ";
		$sql = $sql . "WHERE aw.auction_id=" . $auction_id . " ";
		$sql = $sql . "AND school_id=" . $this->school_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->prizes_won = $row['prizes_won'];
		$this->prize_percentage = round( (($this->prizes_won / $total_prizes) * 100), 2);
	}
	
	//function get_total_students()
	//{
		//global $auction_id;
		//global $total_students;
		//global $total_prizes;
		
		//$sql = "SELECT user_id ";
		//$sql = $sql . "FROM auction_user_prizes AS aup ";
		//$sql = $sql . "JOIN users AS u USING (user_id) ";
		//$sql = $sql . "WHERE aup.auction_id=" . $auction_id . " ";
		//$sql = $sql . "AND u.school_id=" . $this->school_id . " ";
		//$sql = $sql . "GROUP BY aup.user_id ";
		//$query = mysql_query($sql);
		//$this->total_students = mysql_num_rows($query);		
		//$this->student_percentage = round((($this->total_students / $total_students) * 100), 2);		
		//$this->prizes_to_be_won = (($this->student_percentage / 100) * $total_prizes);		
	//}
	
	public function get_registered_users() {
		$sql = "select * from users where user_registered > 0 and school_id = " . $this->school_id;
		$result = mysql_query($sql);
		$this->users = mysql_num_rows($result);
		$sql2 = "select * from users where user_registered > 0";
		$result2 = mysql_query($sql2);
		$total_users = mysql_num_rows($result2);
		$this->user_percentage = round( (($this->users / $total_users) * 100), 2);
	}
}
?>