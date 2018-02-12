<?php
class BpSummary {
	private $campaign;
	private $type;
	
	public function __construct( $campaign, $type ) {
		$this->campaign = mysql_real_escape_string($campaign);
		$this->type = mysql_real_escape_string($type);
	}
	
	public function updateSummary( $id ) {
		if ($this->campaign % 2 != 0) {
			require_once 'class.tanya.php';
			$bp = new Tanya( $this->campaign );
		} else {
			require_once 'class.mishna.php';
			$bp = new Mishna( $this->campaign );
		}
		
		// if updating as school or class, find users in school or class and update each one
		$users = array();
		switch ($this->type) {
			case 'school':
			case 'class':
				$sql = "select user_id from users where {$this->type}_id = " . mysql_real_escape_string($id);
				$result = mysql_query($sql);
				while ($row = mysql_fetch_assoc($result)) {
					$users[] = $row['user_id'];
				}
				break;
			case 'user':
				$users[] = $id;
		}
		
		$success = true;
		foreach ($users as $user_id) {
			$lines = $bp->getTotalLearned( 'user', $user_id );
			if ($lines == '') $lines = 0;
			$sql = "insert ignore into bp_user_summary 
					set campaign_id = " . $this->campaign . ", 
					user_id = " . mysql_real_escape_string($user_id) . ", 
					num_lines = " . mysql_real_escape_string($lines) . "   
					on duplicate key update num_lines = " . mysql_real_escape_string($lines);
			if (! mysql_query($sql)) {
				$success = false;
			}
		}
		return $success;
	}
	
	public function getSummary( $id ) {
		$total = 0;	
		switch ($this->type) {
			case 'school':
			case 'class':
				$sql = "select sum(num_lines) as total from bp_user_summary
						join users using (user_id) 
						where user_id in (
							select user_id from users
							where {$this->type}_id = " . mysql_real_escape_string($id) . "
						) 
						and campaign_id = " . $this->campaign;
				//echo $sql;
				$result = mysql_query($sql);
				if (mysql_num_rows($result) > 0) {
					$row = mysql_fetch_assoc($result);
					$total = $row['total'];
				}
				break;
			case 'user':
				$sql = "select num_lines from bp_user_summary  
						where campaign_id = " . $this->campaign . " 
						and {$this->type}_id = " . mysql_real_escape_string($id);
				//echo $sql . "<br />";
				$result = mysql_query($sql);
				if (mysql_num_rows($result) > 0) {
					$row = mysql_fetch_assoc($result);
					$total = $row['num_lines'];
				}
				break;
		}
		return $total;
		
		// not doing it this way
		// find greatest number between school summary, class summary, and user summary
		/*
		switch ($this->type) {
			case 'school':
				// check class and user summary
				$sql = "select sum(num_lines) as total from bp_class_summary
						join classes using (class_id) 
						where campaign_id = " . $this->campaign . " 
						and {$this->type}_id = " . mysql_real_escape_string($id);
				//echo $sql;
				$result = mysql_query($sql);
				if (mysql_num_rows($result) > 0) {
					$row = mysql_fetch_assoc($result);
					if ($row['total'] > $total) {
						$total = $row['total'];
					}
				}
				
				$sql = "select sum(num_lines) as total from bp_user_summary
						join users using (user_id) 
						where campaign_id = " . $this->campaign . " 
						and {$this->type}_id = " . mysql_real_escape_string($id);
				//echo $sql;
				$result = mysql_query($sql);
				if (mysql_num_rows($result) > 0) {
					$row = mysql_fetch_assoc($result);
					if ($row['total'] > $total) {
						$total = $row['total'];
					}
				}
				break;
			case 'class':
				break;
			case 'user':
				break;
		}
		*/
	}
}
?>