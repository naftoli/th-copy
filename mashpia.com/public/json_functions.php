<?
session_start();

require_once 'db.php';

$function = $_GET['function'];
echo $function();

function update_student_sefer_hamitzvos(){

	include('connection.class.php');
	$Connection = new Connection();
	
	$done = $_GET['done'];
	
	$users = explode(':', $_GET['users']);
	foreach($users as $user_id){
	
		$missions = explode(':', $_GET['missions']);
		foreach($missions as $mission){
		
			$mission_numbers = explode('-', $mission);
			
			if (count($mission_numbers) == 1){
			
				if ($done == 'true'){
					$sql = "SELECT id FROM user_sefer_hamitzvos WHERE user_id=" . $user_id . " AND mission=" . $mission_numbers[0];
					$query = $Connection->query($sql);
					$id = mysql_fetch_assoc($query);
					if (!$id)
						$sql = "INSERT INTO user_sefer_hamitzvos (user_id, mission, date) VALUES (" . $user_id . ", " . $mission_numbers[0] . ", '" . date('Y-m-d') . "')";
						$query = $Connection->query($sql);
				}
				else{
					$sql = "DELETE FROM user_sefer_hamitzvos WHERE user_id=" . $user_id . " AND mission=" . $mission_numbers[0];
					$query = $Connection->query($sql);
				}
				
			}
			else{
			
				if ($done == 'true'){
					$start = $mission_numbers[0];
					$end = $mission_numbers[1];
					for ($mno = $start; $mno <= $end; $mno++){
						$sql = "SELECT id FROM user_sefer_hamitzvos WHERE user_id=" . $user_id . " AND mission=" . $mno;
						$query = $Connection->query($sql);
						$id = mysql_fetch_assoc($query);
						if (!$id){
							$sql = "INSERT INTO user_sefer_hamitzvos (user_id, mission, date) VALUES (" . $user_id . ", " . $mno . ", '" . date('Y-m-d') . "')";
							$query = $Connection->query($sql);
						}
					
					}
				}
				else{
					$start = $mission_numbers[0];
					$end = $mission_numbers[1];
					for ($mno = $start; $mno <= $end; $mno++){
						$sql = "DELETE FROM user_sefer_hamitzvos WHERE user_id=" . $user_id . " AND mission=" . $mno;
						$query = $Connection->query($sql);					
					}
				}
				
			}
			
		}
		
		//update medals / ranks
		require_once 'class.newSubjectsUpdater.php';
        $nsu = new NewSubjectsUpdater( 21 );
        $nsu->updateMedals( $user_id );
        $nsu->updateRanks( $user_id );
	}

}
?>