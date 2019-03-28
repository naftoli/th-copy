<?
abstract class BaalPeh {
	protected $campaign;
	protected $sql;
	
	public function __construct($campaign) {
		$this->campaign = $campaign;
	}
	
	public abstract function getTotalForUser($id);
	
	public function getTotalForSchool($school_id) {
		return $this->getTotal('school', $school_id);
	}
	
	public function getTotalForClass($class_id) {
		return $this->getTotal('class', $class_id);
	}
	
	private function getTotal($type, $id) {
		$users = array();
		$sql = "select user_id from users where {$type}_id = $id and user_registered > 0";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$users[] = $row['user_id'];
		}
		$total = 0;
		foreach ($users as $user) {
			$total += $this->getTotalForUser($user);
		}
		return $total;
	}
}
?>
