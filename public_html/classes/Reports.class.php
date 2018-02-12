<?php
class Reports{
	
	function __construct($exceptions = array()){
		
		$this->set_start();
		
		$this->reports = array();
		
		$sql = "SELECT * ";
		$sql .= "FROM reports ";
		$sql .= "WHERE report_type='mission_cover_sheet' AND ";
		$sql .= "visibility != 'none' ";
		$sql .= "AND start_date > " . $this->start . " ";	
		$sql .= "ORDER BY start_date";
		
		$query = mysql_query($sql);
		
		while ($report = mysql_fetch_assoc($query)){
			if (in_array($report['start_date'], $exceptions)) continue;
			array_push($this->reports, $report);
		}
	}
	
	function set_start(){
		$d = unixtojd();
		$day = date("N");
		$end = $d;
		
		switch ($day) {
		    case 1:
		        $end += 6;
		        break;
		    case 2:
		        $end += 5;
		        break;
		    case 3:
		        $end += 4;
		        break;
		    case 4:
		        $end += 3;
		        break;
		    case 5:
		        $end += 2;
		        break;
		    case 6:
		        $end++;
		        break;
		    case 7:
		        break;
		    default:
		        break;
		}
		$this->start = ($end - 34);
	}
	
	function get_report_name($period){
		$info = explode(';', $period);
		$sql = "SELECT report_name FROM reports WHERE start_date=" . $info[0] . " AND end_date=" . $info[1];
		$query = mysql_query($sql);
		$report = mysql_fetch_assoc($query);
		$this->report_name = $report['report_name'];
	}
	
}
?>