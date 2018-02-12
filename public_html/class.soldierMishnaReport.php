<?
class SoldierMishnaReport extends MishnaReport {
	
	public function __construct( $user ) {
		parent::__construct();
		$this->setLearned( $user );
		$this->setInfo();
	}
	
	protected function setLearned( $user ) {
		//only get users from this school
		$sql = "select * from mishna_learned where user_id = " . $this->user;
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$this->learned[$row['user_id']][] = $row;
		}
	}
}
?>