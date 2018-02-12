<?php
class week_two {
	public $user_id;
	public $start_date;
	public $end_date;
	public $title;
	public $campaigns = array();
	public $tasks = array();
	
	function __construct($user_id, $start_date, $end_date) {
		$this->user_id = $user_id;
		$this->start_date = $start_date;
		$this->end_date = $end_date;
		$this->title = jdtogregorian($start_date) . " - " . jdtogregorian($end_date);
	}
	
	public function get_campaigns($todays_date) {
		$missions = array();
		$tasks = array();
		
		// ***** CAMPAIGNS AND MISSIONS ***** //
		$sql = "SELECT cm.camp_campaign_id, cc.campaign_name, cm.camp_mission_id, cm.mission_name ";
		$sql = $sql . "FROM member_tasks AS mt ";
		$sql = $sql . "JOIN camp_tasks AS ct USING (camp_task_id) ";
		$sql = $sql . "JOIN camp_missions AS cm USING (camp_mission_id) ";
		$sql = $sql . "JOIN camp_campaigns AS cc USING (camp_campaign_id) ";
		$sql = $sql . "WHERE mt.task_date >= " . $this->start_date . " ";
		$sql = $sql . "AND mt.task_date <= " . $this->end_date . " ";
		$sql = $sql . "AND mt.user_id=" . $this->user_id . " ";
		$sql = $sql . "GROUP BY cm.camp_campaign_id, cm.camp_mission_id ";
		$sql = $sql . "ORDER BY cm.camp_campaign_id, cm.camp_mission_id ";
		//echo "<input type='hidden' name='SQL 1' value='" . $sql . "'>\n";
		$query = mysql_query($sql);
		$num_rows = mysql_num_rows($query);
		
		$camp_campaign_id = "";
		$row_num = 0;
		while ($row = mysql_fetch_assoc($query)) {
			$row_num++;
			$prev_camp_campaign_id = $row['camp_campaign_id'];
						
			if ($camp_campaign_id != $prev_camp_campaign_id && $camp_campaign_id != "") {	
				$element = compact('camp_campaign_id', 'campaign_name', 'missions');
				array_push($this->campaigns, $element);
				$missions = array();
			}
			
			$camp_mission_id = $row['camp_mission_id'];
			$mission_name = $row['mission_name'];
			$tasks = $this->get_tasks($camp_mission_id, $todays_date);
			$element = compact('camp_mission_id', 'mission_name', 'tasks');
			array_push($missions, $element);
			
			$camp_campaign_id = $prev_camp_campaign_id;
			$campaign_name = $row['campaign_name'];
			
			if ($row_num == $num_rows) {
				$campaign_name = $row['campaign_name'];
				$element = compact('camp_campaign_id', 'campaign_name', 'missions');
				array_push($this->campaigns, $element);			
			}
		}
		// ***** CAMPAIGNS AND MISSIONS ***** //
		
		echo "\n\n";
	}
	
	public function get_tasks($camp_mission_id, $todays_date) {
		$tasks = array();
		
		$sql1 = "SELECT * FROM camp_tasks WHERE camp_mission_id=" . $camp_mission_id;
		$query1 = mysql_query($sql1);
		while ($row1 = mysql_fetch_assoc($query1)) {
			$camp_task_id = $row1['camp_task_id'];
			$task_name = $row1['task_name'];
			
			$found = false;
			$task_dates = array();
			$difference = ($this->end_date - $this->start_date) + 1;
			for ($dno = 0; $dno < ($difference); $dno++) {
				$task_date = $this->start_date + $dno;
				$sql2 = "SELECT completed FROM member_tasks WHERE user_id=" . $this->user_id . " AND camp_task_id=" . $camp_task_id . " AND task_date=" . $task_date;
				$query2 = mysql_query($sql2);
				$num_rows = mysql_num_rows($query2);
				
				if ($num_rows > 0) {
					$found = true;
					$row2 = mysql_fetch_assoc($query2);
					
					if ($task_date > $todays_date) {
						$completed = "2";
					}
					else {
						if ($row2['completed'] == 0)
							$completed = "0";
						else
							$completed = "1";
					}					
					array_push($task_dates, $completed);
				}
				else {
					$completed = "3";
					array_push($task_dates, $completed);				
				}
			}
				
			if ($found) {
				$element = compact('camp_task_id', 'task_name', 'task_dates');
				array_push($tasks, $element);
			}
		}
		
		return $tasks;
	}
	
}
?>