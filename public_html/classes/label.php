<?
class label {
	public $label_id;
	public $label_name;
	public $label_description;
	public $label_image_id;
	public $frequency_id;
	public $sequence_number;
	public $frequency;
	
	function __construct($row) {
		$this->label_id = $row["label_id"];
		$this->label_name = $row["label_name"];
		$this->label_description = $row["label_description"];
		$this->label_image_id = $row["label_image_id"];
		$this->frequency_id = $row["frequency_id"];
	}
	
	function get_frequency() {
		include_once("frequency.php");
		$sql = "SELECT * FROM frequencies WHERE frequency_id=" . $this->frequency_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->frequency = new frequency($row);
	}
}
?>