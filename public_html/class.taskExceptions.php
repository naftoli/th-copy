<?
class TaskExceptions { 
	
    public function __construct() {
            
    }
	
    public function isException( $taskID, $userID ) {
        //find out school_id and class_id for user
        $sql = "select school_id, class_id from users where user_id = " . $userID;
        $result = mysql_query( $sql );
        $row = mysql_fetch_assoc( $result );
        $schoolID = $row['school_id'];
        $classID = $row['class_id'];

        //find out if school has exception
        if ( isset( $schoolID ) ) {
            //find out if school is unenrolled from subject
            $sql = "select * from school_subjects 
                    where school_id = $schoolID 
                    and subject_id = (
                    select subject_id from date_tasks_missions dtm 
                    join date_tasks dt using (date_tasks_mission_id) 
                    where date_task_id = $taskID)";
            $result = mysql_query($sql);
            if (mysql_num_rows($result) == 0) {
                return true;
            }
            $sql = "select * from school_task_exceptions where school_id = " . $schoolID . 
            " and date_task_id = " . $taskID; 
            $result = mysql_query( $sql );
            $numRows = mysql_num_rows( $result );
            if ( $numRows > 0 ) {
                return true;
            }
        }

        //find out if class has exception
        if ( isset( $classID ) ) {
            $sql = "select * from class_task_exceptions where class_id = " . $classID . 
            " and date_task_id = " . $taskID;
            $result = mysql_query( $sql );
            $numRows = mysql_num_rows( $result );
            if ( $numRows > 0 ) {
                return true;
            }
        }

        //find out if user has exception
        $sql = "select * from user_task_exceptions where user_id = " . $userID . 
        " and date_task_id = " . $taskID;
        $result = mysql_query( $sql );
        $numRows = mysql_num_rows( $result );
        if ( $numRows > 0 ) {
            return true;
        }

        //if we get here then no exception was found
        return false;
    }
}
?>