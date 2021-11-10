<?php

namespace raffles\yearly;
// load the shared classes
require_once(dirname(__FILE__)."/../../shared/classes/Constants.php");
require_once(dirname(__FILE__)."/../../shared/classes/DBAdapter.php");
// import the global singleton
require_once(dirname(__FILE__)."/../../../class.globalSettings.php");

use \GlobalSettings;
use \DBAdapter;
use raffles\shared\Constants as Constants; // was created later and has correct namespace

class YearlyRaffle {
    private $days_of_tasks;
    private $db_conn;
    private $year;
    private $deadline;
    private $start;
    private $grid_id;
    private $cutoff;
    private $num_before_cutoff;
    
    public $eligibility;

    /**
     * new YearlyRaffle()
     *
     * create a new raffle. Pass in an optional DBAdapter instance
     * 
     * @param boolean $db_conn
     */
    public function __construct($db_conn = false) {
        // create a new DB adapter if one is not provided
        $db_conn ? $this->db_conn = $db_conn : $this->db_conn = new DBAdapter();
        $this->year = GlobalSettings::getCurrentYear();
        // find raffle 180
        $result = $this->db_conn->query("select * from raffles where type = 'yearly' order by year desc limit 1");
        $row = $result->fetch_assoc();
        $this->deadline = $row['end_date'];
        $this->start = $row['start_date'];
        $this->days_of_tasks = $row['days_of_tasks'];
        $this->grid_id = 13012;
    }
    // WARNING: IF NO SCHOOL ID IS PROVIDED THIS FUNCTION WILL BE VERY SLOW
    public function set_school_eligibility( $school_id ) {
        $this->eligibility = $this->getAndCacheEligibility($school_id);
        return $this->eligibility;
    }

    /**
     * set_user_eligibility
     * 
     * Sets the interal eligiblitiy for a single user and returns the updated array
     *
     * @param string $user_id
     * @return array
     */
    public function set_user_eligibility( $user_id ) {
        // check the cache
        $eligibility_cache_query = $this->db_conn->query(
             " SELECT days FROM user_yearly_raffle "
            ." WHERE year = " . $this->year . " "
            ." AND user_id = '$user_id' "
        );
        if ( $eligibility_cache_query && $eligibility_cache_query->num_rows() > 0 ){
            $row = $eligibility_cache_query->fetch_assoc();
            $this->eligibility[$user_id] = $row['days'];
            return $this->eligibility;
        }
        // otherwize update the cache
        $this->eligibility[$user_id] = $this->getAndCacheEligibility(false, $user_id)[$user_id];
        
        return $this->eligibility;
    }

    /**
     * get_eligible_users
     * 
     * Generates and returns array of all eligibile users
     *
     * @param boolean $realtime <= load from cache or dynamically generate
     * @param boolean $school_id <= limit to a single school
     * @return array
     */
    public function get_eligible_users( $realtime = false, $school_id = false ) {
        // if realtime update cache first
        if ( $realtime ) {
            $this->getAndCacheEligibility($school_id);
        }
        // generate the SQL to run
        // fist find out how many tasks were done before cutoff
        $users_sql = "SELECT user_id, user_serial, school_name, first, last, days, class_grade, class_sub"
            ." FROM user_yearly_raffle JOIN users USING (user_id) "
            ." JOIN schools USING (school_id) JOIN classes USING (class_id) "
            ." WHERE days >= " . $this->days_of_tasks . " "
            ." AND year = " . $this->year . " "
            .( $school_id ? " AND users.school_id = '$school_id' " : "" ) // limit to school if provided
            ." ORDER BY school_name, class_grade, class_sub, last, first, days ";

        $users_query = $this->db_conn->query( $users_sql );

        $eligible_users = [];
        while( $row = $users_query->fetch_assoc() ) {
            $eligible_users[] = $row;
        }

        return $eligible_users;
    }

    /**
     * getEligibility
     * 
     * gets the amount of eligibile days per user.
     * 
     * @param int $school_id scope to a school 
     * @param int $user_id scope to a user
     * @return array user_id => eligble_days key value pairs
     * 
     */
    private function getEligibility( $school_id = false, $user_id = false ){
        if ($user_id) {
            $users_filter = "dtm.user_id = ". $user_id;
        } else if ($school_id) {
            $users_filter = "dtm.user_id in (select user_id from users where school_id = $school_id)";
        } else {
            $users_filter = "true";
        }

        $sql = "SELECT user_id, COUNT(distinct mark_date) AS total FROM date_tasks_marks dtm
                JOIN date_tasks dt USING (date_task_id) 
                WHERE $users_filter
                AND dt.grid_id = " . $this->grid_id . " 
                AND dtm.mark_date >= " . $this->start . " 
                AND dtm.mark_date <= " . $this->deadline ."
                group by user_id";
        $result = mysql_query($sql);

        $totals = [];
        // if $user_id is provided default to zero instead of the key not existing
        if ($user_id) {
            $totals[$user_id] = 0;
        }

        if (mysql_num_rows($result) > 0) {
            $row = mysql_fetch_array($result);
            $totals[$row['user_id']] = $row['total'];
        }

        return $totals;
    }

    /**
     * getAndCacheEligibility
     * 
     * gets the amount of eligibile days per user and updates the cache with the result.
     * 
     * @param int $school_id scope to a school 
     * @param int $user_id scope to a user
     * @return array
     * 
     */
    private function getAndCacheEligibility($school_id = false, $user_id = false) {
        $totals = $this->getEligibility($school_id, $user_id);
        foreach($totals as $user_id => $total) {
            $this->cacheUser( $user_id,  $total );
        }
        return $totals;
    }

    /**
     * cacheUser
     * 
     * adds a user to the cache
     *
     * @param string $user_id
     * @param string $days
     * @return void
     */
    private function cacheUser( $user_id, $days ){
        $year = $this->year;
        return $this->db_conn->query(
             "INSERT INTO user_yearly_raffle (user_id, days, year) "
            ." VALUES('$user_id', '$days', '$year') ON DUPLICATE KEY UPDATE days='$days'"
        );
    }

    /**
     * getStart
     * 
     * returns the start date
     *
     * @return string
     */
    public function getStart() {
        return $this->start;
    }

    /**
     * getEnd
     * 
     * returns the deadline
     *
     * @return string
     */
    public function getEnd() {
        return $this->deadline;
    }

    /**
     * required_days_of_tasks
     * 
     * return the minimum number of days needed to be in the raffle
     *
     * @return int
     */
    public function required_days_of_tasks() {
        return $this->days_of_tasks ?? Constants::get_yearly_task_requirment();
    }

    public function getYear() {
        return $this->year;
    }
}