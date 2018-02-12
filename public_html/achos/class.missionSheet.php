<?
class MissionSheet {
    
    public function __construct() {
        
    }
    
    public function marked( $user, $week ) {
        //find out start and end dates of week
        $sql = "select start_date, end_date from reports where report_id = " . $week;
        $result = mysql_query( $sql );
        $row = mysql_fetch_assoc( $result );
        $start = $row['start_date'];
        $end = $row['end_date'];
        
        //only find out whether tasks were accomplished for past weeks up to and including current week
        $today = unixtojd();
        if ( $today >= $end || ( $today < $end && $today >= $start ) ) {        
            
            //get list of accomplished task ids for this user
            $sql = "select date_task_id 
                    from date_tasks_marks 
                    where user_id = $user 
                    and date_task_id in (
                        select date_task_id 
                        from date_tasks dt 
                        join date_tasks_missions dtm using (date_tasks_mission_id) 
                        where dtm.start_date >= $start 
                        and dtm.end_date <= $end
                    )";
            $result = mysql_query( $sql );
            $tasks = mysql_num_rows( $result );
            
            //if there are any done tasks, return true, otherwise return false        
            if ( $tasks > 0 )
                return true; 
            else 
                return false;
        } else 
            return false;
    }
}
?>