<?php

class camp_prize {
	public $prize_id;
	public $global_prize_id;
	public $camp_id;
	public $prize_name;
	public $prize_description;
	public $prize_points;
	public $prize_available;
	public $prize_image_id;
	public $installed;
	
	public function __construct(){
	}

	public function new_camp_prize($row) {
		$this->prize_id = $row['prize_id'];
		$this->global_prize_id = $row['global_prize_id'];
		$this->camp_id = $row['camp_id'];
		$this->prize_name = $row['prize_name'];
		$this->prize_description = $row['prize_description'];
		$this->prize_points = $row['prize_points'];
		$this->prize_available = $row['prize_available'];
		$this->prize_image_id = $row['prize_image_id'];
		$this->installed = $row['installed'];	
	}
	
	public function update_prize($prize_id, $prize_name, $prize_description, $prize_points, $prize_available) {
		$sql = "UPDATE prizes_camp SET prize_name='" . mysql_real_escape_string($prize_name) . "', prize_description='" . mysql_real_escape_string($prize_description) . "', prize_points=" . $prize_points . ", prize_available=" . $prize_available . " WHERE prize_id=" . $prize_id;
		$query = mysql_query($sql);		
		if ($query)
			return true;
		else
			return false;
	}
	
	public function add_new_prize($camp_id, $prize_name, $prize_description, $prize_points, $prize_available, $prize_image_id) {
		$sql = "INSERT INTO prizes_camp SET ";
		$sql = $sql . "camp_id=" . $camp_id . ", ";
		$sql = $sql . "prize_name='" . mysql_real_escape_string($prize_name) . "', ";
		$sql = $sql . "prize_description='" . mysql_real_escape_string($prize_description) . "', ";
		$sql = $sql . "prize_points=" . $prize_points . ", ";
		$sql = $sql . "prize_available='" . $prize_available . ", ";
		$sql = $sql . "prize_image_id=" . $prize_image_id;
		$query = mysql_query($sql);		
		if ($query)
			return true;
		else
			return mysql_error();
	}
	
	public function install_global_prize($camp_id, $prize_id) {
		$sql = "SELECT prize_id, prize_name, prize_description, prize_points, prize_image_id FROM global_prizes WHERE prize_id=" . $prize_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
	
		$insert = "INSERT INTO prizes_camp SET global_prize_id=" . $prize_id . ", camp_id=" . $camp_id . ", prize_name='" . mysql_real_escape_string($row['prize_name']) . "', prize_description='" .  mysql_real_escape_string($row['prize_description']) . "', prize_points=" . $row['prize_points'] . ", prize_image_id=" . $row['prize_image_id'] . ", prize_available=0";
		$insert_query = mysql_query($insert);
		if ($insert_query) 
			return true;
		else
			return false;
	}
	
	public function delete_prize($prize_id) {
		$sql = "DELETE FROM prizes_camp WHERE prize_id=" . $prize_id;
		$query = mysql_query($sql);
		if ($insert_query) 
			return true;
		else
			return false;
	}
}

?>