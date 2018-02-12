<?
require_once 'class.balPehCampaign.php';

class Mishna extends BalPehCampaign {
	
	public function __construct( $campaign ) {
		parent::__construct( $campaign );
	}
	
	public function getUsersTotalLearned( array $users ) {
		/*
		$in = implode(',', $users);	
		$sql = "select sum(total) t from 
				( select max(dtmm.done_qty) as total from date_tasks_marks dtmm
					join date_tasks dt using (date_task_id) 
					join date_tasks_missions dtm using (date_tasks_mission_id) 
					join users u using (user_id) 
					where u.user_id in ($in)   
					and dtm.subject_id = 16 
					and dtm.start_date >= $this->start_date  
					and dt.quantity > 1 
					and dt.name = \"I learned Mishnayus Baal Peh for the Rebbe's Birthday. Enter the amount of lines that you know by heart.\" 
					group by user_id 
				) as totalSum";
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		return isset($row['t']) ? $row['t'] : 0 ;
		 * 
		 */
		$total = 0;
		foreach ($users as $user) {
			/*
			$sql = "select sum(total) t from 
					( select max(dtmm.done_qty) as total from date_tasks_marks dtmm
						join date_tasks dt using (date_task_id) 
						join date_tasks_missions dtm using (date_tasks_mission_id) 
						join users u using (user_id) 
						where u.user_id = $user    
						and dtm.subject_id = 16 
						and dtm.start_date >= $this->start_date  
						and dt.quantity is not null 
						and dt.short_name = \"Mishnayos for the Rebbe\"  
					) as totalSum";
			//echo $sql;
			*/
			$sql = "select lines_learned as t from lines_learned where campaign_id = " . $this->campaign_id . " and user_id = " . $user;
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
			$total += isset($row['t']) ? $row['t'] : 0 ;
		}
		return $total;
	}
}
?>