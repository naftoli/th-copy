<?php // dependencies
require_once( dirname(__FILE__) . "/Donor.php" );

// this is a class that is designed to look like a Caller but returns the info for no callers
class NoCaller {

    public $donors;

    public function fullName() {
        return "N/A";
    }

    public function loadDonors( $year ){
        $year = mysql_real_escape_string( $year );
        // get all the donor info for the year provided
        $query = mysql_query(
             " SELECT cd.* FROM mashpia_charidy.donors cd "
            ." LEFT JOIN charidy_donors_callers cdc ON cd.donor_id = cdc.donor_id AND year = $year "
            ." WHERE charidy_caller_id IS NULL "
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

}