<?
abstract class BalPehCampaign {
	protected $campaign_id;
	protected $campaign_type;
	protected $school_id;
	protected $class_id;
	protected $user_id;
	protected $start_date;
	protected $parentEntered;
	
	public function __construct( $id ) {
		$this->campaign_id = $id;
		$sql = "select start_date from line_campaigns where id = " . $this->campaign_id;
		$result = mysql_query( $sql );
		$row = mysql_fetch_assoc( $result );
		$this->start_date = $row['start_date'];
		$this->parentEntered = array();
	}
	
	public function getTotalPledged( $type, $id ) {
		$total = 0;
		//find pledges per child and if exists for entire "school"/"class"
		switch ($type) {
			case 'school':
				//get total for school
				//get total by adding up class totals per class
				//get total by adding up individual total
				//return greatest number
				$sql = "select lines_pledged as total from lines_pledged where campaign_id = " . $this->campaign_id . " 
						and school_id = $id and class_id = 0 and user_id = 0";
				$result = mysql_query($sql);
				$row = mysql_fetch_assoc($result);
				//echo $row['total'] . "<br />";
				$totals[] = $row['total'];
				
				//get list of classes in school
				/*
				require_once 'class.schoolClasses.php';
				$sc = new SchoolClasses( $id );
				$classIDs = $sc->getClassIDs();
				$amount = 0;
				if (!empty($classIDs)) {
					foreach ($classIDs as $classID) {
						$amount += $this->getTotalPledged( 'class', $classID );
					}
				}
				//echo $amount . "<br />";
				$totals[] = $amount;
				*/
				$sql = "select sum(lines_pledged) as total from lines_pledged where campaign_id = " . $this->campaign_id . " 
						and school_id = $id and class_id > 0 and user_id > 0";
				$result = mysql_query($sql);
				$row = mysql_fetch_assoc($result);
				//echo $row['total'] . "<br />";
				$totals[] = $row['total'];

				//find highest number
				sort( $totals );
				$total = end( $totals );
				break;
			case 'class':
				//get total by adding up class totals
				//get total by adding up individual total
				//return greatest number
				/*
				$sql = "select lines_pledged as total from lines_pledged where campaign_id = " . $this->campaign_id . " 
						and class_id = $id and user_id = 0";
				$result = mysql_query($sql);
				$row = mysql_fetch_assoc($result);
				$total1 = $row['total'];
				 * 
				 */
				$sql = "select sum(lines_pledged) as total from lines_pledged where campaign_id = " . $this->campaign_id . " 
						and class_id = $id and user_id > 0";
				$result = mysql_query($sql);
				$row = mysql_fetch_assoc($result);
				$total2 = $row['total'];
				$total = $total2;
				//$total = $total1 > $total2 ? $total1 : $total2;
				break;
			case 'user':
				$sql = "select lines_pledged from lines_pledged where user_id = " . $id . " and campaign_id = " . $this->campaign_id;
				$result = mysql_query($sql);
				$row = mysql_fetch_assoc($result);
				$total = $row['lines_pledged'];
				break;
		}
		return $total;
	}
	
	public function getTotalLearned( $type, $id ) {
		$total = 0;
		$users = array();
		
		switch ($type) {
			case 'school':
			case 'class':
				$sql = "select sum(lines_learned) as total from lines_learned
						where user_id in (
							select user_id from users where {$type}_id = " . mysql_real_escape_string($id) . "
							and (user_registered > 0 or yan = 1)  
						)
						and campaign_id = " . $this->campaign_id;
				//echo $sql . "<br />";
				$result = mysql_query( $sql );
				if (mysql_num_rows( $result )) {
					$row = mysql_fetch_assoc( $result );
					$total = $row['total'];
				}
				break;
			case 'user':
				$sql = "select lines_learned, mission_sheet_amount from lines_learned where user_id = " . mysql_real_escape_string($id) . " and campaign_id = " . $this->campaign_id;
				$result = mysql_query($sql);
				$row = mysql_fetch_assoc($result);
				$total = $row['lines_learned'];
				// update mission sheet entered amount
				$this->parentEntered[$id][$this->campaign_id] = empty($row['mission_sheet_amount']) ? 0 : $row['mission_sheet_amount'];
				break;
		}
		return $total;
		
		/*
		switch ($type) {
			case 'school':
				
				//get total for school
				//get total by adding up class totals per class
				//get total by adding up individual total
				//return greatest number
				$sql = "select lines_learned as total from lines_learned where campaign_id = " . $this->campaign_id . " 
						and school_id = $id and class_id = 0 and user_id = 0";
				$result = mysql_query($sql);
				
				$row = mysql_fetch_assoc($result);
				//echo $row['total'] . "<br />";
				$totals[] = $row['total'];
				
				//get list of classes in school
				require_once 'class.schoolClasses.php';
				$sc = new SchoolClasses( $id );
				$classIDs = $sc->getClassIDs();
				$amount = 0;
				if (!empty($classIDs)) {
					foreach ($classIDs as $classID) {
						$amount += $this->getTotalLearned( 'class', $classID );
					}
				}
				//echo $amount . "<br />";
				$totals[] = $amount;
				
				$sql = "select sum(lines_learned) as total from lines_learned where campaign_id = " . $this->campaign_id . " 
						and school_id = $id and class_id > 0 and user_id > 0";
				$result = mysql_query($sql);
				$row = mysql_fetch_assoc($result);
				//echo $row['total'] . "<br />";
				$totals[] = $row['total'];
				
				//print_r($totals);

				//find highest number
				sort( $totals );
				$total = end( $totals );
				
				//break;
			case 'class':
				// not doing class amounts anymore
				break;
			
				//get total by adding up class totals
				//get total by adding up individual total
				//return greatest number
				$sql = "select lines_learned as total from lines_learned where campaign_id = " . $this->campaign_id . " 
						and class_id = $id and user_id = 0";
				$result = mysql_query($sql);
				$row = mysql_fetch_assoc($result);
				$total1 = $row['total'];
				$sql = "select sum(lines_learned) as total from lines_learned where campaign_id = " . $this->campaign_id . " 
						and class_id = $id and user_id > 0";
				$result = mysql_query($sql);
				$row = mysql_fetch_assoc($result);
				$total2 = $row['total'];
				$total = $total1 > $total2 ? $total1 : $total2;
				//break;
			case 'user':
				$sql = "select sum(lines_learned) as total, mission_sheet_amount from lines_learned where user_id = " . $id . " and campaign_id = " . $this->campaign_id;
				$result = mysql_query($sql);
				$row = mysql_fetch_assoc($result);
				$total = $row['total'];
				// update mission sheet entered amount
				$this->parentEntered[$id] = empty($row['mission_sheet_amount']) ? 0 : $row['mission_sheet_amount'];
				break;
		}
		*/
	}
	
	public function getParentEntered( $user ) {
		return $this->parentEntered[$user];
	}
	
	abstract public function getUsersTotalLearned( array $users );
	
	static function getInstance( $campaign ) {
		if ($campaign % 2 == 0) {
			require_once 'class.mishna.php';
			return new Mishna( $campaign );
		} else {
			require_once 'class.tanya.php';
			return new Tanya( $campaign );
		}
	}
}
?>