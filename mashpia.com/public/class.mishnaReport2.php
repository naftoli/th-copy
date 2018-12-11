<?
abstract class MishnaReport {
	protected $learned;
	protected $mishnos;
	protected $perokim;
	protected $mesechtos;
	protected $lines;
	protected $mesechtoNames;
	public $he_chars;
	
	public function __construct() {
		$this->mishnos = array();
		$this->perokim = array();
		$this->mesechtos = array();
		$this->lines = 0;
		$this->setMesechtoNames();
		$this->he_chars = array(
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
	
	abstract public function setLearned();
	
	public function setMesechtoNames() {
		$sql = "select * from mesechtos";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$this->mesechtoNames[$row['mesechto_id']] = $row['mesechto'];
		}
	}
	
	protected function setInfo() {
		if (!empty($this->learned)) {
			$user = 0;
			foreach ($this->learned as $id => $info) {
				foreach ($info as $row) {
					$mesechto = $row['mesechto_id'];
					$perek = $row['perek'];
					$mishna = $row['mishna'];
					$lines = $row['lines_learned'];
					$this->lines += $lines; 
					
					if ($user != $id) {
						$this->mesechtos[] = $mesechto;
						$this->perokim[$mesechto][] = $perek;
						$this->mishnos[$mesechto][$perek][] = $mishna;
						$user = $id;
					} else {
						if (!in_array($mesechto, $this->mesechtos)) {
							$this->mesechtos[] = $mesechto;
						}
						if (!isset($this->perokim[$mesechto]) || !in_array($perek, $this->perokim[$mesechto])) {
							$this->perokim[$mesechto][] = $perek;
						}
						if (!isset($this->mishnos[$mesechto][$perek]) || !in_array($mishna, $this->mishnos[$mesechto][$perek])) {
							$this->mishnos[$mesechto][$perek][] = $mishna;
						}
					}
				}
			}
		}		
	}
	
	public function getMesechtos() {
		return $this->mesechtos;
	}
	
	public function getPerokim( $mesechto ) {
		return $this->perokim[$mesechto];
	}
	
	public function getMishnos( $mesechto, $perek ) {
		return $this->mishnos[$mesechto][$perek];
	}
	
	public function getAllMishnos() {
		return $this->mishnos;
	}
	
	public function getLines() {
		return $this->lines;
	}
	
	public function getMesechtoNames() {
		return $this->mesechtoNames;
	}
	
	public static function getInstance( $type ) {
		switch ($type) {
			case 'army':
				require_once 'class.armyMishnaReport.php';
				return new ArmyMishnaReport();
				break;
			case 'base':
				require_once 'class.baseMishnaReport.php';
				return new BaseMishnaReport();
				break;
			case 'platoon';
				require_once 'class.platoonMishnaReport.php';
				return new PlatoonMishnaReport();
				break;
			case 'soldier':
				require_once 'class.soldierMishnaReport.php';
				return new SoldierMishnaReport();
				break;
		}
	}
}
?>