<?php
class ParseDonation5778 
{
    private $info;
    private $donation;
    private $year;
    private $qrys;

    public function __construct( $donation_info, $donation_json = array() ) {
        $this->info = $donation_info;
        $this->donation = $donation_json;
        $this->year = 5778;
        $this->error = '';
    }

    public function createDonation() {
        //echo "<pre>"; print_r( $this->info ); echo "</pre>";
        //echo "<pre>"; print_r( $this->donation ); echo "</pre>"; 
        // set donation id to 0 b/c they don't correspond to correct id anymore
        if ( !empty( $this->donation ) ) $this->donation->donor_id = 0;       
        if ( !empty( $this->donation ) && $this->donation->donor_id == 0 
            && isset( $this->donation->children ) && !empty( $this->donation->children ) ) {
            $this->findDonorFromChildren();
        }

        // try to find donor based on phone / email / name 
        if ( !empty( $this->donation ) && $this->donation->donor_id == 0 ) $this->findDonor();

        if ( isset( $this->donation->donor_id ) && $this->donation->donor_id > 0 ) {
            $this->createFromDonorID();
        } else {
            $this->createFromInfo();
        }
    }

    public function getError() {
        return $this->error;
    }

    private function findDonorFromChildren() {
        // if we have children amounts, see if we can find parent id / donor id
        if ( isset( $this->donation->children ) && !empty( $this->donation->children ) ) {
            foreach ($this->donation->children as $child) {
                // see if we can find the user id / parent id with the info that we have
                $user_id = $child->user_id;
                if (is_numeric( $user_id )) {
                    // if we have a valid user id then we can find parent id 
                    if ($this->findParentByUserID( $user_id )) {
                        // make sure we have the donor id based on the parent id
                         $this->findDonorByParentID();
                        // fix email of donor id to match email of current donor / donation
                        $this->fixParentDonor();
                        break;
                    }
                }
                
                // if we don't have a user_id check by name and school
                // break up name into last name, first name
                if ( $child->school_id && $child->name ) {
                    $school_id = $child->school_id;
                    $name = $this->getFirstLast( $child->name );
                    $first_name = $name['first'];
                    $last_name = $name['last'];
                    if ($this->findParentByChildName( $first_name, $last_name, $school_id )) {
                        // make sure we have the donor id based on the parent id
                        $this->findDonorByParentID();
                        // fix email of donor id to match email of current donor / donation
                        $this->fixParentDonor();
                        break;
                    }
                } else if ( $child->picture ) {
                    // then try by picture
                    $picture = $child->picture;
                    if ($this->findParentByUserPicture( $picture )) {
                        // make sure we have the donor id based on the parent id
                        $this->findDonorByParentID();
                        // fix email of donor id to match email of current donor / donation
                        $this->fixParentDonor();
                        break;
                    }
                }
            }
        }
    }

    private function createFromDonorID() {
        $donor_id = mysql_real_escape_string( $this->donation->donor_id );
        $parent_id = mysql_real_escape_string( $this->donation->parent_id );
        $amount = mysql_real_escape_string( $this->donation->amount );
        $dedication_name = isset( $this->donation->dedication_name ) ? mysql_real_escape_string( $this->donation->dedication_name ) : '';
        $dedication_text = isset( $this->donation->dedication_text ) ? mysql_real_escape_string( $this->donation->dedication_text ) : '';
        $dedication_user_id = isset( $this->donation->dedication_user_id ) ? mysql_real_escape_string( $this->donation->dedication_user_id ) : 0;
        $donation_date = $this->extractDate();

        // find out if children have user_id and amount so that we can make separate donation entry for them 
        // if not, update amount to total amount given and only make one donation entry
        // also check that at least one child has an amount given for him / her
        $children_donations = true;
        $children_with_amounts = 0;
        $children = $this->donation->children;
        if (!empty( $children )) {
            foreach ($children as $child) {                    
                // keep track of how many children have an amount given for him / her
                if ($child->amount > 0) {
                    $children_with_amounts++;
                }
            }                
        } else {
            $children_donations = false;
        }

        // if (!$children_donations) {
        //     $amount = mysql_real_escape_string( $this->donation->total_donation_amount );
        //     if ($this->donation->total_donation_amount != $this->donation->amount) {
        //         echo "<br />Discrepancy found: " . $this->donation->donor_id . "<br /><br />";
        //         echo "<pre>"; print_r( $this->donation ); echo "</pre>";
        //         return;
        //     }
        // }

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
            $sql = "insert into mashpia_charidy.donations 
                    set donor_id = " . $donor_id . ", 
                    year = " . $this->year . ", 
                    amount = " . $amount . ", 
                    donation_date = '" . $donation_date . "', 
                    user_id = " . $dedication_user_id . ", 
                    dedication_name = '" . addslashes( $dedication_name ) . "', 
                    dedication_text = '" . addslashes( $dedication_text ) . "', 
                    child_only_donation = 0";
            //echo $sql . "<br />";
            if (!mysql_query( $sql )) {
                $this->error = $sql . "<br />" . mysql_error();   
            }
        }

        if ($children_donations && $children_with_amounts) {
            foreach ($children as $child) {
                $user_id = mysql_real_escape_string( $child->user_id );
                $amount = mysql_real_escape_string( $child->amount );
                if ($amount > 0 && is_numeric($user_id)) {
                    $sql = "insert into mashpia_charidy.donations 
                            set donor_id = " . $donor_id . ", 
                            year = " . $this->year . ", 
                            amount = " . $amount . ", 
                            donation_date = '" . $donation_date . "', 
                            user_id = " . $user_id . ", 
                            dedication_name = '" . addslashes( $dedication_name ) . "', 
                            dedication_text = '" . addslashes( $dedication_text ) . "', 
                            child_only_donation = 1";
                    //echo $sql . "<br />";
                    if (!mysql_query( $sql )) {
                        $this->error = $sql . "<br />" . mysql_error();   
                    }
                }
            }
        }
    }

    private function createFromInfo() {
        // create donor profile from info
        // separate first name from last name
        $name = $this->getFirstLast( $this->info->name );
        $first_name = $name['first'];
        $last_name = $name['last'];
        
        $address = $this->info->address1 . ' ' . $this->info->address2;
        $city = $this->info->city;
        $state = $this->info->state;
        $zip = $this->info->zip;
        $country = $this->info->country;
        $phone = $this->info->phone;
        $email = $this->info->email;

        // we can't create a donor if no email or invalid email is given
        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
            $donor_id = 0;

            // check if email already exists in donor database and use that id
            $sql = "select donor_id from mashpia_charidy.donors where email = '" . $this->info->email . "'";
            $result = mysql_query( $sql );
            if (mysql_num_rows( $result) > 0) {
                $row = mysql_fetch_assoc( $result );
                $donor_id = $row['donor_id'];
            }

            if ($donor_id == 0) {
                // create donor in database
                $sql = "insert into mashpia_charidy.donors 
                        set first_name = '" . addslashes( $first_name ) . "', 
                        last_name = '" . addslashes( $last_name ) . "', 
                        address = '" . addslashes( $address ) . "', 
                        city = '" . addslashes( $city ) . "', 
                        state = '" . $state . "', 
                        zip = '" . $zip . "',
                        phone = '" . addslashes( $phone ) . "', 
                        email = '" . $email . "'";
                //echo $sql . "<br />";
                if (mysql_query( $sql )) {
                    $donor_id = mysql_insert_id();
                } else {
                    $this->error = $sql . "<br />" . mysql_error();   
                }
            }

            // find out user_id if we were given a serial number
            $user_id = 0;
            if (isset( $this->info->dedication_user_id ) && $this->info->dedication_user_id > 0) {
                $sql = "select user_id from users where user_serial = " . mysql_real_escape_string( $this->info->dedication_user_id );
                $result = mysql_query( $sql );
                if (mysql_num_rows( $result ) > 0) {
                    $row = mysql_fetch_assoc( $result );
                    $user_id = $row['user_id'];
                }
            }

            // now add donation
            $amount = mysql_real_escape_string( $this->info->donation_amount );
            $dedication_name = isset( $this->donation->dedication_name ) ? mysql_real_escape_string( $this->donation->dedication_name ) : '';
            $dedication_text = isset( $this->donation->dedication_text ) ? mysql_real_escape_string( $this->donation->dedication_text ) : '';
            $donation_date = $this->extractDate();
            
            $sql = "insert into mashpia_charidy.donations 
                    set donor_id = " . $donor_id . ", 
                    year = " . $this->year . ", 
                    amount = " . $amount . ", 
                    donation_date = '" . $donation_date . "', 
                    user_id = " . $user_id . ", 
                    dedication_name = '" . addslashes( $dedication_name ) . "', 
                    dedication_text = '" . addslashes( $dedication_text ) . "', 
                    child_only_donation = 0";
            //echo $sql . "<br />";
            if (!mysql_query( $sql )) {
                $this->error = $sql . "<br />" . mysql_error();   
            }
        }
    }

    private function getFirstLast( $name ) {
        // separate first name from last name
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

        return array(
            'first' =>  $first_name, 
            'last'  =>  $last_name
        );
    }

    private function findParentByUserID( $user_id ) {
        $sql = "select admin_id from admin_auths where auth = 'user' and id = " . mysql_real_escape_string( $user_id );
        $result = mysql_query( $sql );
        if (mysql_num_rows( $result ) > 0) {
            $row = mysql_fetch_assoc( $result );
            $this->donation->parent_id = $row['admin_id'];
            return true;
        }
        return false;
    }

    private function findParentByChildName( $first_name, $last_name, $school_id ) {
        // first try by using first name, last name, and school id
        $sql = "select user_id from users 
                where last ='" . mysql_real_escape_string( $last_name ) . "' and first = '" . mysql_real_escape_string( $first_name ) . "'";
        if ($school_id > 0) $sql .= " and school_id = " . mysql_real_escape_string( $school_id );
        $result = mysql_query( $sql );
        // see if we have any results and that there's only one result
        $numRows = mysql_num_rows( $result );
        if ($numRows == 1) {
            $row = mysql_fetch_assoc( $result );
            $user_id = $row['user_id'];
            return $this->findParentByUserID( $user_id );
        }
        return false;
    }

    private function findParentByUserPicture( $picture ) {
        // find out if picture is a thumb or a mobile pic
        if ($pos = strpos('/thumbs/', $picture) !== false) {
            $mobile_pic = substr($picture, $pos);
            //echo $mobile_pic . "<br />";
            $sql = "select user_id from users where mobile_pic = '" . $mobile_pic . "'";
            $result = mysql_query( $sql );
            if (mysql_num_rows( $result ) == 1) {
                $row = mysql_fetch_assoc( $result );
                return $this->findParentByUserID( $row['user_id'] );
            }
        } else if ($pos = strpos('/reg/', $picture) !== false) {
            $thumb = substr($picture, $pos);
            //echo $thumb;
            $sql = "select user_id from users u 
                    join thumbs t on t.file_id = u.user_photo_id 
                    where t.thumb = '" . $thumb . "'";
            $result = mysql_query( $sql );
            if (mysql_num_rows( $result )) {
                $row = mysql_fetch_assoc( $result );
                return $this->findParentByUserID( $row['user_id'] );
            }
        }
        return false;
    }

    private function fixParentDonor() {
      // forget this
      return;
        // find out email that we have on file and update it to this email in admins as well as donors
        $sql = "select admin_email from admins where admin_id = " . $this->donation->parent_id;
        //echo $sql . "<br />";
        $result = mysql_query( $sql );
        if ($row = mysql_fetch_assoc( $result )) {
            $oldEmail = $row['admin_email'];

            // find out donor id based on old email
            $sql = "select * from mashpia_charidy.donors where email = '" . $oldEmail . "'";
            $result = mysql_query( $sql );
            if ($row = mysql_fetch_assoc( $result )) {
                if ($this->donation->parent_id == $row['parent_admin_id']) {
                    $this->donation->donor_id = $row['donor_id'];
                    $sql = "update mashpia_charidy.donors set email = '" . $this->info->email . "' where donor_id = " . $this->donation->donor_id;
                    //echo $sql . "<br />";
                    mysql_query( $sql );
                    $sql = "update admins set admin_email = '" . $this->info->email . "' where admin_id = " . $this->donation->parent_id;
                    //echo $sql;
                    mysql_query( $sql );
                }
            }
        }
    }

    private function findDonorByParentID() {
        $sql = "select donor_id from mashpia_charidy.donors where parent_admin_id = " . mysql_real_escape_string( $this->donation->parent_id );
        $result = mysql_query( $sql );
        if ($row = mysql_fetch_assoc( $result )) {
            $this->donation->donor_id = $row['donor_id'];
        }
    }

    private function findDonor() {
      $emailExceptions = [
        'accounting@gmail.com',
        'accounting@tzivoshashem.org',
        'chayazirkind@gmail.com',
        'kaplanmussi@gmail.com',
        'shimmy@jcm.museum',
        'shimmy@tzivoshashem.org',
        'sholomber@jcm.museum'
      ];
      $phone = $this->info->phone;
      $name = $this->getFirstLast( $this->info->name );
      $sql = "select donor_id from mashpia_charidy.donors where phone = '" . $phone . "' or (first_name = '" . $name['first'] . "' and last_name = '" . $name['last'] . "')";
      if ( $this->info->email && !in_array( $this->info->email, $emailExceptions ) ) $sql .= ", or email = '" . $this->info->email . "'";
      $result = mysql_query( $sql );
      if ( mysql_num_rows( $result ) > 0 ) {
        $row = mysql_fetch_assoc( $result );
        $this->donation->donor_id = $row['donor_id'];
      }
    }

    private function extractDate() {
        if ( $this->info->donation_date > 0 || isset( $this->donation->date_time ) ) {
            $date_to_parse = $this->info->donation_date > 0 ? $this->info->donation_date : $this->donation->date_time;
            $pos = strpos( $date_to_parse, '.' );
            $date = substr( $date_to_parse, 0, $pos );
            $donation_date = str_replace( 'T', ' ', $date );
        } else {
            $donation_date = '2018-05-01 00:00:00';
        }
        return mysql_real_escape_string( $donation_date );
    }
}
?>