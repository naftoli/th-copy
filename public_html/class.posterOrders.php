<?
class PosterOrders {
	private $schoolID;
	private $classes;
	
	public function __construct($school_id) {
		$this->schoolID = $school_id;
		require_once 'class.schoolClasses.php';
		$c = new SchoolClasses($school_id);
		$this->classes = $c->getClassIDs();
	}
	
	public function getOrders($type) {
		$orders = array();
		if (!empty($this->classes)) {
			$sql = "select class_id, qty from poster_orders where type = '$type' and class_id in (" . implode(",", $this->classes) . ")";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$orders[$row['class_id']] = $row['qty'] ? $row['qty'] : 0;
			}
		}
		return $orders;
	}
	
	public function getSchoolOrders($type) {
		$sql = "select qty from poster_orders where type = '$type' and school_id = " . $this->schoolID;
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		return $row['qty'] ? $row['qty'] : 0;
	}
	
	public function getClassOrders($id, $type) {
		$sql = "select qty from poster_orders where type = '$type' and class_id = " . $id;
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		return $row['qty'] ? $row['qty'] : 0;
	}
	
	public static function updateClassOrders($id, $qty, $type) {
		//find out if class already has an entry in db
		$sql = "select * from poster_orders where class_id = $id and type = '$type'";
		$result = mysql_query($sql);
		if (mysql_num_rows($result) > 0) {
			$sql = "update poster_orders set qty = $qty where class_id = $id and type = '$type'";
			mysql_query($sql);
		} else {
			$sql = "insert into poster_orders values(null, '$type', '', $id, '', $qty)";
			mysql_query($sql);
		}
	}
}
?>