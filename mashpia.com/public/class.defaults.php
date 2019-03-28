<?php
class Defaults {   
    private $school;
    private $class;
    private $user;
    private $type;

    public function __construct($id, $type='user') {
        $this->class = 0;
        $this->user = 0; 
        $this->type = $type;
        if ($type == 'user') {
            $this->user = $id;
            //find out school_id and class_id for user
            $sql = "select school_id, class_id from users where user_id = " . $id;
            $result = mysql_query( $sql );
            $row = mysql_fetch_assoc( $result );
            $this->school = $row['school_id'];
            $this->class = $row['class_id'] ? $row['class_id'] : 0;
        } else if ($type == 'class') {
            $this->class = $id;
            $sql = "select school_id from classes where class_id = " . $id;
            $result = mysql_query($sql);
            $row = mysql_fetch_assoc($result);
            $this->school = $row['school_id'];
        } else {
            $this->school = $id; 
        }
    }

    public function addOn($id, $table) {
        $typeID = $this->{$this->type};
        // inserts row into the join table if it does not exist
        $sql = "INSERT ignore into {$this->type}_{$table}s values ($typeID, $id)";
        mysql_query($sql);
        //add default also to mission
        $sql = "SELECT date_tasks_mission_id FROM date_tasks WHERE date_task_id = " . $id;
        //echo $sql;
        $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0) {
            $row = mysql_fetch_assoc($result);
            $missionID = $row['date_tasks_mission_id'];
            // insert it again into the _missions join table (ignore errors if duplicate)
            $sql2 = "INSERT ignore INTO {$this->type}_missions VALUES ($typeID, $missionID)";
            mysql_query($sql2);
        }
    }
    
    public function deleteOn($id, $table) {
        $typeID = $this->{$this->type};
        $sql = "delete from {$this->type}_{$table}s where {$this->type}_id = $typeID and task_id = $id";
        mysql_query($sql);
        //delete default also from mission
        $sql = "select date_tasks_mission_id from date_tasks where date_task_id = " . $id;
        $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0) {
            $row = mysql_fetch_assoc($result);
            $missionID = $row['date_tasks_mission_id'];
            mysql_query("delete from {$this->type}_missions where {$this->type}_id = $typeID and mission_id = $missionID");
        }
    }
    
    public function isOn($id, $table) {
        if (!$this->class && !$this->user) {
        	if (is_array( $id )) {
        		$sql3 = "select * from school_{$table}s where school_id = $this->school and {$table}_id in (" . implode(',', $id) . ")";
        	} else {
        		$sql3 = "select * from school_{$table}s where school_id = $this->school and {$table}_id = " . $id;
        	}
            $result3 = mysql_query($sql3);
            if (mysql_num_rows($result3) > 0) {
                return true;
            } else {
                return false;
            }
        } else if (!$this->user) {
        	if (is_array( $id )) {
        		$sql2 = "SELECT * from class_{$table}s where class_id = $this->class and {$table}_id in (" . implode(',', $id) . ")";
				$sql3 = "SELECT * from school_{$table}s where school_id = $this->school and {$table}_id (" . implode(',', $id) . ")";
			} else {
            	$sql2 = "SELECT * from class_{$table}s where class_id = $this->class and {$table}_id = " . $id;
            	$sql3 = "SELECT * from school_{$table}s where school_id = $this->school and {$table}_id = " . $id;
			}
            $result2 = mysql_query($sql2);
            $result3 = mysql_query($sql3);

            if (
                ( $result2 && mysql_num_rows( $result2 ) > 0 ) || 
                ( $result3 && mysql_num_rows( $result3 ) > 0 )
            ) return true;
            else
                return false;
        } else {
            //find out if user or user's class or user's school is default on
            if (is_array( $id )) {
            	$sql1 = "select * from user_{$table}s where user_id = $this->user and {$table}_id in (" . implode(',', $id) . ")";
				$sql2 = "select * from class_{$table}s where class_id = $this->class and {$table}_id in (" . implode(',', $id) . ")";
				$sql3 = "select * from school_{$table}s where school_id = $this->school and {$table}_id in (" . implode(',', $id) . ")";
				//echo '<input type="hidden" name="isOn" value="'.$sql1.'"/>';
            } else {
            	$sql1 = "select * from user_{$table}s where user_id = $this->user and {$table}_id = " . $id;
				$sql2 = "select * from class_{$table}s where class_id = $this->class and {$table}_id = " . $id;
				$sql3 = "select * from school_{$table}s where school_id = $this->school and {$table}_id = " . $id;
				//echo '<input type="hidden" name="isOn" value="'.$sql1.'"/>';
            }
            $result1 = mysql_query($sql1);
            $result2 = mysql_query($sql2);
            $result3 = mysql_query($sql3);
            if (mysql_num_rows($result1) > 0 || mysql_num_rows($result2) > 0 || mysql_num_rows($result3) > 0) {
                return true;
            } else {
                return false;
            }
        }
    }
}
?>