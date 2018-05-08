<?php // dependencies
require_once( dirname(__FILE__) . "/Caller.php" );

class Donor {

    /** INSTANCE VARIABLES */
    public $donor_id;
    public $parent_admin_id;
    public $first_name;
    public $last_name;
    public $address;
    public $city;
    public $state;
    public $zip;
    public $country;
    public $phone;
    public $email;
    public $mashpiaPhone;

    public $donations = [];
    public $on_shabbaton = [];
    public $caller;

    /** STATIC FUNCTIONS */
    /**
     * ::load function
     * 
     * Loads a Donor from the database and returns an instance of the Donor class.
     * 
     * Throws 'Donor Not Found' exception if charidy_caller_id is invalid
     *
     * @param int $donor_id
     * @return Donor
     * @throws Exception 'Donor Not Found'
     */
    public static function Load( $donor_id ) {
        $donor_id = mysql_real_escape_string( $donor_id );
        $query = mysql_query(
            "SELECT * FROM charidy_donors where donor_id = $donor_id;"
        );
        if ( mysql_num_rows( $query ) > 0 ) {
            $row = mysql_fetch_assoc( $query );
            return self::LoadFromRow($row);
        } else {
            throw new Exception('Donor Not Found');
        }
    }

    /**
     * ::LoadAll
     * 
     * returns array containing all donors
     *
     * @return array[ Donor ]
     */
    public static function LoadAll() {
        $donors = [];

        $query = mysql_query(
            "SELECT * FROM charidy_donors ORDER BY first_name, last_name;"
        );
        while ( $row = mysql_fetch_assoc( $query ) ){
            // if there's no phone number, see if it's connected to parent account and get phone from parent account
            if ( empty( $row['phone'] ) ) {
                if ( !empty( $row['parent_admin_id'] ) ) {
                    $parent_id = $row['parent_admin_id'];
                    $parent_sql = "select * from admins where admin_id = " . $parent_id;
                    $parent_result = mysql_query( $parent_sql );
                    $parent_row = mysql_fetch_assoc( $parent_result );
                    $row['mashpiaPhone'] = $parent_row['phone3'] ? $parent_row['phone3'] : ( $parent_row['phone4'] ? $parent_row['phone4'] : (
                                    $parent_row['phone1'] ? $parent_row['phone1'] : ( $parent_row['phone2'] ? $parent_row['phone2'] : '' ) ) );
                }
            }
            $donors[] = self::LoadFromRow( $row );
        }

        return $donors;
    }

    /**
     * ::loadFromRow function
     * 
     * Creates and returns a Donor instance from a DBS row
     *
     * @param array $row
     * @return Donor
     */
    public static function LoadFromRow( $row ){
        $instance = new self(); // create instance to return
        // load them all in raw for now
        foreach ($row as $prop => $val) {
            $instance->{ $prop } = $val;
        }
        // return a new instance
        return $instance;
    }

    /** INSTANCE FUNCTIONS */
    /**
     * fullName
     * 
     * returns the Donors full name in lower case
     *
     * @return string
     */
    public function fullName() {
        return strtolower( $this->first_name . " " . $this->last_name );
    }

    /**
     * phoneNumber
     * 
     * returns a formatted version of their phone number
     *
     * @return string
     */
    public function phoneNumber() {
        return preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $this->phone). "\n";
    }
    
    /**
     * mashpiaPhoneNumber
     * 
     * returns a formatted version of their mashpia phone number
     *
     * @return string
     */
    public function mashpiaPhoneNumber() {
        return preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $this->mashpiaPhone). "\n";
    }

    /**
     * getDonated
     * 
     * Check if a donor donated in a given year
     *
     * @param string $year
     * @return boolean
     */
    public function getDonated( $year = false ) {
        if ( $year )
            $year = mysql_real_escape_string( $year );
        // pull from cache if we can
        if ( isset( $this->donations[ $year ] ) )
            return true;

        $query = mysql_query(
             " SELECT SUM(amount) as amount, donation_date, year FROM charidy_donations "
            ." WHERE donor_id = '" . $this->donor_id . "'"
            .( $year ? " AND year = $year" : "" )
            ." GROUP BY donor_id, year;"
        );
        // return true or false depending on if we have any information
        if ( mysql_num_rows( $query ) > 0 ){
            while ( $row = mysql_fetch_assoc( $query ) ){
                $row['amount'] = intval( $row['amount'] );
                $this->donations[ $row['year'] ] = $row;
            }
            return true;
        };
        return false;
    }

    /**
     * onShabbaton
     * 
     * Returns the total number of kids this donor had on the shabbaton
     *
     * @param string $year
     * @return int
     */
    public function onShabbaton( $year ) {
        $year = mysql_real_escape_string( $year );
        // return from the cache if we can
        if ( isset( $this->on_shabbaton[ $year ] ) )
            return $this->on_shabbaton[ $year ];
        // set this year to an empty array.
        $this->on_shabbaton[ $year ] = [];
        
        $query = mysql_query(
             " SELECT first, last FROM th_chidon "
            ." JOIN users USING (user_id) "
            ." JOIN admin_auths ON id = user_id AND auth = 'user' "
            ." WHERE admin_id = '" . $this->parent_admin_id . "' "
            ." AND year = '$year' "
            ." AND date_paid IS NOT NULL; "
        );
        while ( $row = mysql_fetch_assoc( $query ) ) {
            $this->on_shabbaton[ $year ][] = $row;
        }

        return $this->on_shabbaton[ $year ];
    }

    public function getCaller( $year ) {
        $year = mysql_real_escape_string( $year );

        $query = mysql_query(
             " SELECT charidy_callers.* FROM charidy_callers "
            ." JOIN charidy_donors_callers USING (charidy_caller_id) "
            ." WHERE donor_id = '" . $this->donor_id ."' "
            ." AND year = '" . $year . "' LIMIT 1;"
        );

        if ( mysql_num_rows( $query ) > 0 ){
            $row = mysql_fetch_assoc( $query );
            $this->caller = Caller::LoadFromRow( $row );
            return true;
        };
        return false;
    }
}