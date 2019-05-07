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
    private $DAY_COUNT = 180; // iber yor, up limit to 180 days
    private $db_conn;
    private $year;
    // ends on lag baomer
    // private $deadline = 2458243; // 5778, May 4 2018
    private $deadline = 2458627; // 5779, May 24 2019
    private $start = 2458362;
    
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
        $this->year  = GlobalSettings::getCurrentYear();
    }
// WARNING: IF NO SCHOOL ID IS PROVIDED THIS FUNCTION WILL BE VERY SLOW
    public function set_school_eligibility( $school_id ) {
        $eligibility_query = $this->db_conn->query(
             " SELECT user_id, COUNT( DISTINCT(mark_date) ) as days "
            ." FROM date_tasks_marks JOIN users USING (user_id) "
            ." WHERE mark_date > " . $this->start . " "
            ." AND mark_date < " . $this->deadline . " "
            ." AND school_id = '$school_id' "
            ." GROUP BY user_id;"
        );
        // load all the eligible users
        $this->eligibility = [];
        while ( $row = $eligibility_query->fetch_assoc() ) {
            $this->eligibility[$row['user_id']] = $row['days'];
            if ( intval($row['days']) > $this->DAY_COUNT )
                $this->cacheUser( $row['user_id'], $row['days'] );
        }
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
        // default to generating the information
        $eligibility_query = $this->db_conn->query(
             " SELECT user_id, COUNT( DISTINCT(mark_date) ) as days "
            ." FROM date_tasks_marks JOIN users USING (user_id) "
            ." WHERE mark_date > " . $this->start . " "
            ." AND mark_date < " . $this->deadline . " "
            ." AND user_id = '$user_id' "
        );

        // load all the eligible users
        while ( $row = $eligibility_query->fetch_assoc() ) {
            $this->eligibility[ $user_id ] = $row['days'];
            // cache the user if they are eligible
            if ( intval($row['days']) > $this->DAY_COUNT )
                $this->cacheUser( $user_id, $row['days'] );
        }
        
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
        // generate the SQL to run
        if ( $realtime )
            $users_sql = "SELECT user_id, user_serial, school_name, first, last, COUNT(DISTINCT (mark_date)) AS days, class_grade, class_sub "
                ." FROM date_tasks_marks JOIN users USING (user_id) "
                ." JOIN schools USING (school_id) JOIN classes USING (class_id) "
                ." WHERE mark_date > " . $this->start . " "
                ." AND mark_date < " . $this->deadline . " "
                .( $school_id ? " AND users.school_id = '$school_id' " : "" ) // limit to school if provided
                ." GROUP BY user_id "
                ." HAVING days >= " . $this->DAY_COUNT . " "
                ." ORDER BY school_name, class_grade, class_sub, last, first ";
        else
            $users_sql = "SELECT user_id, user_serial, school_name, first, last, days, class_grade, class_sub"
                ." FROM user_yearly_raffle JOIN users USING (user_id) "
                ." JOIN schools USING (school_id) JOIN classes USING (class_id) "
                ." WHERE days >= " . $this->DAY_COUNT . " "
                ." AND year = " . $this->year . " "
                .( $school_id ? " AND users.school_id = '$school_id' " : "" ) // limit to school if provided
                ." ORDER BY school_name, class_grade, class_sub, last, first, days ";

        $users_query = $this->db_conn->query( $users_sql );

        $eligible_users = [];
        while( $row = $users_query->fetch_assoc() ) {
            $eligible_users[] = $row;
            if( $realtime )
                $this->cacheUser( $row['user_id'], $row['days'] );
        }
        return $eligible_users;
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
     * getDayCount
     * 
     * return the minimum number of days needed
     *
     * @return int
     */
    public function getDayCount() {
        return $this->DAY_COUNT;
    }
}