<?php
namespace classes;
// NOTE, THIS CLASS IS A MANUAL ORM, DEPENDS ON DB CONNECTION

class school 
{	
	public $school_id;
	public $school_name;
	public $school_name_he;
	public $inst_id;
	public $school_makeup_id;
	public $school_settings;
	public $package_id;
	public $school_gender;
	public $school_number;
	public $logo;
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
	public $cc_first;
	public $cc_last; 
	public $cc_address;
	public $cc_state;
	public $cc_zip;
	public $cc_number;
	public $cc_exp;
	public $cc_cvv;
	public $authorize_customer_profile_id;
	public $authorize_payment_profile_id;
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
	public $last_register_date;
	public $cc_approval_number;
	
	public $add_on_one_grades = array();
	public $add_on_two_grades = array();
	
	public $auction_students;
	
	public $subjects = array();
	public $users = array();
	public $classes = array();
	public $ranks = array();
	
	public $number_of_registered_students;
	public $number_of_teachers;
	
	function __construct($row = NULL, $school_id = NULL) 
	{
		if (!is_null($school_id))
		{
			$sql = "SELECT * FROM shools WHERE school_id=" . $school_id;
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);
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
		$this->logo = $row['logo'];
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
		$this->cc_first = $row['cc_first'];
		$this->cc_last = $row['cc_last']; 
		$this->cc_address = $row['cc_address'];
		$this->cc_state = $row['cc_state'];
		$this->cc_zip = $row['cc_zip'];
		$this->cc_number = $row['cc_number'];
		$this->cc_exp = $row['cc_exp'];
		$this->cc_cvv = $row['cc_cvv'];
		$this->authorize_customer_profile_id = $row['authorize_customer_profile_id'];
		$this->authorize_payment_profile_id = $row['authorize_payment_profile_id'];
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
		$this->last_register_date = $row['last_register_date'];
		$this->cc_approval_number = $row['cc_approval_number'];
		
		$this->auction_students = 0;
	}

	public function logoPath(){
		return "/schoolLogos/$this->logo";
	}
	
	function get_student_rank_totals($ranks_totals)
	{		
		$ranks = array();
		$sql = "SELECT ranks.*, COUNT(*) num ";
		$sql = $sql . "FROM ranks ";
		$sql = $sql . "LEFT JOIN rank_marks USING (rank_ord) ";
		$sql = $sql . "WHERE rank_ord <= (SELECT MAX(rank_ord) FROM rank_marks) ";
		$sql = $sql . "GROUP BY rank_ord, rank_name, rank_color ";
		$sql = $sql . "ORDER BY rank_ord";
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) 
		{
			$rank = new rank($row);
			array_push($ranks, $rank);
		}		
		
		$sql = "SELECT user_id, rank_ord ";
		$sql = $sql . "FROM users ";
		$sql = $sql . "JOIN rank_marks USING (user_id) ";
		$sql = $sql . "WHERE school_id=" . $this->school_id . " ";
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query))
		{
			foreach ($ranks as $rank)
			{
				if ($rank->rank_ord == $row['rank_ord'])
				{
					$rank->total_students++;
				}
			}

			foreach ($ranks_totals as $rank)
			{
				if ($rank->rank_ord == $row['rank_ord'])
				{
					$rank->total_students++;
				}
			}
			
		}
		
		$this->ranks = $ranks;		
	}
	
	function get_classes()
	{
		$sql = "SELECT * FROM classes AS c WHERE school_id=" . $this->school_id . " ORDER BY class_grade, class_sub";
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query))
		{
			$class = new school_class($row);
			array_push($this->classes, $class);
		}	
	}
	
	function get_subjects() {
		$sql = "SELECT s.subject_id, s.subject_name FROM school_subjects AS ss JOIN subjects AS s USING (subject_id) WHERE ss.school_id=" . $this->school_id . " ORDER BY s.subject_name";
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			array_push($this->subjects, $row);
		}	
	}
	
	function get_users()
	{		
		$sql = "SELECT * FROM users WHERE school_id=" . $this->school_id . " AND user_registered > 0 ORDER BY first, last";
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			$user = new user($row);
			$user->get_school_class();
			array_push($this->users, $user);
		}	
	}
	
	function get_number_of_registered_students()
	{
		$sql = "SELECT count(*) AS number_of_registered_students FROM users WHERE school_id=" . $this->school_id . " AND user_registered > 0";
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->number_of_registered_students = $row['number_of_registered_students'];		
	}
	
	function get_number_of_teachers()
	{
		$sql = "SELECT count(*) AS number_of_teachers FROM classes WHERE class_teacher <> '' AND school_id=" . $this->school_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->number_of_teachers = $row['number_of_teachers'];				
	}
}
?>