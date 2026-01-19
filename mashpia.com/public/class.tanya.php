<?php
require_once 'class.balPehCampaign.php';

class Tanya extends BalPehCampaign {
	
	public function __construct( $campaign = 0, $type = 'Tanya' ) {
		if ($campaign > 0) {
			parent::__construct( $campaign );
		} else {
			$sql = "SELECT id FROM line_campaigns WHERE type = '" . $type . "' ORDER BY id DESC LIMIT 1";
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
			$campaign = $row['id'];
			parent::__construct( $campaign );
		}
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
					and dtm.subject_id = 27 
					and dtm.start_date >= $this->start_date  
					and dt.quantity > 1 
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
						and dtm.subject_id = 27 
						and dtm.start_date >= $this->start_date  
						and dt.quantity is not null 
						and dt.grid_id = 54   
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

	public function getTotalLearnedForDuch( $user ) {
		global $MASHPIA_DB;
		$sql = "SELECT IFNULL(SUM(mission_sheet_amount), 0) as total FROM lines_learned WHERE user_id = :user_id and campaign_id = :campaign_id";
		$stmt = $MASHPIA_DB->prepare($sql);
		$stmt->execute([
			'user_id' => $user,
			'campaign_id' => $this->campaign_id
		]);	
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return $row['total'];
    }
}