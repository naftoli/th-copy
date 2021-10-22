<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

// are all these needed?
// use dirname(__FILE__) so that the import is relative to this file and not the one that is importing it
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

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
        $this->start_date = GlobalSettings::getCurYearDates()['start'];
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
    public function total_weeks_with_task( $update = false ){
        // realtime or cached....
        if ( $update ) $this->update_all_weeks();
        // pull results from the cache table ( do not calculate the information )
        return $this->cached_total_weeks_with_task($this->week_dates[0]['start'], end( $this->week_dates )["end"]);
    }

    /**
     * check if a single week has a task.
     * with options for bypassing and updating the cache
     * @param int $start start of week julian date
     * @param int $end end of week julian date
     * @param bool $realtime bypasses the cache and updates the cache
     * @return bool
     */
    public function week_has_task($start, $end, $update = false){
        $row = $this->cached_weeks_with_task($start, $end)[0];
        // if they are marked or realtime is false return the result of the above query
        if ( !$update || !($row && $row['is_override']) )
            return ($row && $row['marked']);

        if ($this->realtime_weeks_with_task($start, $end)->length){
            $this->mark_week_task( $start, $end );
            return true; // we have a vaid task for the week
        } else {
            $this->clear_week_task( $start, $end ); // delete it if it is blank
            return false;
        }
    }

    /**
     * sql for realtime query
     */
    private function realtime_sql(int $start, int $end) : string {
        return "SELECT DISTINCT start, end
            from (
                SELECT
                    ROUND((mark_date - 4) DIV 7, 0) * 7 + 4 AS start,
                    ROUND((mark_date - 4) DIV 7, 0) * 7 + 10 AS end
                FROM date_tasks_marks as dtm
                JOIN date_tasks as dt using (date_task_id)
                WHERE dtm.user_id = {$this->user_id}
                and dtm.mark_date >= $start
                and dtm.mark_date <= $end
                and ( dt.quantity IS NULL || dtm.done_qty >= dt.quantity)
                UNION
                SELECT start_date AS start, end_date as end FROM user_yearly_gift
                WHERE user_id = {$this->user_id}
                and start_date >= $start
                and end_date <= $end
                and marked = 1
                and is_override = 1
            ) as marks
            order by start
        ";
    }
    /**
     * returns the an array weeks ( as [$start =>, $end =>]) where the user did at least one mission
     * within the $start to $end date range.
     * also works great for single weeks.
     */
    public function realtime_weeks_with_task(int $start, int $end) : array {
        return mysql_fetch_all_assoc(mysql_query($this->realtime_sql($start, $end)));
    }

    /**
     * returns the number of weeks where the user did at least one mission
     * within the $start to $end date range.
     * also works great for single weeks.
     */
    public function realtime_total_weeks_with_task(int $start, int $end) : int {
        return mysql_num_rows(mysql_query($this->realtime_sql($start, $end)));
    }

    /**
     * sql for cached query
     */
    public function cached_sql(int $start, int $end){
        return "SELECT * FROM user_yearly_gift
            WHERE start_date >= '$start'
            AND end_date <= '$end'
            AND user_id = '$this->user_id'
            AND marked = 1
            order by start_date
        ";
    }

    // get all the weeks with a task since default start date
    public function cached_weeks_with_task(int $start, int $end){
        return mysql_fetch_all_assoc(mysql_query($this->cached_sql($start, $end)));
    }

    // get all the weeks with a task since default start date
    public function cached_total_weeks_with_task(int $start, int $end){
        return mysql_num_rows(mysql_query($this->cached_sql($start, $end)));
    }

    // insert a row into the user_yearly_gift table
    private function mark_week_task( $start, $end, $is_override = false){
        $is_override = $is_override ? 1 : 0;
        $sql = "INSERT INTO user_yearly_gift (user_id, start_date, end_date, marked, is_override) VALUES ('".$this->user_id."', '$start', '$end', 1, $is_override)";
        return mysql_query($sql);
    }

    public function clear_week_task( $start, $end, $clear_override = false) {
        $sql = " DELETE FROM user_yearly_gift WHERE user_id = $this->user_id AND start_date = $start AND end_date = $end";
        $sql .= $clear_override ? "" : " AND is_override = 0 ";
        $result = mysql_query($sql);
        return $result;
    }
    
    public function update_all_weeks() {
        if ((!$this->week_dates) || !count($this->week_dates)) $this->get_week_dates();

        $marked_weeks = $this->realtime_weeks_with_task($this->week_dates[0]['start'], end( $this->week_dates )["end"]);
        $cached_weeks = $this->cached_weeks_with_task($this->week_dates[0]['start'], end( $this->week_dates )["end"]);

        foreach($this->week_dates as $week) {
            $cached = false;
            foreach($cached_weeks as $cached_week) {
                if ($cached_week['start_date'] == $week['start'] && $cached_week['end_date'] == $week['end']) {
                    $cached = true;
                    break;
                }
            }
            $marked = false;
            foreach($marked_weeks as $marked_week) {
                if ($marked_week['start'] == $week['start'] && $marked_week['end'] == $week['end']) {
                    $marked = true;
                    break;
                }
            }

            if ($marked && !$cached) {
                $this->mark_week_task( $week['start'], $week['end']);
            }
            if ($cached && !$marked) {
                $this->clear_week_task( $week['start'], $week['end']);
            }
        }
    }

    /**
     * updateUser
     * 
     * static funciton that creates an instance under the hood and updates the cache
     *
     * @param int $user_id
     * @param int $mark_date
     * @return void
     */
    public static function updateUser( $user_id, $mark_date ){
        $current_parsha_query = mysql_query(
            "SELECT * FROM parshos WHERE start <= '$mark_date' AND end >= '$mark_date' ORDER BY end DESC LIMIT 1;"
        );
        $current_parsha = mysql_fetch_assoc( $current_parsha_query );

        $instance = new self( $user_id, $current_parsha['end'] );
        
        return $instance->week_has_task( $current_parsha['start'], $current_parsha['end'], true );
    }

    /**
     * updateUserAll
     * 
     * static funciton that creates an instance under the hood and updates the cache
     *
     * @param int $user_id
     * @param int $mark_date
     * @return void
     */
    public static function updateAllWeeks( $user_id ){
        $week_end_date = round((unixtojd() - 4) / 7) * 7 + 10;
        $instance = new self( $user_id, $week_end_date );
        
        return $instance->update_all_weeks();
    }
}


/**
 * Fetches each row's associative array and return them in an array .
 * @param resource|false $mysql_query_result the return value from mysql_query() on a select statement
 * @return array of mysql_fetch_assoc's rows
 */
function mysql_fetch_all_assoc($mysql_query_result) : array {
	if (!$mysql_query_result) return [];
	$rows = [];
	while ($row = mysql_fetch_assoc($mysql_query_result)) { // for each row
		$rows[] = $row;
	}
	return $rows;
}