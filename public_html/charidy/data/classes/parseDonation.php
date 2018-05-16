<?php
require '../../class.globalSettings.php';

class ParseDonation 
{
    private $donation;
    private $year;
    private $qrys;
    private $personalInfo;

    public function __construct( $donation, $personalInfo = array() ) {
        $this->donation = $donation;
        $this->year = GlobalSettings::getCharidyYear();
        $this->qrys = array();
        $this->personalInfo = $personal;
    }

    public function parse() {
        print_r( $this->donation );
    }

    public function createDonation() {
        $donor_id = mysql_real_escape_string( $this->donation->donor_id );
        $parent_id = mysql_real_escape_string( $this->donation->parent_id );
        $amount = mysql_real_escape_string( $this->donation->amount );
        $dedication_name = isset( $this->donation->dedication_name ) ? mysql_real_escape_string( $this->donation->dedication_name ) : '';
        $dedication_text = isset( $this->donation->dedication_text ) ? mysql_real_escape_string( $this->donation->dedication_text ) : '';
        $dedication_user_id = isset( $this->donation->dedication_user_id ) ? mysql_real_escape_string( $this->donation->dedication_user_id ) : 0;
        $donation_date = mysql_real_escape_string( $this->donation->date_time );
        // fix donation date for correct format for mysql
        $pos = strpos( $donation_date, '.' );
        $date = substr( $donation_date, 0, $pos );
        $donation_date = str_replace( 'T', ' ', $date );

        if ($this->donation->donor_id > 0) {
            // find out if children have user_id so that we can make separate donation entry for them 
            // if not, update amount to total amount given and only make one donation entry
            $children_donations = true;
            $children = $this->donation->children;
            if (!empty( $children )) {
                foreach ($children as $child) {
                    if (isset( $child->user_id )) {
                        $user_id = mysql_real_escape_string( $child->user_id );
                        if (!is_numeric( $user_id )) {
                            echo "Missing User ID: " . $user_id . "<br />";
                            $children_donations = false;
                            break;
                        }
                    } else {
                        $children_donations = false;
                        break;
                    }
                }
            } else {
                $children_donations = false;
            }

            if (!$children_donations) {
                $amount = mysql_real_escape_string( $this->donation->total_donation_amount );
            }
            
            // insert into donations table
            $sql = "insert into charidy_donations 
                    set donor_id = " . $donor_id . ", 
                    year = " . $this->year . ", 
                    amount = " . $amount . ", 
                    donation_date = '" . $donation_date . "', 
                    user_id = " . $dedication_user_id . ", 
                    dedication_name = '" . addslashes( $dedication_name ) . "', 
                    dedication_text = '" . addslashes( $dedication_text ) . "', 
                    child_only_donation = 0";
            echo $sql . "<br />";

            if ($children_donations) {
                foreach ($children as $child) {
                    $user_id = mysql_real_escape_string( $child->user_id );
                    $amount = mysql_real_escape_string( $child->amount );
                    $sql = "insert into charidy_donations 
                            set donor_id = " . $donor_id . ", 
                            year = " . $this->year . ", 
                            amount = " . $amount . ", 
                            donation_date = '" . $donation_date . "', 
                            user_id = " . $user_id . ", 
                            dedication_name = '" . addslashes( $dedication_name ) . "', 
                            dedication_text = '" . addslashes( $dedication_text ) . "', 
                            child_only_donation = 1";
                    echo $sql . "<br />";
                }
            }
        } else {
            // first create donor profile
            $first_name = ;
            $last_name = ;
            $address = ;
            $city = ;
            $state = ;
            $zip = ;
            $country = ;
            $phone = ;
            $email = ;
        }
    }

    public function saveDonation() {
        $errors = array();

        mysql_query('set autocommit = 0');
        mysql_query('begin');
        foreach ($this->qrys as $qry) {
            if (!mysql_query( $qry )) {
                $errors[] = $sql . "<br />" . mysql_error() . "<br />";
                break;
            }
        }
        if (empty( $errors )) {
            mysql_query('commit');
        } else {
            mysql_query('rollback');
            foreach ($errors as $error) {
                echo $error;
            }
        }
        mysql_query('set autocommit=1');
    }
}
?>