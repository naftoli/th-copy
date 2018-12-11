<?php
class user_tls {
	var $track_id;
	var $level;
	var $school_type_id;
	var $cutoff_date;
	var $greg_cutoff_date;
	
	function user_tls ($track_id, $level, $school_type_id, $cutoff_date) {
		$this->track_id = $track_id;
		$this->level = $level;
		$this->school_type_id = $school_type_id;
		$this->cutoff_date = $cutoff_date;
		$this->greg_cutoff_date = $this->gregorian_date($cutoff_date);
	}
	
	function gregorian_date($date) {
		$dates = cal_from_jd($date, CAL_GREGORIAN);
		return $dates['date'];
	}	
}
?>