<?
class rank_mark
{
	public $rank_ord;
	public $user_id; 
	public $date_promoted;
	public $date_printed;
	public $date_book_received;
	public $date_card_received;
	public $ranks_updated;
	
	
	function __construct($row) 
	{
		$this->rank_ord = $row['rank_ord'];
		$this->user_id = $row['user_id']; 
		$this->date_promoted = $row['date_promoted'];
		$this->date_printed = $row['date_printed'];
		$this->date_book_received = $row['date_book_received'];
		$this->date_card_received = $row['date_card_received'];
		$this->ranks_updated = $row['ranks_updated'];
	}
	
}
?>