<?
class MissionSheet {
    private $weeks;
	
    public function __construct() {
        $this->weeks = array();
    }
    
    public function marked( $user, $week, $start = 0, $end = 0 ) {
        //find out start and end dates of week
        if ( $week ) {
        	if ( !array_key_exists( $week, $this->weeks ) ) {
		        $sql = "select start_date, end_date from reports where report_id = " . $week;
		        $result = mysql_query( $sql );
		        $row = mysql_fetch_assoc( $result );
		        $start = $row['start_date'];
		        $end = $row['end_date'];
				$this->weeks[$week]['start'] = $start;
				$this->weeks[$week]['end'] = $end;
			} else {
				$start = $this->weeks[$week]['start'];
				$end = $this->weeks[$week]['end'];
			}
		}
		//print_r($this->weeks); exit;
        
        //only find out whether tasks were accomplished for past weeks up to and including current week
        $today = unixtojd();
        if ( $today >= $end || ( $today < $end && $today >= $start ) ) {        
            
//            //get list of accomplished task ids for this user
//            $sql = "select date_task_id 
//                    from date_tasks_marks dtmm 
//                    join date_tasks dt using (date_task_id) 
//                    join date_tasks_missions dtm using (date_tasks_mission_id) 
//                    where user_id = $user 
//                    and dtm.start_date >= $start 
//                    and dtm.end_date <= $end
//					and dtmm.done_qty >= 1";
//			//echo $sql . "<br />"; return;
//			
//            $result = mysql_query( $sql ) or die( mysql_error() );            
//            //if there are any done tasks, return true, otherwise return false        
//            if ( mysql_num_rows( $result ) )
//                return true; 
//            else 
//                return false;
			
			 // check if there is any marks during the week period
			$sql = "select dtmarks.date_task_id, count(*) as 'total', dtmarks.done_qty, dt.needed, dt.quantity from user_tracks ut "
				."join date_tasks_missions dtm on ut.level = dtm.level and ut.track_id = dtm.track_id and ut.subject_id = dtm.subject_id "
				."join date_tasks dt using (date_tasks_mission_id) join date_tasks_marks dtmarks using (date_task_id) "
				."where dtmarks.user_id = ".$user." and ut.user_id = ".$user." "
				."and dtmarks.mark_date >= $start and dtmarks.mark_date <= $end "
				."group by dtmarks.date_task_id";
				
			//if($this->user_id == 50628) echo $sql."<br/><br/>";
	
			$query = mysql_query($sql);
			while ($row = mysql_fetch_assoc($query)){
				if ($row['total'] >= 1 && // if the amount of rows is equal to what is needed (covers daily tasks)
					($row['quantity'] ? $row['done_qty'] >= $row['quantity'] : true)){ // make sure that the quanity is good (covers non daily tasks)
					return true; // we have a vaid task for the week
				}
			}
        }
		return false;
    }
}
?>