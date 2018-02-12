<?
abstract class MishnaReport {
	public $numMesechtos;
	public $numPerokim;
	public $numMishnos;
	public $numLines;
	public $mesechtos;
	public $perokim;
	public $mishnos;
	public $heChars;
	public $mesechtoNames;
	public $numMesechtosAtOnce;
	public $numPerokimAtOnce;

	public function __construct() {
		$this->numMesechtos = 0;
		$this->numPerokim = 0;
		$this->numMishnos = 0;
		$this->numLines = 0;
		$this->numMesechtosAtOnce = 0;
		$this->numPerokimAtOnce = 0;
		$this->mesechtos = array();
		$this->perokim = array();
		$this->mishnos = array();
		$this->setHeChars();
		$this->setMesechtoNames();
		$this->setSummary();
	}
	
	private function setHeChars() {
		$this->heChars = array(
			1	=>	'א',
			2	=>	'ב',
			3	=>	'ג',
			4	=>	'ד',
			5	=>	'ה',
			6	=>	'ו',
			7	=>	'ז',
			8	=>	'ח',
			9	=>	'ט',
			10	=>	'י',
			11	=>	'יא',
			12	=>	'יב',
			13	=>	'יג',
			14	=>	'יד',
			15	=>	'טו',
			16	=>	'טז',
			17	=>	'יז',
			18	=>	'יח',
			19	=>	'יט',
			20	=>	'כ',
			21	=>	'כא',
			22	=>	'כב',
			23	=>	'כג',
			24	=>	'כד',
			25	=>	'כה',
			26	=>	'כו',
			27	=>	'כז',
			28	=>	'כח',
			29	=>	'כט',
			30	=>	'ל'
		);
	}
	
	private function setMesechtoNames() {
		$sql = "select * from mesechtos";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$this->mesechtoNames[$row['mesechto_id']] = $row['mesechto'];
		}
	}
	
	abstract public function setSummary();
	
	public static function getInstance( $type, $id = 0 ) {
		if ($type == 'army') {
			require_once 'class.armyMishnaReport.php';
			return new ArmyMishnaReport();
			break;
		} else {
			require_once 'class.specificMishnaReport.php';
			switch ($type) {
				case 'base':
					$field = 'school';
					break;
				case 'platoon':
					$field = 'class';
					break;
				case 'soldier':
					$field = 'user';
					break;
			}
			return new SpecificMishnaReport($type, $field, $id);
			break;
		}
	}
}
?>