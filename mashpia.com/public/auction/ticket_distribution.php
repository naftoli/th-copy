<?
class ticket_distribution
{
	function __construct($auction_id)
	{
		$auction_sql = "SELECT * FROM auctions WHERE auction_id=" . $auction_id;
		$auction_query = mysql_query($auction_sql);
		$auction_row = mysql_fetch_assoc($auction_query);
		
		$total_users = 0;
		$total_points = 0;
		$tickets_given = 0;
		
		$user_sql = "SELECT * FROM users WHERE user_registered > 0 AND school_id not in (12,13,14)";
		$user_query = mysql_query($user_sql);
		while ($user_row = mysql_fetch_assoc($user_query))
		{
			$auction_points = auctionPoints($user_row['user_id'], $auction_row, FALSE);
			//$miles_left = $auction_points['left'];
			$miles = $auction_points['cur'];
			$user_prizes_total = mysql_result(mq("SELECT IFNULL(SUM(prize_points * quantity), 0) total FROM auction_user_prizes JOIN prizes_auction USING (prize_id) WHERE auction_id = $auction_id AND user_id = {$user_row['user_id']}"), 0);
			$miles_left = $miles - $user_prizes_total;
			//added for debugging
			//print_r($auction_points);
			//echo $user_row['user_id'] . ": " . $miles_left . "<br />";
			//continue;
			
			if ($miles_left > 9) //changing number to less than 9 will create problems
			{
				$total_users++;
				$total_points = $total_points + $miles_left;
				
				$prize_sql = "SELECT ap.prize_id, pa.prize_points ";
				$prize_sql = $prize_sql . "FROM auction_prizes AS ap ";
				$prize_sql = $prize_sql . "JOIN prizes_auction AS pa ";
				$prize_sql = $prize_sql . "WHERE ap.auction_id=" . $auction_id . " ";
				$prize_sql = $prize_sql . "AND pa.prize_points > 9 ";							
				$prize_sql = $prize_sql . "AND pa.prize_points <= " . $miles_left . " ";
				$prize_sql = $prize_sql . "AND pa.prize_id in (6,10,16,99,20,128) ";
				$prize_sql = $prize_sql . "ORDER BY pa.prize_points DESC ";
				//echo $prize_sql . "<br />";
				$prize_query = mysql_query($prize_sql) or die(mysql_error());
				
				//do {
					/*
					switch ($user_row['school_type_id']) {
						case '2':
						case '12':
							$prize_sql = "SELECT ap.prize_id, pa.prize_points ";
							$prize_sql = $prize_sql . "FROM auction_prizes AS ap ";
							$prize_sql = $prize_sql . "JOIN prizes_auction AS pa ";
							$prize_sql = $prize_sql . "WHERE ap.auction_id=" . $auction_id . " ";
							$prize_sql = $prize_sql . "AND pa.prize_points > 9 ";							
							$prize_sql = $prize_sql . "AND pa.prize_points < " . $miles_left . " ";
							$prize_sql = $prize_sql . "ORDER BY pa.prize_points DESC ";
							$prize_sql = $prize_sql . "LIMIT 1";
							break;
						case '3':
						case '13':
							$prize_sql = "SELECT ap.prize_id, pa.prize_points ";
							$prize_sql = $prize_sql . "FROM auction_prizes AS ap ";
							$prize_sql = $prize_sql . "JOIN prizes_auction AS pa ";
							$prize_sql = $prize_sql . "WHERE ap.auction_id=" . $auction_id . " ";
							$prize_sql = $prize_sql . "AND pa.prize_points > 9 ";							
							$prize_sql = $prize_sql . "AND pa.prize_points < " . $miles_left . " ";
							$prize_sql = $prize_sql . "ORDER BY pa.prize_points DESC ";
							$prize_sql = $prize_sql . "LIMIT 1";
							break;
						default:
							$prize_sql = "SELECT ap.prize_id, pa.prize_points ";
							$prize_sql = $prize_sql . "FROM auction_prizes AS ap ";
							$prize_sql = $prize_sql . "JOIN prizes_auction AS pa ";
							$prize_sql = $prize_sql . "WHERE ap.auction_id=" . $auction_id . " ";
							$prize_sql = $prize_sql . "AND pa.prize_points > 9 ";							
							$prize_sql = $prize_sql . "AND pa.prize_points < " . $miles_left . " ";
							$prize_sql = $prize_sql . "ORDER BY pa.prize_points DESC ";
							$prize_sql = $prize_sql . "LIMIT 1";
							break;
					}
					 * 
					 */
					
					//loop through prizes and give out tickets
					do {
						$prize_row = mysql_fetch_assoc($prize_query);
						//$prize_row = mysql_fetch_assoc($prize_query);
						//print_r($prize_row);
	
						/*	
						$prize_sql = "SELECT ap.prize_id, pa.prize_points ";
						$prize_sql = $prize_sql . "FROM auction_prizes AS ap ";
						$prize_sql = $prize_sql . "JOIN prizes_auction AS pa ";
						$prize_sql = $prize_sql . "WHERE ap.auction_id=" . $auction_id . " ";
						$prize_sql = $prize_sql . "AND pa.prize_points < " . $miles_left . " ";
						$prize_sql = $prize_sql . "ORDER BY pa.prize_points DESC ";
						$prize_sql = $prize_sql . "LIMIT 1";
						$prize_query = mysql_query($prize_sql);
						$prize_row = mysql_fetch_assoc($prize_query);
						//echo $prize_sql . "<br />";
						 * 
						 */
					
  						$user_prize_sql = "SELECT * ";
						$user_prize_sql = $user_prize_sql . "FROM auction_user_prizes ";
						$user_prize_sql = $user_prize_sql . "WHERE auction_id=" . $auction_id . " ";
						$user_prize_sql = $user_prize_sql . "AND user_id=" . $user_row['user_id'] . " ";
						$user_prize_sql = $user_prize_sql . "AND prize_id=" . $prize_row['prize_id'];
						$user_prize_query = mysql_query($user_prize_sql);
						$num_rows = mysql_num_rows($user_prize_query);
						echo $user_prize_sql . "<br />";
						
						if ($num_rows == 0)
						{
							$ticket_sql = "INSERT INTO auction_user_prizes SET auction_id=" . $auction_id . ", ";
							$ticket_sql = $ticket_sql . "user_id=" . $user_row['user_id'] . ", ";
							$ticket_sql = $ticket_sql . "prize_id=" . $prize_row['prize_id'] . ", ";
							$ticket_sql = $ticket_sql . "quantity=1, ";
							$ticket_sql = $ticket_sql . "system_awarded=1 ";
						}
						else
						{
							$user_prize_row = mysql_fetch_assoc($user_prize_query);
							
							$quantity = $user_prize_row['quantity'] + 1;
							$ticket_sql = "UPDATE auction_user_prizes SET quantity=" . $quantity . " ";
							$ticket_sql = $ticket_sql . "WHERE auction_id=" . $auction_id . " AND ";
							$ticket_sql = $ticket_sql . "user_id=" . $user_row['user_id'] . " AND ";
							$ticket_sql = $ticket_sql . "prize_id=" . $prize_row['prize_id'];
						}
						
						echo $ticket_sql;
						$ticket_query = mysql_query($ticket_sql);
						
						$miles_left = $miles_left - $prize_row['prize_points'];
						
						$tickets_given++;
											
				} while ($miles_left > 9); //changing number to less than 9 will create problems
			}
						
		}
		
	}
} 
?>