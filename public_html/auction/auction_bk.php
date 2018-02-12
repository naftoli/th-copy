<?
class auction 
{
	public $auction_id;
	public $auction_name;
	public $school_id;
	public $auction_points_start_date;
	public $auction_date;
	public $auction_points_trigger_date;
	public $auction_run_date;
	public $auction_message;
	public $max_prize_points;
	public $auction_ran;
	public $approved;
	
	public $big_auction_prizes = array();	
	public $small_auction_prizes = array();	
	
	public $ratio;
	
	function __construct($row)
	{
		$this->auction_id = $row['auction_id'];
		$this->auction_name = $row['auction_name'];
		$this->school_id = $row['school_id'];
		$this->auction_points_start_date = $row['auction_points_start_date'];
		$this->auction_date = $row['auction_date'];
		$this->auction_points_trigger_date = $row['auction_points_trigger_date'];
		$this->auction_run_date = $row['auction_run_date'];
		$this->auction_message = $row['auction_message'];
		$this->max_prize_points = $row['max_prize_points'];
		$this->auction_ran = $row['auction_ran'];
		$this->approved = $row['approved'];	
	}
	
	public function get_big_prizes()
	{		
		$sql = "SELECT ap.* ";
		$sql = $sql . "FROM auction_prizes AS ap ";
		$sql = $sql . "JOIN prizes_auction AS pa USING (prize_id) ";
		$sql = $sql . "WHERE ap.auction_id=" . $this->auction_id . " ";
		$sql = $sql . "AND pa.prize_points > 71 ";
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query))
		{
			$auction_prize = new auction_prize($row);
			array_push($this->big_auction_prizes, $auction_prize);
		}
	}
	
	public function award_big_prizes()
	{
		global $number_of_prizes_awarded;
		$schools = array();
		$next = false;
		
		foreach ($this->big_auction_prizes as $big_auction_prize)
		{
			$available = $big_auction_prize->available;
			
			$prizes_awarded = 0;
			//do {				
				
				// ***** Find the school with the least number of big prizes won ***** //
				$sql = "SELECT u.user_id, u.school_id, u.big_prizes_won AS user_big_prizes_won, s.big_prizes_won AS school_big_prizes_won ";
				$sql = $sql . "FROM auction_user_prizes AS aup ";
				$sql = $sql . "JOIN users AS u USING (user_id) ";
				$sql = $sql . "JOIN schools AS s ON (u.school_id=s.school_id) ";
				$sql = $sql . "WHERE aup.auction_id=" . $this->auction_id . " ";
				$sql = $sql . "AND aup.prize_id=" . $big_auction_prize->prize_id . " ";
				$sql = $sql . "AND u.school_id <> 12 ";
				$sql = $sql . "AND u.school_id <> 13 ";
				$sql = $sql . "AND u.school_id <> 14 ";			
				$sql = $sql . "ORDER BY school_big_prizes_won, user_big_prizes_won ";
				//$sql = $sql . "LIMIT 1 ";
				
				$query = mysql_query($sql);
				while ($row = mysql_fetch_assoc($query)) {
				
					if ($this->ratio_reached($row['school_id']))
						continue;	
					else {
					
						// ***** Award the big prize to one of the students from the school ***** //
						$insert_sql = "INSERT INTO auction_winners ";
						$insert_sql = $insert_sql . "SET auction_id=" . $this->auction_id. ", ";
						$insert_sql = $insert_sql . "user_id=" . $row['user_id'] . ", ";
						$insert_sql = $insert_sql . "prize_id=" . $big_auction_prize->prize_id . ", ";
						$insert_sql = $insert_sql . "quantity=1";
						$insert_query = mysql_query($insert_sql);
						
						// ***** Add one to the number of big prizes won for the corresponding school ***** //
						$big_prizes_won = $row['school_big_prizes_won'] + 1;
						$update_sql1 = "UPDATE schools ";
						$update_sql1 = $update_sql1 . "SET big_prizes_won=" . $big_prizes_won . " ";
						$update_sql1 = $update_sql1 . "WHERE school_id=" . $row['school_id'];
						$update_query1 = mysql_query($update_sql1);
						
						// ***** Add one to the number of big prizes won for the corresponding user ***** //
						$big_prizes_won = $row['user_big_prizes_won'] + 1;
						$update_sql2 = "UPDATE users ";
						$update_sql2 = $update_sql2 . "SET big_prizes_won=" . $big_prizes_won . " ";
						$update_sql2 = $update_sql2 . "WHERE user_id=" . $row['user_id'];
						$update_query2 = mysql_query($update_sql2);			
						
						$number_of_prizes_awarded++;
						$prizes_awarded++;
						
						if ($prizes_awarded < $available)
							break;
					}
					
				}
				
				
			//} while ($prizes_awarded < $available);
			
		}
	}

	public function get_small_prizes()
	{		
		$sql = "SELECT ap.* ";
		$sql = $sql . "FROM auction_prizes AS ap ";
		$sql = $sql . "JOIN prizes_auction AS pa USING (prize_id) ";
		$sql = $sql . "WHERE ap.auction_id=" . $this->auction_id . " ";
		$sql = $sql . "AND pa.prize_points < 72 ";
		$sql = $sql . "ORDER BY prize_id ";
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query))
		{
			$auction_prize = new auction_prize($row);
			array_push($this->small_auction_prizes, $auction_prize);
		}
	}
	
	public function award_small_prizes()
	{
		global $beginning_of_hebrew_year;
		global $number_of_prizes_awarded;
		
		foreach ($this->small_auction_prizes as $small_auction_prize)
		{	
			$small_auction_prize->small_auction_user_prizes = array();
			
			$sql = "SELECT aup.* ";
			$sql = $sql . "FROM auction_user_prizes AS aup ";
			$sql = $sql . "JOIN users AS u USING (user_id) ";
			$sql = $sql . "WHERE aup.auction_id=" . $this->auction_id . " ";
			$sql = $sql . "AND aup.prize_id=" . $small_auction_prize->prize_id . " ";
			$sql = $sql . "AND u.small_prizes_won < 3 ";
			$sql = $sql . "AND u.school_id <> 12 ";
			$sql = $sql . "AND u.school_id <> 13 ";
			$sql = $sql . "AND u.school_id <> 14 ";		
			
			$query = mysql_query($sql);
			while ($row = mysql_fetch_assoc($query))
			{
				$quantity = $row['quantity'];
				
				for ($ticket_number = 0; $ticket_number < $quantity; $ticket_number++)
				{
					$auction_user_prize = new auction_user_prize($row);
					array_push($small_auction_prize->small_auction_user_prizes, $auction_user_prize);
				}
			}
		}
		
		foreach ($this->small_auction_prizes as $small_auction_prize)
		{
			$available = $small_auction_prize->available;
			$number_of_tickets = count($small_auction_prize->small_auction_user_prizes);
			
			$prizes_awarded = 0;
			do {				
				$winner = rand(0, ($number_of_tickets - 1));
				
				/*
				$school_sql = "select school_id from users where user_id = " . $small_auction_prize->small_auction_user_prizes[$winner]->user_id;
				$school_res = mysql_query($school_sql);
				$school_row = mysql_fetch_row($school_res);
				$school_id = $school_row[0];
				
				if ($this->ratio_reached($school_id)) continue;
				*/
				
				// ***** Make sure that this student has not already won this prize this year ***** //
				$sql1 = "SELECT * ";
				$sql1 = $sql1 . "FROM auction_winners AS aw ";
				$sql1 = $sql1 . "JOIN auctions AS a USING (auction_id) ";
				$sql1 = $sql1 . "WHERE aw.user_id=" . $small_auction_prize->small_auction_user_prizes[$winner]->user_id . " ";
				$sql1 = $sql1 . "AND aw.prize_id=" . $small_auction_prize->prize_id . " ";
				$sql1 = $sql1 . "AND a.auction_date > " . $beginning_of_hebrew_year;
				$query1 = mysql_query($sql1);
				$num_rows1 = mysql_num_rows($query1); 
				// ***** Make sure that this student has not already won this prize this year ***** //
				
				// ***** Make sure that this student has not already won 3 small prizes this year ***** //
				$sql2 = "SELECT small_prizes_won FROM users WHERE user_id=" . $small_auction_prize->small_auction_user_prizes[$winner]->user_id;
				$query2 = mysql_query($sql2);
				$row2 = mysql_fetch_assoc($query2);
				$small_prizes_won = $row2['small_prizes_won'];
				// ***** Make sure that this student has not already won 3 small prizes this year ***** //
								
				if ($num_rows1 == 0 && $small_prizes_won < 3)
				{
					$insert_sql = "INSERT INTO auction_winners SET auction_id=" . $this->auction_id . ", user_id=" . $small_auction_prize->small_auction_user_prizes[$winner]->user_id . ", prize_id=" . $small_auction_prize->prize_id . ", quantity=1";
					$insert_query = mysql_query($insert_sql);
					$prizes_awarded++;

					$number_of_prizes_awarded++;
					
					$small_prizes_won++;

					// ***** Update the number of small prizes won for the corresponding student ***** //
					$update_sql = "UPDATE users SET small_prizes_won=" . $small_prizes_won . " WHERE user_id=" . $small_auction_prize->small_auction_user_prizes[$winner]->user_id;
					$update_query = mysql_query($update_sql);
				}
								
			} while ($prizes_awarded < $available);			
		}
	}
	
	public function ratio() {
		$sql = "SELECT SUM(available) FROM  `auction_prizes` WHERE auction_id = " . $this->auction_id;
		$result = mysql_query($sql);
		$row = mysql_fetch_row($result);
		$total_prizes = $row[0];
		
		$sql = "select * from users where user_registered > 0";
		$result = mysql_query($sql);
		$total_users = mysql_num_rows($result);
		
		$ratio = round($total_users / $total_prizes);
		$this->$ratio = $ratio;
	}
	
	public function ratio_reached($school_id)
	{
		$sql = "select * from users where user_registered > 0 and school_id = $school_id";
		$result = mysql_query($sql);
		$num_users = mysql_num_rows($result);
		
		$sql = "select aw.* from auction_winners as aw, users as u 
				where u.user_id = aw.user_id 
				and aw.auction_id = " . $this->auction_id . "
				and u.school_id = $school_id";
		$result = mysql_query($sql);
		$num_prizes = mysql_num_rows($result);
		
		if ($num_prizes > 0) {
			if (round($num_users / $num_prizes) >= $this->ratio) 
				return true;
			else 
				return false;
		} else 
			return false;
	}
} 
?>