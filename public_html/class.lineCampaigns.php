<?
class LineCampaigns {
	private $campaign;
	
	public function __construct($campaignID) {
		$this->campaign = $campaignID;	
	}
	
	public function getLinesPledged($schools) {
		$results = array();
		$userPledges = array();
		$in = implode(',', $schools);
		$sql = "select * from lines_pledged where school_id in ($in) and campaign_id = $this->campaign";
		//echo $sql;
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			//check for myshliach pledges from users
			if ($row['school_id'] == 61) {
				if ($row['user_id'] == 0) {
					$results[61] = $row['lines_pledged'];
				} else {
					$userPledges[] = $row['lines_pledged'];
				}
			} else {
				$results[$row['school_id']] = $row['lines_pledged'];
			}
		}
		
		if (!isset($results[61])) {
			$total = 0;
			foreach ($userPledges as $val) {
				$total += $val;
			}
			$results[61] = $total;
		}
		return $results;
	}
	
	public function getLinesPledgedByStudent($id) {
		$sql = "select * from lines_pledged where user_id = $id and campaign_id = " . $this->campaign;
		$result = mysql_query($sql);
		if (mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			return $row['lines_pledged'];
		} else {
			return 0;
		}
	}
	
	public function getLinesLearned($ids, $type='school') {
		//echo "<pre>"; print_r($ids); echo "</pre>"; exit;
		$results = array();
		
		$map = array(3 => 'tanya', 4 => 'mishna');
		$name = $map[$this->campaign];
		require_once "class.{$name}.php";
		$class = ucfirst($name);
		
		$obj = new $class;
		foreach ($ids as $id) {
			//first check if lines learned table has info
			$results[$id] = $obj->getTotal($id, $type);
		}
		
		return $results;
	}
}
?>