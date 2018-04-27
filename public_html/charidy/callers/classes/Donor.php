<?php

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

    public $donations = [];

    private $on_shabbaton_cache = false;

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
     * ::loadFromRow function
     * 
     * Creates and returns a Caller instance from a DBS row
     *
     * @param array $row
     * @return Caller
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
     * returns the Donors full name
     *
     * @return string
     */
    public function fullName() {
        return $this->first_name . " " . $this->last_name;
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
     * donatedIn
     * 
     * Check if a donor donated in a given year
     *
     * @param string $year
     * @return boolean
     */
    public function donatedIn( $year ) {
        $year = mysql_real_escape_string( $year );
        // pull from cache if we can
        if ( isset( $this->donations[ $year ] ) )
            return true;

        $query = mysql_query(
             " SELECT donation, with_matching, donation_date FROM charidy "
            ." WHERE ( email = '". $this->email ."' OR phone = '" . $this->phone . "')"
            ." AND year = $year;"
        );
        // return true or false depending on if we have any information
        if ( mysql_num_rows( $query ) > 0 ){
            $row = mysql_fetch_assoc( $query );
            $this->donations[ $year ] = $row;
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
        if ( $this->on_shabbaton_cache !== false )
            return $this->on_shabbaton_cache;
        
        $query = mysql_query(
             " SELECT COUNT(*) as total FROM th_chidon "
            ." JOIN users USING (user_id) "
            ." JOIN admin_auths ON id = user_id AND auth = 'user' "
            ." WHERE admin_id = '" . $this->parent_admin_id . "' "
            ." AND year = '$year' "
            ." AND date_paid IS NOT NULL; "
        );

        $this->on_shabbaton_cache = mysql_fetch_assoc( $query )['total'];

        return $this->on_shabbaton_cache;
    }
}