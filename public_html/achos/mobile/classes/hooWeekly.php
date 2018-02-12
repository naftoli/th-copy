<?php
class HooWeekly {
    private $student;
    private $debug;
    private $parsha;
	private $isFC;
    
    public function __construct( $id, $parsha ) {
        $this->student = $id;
        $this->parsha = $parsha;
        $this->debug = false;
		$this->isFC = false;
		$this->checkFC();
    }
	
	private function checkFC() {
		$sql = "select subject_id from user_tracks where user_id = " . $this->student;
		$result = mysql_query( $sql );
		$row = mysql_fetch_assoc( $result );
		if ($row['subject_id'] == 3) $this->isFC = true;
	}
    
    public function setDebug( $bool ) {
		$this->debug = $bool;
	}
    
    public function calcPoints() {
        $points = array();
        $sql = "select dtm.mark_date, dtm.done_qty as total
                from date_tasks_marks dtm 
                join date_tasks dt using (date_task_id)
                join date_tasks_missions dtmm using (date_tasks_mission_id) 
                where dtm.done_qty >= 60 
                and dtm.user_id = " . $this->student . "
				and dt.label_id = 17 
                and dtmm.start_date = " . $this->parsha['start'] . "
                and dtmm.end_date = " . $this->parsha['end'];
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $points[$row['mark_date']] = floor($row['total'] / 60);
			if ($this-isFC) {
				$points[$row['mark_date']] *= 10;
			}
        }
        return $points;
    }
	
	public function calcMinutes() {
        $minutes = array();
		$sql = "select dtm.mark_date, dtm.done_qty as total
                from date_tasks_marks dtm 
                join date_tasks dt using (date_task_id)
                join date_tasks_missions dtmm using (date_tasks_mission_id) 
                where dtm.user_id = " . $this->student . "
				and dt.label_id = 17 
                and dtmm.start_date = " . $this->parsha['start'] . "
                and dtmm.end_date = " . $this->parsha['end'];
		$result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $minutes[$row['mark_date']] = floor($row['total']);
        }
        return $minutes;
	}
}
?>