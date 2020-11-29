<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

// are all these needed?
// use dirname(__FILE__) so that the import is relative to this file and not the one that is importing it

class TotalWeeklyTasks {
    // public variables
    public $start_date;
    public $end_date;
    public $week_dates = array();
    
    public $user_id; // public for performance imporvments when generating reports
    
    /*
     * new totalWeeklyTasks($user_id, $end_date)
     *
     * Paramaters:
     *  - $user_id: the user id of the user we are getting the tasks for
     *  - $end_date: The end date for the report
     *
     * sets $this->start date to default date on the julian calander
     *
     */
    public function __construct($user_id, $end_date) {
        // store this to get the user info later
        $this->user_id = $user_id;
        $this->start_date = 2459097; // September 4, 2020
        $this->end_date = $end_date;
    }
    
    /*
     * totalWeeklyTasks.get_week_dates()
     *
     * Fills the array with all the weeks between the start date and the end date (julian) that is passed in
     *
     */
    public function get_week_dates() {
        // $date will refer to the date that is moving from the start date till the end date
        $date = $this->start_date;
        while ($date <= $this->end_date) {
            $week_start = $date;
            $date += 6; // move to the end of the week
            
            array_push($this->week_dates, ["start" => $week_start, "end" => $date]); // pass the newly generated week into the array with start and end keys
            
            $date += 1; // move up one for the next start date (since the start date is included in the equation)
        }
    }
    // count all the weeks with a task since default start date
    public function total_weeks_with_task( $realtime = false ){
        // realtime or cached....
        if ( $realtime ){
            foreach ($this->week_dates as $week_dates) {
                if ( $this->week_has_task( $week_dates["start"], $week_dates["end"], true, true ) ) { // get the parts of the week by their start and end keys
                    $total_weeks += 1; // another week has tasks marked for it
                }
            };
        } else { // pull results from the cache table ( do not calculate the information )
            $total_weeks_query = mysql_query(
                 " SELECT COUNT(*) as total FROM user_yearly_gift WHERE start_date >= '" . $this->week_dates[0]['start'] ."' "
                ." AND end_date <= '" . end( $this->week_dates )["end"] . "' "
                ." AND user_id = '" . $this->user_id . "' "
                ." AND marked = 1 "
            );
            return mysql_fetch_assoc( $total_weeks_query )['total'];
        }
    }

    /**
     * check if a single week has a task.
     * with options for bypassing and updating the cache
     * @param int $start start of week julian date
     * @param int $end end of week julian date
     * @param bool $realtime bypasses the cache and adds marks to the cache
     * @param bool $deleting bypasses the cache and adds/removes marks from the cache, overiding $realtime
     * @return bool
     */
    public function week_has_task($start, $end, $realtime = false, $deleting = false){
        if ( !$deleting ) {
            // check if they are marked in the yearly gift table
            $sql = "SELECT EXISTS(
                    SELECT * FROM user_yearly_gift WHERE user_id = {$this->user_id} AND start_date = $start AND end_date = $end AND marked = 1
                ) as marked;"; // check if there is a mark for this user on this week
            $query = mysql_query($sql);
            // if they are marked or realtime is false return the result of the above query
            if (mysql_fetch_assoc($query)['marked']) {
                return true;
            }
            if ( !$realtime )
                return false;
            // if it's not marked and realtime is true then
            // continue checking below in case it's not yet in the cache
        }

        // check if there is any marks during the week period
//        $sql = "select dtmarks.date_task_id, count(*) as 'total', dtmarks.done_qty, dt.needed, dt.quantity from user_tracks ut "
//            ."join date_tasks_missions dtm on ut.level = dtm.level and ut.track_id = dtm.track_id and ut.subject_id = dtm.subject_id "
//            ."join date_tasks dt using (date_tasks_mission_id) join date_tasks_marks dtmarks using (date_task_id) "
//            ."where dtmarks.user_id = ".$this->user_id." and ut.user_id = ".$this->user_id." "
//            ."and dtmarks.mark_date >= $start and dtmarks.mark_date <= $end "
//            ."group by dtmarks.date_task_id";
        $sql = "select dtmarks.date_task_id, count(*) as 'total', dtmarks.done_qty, dt.needed, dt.quantity from date_tasks_missions dtm "
            ."join date_tasks dt using (date_tasks_mission_id) join date_tasks_marks dtmarks using (date_task_id) "
            ."where dtmarks.user_id = ".$this->user_id." and dtmarks.mark_date >= $start and dtmarks.mark_date <= $end "
            ."group by dtmarks.date_task_id";
        //if($this->user_id == 50628) echo $sql."<br/><br/>";

        if ($this->realtime_total_weeks_with_task($start, $end)){
            $this->mark_week_task( $start, $end );
            return true; // we have a vaid task for the week
        } else {
            $this->clear_week_task( $start, $end ); // delete it if it is blank
            return false;
        }
    }

    /**
     * returns the number of weeks where the user did at least one mission
     * within the $start to $end date range.
     * also works great for single weeks.
     */
    public function realtime_total_weeks_with_task(int $start, int $end) : int {
        // the 4 would need to be adjusted to {week_start_jd % 7} if the week didn't start on friday
        $sql = "SELECT DISTINCT
            ROUND((mark_date - 4) DIV 7, 0) * 7 + 4 AS marked_week_start
            FROM date_tasks_marks as dtm
            JOIN date_tasks as dt using (date_task_id)
            WHERE dtm.user_id = {$this->user_id}
            and dtm.mark_date >= $start
            and dtm.mark_date <= $end
            and ( dt.quantity IS NULL || dtm.done_qty >= dt.quantity)
        ";
        $query = mysql_query($sql);

        return intval(mysql_num_rows($query));
    }

    // insert a row into the user_yearly_gift table
    private function mark_week_task( $start, $end ){
        $sql = "INSERT INTO user_yearly_gift (user_id, start_date, end_date, marked) VALUES ('".$this->user_id."', '$start', '$end', 1)";
        return mysql_query($sql);
    }

    public function clear_week_task( $start, $end ) {
        return mysql_query(
             " DELETE FROM user_yearly_gift WHERE user_id = '" . $this->user_id . "' "
            ." AND start_date = '$start' AND end_date = '$end' " 
        );
    }

    /**
     * updateUser
     * 
     * static funciton that creates an instance under the hood and updates the cache
     *
     * @param [type] $user_id
     * @param [type] $mark_date
     * @return void
     */
    public static function updateUser( $user_id, $mark_date, $deleting = false ){
        $current_parsha_query = mysql_query(
            "SELECT * FROM parshos WHERE start <= '$mark_date' AND end >= '$mark_date' ORDER BY end DESC LIMIT 1;"
        );
        $current_parsha = mysql_fetch_assoc( $current_parsha_query );

        $instance = new self( $user_id, $current_parsha['end'] );
        
        return $instance->week_has_task( $current_parsha['start'], $current_parsha['end'], true, $deleting );
    }
}
