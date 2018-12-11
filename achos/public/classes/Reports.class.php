<?php
class Reports{
	
	function __construct(){
		
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
			array_push($this->reports, $report);
		}
	}
	
	function set_start(){
		$d = unixtojd();
		$day = date("N");
		$this->start = $d;

		switch ($day) {
			case 1:
				$this->start -= $day;
				break;
			case 2:
				$this->start += 5;
				break;
			case 3:
				$this->start += 4;
				break;
			case 4:
				$this->start += 3;
				break;
			case 5:
				$this->start += 2;
				break;
			case 6:
				$this->start++;
				break;
			case 7:
				break;
			default:
				break;
		}
		
		$this->start -= 14;	
		//$this->start = 2456452; //beg of summer 2013
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