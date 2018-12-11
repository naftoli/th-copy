<?php
class camp {
	public $camp_id;
	public $camp_type;
	public $camp_name;
	public $camp_name_he;
	public $inst_id;
	public $camp_settings;
	public $package_id;
	public $camp_gender;
	public $camp_logo_id;
	public $camp_logo_kiosk_id;
	public $camp_no_logo;
	public $camp_file_id;
	public $camp_address1;
	public $camp_address2;
	public $camp_city;
	public $camp_state;
	public $camp_country;
	public $camp_postal;
	public $camp_phone;
	public $cc_number;
	public $cc_exp;
	public $cc_cvv;
	public $kiosk_print;
	public $camp_era;
	public $shipping_method;
	public $shipping_first;
	public $shipping_last;
	public $shipping_phone;
	public $shipping_address1;
	public $shipping_address2;
	public $shipping_city;
	public $shipping_state;
	public $shipping_postal;
	public $shipping_country;
	public $start_date;
	public $end_date;
	public $camp_type_id;
	public $session_one_start;
	public $session_one_end;
	public $session_two_start;
	public $session_two_end; 
	public $camp_number;	
	public $group_types = array();
	
	public function __construct($row){
		$this->camp_id = $row['camp_id'];
		$this->camp_type = $row['camp_type'];
		$this->camp_name = $row['camp_name'];
		$this->camp_name_he = $row['camp_name_he'];
		$this->inst_id = $row['inst_id'];
		$this->camp_settings = $row['camp_settings'];
		$this->package_id = $row['package_id'];
		$this->camp_gender = $row['camp_gender'];
		$this->camp_logo_id = $row['camp_logo_id'];
		$this->camp_logo_kiosk_id = $row['camp_logo_kiosk_id'];
		$this->camp_no_logo = $row['camp_no_logo'];
		$this->camp_file_id = $row['camp_file_id'];
		$this->camp_address1 = $row['camp_address1'];
		$this->camp_address2 = $row['camp_address2'];
		$this->camp_city = $row['camp_city'];
		$this->camp_state = $row['camp_state'];
		$this->camp_country = $row['camp_country'];
		$this->camp_postal = $row['camp_postal'];
		$this->camp_phone = $row['camp_phone'];
		$this->cc_number = $row['cc_number'];
		$this->cc_exp = $row['cc_exp'];
		$this->cc_cvv = $row['cc_cvv'];
		$this->kiosk_print = $row['kiosk_print'];
		$this->camp_era = $row['camp_era'];
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
		$this->start_date = $row['start_date'];
		$this->end_date = $row['end_date'];
		$this->camp_type_id = $row['camp_type_id'];
		$this->session_one_start = $row['session_one_start'];
		$this->session_one_end = $row['session_one_end'];
		$this->session_two_start = $row['session_two_start'];
		$this->session_two_end = $row['session_two_end']; 
		$this->camp_number = $row['camp_number'];		
	}
	
	public function get_group_types() {
		$sql = "SELECT * FROM group_types WHERE camp_id=" . $this->camp_id;
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			$group_type = new group_type($row);
			array_push($this->group_types, $group_type);
		}
	}
}
?>