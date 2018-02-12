<?php
class kiosk_type {	
	public $kiosk_type_id;
	public $kiosk_name;
	public $price;
	public $non_ded_price;
	public $with_dedication;
	public $quantity;
	
	function __construct($row) {
		$this->kiosk_type_id = $row["kiosk_type_id"];
		$this->kiosk_name = $row["kiosk_name"];
		$this->price = $row["price"];
		$this->non_ded_price = $row["non_ded_price"];	
	}
	
	function get_school_quantity($school_id) {
		$sql = "SELECT * FROM school_kiosks WHERE school_id=" . $school_id . " AND kiosk_type_id=" . $this->kiosk_type_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->with_dedication = $row["with_dedication"];
		$this->quantity = $row["quantity"];
	}
}
?>

