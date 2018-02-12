<?php
class Hoo {
    private $student;
    private $debug;
    private $start;
	private $isFC;
    
    public function __construct( $id ) {
        $this->student = $id;
        $this->debug = false;
		// $this->start = 2457691; // October 29 2016
        $this->start = 2458007; // September 10 2017
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
    
    public function calcPoints($start = 0, $end = 0) {
		/*
        $sql = "select sum(dtm.done_qty) as total, dt.points 
                from date_tasks_marks dtm 
                join date_tasks dt using (date_task_id)
                join date_tasks_missions dtmm using (date_tasks_mission_id) 
                where dtm.done_qty >= 60 
                and dtm.user_id = " . $this->student;
		*/
		$total = 0;
		$sql = "select dt.name, dt.quantity, sum(dtm.done_qty) as total   
                from date_tasks_marks dtm 
                join date_tasks dt using (date_task_id)
                join date_tasks_missions dtmm using (date_tasks_mission_id) 
                where dtm.user_id = " . $this->student;
		if ($start && $end) {
			$sql .= " and dtmm.start_date >= " . $start . " and dtmm.end_date <= " . $end;
		} else {
			$sql .= " and dtmm.start_date >= " . $this->start;
		}
		$sql .= " group by dt.name";
        echo "<input type='hidden' name='sql' value=\"" . $sql . "\" />";
		//echo $sql; exit;
        $result = mysql_query($sql);
        if (mysql_num_rows($result)) {
            while ($row = mysql_fetch_assoc($result)) {
				if ($row['quantity'] > 0) {
					$points = floor($row['total'] / 60);
					if ($this-isFC) {
						$points *= 10;
					}
				} else {
					$points = $row['total'];
				}
				$total += $points;
			}
        }
		return $total;
    }
    
    public function calcVisits($start = 0, $end = 0) {
        $sql = "select count(*) as total
                from date_tasks_marks dtm 
                join date_tasks dt using (date_task_id)
                join date_tasks_missions dtmm using (date_tasks_mission_id) 
                where dtm.done_qty >= 60
				and dt.label_id = 17 
                and dtm.user_id = " . $this->student;
		if ($start && $end) {
			$sql .= " and dtmm.start_date >= " . $start . " and dtmm.end_date <= " . $end;
		} else {
			$sql .= " and dtmm.start_date >= " . $this->start;
		}
        $result = mysql_query($sql);
        if (mysql_num_rows($result)) {
            $row = mysql_fetch_assoc($result);
            return $row['total'];
        } else {
            return 0;
        }
    }
	
	public function calcMinutes($start = 0, $end = 0) {
		$sql = "select sum(dtm.done_qty) as total
                from date_tasks_marks dtm 
                join date_tasks dt using (date_task_id)
                join date_tasks_missions dtmm using (date_tasks_mission_id) 
                where dtm.user_id = " . $this->student . "
				and dt.label_id = 17";
        if ($start && $end) {
			$sql .= " and dtmm.start_date >= " . $start . " and dtmm.end_date <= " . $end;
		} else {
			$sql .= " and dtmm.start_date >= " . $this->start;
		}
		$result = mysql_query($sql);
        if (mysql_num_rows($result)) {
            $row = mysql_fetch_assoc($result);
            $points = floor($row['total']);
            return $points;
        } else {
            return 0;
        }
	}
}
?>