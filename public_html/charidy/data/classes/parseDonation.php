<?php
require '../../class.globalSettings.php';

class ParseDonation 
{
    private $donation;
    private $info;
    private $year;
    private $qrys;

    public function __construct( $donation_json, $donation_info = array() ) {
        $this->donation = $donation_json;
        $this->info = $donation_info;
        $this->year = GlobalSettings::getCharidyYear();
        $this->qrys = array();
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
            // find out if children have user_id and amount so that we can make separate donation entry for them 
            // if not, update amount to total amount given and only make one donation entry
            // also check that at least one child has an amount given for him / her
            $children_donations = true;
            $children_with_amounts = 0;
            $children = $this->donation->children;
            if (!empty( $children )) {
                foreach ($children as $child) {
                    if (isset( $child->user_id )) {
                        $user_id = mysql_real_escape_string( $child->user_id );
                        if (!is_numeric( $user_id )) {
                            echo "Missing User ID: " . $user_id . "<br /><br />";
                            echo "<pre>"; print_r( $child ); echo "</pre>";
                            $children_donations = false;
                            break;
                        } else {
                            // keep track of how many children have an amount given for him / her
                            if ($child->amount > 0) {
                                $children_with_amounts++;
                            }
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
                if ($this->donation->total_donation_amount != $this->donation->amount) {
                    echo "<br />Discrepancy found: " . $this->donation->donor_id . "<br /><br />";
                    echo "<pre>"; print_r( $this->donation ); echo "</pre>";
                    return;
                }
            }

            // if we only have 1 child being paid for, and amount on parent object equals amount on child object
            // only use child object to create donation
            $child_only = false;
            if ($children_donations && $children_with_amounts == 1) {
                // find child with amount and check that amount equals amount on parent object 
                // if it does only insert child donation
                foreach ($children as $child) {
                    if ($child->amount > 0) {
                        if ($amount == $child->amount) {
                            $child_only = true;
                            break;
                        }
                    }
                }
            }
            
            if (!$child_only && $amount > 0) {
                // insert parent donation into donations table
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
            }

            if ($children_donations && $children_with_amounts) {
                foreach ($children as $child) {
                    $user_id = mysql_real_escape_string( $child->user_id );
                    $amount = mysql_real_escape_string( $child->amount );
                    if ($amount > 0) {
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
            }
        } else {
            // first create donor profile from info
            $donor_id = 0;         
            
            // separate first name from last name
            $name = $this->info->name;
            $name_array = explode(' ', $name);
            $number = count( $name_array );
            if ($number > 1) {
                $first_name = '';
                $end = $number - 1;
                $last_name = $name_array[$end];
                for ($i = 0; $i < $end; $i++) {
                    $first_name .= trim($name_array[$i]);
                    if ($i != ($end - 1)) {
                        // add space
                        $first_name .= ' ';
                    }
                }
            } else {
                $first_name = '';
                $last_name = $name_array[0];
            }
            
            $address = $this->info->address1 . ' ' . $this->info->address2;
            $city = $this->info->city;
            $state = $this->info->state;
            $zip = $this->info->zip;
            $country = $this->info->country;
            $phone = $this->info->phone;
            $email = $this->info->email;

            // we can't create a donor if no email is given
            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {


                // check if email already exists in donor database and use that id
                $sql = "select donor_id from charidy_donors where email = '" . $this->info->email . "'";
                $result = mysql_query( $sql );
                if (mysql_num_rows( $result) > 0) {
                    $row = mysql_fetch_assoc( $result );
                    $donor_id = $row['donor_id'];
                }

                if ($donor_id == 0) {
                    // create donor in database
                    $sql = "insert into charidy_donors 
                            set first_name = '" . addslashes( $first_name ) . "', 
                            last_name = '" . addslashes( $last_name ) . "', 
                            address = '" . addslashes( $address ) . "', 
                            city = '" . addslashes( $city ) . "', 
                            state = '" . $state . "', 
                            zip = '" . $zip . "',
                            phone = '" . addslashes( $phone ) . "', 
                            email = '" . $email . "'";
                    echo $sql . "<br />";
                    $donor_id = 1111;
                }

                // find out user_id if we were given a serial number
                if ($dedication_user_id > 0) {
                    $sql = "select user_id from users where user_serial = " . $dedication_user_id;
                    $result = mysql_query( $sql );
                    if (mysql_num_rows( $result ) > 0) {
                        $row = mysql_fetch_assoc( $result );
                        $dedication_user_id = $row['user_id'];
                    }
                }

                // now add donation
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
            } else {
                return;
            }
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