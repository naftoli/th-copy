<?
class MishnaInfo {	
	public static function getSedorim() {
		$sql = "select * from sedorim";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$sedorim[$row['seder_id']] = $row['seder'];
		}
		return $sedorim;
	}
	
	public static function getMesechtos( $seder ) {
		$sql = "select * from mesechtos where seder_id = " . mysql_real_escape_string($seder) . " order by mesechto_id";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$mesechtos[$row['mesechto_id']] = $row['mesechto'];
		}
		return $mesechtos;
	}
	
	public static function getMesechtosByList() {
		$sql = "select * from mesechtos order by mesechto";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$mesechtos[$row['mesechto_id']] = $row['mesechto'];
		}
		return $mesechtos;
	}
	
	public static function getPerokim( $mesechto ) {
		$sql = "select distinct perek from mishnos where mesechto_id = " . mysql_real_escape_string($mesechto);
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$perokim[] = $row['perek'];
		}
		return $perokim;
	}
	
	public static function getMishnos( $mesechto, $perek ) {
		$sql = "select * from mishnos where mesechto_id = " . mysql_real_escape_string($mesechto) . " 
				and perek = " . mysql_real_escape_string($perek);
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$mishnos[$row['mishna']] = $row['num_lines'];
		}
		return $mishnos;
	}

	public static function getLearned( $mesechto, $perek, $user ) {
		$learned = array();
		$sql = "select * from mishna_learned 
				where mesechto_id = " . mysql_real_escape_string($mesechto) . " 
				and perek = " . mysql_real_escape_string($perek) . " 
				and user_id = " . mysql_real_escape_string($user);
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$learned[$row['mishna']] = $row['lines_learned']; 
		}
		return $learned;
	}
	
	public static function getAssigned( $seder, $school_id, $class_id, $user = 0 ) {
		//if there is a user id then get assigned both for class and user
		$mesechtos = array();
		if ($user) {
			$sql = "select * from mishna_assigned 
					where seder_id = $seder 
					and school_id = $school_id 
					and class_id = $class_id 
					and user_id = $user";
			$result = mysql_query( $sql );
			while ($row = mysql_fetch_assoc($result)) {
				$mesechtos[] = $row['mesechto_id'];
			}
		}
		
		$sql = "select * from mishna_assigned 
				where seder_id = $seder 
				and school_id = $school_id 
				and class_id = $class_id
				and user_id = 0";
		//echo $sql;
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			if (!in_array($row['mesechto_id'], $mesechtos))
				$mesechtos[] = $row['mesechto_id'];
		}
		return $mesechtos;
	}
	
	public static function getAssignedAll( $school_id, $class_id, $user = 0, $withNames = false ) {
		//if there is a user id then get assigned both for class and user
		$mesechtos = array();
		$sedorim = array(1,2,3,4,5,6);
		
		foreach ($sedorim as $seder) {
			if ($user) {
				//need to check both for individual and class
				$sql = "select * from mishna_assigned 
						where school_id = $school_id 
						and class_id = $class_id 
						and user_id = $user   
						and seder_id = $seder";
				$result = mysql_query( $sql );
				while ($row = mysql_fetch_assoc($result)) {
					$mesechtos[] = $row['mesechto_id'];
				}
			}
			$sql = "select * from mishna_assigned 
						where school_id = $school_id 
						and class_id = $class_id 
						and seder_id = $seder
						and user_id = 0";
			//echo $sql;
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				if (!in_array($row['mesechto_id'], $mesechtos))
					$mesechtos[] = $row['mesechto_id'];
			}
		} 
		//if we need names of mesechtos
		if (!empty($mesechtos) && $withNames) {
			$info = array();
			$sql = "select * from mesechtos where mesechto_id in (" . implode(',', $mesechtos) . ") order by mesechto";
			$result = mysql_query( $sql );
			while ($row = mysql_fetch_assoc($result)) {
				$info[$row['mesechto_id']] = $row['mesechto'];
			}
			$mesechtos = $info;
		} 
		return $mesechtos;
	}
}
?>