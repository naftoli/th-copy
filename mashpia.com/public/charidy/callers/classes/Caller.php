<?php // dependencies
require_once( dirname(__FILE__) . "/Donor.php" );

class Caller {

    /** INSTANCE VARIABLES */
    public $charidy_caller_id;
    public $first;
    public $last;

    public $donors = [];

    /** STATIC FUNCTIONS */
    /**
     * ::Load function
     * 
     * Loads a Caller from the database and returns an instance of the Caller class.
     * 
     * Throws 'Caller Not Found' exception if charidy_caller_id is invalid
     *
     * @param int $charidy_caller_id
     * @return Caller
     * @throws Exception 'Caller Not Found'
     */
    public static function Load( $charidy_caller_id ) {
        $charidy_caller_id = mysql_real_escape_string( $charidy_caller_id );
        $query = mysql_query(
            "SELECT * FROM charidy_callers where charidy_caller_id = $charidy_caller_id;"
        );
        if ( mysql_num_rows( $query ) > 0 ) {
            $row = mysql_fetch_assoc( $query );
            return self::LoadFromRow($row);
        } else {
            throw new Exception('Caller Not Found');
        }
    }

    public static function LoadAll() {
        $callers = [];

        $query = mysql_query(
            "SELECT * FROM charidy_callers ORDER BY first, last;"
        );
        while ( $row = mysql_fetch_assoc( $query ) ){
            $callers[] = self::LoadFromRow( $row );
        }

        return $callers;
    }

    /**
     * ::LoadFromRow function
     * 
     * Creates and returns a Caller instance from a DBS row
     *
     * @param array $row
     * @return Caller
     */
    public static function LoadFromRow( $row ){
        $instance = new self(); // create instance to return

        $instance->charidy_caller_id = $row['charidy_caller_id'];
        $instance->first = $row['first'];
        $instance->last  = $row['last'];

        return $instance;
    }

    /** INSTANCE FUNCTIONS */
    /**
     * load_donors function
     * 
     * Loads all the donors tied to this caller
     *
     * @param string $year
     * @return void
     */
    public function loadDonors( $year ) {
        $year = mysql_real_escape_string( $year );
        // get all the donor info for the year provided
        $query = mysql_query(
             " SELECT cd.* FROM mashpia_charidy.donors cd "
            ." JOIN charidy_donors_callers cdc ON cd.donor_id = cdc.donor_id AND year = $year "
            ." WHERE charidy_caller_id = " . $this->charidy_caller_id . " "
            ." ORDER BY cd.last_name, cd.first_name;"
        );
        // load and create instances for each donor
        while ( $row = mysql_fetch_assoc( $query ) ) {
            try {
                $this->donors[] = Donor::LoadFromRow( $row ); // create a new Donor
            } catch (Exception $e) {
                // do nothing (not added to array...)
            }
        }

        return $this->donors;
    }

    /**
     * fullName
     * 
     * returns the Donors full name in lower case
     *
     * @return string
     */
    public function fullName() {
        return strtolower( $this->first . " " . $this->last );
    }
}