<?php
class school_kiosk {	
	public $school_kiosk_id;
	public $school_id;
	public $kiosk_type_id;
	public $with_dedication;
	public $quantity;
	public $kiosk_type;
	public $price;
	
	function __construct($row) {
		$this->school_kiosk_id = $row["school_kiosk_id"];
		$this->school_id = $row["school_id"];
		$this->kiosk_type_id = $row["kiosk_type_id"];
		$this->with_dedication = $row["with_dedication"];
		$this->quantity = $row["quantity"];	
	}
	
	function get_kiosk_type() {
		$sql = "SELECT * FROM kiosk_types WHERE kiosk_type_id=" . $this->kiosk_type_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$kiosk_type = new kiosk_type($row);
		$this->kiosk_type = $kiosk_type;
	
		if ($this->with_dedication == 1) {	
			$this->price = $this->kiosk_type->price;
		}
		else {
			$this->price = $this->kiosk_type->non_ded_price;
		}
		
	}
}
?>

