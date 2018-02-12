<?
class EditTasks {
	private $id;
	private $type;
	private $tasks;
	
	public function __construct($id = 0, $type = 'school') {
		$this->type = $type;
		$this->id = $id;
		$this->tasks = array();
	}
	
	private function setTasks() {
		if ($this->type == 'school') {
			if ($this->id > 0) {
				$where = "where dtm.created_by_school = " . $this->id;
			} else {
				$where = "where dtm.created_by_school is not null";
			}
			$sql = "select dt.name, su.subject_name, dtm.created_by_school, s.school_name from date_tasks_missions dtm 
					join date_tasks dt using (date_tasks_mission_id) 
					join schools s on (s.school_id = dtm.created_by_school) 
					join subjects su using (subject_id) 
					$where  
					group by dt.name 
					order by s.school_name, su.subject_id, dt.name";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$school = $row['created_by_school'] . ":" . $row['school_name'];
				$this->tasks[$school][$row['subject_name']][] = $row['name'];
			}
			//asort($this->tasks);
		} else if ($this->type == 'parent') {
			require_once 'class.defaults.php';
			$sql = "SELECT * FROM admins WHERE admin_id = " . $this->id;
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);
			$admin = new admin($row);
			$admin->get_children();
			if (!empty($admin->children)) {
				$sql = "select dt.date_task_id, dt.name, s.subject_name from date_tasks dt 
						join date_tasks_missions dtm using (date_tasks_mission_id) 
						join subjects s using (subject_id) 
						where default_on = 0 
						order by s.subject_name, dt.name";
				$result = mysql_query($sql);
				foreach ($admin->children as $child) {
					$this->d = new Defaults($child->user_id);
					while ($row = mysql_fetch_assoc($result)) {
						if ($this->d->isOn($row['date_task_id'], 'task')) {
							if (!in_array($row['name'], $this->tasks)) {
								$this->tasks[$row['subject_name']][] = $row['name'];
							}
						}
					}
				}
			}
		}
	}
	
	public function getTasks() {
		if (empty($this->tasks)) {
			$this->setTasks();
		}
		return $this->tasks;
	}
	
	public function delete($task) {
		if ($this->type == 'school') {
			$sql = "delete dt.* from date_tasks dt 
					join date_tasks_missions dtm using (date_tasks_mission_id) 
					where dt.name = '" . mysql_real_escape_string($task) . "' 
					and dtm.created_by_school = " . $this->id;
		} else if ($this->type == 'parent') {
			$sql = "delete from date_tasks 
					where name = '" . mysql_real_escape_string($task) . "' 
					and cat like 'My Personal Task%' 
					and default_on = 0";
		}
		if (mysql_query($sql)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function edit($old, $new) {
		//we aren't escaping the $old var b/c it has already been escaped in the view
		if ($this->type == 'school') {
			$sql = "update date_tasks dt 
					join date_tasks_missions dtm using (date_tasks_mission_id) 
					set dt.name = '" . mysql_real_escape_string($new) . "' 
					where dt.name = '" . $old . "' 
					and dtm.created_by_school = " . $this->id;
		} else if ($this->type == 'parent') {
			$sql = "update date_tasks 
					set name = '" . mysql_real_escape_string($new) . "'  
					where name = '" . $old . "' 
					and cat like 'My Personal Task%' 
					and default_on = 0";
		}
		//echo $sql;
		if (mysql_query($sql)) {
			return true;
		} else {
			return false;
		}
	}
}
?>