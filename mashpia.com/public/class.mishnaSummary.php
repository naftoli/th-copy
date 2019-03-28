<?php
require_once 'class.bpSummary.php';

class MishnaSummary {
	
	public static function updateSummary( $user_id ) {
		$learned = array();
		$sql = "select * from mishna_learned where user_id = " . mysql_real_escape_string($user_id);
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$mesechto = $row['mesechto_id'];
			$perek = $row['perek'];
			$mishna = $row['mishna'];
			$lines = $row['lines_learned'];
			$learned[$mesechto][$perek][$mishna] = $lines;
		}
		
		//echo "<pre>";
		//print_r($learned);
		//echo "</pre>";
			
		$mesechtosLearned = array();
		$perokimLearned = array();
		$mishnosLearned = array();	
		foreach ($learned as $mesechto => $info) {
			$mesechtosLearned[] = $mesechto;
			$numPerokimPerMesechto[$mesechto] = 0;
			$numMishnosPerMesechto[$mesechto] = 0;
			$numLinesPerMesechto[$mesechto] = 0;
			
			foreach ($info as $perek => $other) {
				$perokimLearned[$mesechto][] = $perek;
				$numPerokimPerMesechto[$mesechto]++;
				$numMishnosPerPerek[$mesechto][$perek] = 0;
				$numLinesPerPerek[$mesechto][$perek] = 0;
				
				foreach ($other as $mishna => $lines) {
					$mishnosLearned[$mesechto][$perek][] = $mishna;
					$numMishnosPerMesechto[$mesechto]++;
					$numMishnosPerPerek[$mesechto][$perek]++;
					$numLinesPerMesechto[$mesechto] += $lines;
					$numLinesPerPerek[$mesechto][$perek] += $lines;
				}
			}
		}
		
		foreach ($mesechtosLearned as $mesechto) {
			$sql = "select * from mesechtos_summary where mesechto_id = " . $mesechto;
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
			$perokim = $row['total_perokim'];
			$mishnos = $row['total_mishnos'];
			$lines = $row['total_lines'];
			
			//echo "Perokim: " . $perokim . ", Learned: " . $numPerokimPerMesechto[$mesechto] . "<br />";
			//echo "Mishnos: " . $mishnos . ", Learned: " . $numMishnosPerMesechto[$mesechto] . "<br />";
			//echo "Lines: " . $lines . ", Learned: " . $numLinesPerMesechto[$mesechto] . "<br />";
			
			if ($perokim == $numPerokimPerMesechto[$mesechto] 
				&& $mishnos == $numMishnosPerMesechto[$mesechto] 
				&& $lines == $numLinesPerMesechto[$mesechto]) {
				
				$sql = "insert ignore into mesechtos_learned set mesechto_id = " . $mesechto . ", user_id = " . $user_id;
				//echo $sql;
				mysql_query($sql);
			}
				
			foreach ($perokimLearned[$mesechto] as $perek) {
				$sql = "select * from perokim_summary where mesechto_id = " . $mesechto . " and perek = " . $perek;
				$result = mysql_query($sql);
				$row = mysql_fetch_assoc($result);
				$mishnos = $row['total_mishnos'];
				$lines = $row['total_lines'];
				
				if ($mishnos == $numMishnosPerPerek[$mesechto][$perek] 
					&& $lines == $numLinesPerPerek[$mesechto][$perek]) {
						
					$sql = "insert ignore into perokim_learned 
							set mesechto_id = " . $mesechto . ", 
							perek = " . $perek . ", 
							user_id = " . $user_id;
					mysql_query($sql);							
				}
			}
		}
		
		// update yud alef nissan tbp
		$campaign_id = 10;
		$sql = "select sum(lines_learned) as total from mishna_learned where user_id = " . $user_id;
		$result = mysql_query($sql);
		if (mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			$lines = $row['total'];
			$sql = "select lines_learned from lines_learned where user_id = " . $user_id . " and campaign_id = " . $campaign_id;
			$result = mysql_query($sql);
			if (mysql_num_rows($result) > 0) {
				$row = mysql_fetch_assoc($result);
				$learned = $row['lines_learned'];
				if ($lines > $learned) {
					// update lines learned
					$sql = "update lines_learned set lines_learned = " . $lines . " where user_id = " . $user_id . " and campaign_id = " . $campaign_id;
					mysql_query($sql);
				}
			} else {
				// get users school id
				$sql = "select school_id from users where user_id = " . $user_id;
				$result = mysql_query($sql);
				$row = mysql_fetch_assoc($result);
				$school = $row['school_id'];
				
				// insert into lines learned
				$sql = "insert into lines_learned
						set user_id = " . $user_id . ",
						campaign_id = " . $campaign_id . ",
						lines_learned = " . $lines . ",
						school_id = " . $school;
				mysql_query($sql);
			}
			// update summary table
			$bps = new BpSummary( $campaign_id, 'user' );
			$bps->updateSummary( $user_id );
		}
	}
}
?>