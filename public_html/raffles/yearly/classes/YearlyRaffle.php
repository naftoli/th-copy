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
    private $db_conn;
    private $dates;
    
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
            ." AND mark_date < " . $this->dates['end'] . " "
            ." AND school_id = '$school_id' "
            ." GROUP BY user_id;"
        );
        // load all the eligible users
        $this->eligibility = [];
        while ( $row = $eligibility_query->fetch_assoc() ) {
            $this->eligibility[$row['user_id']] = $row['days'];
        }
        return $this->eligibility;
    }

    public function set_user_eligibility( $user_id ) {
        $eligibility_query = $this->db_conn->query(
             " SELECT user_id, COUNT( DISTINCT(mark_date) ) as days "
            ." FROM date_tasks_marks JOIN users USING (user_id) "
            ." WHERE mark_date > " . $this->dates['start'] . " "
            ." AND mark_date < " . $this->dates['end'] . " "
            ." AND user_id = '$user_id' "
        );
        // load all the eligible users
        while ( $row = $eligibility_query->fetch_assoc() ) {
            $this->eligibility[$row['user_id']] = $row['days'];
        }
        return $this->eligibility;
    }

    public function getStart() {
        return $this->dates['start'];
    }

    public function getEnd() {
        return $this->dates['end'];
    }
}