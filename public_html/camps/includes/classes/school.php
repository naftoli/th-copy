<?php
class school {
	public $school_id;
	public $school_name;
	public $school_name_he;
	public $inst_id; 
	public $school_makeup_id;
	public $school_settings;
	public $package_id; 
	public $school_gender;
	public $school_number;
	public $school_logo_id;
	public $school_logo_kiosk_id;
	public $school_no_logo;
	public $school_file_id;
	public $school_address1;
	public $school_address2;
	public $school_city;
	public $school_state;
	public $school_country;
	public $school_postal;
	public $school_phone;
	public $kiosk_print;
	public $school_era;
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
	public $school_store;
	public $camp_id;
	public $add_on_one;
	public $add_on_two;
	
	public function __construct($row){
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
	}
	
}
?>