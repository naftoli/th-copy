<?php
class Defaults {   
    private $user;
    private $type;

    public function __construct($id, $type='user') {
        $this->user = $id; 
        $this->type = $type;
    }

    public function addOn($id, $table) {
        $typeID = $this->{$this->type};
        $sql = "insert ignore into {$this->type}_{$table}s values ($typeID, $id)";
        mysql_query($sql);
    }
    
    public function deleteOn($id, $table) {
        $typeID = $this->{$this->type};
        $sql = "delete from {$this->type}_{$table}s where {$this->type}_id = $typeID and task_id = $id";
        mysql_query($sql);
    }
    
    public function isOn($id, $table) {
        //find out if user has default set to on
        $sql1 = "select * from user_{$table}s where user_id = $this->user and {$table}_id = " . $id;
		//echo $sql1; exit;
        $result1 = mysql_query($sql1);
        if (mysql_num_rows($result1) > 0) {
            return true;
        } else {
            return false;
        }
    }
}
?>
