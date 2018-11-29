<?php
class school {	
	
	function __construct($row = NULL, $school_id = NULL) {
	
		if (is_null($row)) {			
			$sql = "SELECT * FROM schools WHERE school_id=" . $school_id;
			$query = mysql_query($sql);
			$row = @mysql_fetch_assoc($query);
		}
		
		$this->school_id = $row['school_id'];
		$this->school_name = $row['school_name'];
		$this->school_name_he = $row['school_name_he'];
		$this->inst_id = $row['inst_id'];
		$this->school_makeup_id = $row['school_makeup_id'];
		$this->school_settings = $row['school_settings'];
		$this->package_id = $row['package_id'];
		$this->school_gender = $row['school_gender'];
		$this->school_number = $row['school_number'];
		$this->school_logo_id = $row['school_logo_id'];
		$this->school_logo_kiosk_id = $row['school_logo_kiosk_id'];
		$this->school_no_logo = $row['school_no_logo'];
		$this->school_file_id = $row['school_file_id'];
		$this->school_address1 = $row['school_address1'];
		$this->school_address2 = $row['school_address2'];
		$this->school_city = $row['school_city'];
		$this->school_state = $row['school_state'];
		$this->school_country = $row['school_country'];
		$this->school_postal = $row['school_postal'];
		$this->school_phone = $row['school_phone']; 
		$this->kiosk_print = $row['kiosk_print'];
		$this->school_era = $row['school_era'];
		$this->shipping_method = $row['shipping_method'];
		$this->shipping_first = $row['shipping_first'];
		$this->shipping_last = $row['shipping_last'];
		$this->shipping_phone = $row['shipping_phone'];
		$this->shipping_address1 = $row['shipping_address1'];
		$this->shipping_address2 = $row['shipping_address2'];
		$this->shipping_city = $row['shipping_city'];
		$this->shipping_state = $row['shipping_state'];
		$this->shipping_postal = $row['shipping_postal'];
		$this->shipping_country = $row['shipping_country'];
		$this->school_store = $row['school_store'];
		$this->camp_id = $row['camp_id'];
		$this->add_on_one = $row['add_on_one'];
		$this->add_on_two = $row['add_on_two'];
		$this->cc_approval_number = $row['cc_approval_number'];
	}
	
	function get_students($school_id) {		
		$sql = "SELECT * FROM users WHERE school_id=" . $this->school_id;
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			$student = new student($row);
			array_push($this->students, $student);
		}		
	}
	
	function get_classes() {
		$sql = "SELECT * FROM classes AS c WHERE school_id=" . $this->school_id . " ORDER BY class_grade, class_sub";
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			$class = new school_class($row);
			array_push($this->classes, $class);
		}	
	}
	
}
?>