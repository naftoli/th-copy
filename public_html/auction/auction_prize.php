<? 
class auction_prize
{
	public $auction_id;
	public $prize_id;
	public $available;
	
	function __construct($row)
	{
		$this->auction_id = $row['auction_id'];
		$this->prize_id = $row['prize_id'];
		$this->available = $row['available'];
	}
	
}
?>
