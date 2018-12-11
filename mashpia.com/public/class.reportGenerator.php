<?
class ReportGenerator {
	private $fields;
	private $selectedFields;
	private $dates;
	private $schools;
	private $classes;
	private $sql;
	private $where;
	private $sortBy;
	
	public function __construct() {
		$this->fields = array(
			'users'	=> array(
					'user_code'		=>	'barcode', 
					'first'			=>	'first name', 
					'last'			=>	'last name', 
					'first_he'		=>	'hebrew first', 
					'last_he'		=>	'hebrew last', 
					'school_type_id'=>	'school type', 
					'user_serial'	=>	'serial number', 
					'user_address1'	=>	'address', 
					'user_address2'	=>	'address2', 
					'user_city'		=>	'city', 
					'user_postal'	=>	'zip', 
					'user_country'	=> 	'country', 
					'user_phone'	=>	'phone', 
					'gender'		=>	'gender', 
					'user_start_date' => 'start date', 
					'user_registered' => 'registered', 
					'dob'			=>	'dob', 
					'dob_he'		=>	'hebrew dob'										
				),
			'date_tasks_missions' => array(
			
				),
			'date_tasks_mission_marks' => array(
			
				),
			'date_tasks' => array(
			
				),
			'date_tasks_marks' => array(
			
				),
			'medal_marks' => array(
			
				),
			'rank_marks' => array(
				
				)			
		);
		$this->selectedFields = array();
		$this->dates = array();
		$this->schools = array();
		$this->classes = array();
		$this->sql = "";
		$this->where = "";
		$this->sortBy = "";
	}
	
	public function getFields( $key ) {
		return $this->fields[$key];
	}
	
	public function setSelectedFields( $fields ) {
		$this->selectedFields = $fields;
	}
	
	public function setSortBy( $sortBy ) {
		$this->sortBy = $sortBy;
	}
	
	public function setDates( $dates ) {
		$this->dates = $dates;
	}
	
	public function setSchools( $schools ) {
		$this->schools = $schools;
	}
	
	public function setClasses( $classes ) {
		$this->classes = $classes;
	}
	
	public function createQuery() {
		$str = array();
		$tables = array(); 
		foreach( $this->selectedFields as $field ) {
			//find table for field by finding index in fields array
			$table = array_search( $field, $this->fields );
			$tables[] = $table;
			$str[] = $table . "." . $field;
		} 
		$this->sql = "select ";
		$this->sql .= implode(', ', $str);
		$this->sql .= " from " . implode(', ', $tables);
		if ( !empty( $this->where ) ) 
			$sql .= " where " . $this->where;
		if ( !empty( $this->sortBy ) ) 
			$this->sql .= " order by " . $this->sortBy;
	}
	
	public function runQuery() {
		echo $this->sql;
	}
}
?>