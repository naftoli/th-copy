<?
class staff_type {
	public $staff_types_id;
	public $type_name;
	public $no_of_staff;
	
	public function __construct($row){
		$this->staff_types_id = $row['staff_types_id'];
		$this->type_name = $row['type_name'];
	}	
	
	public function get_no_of_staff($camp_id) {
		$sql = "SELECT COUNT(*) AS no_of_staff FROM admins WHERE camp_id=" . $camp_id . " AND staff_type_id=" . $this->staff_types_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$this->no_of_staff = $row['no_of_staff'];
	}
}
?>