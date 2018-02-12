<?
class auction_user
{
	public $user_id;
	public $miles_available;
	public $miles_used;
	public $miles_left;
	public $has_won;
	public $prizes_won = array();
	
	function __construct($user_id)
	{
		$this->user_id = $user_id;
		$this->has_won = false;
	}
	
	function set_miles_available($miles_available)
	{
		$this->miles_available = $miles_available;
	}
	
	//function set_miles_available($miles_available)
	//{
	//	$this->miles_available = $miles_available;
	//}
		
	function set_miles_used($miles_used)
	{
		$this->miles_used = $miles_used;
	}
		
	function set_miles_left()
	{
		$this->miles_left = $this->miles_available - $this->miles_used;
	}
		
} 
?>