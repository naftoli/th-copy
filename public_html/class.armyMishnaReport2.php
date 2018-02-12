<?
class ArmyMishnaReport extends MishnaReport {
		
	public function __construct() {
		parent::__construct();
		$this->setLearned();
		$this->setInfo();
	}
	
	public function setLearned() {
		$sql = "select * from mishna_learned";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$this->learned[$row['user_id']][] = $row;
		}
	}	
}
?>