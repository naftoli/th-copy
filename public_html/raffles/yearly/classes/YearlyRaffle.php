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
    private $DAY_COUNT = 160;
    private $db_conn;
    private $dates;
    private $deadline = 2458243; // May 4 2018
    
    public $eligibility;

    public function __construct($db_conn = false) {
        // create a new DB adapter if one is not provided
        $db_conn ? $this->db_conn = $db_conn : $this->db_conn = new DBAdapter();
        // get the dates for the raffle
        $this->dates = GlobalSettings::getCurYearDates();
    }
// WARNING: IF NO SCHOOL ID IS PROVIDED THIS FUNCTION WILL BE VERY SLOW
    public function set_school_eligibility( $school_id ) {
        $eligibility_query = $this->db_conn->query(
             " SELECT user_id, COUNT( DISTINCT(mark_date) ) as days "
            ." FROM date_tasks_marks JOIN users USING (user_id) "
            ." WHERE mark_date > " . $this->dates['start'] . " "
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

    public function set_user_eligibility( $user_id ) {
        $eligibility_query = $this->db_conn->query(
             " SELECT user_id, COUNT( DISTINCT(mark_date) ) as days "
            ." FROM date_tasks_marks JOIN users USING (user_id) "
            ." WHERE mark_date > " . $this->dates['start'] . " "
            ." AND mark_date < " . $this->deadline . " "
            ." AND user_id = '$user_id' "
        );
        // load all the eligible users
        while ( $row = $eligibility_query->fetch_assoc() ) {
            $this->eligibility[$row['user_id']] = $row['days'];
            // cache the user if they are eligible
            if ( intval($row['days']) > $this->DAY_COUNT )
                $this->cacheUser( $row['user_id'], $row['days'] );
        }
        return $this->eligibility;
    }

    public function get_eligible_users( $realtime = false ) {
        // generate the SQL to run
        if ( $realtime )
            $users_sql = "SELECT user_id, user_serial, school_name, first, last, COUNT(DISTINCT (mark_date)) AS days "
                ." FROM date_tasks_marks JOIN users USING (user_id) JOIN schools USING (school_id) "
                ." WHERE mark_date > " . $this->dates['start'] . " "
                ." AND mark_date < " . $this->deadline . " "
                ." GROUP BY user_id "
                ." HAVING days >= " . $this->DAY_COUNT . " "
                ." ORDER BY school_name, last, first ";
        else
            $users_sql = "SELECT user_id, user_serial, school_name, first, last, days "
                ." FROM user_yearly_raffle JOIN users USING (user_id) JOIN schools USING (school_id) "
                ." WHERE days >= " . $this->DAY_COUNT . " "
                ." ORDER BY school_name, last, first, days ";

        $users_query = $this->db_conn->query( $users_sql );

        $eligible_users = [];
        while( $row = $users_query->fetch_assoc() ) {
            $eligible_users[] = $row;
            if( $realtime )
                $this->cacheUser( $row['user_id'], $row['days'] );
        }
        return $eligible_users;
    }

    private function cacheUser( $user_id, $days ){
        return $this->db_conn->query(
             "INSERT INTO user_yearly_raffle (user_id, days) "
            ." VALUES('$user_id', '$days') ON DUPLICATE KEY UPDATE days='$days'"
        );
    }

    public function getStart() {
        return $this->dates['start'];
    }

    public function getEnd() {
        return $this->deadline;
    }
}