<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

// are all these needed?
// use dirname(__FILE__) so that the import is relative to this file and not the one that is importing it
require_once(dirname(__FILE__) . "/../db.php");
require_once(dirname(__FILE__) . "/../classes/user.php");
require_once dirname(__FILE__) . "/../class.defaults.php";
require_once(dirname(__FILE__) . "/../classes/user_track.php");
require_once(dirname(__FILE__) . "/../classes/school_class.php");
require_once(dirname(__FILE__) . "/../class.taskExceptions.php");
require_once(dirname(__FILE__) . "/../classes/date_tasks_mission.php");
require_once(dirname(__FILE__) . "/../classes/daily_task.php");
require_once(dirname(__FILE__) . "/../classes/weekly_task.php");
require_once(dirname(__FILE__) . "/../classes/shabbos_task.php");
require_once(dirname(__FILE__) . "/../classes/no_label_task.php");
require_once(dirname(__FILE__) . "/../classes/task.php");
require_once(dirname(__FILE__) . "/../classes/date_tasks_mark.php");

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
     * sets $this->start date to October 20, 2017 on the julian calander
     *
     */
    public function __construct($user_id, $end_date) {
        // store this to get the user info later
        $this->user_id = $user_id;
        
        $this->start_date = 2458047; // October 20, 2017
        
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
        while($date <= $this->end_date) {
            $week_start = $date;
            $date += 6; // move to the end of the week
            
            array_push($this->week_dates, ["start" => $week_start, "end" => $date]); // pass the newly generated week into the array with start and end keys
            
            $date += 1; // move up one for the next start date (since the start date is included in the equation)
        }
    }
    // count all the weeks with a task since October 20 2017 (default start date)
    public function total_weeks_with_task(){
        $total_weeks = 0; // start with no weeks with tasks
        // get the user
        foreach($this->week_dates as $week_dates){
            if($this->week_has_task_sql($week_dates["start"], $week_dates["end"])){ // get the parts of the week by their start and end keys
                $total_weeks += 1; // another week has tasks marked for it
            }
        };
        return $total_weeks;
    }
    public function week_has_task_sql($start, $end){
        // check if they are marked in teh yearly gift table
        $sql = "SELECT * FROM user_yearly_gift WHERE user_id = " . $this->user_id . " AND start_date = $start AND end_date = $end"; // check if there is a mark for this user on this week
        $query = mysql_query($sql);
        if (mysql_num_rows($query) > 0 ) { // if we have an entry in the table
            $row = mysql_fetch_assoc($query);
            if ($row['marked'] == 1) return true; // if it is set to one, then return true otherwise keep checking the marks
        }
        
        $sql = "select dtmarks.date_task_id, count(*) as 'total', dtmarks.done_qty, dt.needed, dt.quantity from user_tracks ut "
            ."join date_tasks_missions dtm on ut.level = dtm.level and ut.track_id = dtm.track_id and ut.subject_id = dtm.subject_id "
            ."join date_tasks dt using (date_tasks_mission_id) join date_tasks_marks dtmarks using (date_task_id) "
            ."where dtmarks.user_id = ".$this->user_id." and ut.user_id = ".$this->user_id." "
            ."and dtmarks.mark_date >= $start and dtmarks.mark_date <= $end "
            ."group by dtmarks.date_task_id";
            
        //if($this->user_id == 50628) echo $sql."<br/><br/>";

        $query = mysql_query($sql);
        while ($row = mysql_fetch_assoc($query)){
            if ($row['total'] >= 1 && // if the amount of rows is equal to what is needed (covers daily tasks)
                ($row['quantity'] ? $row['done_qty'] >= $row['quantity'] : true)){ // make sure that the quanity is good (covers non daily tasks)
                return true; // we have a vaid task for the week
            }
        }
        return false;
    }
    // check if a single week has a task
    public function week_has_task($start, $end) {
        // first check if it is in the user_yearly_gift table
        $sql = "SELECT * FROM user_yearly_gift WHERE user_id = " . $this->user_id . " AND start_date = $start AND end_date = $end"; // check if there is a mark for this user on this week
        $query = mysql_query($sql);
        if (mysql_num_rows($query) > 0 ) { // if we have an entry in the table
            $row = mysql_fetch_assoc($query);
            if ($row['marked'] == 1) return true; // if it is set to one, then return true otherwise keep checking the marks
        }
        $sql = "SELECT * FROM users WHERE user_id = " . $this->user_id;
        $query = mysql_query($sql); // run the query
        $row = mysql_fetch_assoc($query); // get the row
        $user = new user($row); // create the user
        $user->get_user_tracks( -1, $start, $end, array(), $user->lang_id ); // get the user tracks
        //print_r($user);
        // go through each track
        foreach($user->user_tracks as $track){ // get all the users tracks
            // lets check the daily tasks first
            foreach($track->daily_tasks as $task) {
                if ($this->check_marks($task)){
                    return true;
                }
            }
            // then check the weeklys - only one task mark
            foreach($track->weekly_tasks as $task){
                if ($this->check_mark($task)){
                    return true;
                }
            }
            // then check the shabbos ones - only one task mark
            foreach($track->shabbos_tasks as $task){
                if ($this->check_mark($task)){
                    return true;
                }
            }
            // then check the unlabled ones - only one task mark
            foreach($track->no_label_tasks as $task){
                if ($this->check_marks($task) || $this->check_mark($task)){ // combine both checks for not labeled tasks
                    return true;
                }
            }
        };
        //print_r($user);
        return false;
    }
    
    // function to check task with multiple marks for if any are checked
    private function check_marks($task){ // get the date_task_marks
        if(count($task->date_task_marks) > 0){ // removes invalid foreach errors
            foreach($task->date_task_marks as $mark){ // for each mark
                if ($mark->marked){ // check if it was marked and the done_qty is greater then 0 (defaults to 1)
                    if(!$task->quantity || ($task->quantity && $mark->done_qty >= $task->quantity)){ // if a quanity is set make sure that it is above the tasks quantity
                        return true; // then it is checked
                    }
                }
            };
        }
        return false; // defaults to unchcked
    }
    // function to check task with single mark if it is checked
    private function check_mark($task) {
        if ($task->date_task_mark->marked) { // if the task is marked
            if(!$task->quantity || ($task->quantity && $task->date_task_mark->done_qty >= $task->quantity)){ // if a quanity is set make sure that it is above the tasks quantity
                return true; // then it is checked
            }
        }
        return false; // defaults to unchcked
    }
}

