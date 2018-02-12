<?
class PlatoonMishnaReport extends MishnaReport {
	private $grade;

	public function __construct( $grade ) {
		$this->grade = $grade;
		parent::__construct();
	}
	
	public function setSummary() {
		$sql = "select count(mesechto_id) as total 
				from mesechtos_learned 
				join users u using (user_id) 
				where u.class_id = " . $this->grade;
		$result = mysql_query($sql);
		if (mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			$this->numMesechtos = $row['total'];
		}
		
		$sql = "select count(perek) as total 
				from perokim_learned 
				join users u using (user_id) 
				where u.class_id = " . $this->grade;
		$result = mysql_query($sql);
		if (mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			$this->numPerokim = $row['total'];
		}
		
		$sql = "select count(mishna) as total_mishna, sum(lines_learned) as total_lines 
				from mishna_learned 
				join users u using (user_id) 
				where u.class_id = " . $this->grade;
		$result = mysql_query($sql);
		if (mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			$this->numMishnos = $row['total_mishna'];
			$this->numLines	= $row['total_lines'];
		}
		
		//find out actual mesechtos, perokim, mishnos learned
		$this->setInfo();
	}
	
	private function setInfo() {
		$sql = "select distinct mesechto_id from mishna_learned 
				join users u using (user_id) 
				where u.class_id = " . $this->grade . " 
				order by mesechto_id";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$this->mesechtos[] = $row['mesechto_id'];
		}
		
		if (!empty($this->mesechtos)) {
			foreach ($this->mesechtos as $mesechto) {
				$sql = "select distinct perek from mishna_learned 
						join users u using (user_id) 
						where u.class_id = " . $this->grade . " 
						and mesechto_id = $mesechto 
						order by perek";
				$result = mysql_query($sql);
				while ($row = mysql_fetch_assoc($result)) {
					$this->perokim[$mesechto][] = $row['perek'];
				}
			}
			
			if (!empty($this->perokim)) {
				foreach ($this->perokim as $mesechto => $other) {
					foreach ($other as $perek) {
						$sql = "select distinct mishna from mishna_learned 
								join users u using (user_id) 
								where u.class_id = " . $this->grade . " 
								and mesechto_id = $mesechto 
								and perek = $perek 
								order by mishna";
						$result = mysql_query($sql);
						while ($row = mysql_fetch_assoc($result)) {
							$this->mishnos[$mesechto][$perek][] = $row['mishna'];
						}
					}
				}
			}
		}
	}
}
?>