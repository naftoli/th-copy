<?
class specificMishnaReport extends MishnaReport {
	protected $type;
	protected $field;
	protected $id;
	protected $points;
	
	public function __construct( $type, $field, $id ) {
		$this->type = $type;
		$this->field = $field;
		$this->id = $id;
		$this->points = 0;
		parent::__construct();
	}
	
	public function setSummary() {
		$sql = "select count(mesechto_id) as total 
				from mesechtos_learned 
				join users u using (user_id) 
				where u." . $this->field . "_id = " . $this->id;
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$this->numMesechtos = $row['total'] ? $row['total'] : '';
		
		$sql = "select count(perek) as total 
				from perokim_learned 
				join users u using (user_id) 
				where u." . $this->field . "_id = " . $this->id;
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$this->numPerokim = $row['total'] ? $row['total'] : '';
		
		$sql = "select count(mishna) as total_mishna, sum(lines_learned) as total_lines 
				from mishna_learned 
				join users u using (user_id) 
				where u." . $this->field . "_id = " . $this->id;
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$this->numMishnos = $row['total_mishna'] ? $row['total_mishna'] : '';
		$this->numLines	= $row['total_lines'] ? $row['total_lines'] : '';
		
		//find out number of mesechtos at once and perokim at one
		$sql = "select count(*) as total from mishna_at_once 
				join users u using (user_id) 
				where u." . $this->field . "_id = " . $this->id . " 
				and perek = 0";
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$this->numMesechtosAtOnce = $row['total'] ? $row['total'] : '';
		
		$sql = "select count(*) as total from mishna_at_once 
				join users u using (user_id) 
				where u." . $this->field . "_id = " . $this->id . " 
				and perek > 0";
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$this->numPerokimAtOnce = $row['total'] ? $row['total'] : '';
		
		//find out actual mesechtos, perokim, mishnos learned
		if ($this->type == 'soldier') {
			$this->setInfo();
			$this->setPoints();
		}
	}

	protected function setInfo() {
		$sql = "select distinct mesechto_id from mishna_learned 
				join users u using (user_id) 
				where u." . $this->field . "_id = " . $this->id . " 
				order by mesechto_id";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$this->mesechtos[] = $row['mesechto_id'];
		}
		
		if (!empty($this->mesechtos)) {
			foreach ($this->mesechtos as $mesechto) {
				$sql = "select distinct perek from mishna_learned 
						join users u using (user_id) 
						where u." . $this->field . "_id = " . $this->id . " 
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
								where u." . $this->field . "_id = " . $this->id . " 
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

	protected function setPoints() {
		$sql = "select sum(points) as points from bp_points where bp_type_id = 1 and user_id = " . $this->id;
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$this->points = $row['points'];
	}
	
	public function getPoints() {
		return $this->points;
	}
}
?>