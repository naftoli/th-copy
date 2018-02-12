<? 
class frequency {
	public $frequency_id;
	public $frequency_name;
	public $frequency_period_id;
	public $monday;
	public $tuesday;
	public $wednesday;
	public $thursday;
	public $friday;
	public $shabbos;
	public $sunday;
	public $frequency_period;
	
	function __construct($row) {
		$this->frequency_id = $row["frequency_id"];
		$this->frequency_name = $row["frequency_name"];
		$this->frequency_period_id = $row["frequency_period_id"];
		$this->monday = $row["monday"];
		$this->tuesday = $row["tuesday"];
		$this->wednesday = $row["wednesday"];
		$this->thursday = $row["thursday"];
		$this->friday = $row["friday"];
		$this->shabbos = $row["shabbos"];
		$this->sunday = $row["sunday"];
	}	
	
	function get_frequency_period() {
		include_once("frequency_period.php");
		$sql = "SELECT * FROM frequency_periods WHERE frequency_period_id=" . $this->frequency_period_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->frequency_period = new frequency_period($row);
	}
}
?>
