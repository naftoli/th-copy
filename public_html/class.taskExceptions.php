<?
class TaskExceptions { 
	
    public function __construct() {
            
    }
	
    public function isException( $taskID, $userID ) {
        //find out school_id and class_id for user
        $sql = "SELECT school_id, class_id FROM users WHERE user_id = " . $userID;
        $result = mysql_query( $sql );
        $row = mysql_fetch_assoc( $result );
        $schoolID = $row['school_id'];
        $classID = $row['class_id'];

        //find out if school has exception
        if ( isset( $schoolID ) ) {
            //find out if school is unenrolled from subject
            $sql = "SELECT * FROM school_subjects 
                    WHERE school_id = $schoolID 
                    AND subject_id = (
                    SELECT subject_id FROM date_tasks_missions dtm 
                    JOIN date_tasks dt USING (date_tasks_mission_id) 
                    WHERE date_task_id = $taskID)";
            $result = mysql_query($sql);
            if (mysql_num_rows($result) == 0) {
                return true;
            }
            
            $sql = "SELECT * FROM school_task_exceptions WHERE school_id = " . $schoolID . 
            " AND date_task_id = " . $taskID; 
            $result = mysql_query( $sql );
            $numRows = mysql_num_rows( $result );
            if ( $numRows > 0 ) {
                return true;
            }
        }

        //find out if class has exception
        if ( isset( $classID ) ) {
            $sql = "SELECT * FROM class_task_exceptions WHERE class_id = " . $classID . 
            " AND date_task_id = " . $taskID;
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