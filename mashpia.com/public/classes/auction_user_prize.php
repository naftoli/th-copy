<?
class auction_user_prize
{
	public $auction_id;
	public $user_id;
	public $prize_id;
	public $quantity;
	
	function __construct($row)
	{
		$this->auction_id = $row['auction_id'];
		$this->user_id = $row['user_id'];
		$this->prize_id = $row['prize_id'];
		$this->quantity = $row['quantity'];
	}	
		
} 
?>