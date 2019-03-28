<?php
require dirname(__FILE__) . "/../../db.php";

$info = array();
$sql = "select * from charidy where year = 5777";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $info[] = $row;
}

foreach ($info as $row) {
    $email = $row['email'];
    $parent_id = $row['parent_admin_id'];
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
        $sql = "select * from charidy_donors where email = '" . $email ."'";
        $result = mysql_query( $sql );
        if (mysql_num_rows( $result ) == 0) {
            // insert into charidy donors
            if ($row['parent_admin_id'] > 0) {
                $parentSql = "select * from admins where admin_id = " . $row['parent_admin_id'];
                $parentResult = mysql_query( $parentSql );
                $parentRow = mysql_fetch_assoc($parentResult);
                $insert = "insert into charidy_donors
                            set first_name = \"" . $parentRow['first'] . "\",
                            last_name = \"" . $parentRow['last'] . "\",
                            address = \"" . $parentRow['admin_address1'] . "\",
                            city = '" . $parentRow['admin_city'] . "',
                            state = '" . $parentRow['admin_state'] . "',
                            zip = '" . $parentRow['admin_postal'] . "',
                            country = '" . $parentRow['admin_country'] . "',
                            phone = '" . $row['phone'] . "',
                            email = '" . $email . "',
                            needs_call = 1";
            } else {
                $insert = "insert into charidy_donors
                            set first_name = '" . $row['first_name'] . "',
                            last_name = '" . $row['last_name'] . "',
                            phone = '" . $row['phone'] . "',
                            email = '" . $row['email'] . "',
                            needs_call = 1";
            }
            if (mysql_query( $insert )) {
                $id = mysql_insert_id();
                //echo $insert . "<br />";
                //$id = 56777665;
                $sqlDonation = "insert into charidy_donations
                                set donor_id = " . $id . ",
                                amount = " . $row['donation'] . ",
                                year = 5777";
                //echo $sqlDonation . "<br /><br />";
                mysql_query( $sqlDonation );
            }
        } else {
            $donor = mysql_fetch_assoc( $result );
            $donor_id = $donor['donor_id'];
            // make sure needs_call is set to true
            mysql_query("update charidy_donors set needs_call = 1 where donor_id = " . $donor_id);
            // check that donation has been entered into charidy_donations
            $sqlDonation = "select * from charidy_donations where donor_id = " . $donor_id . " and year = 5777";
            $donationResult = mysql_query( $sqlDonation );
            if (mysql_num_rows( $donationResult ) == 0) {
                $insertDonation = "insert into charidy_donations
                                    set donor_id = " . $donor_id . ",
                                    amount = " . $row['donation'] . ",
                                    year = 5777";
                //echo $insertDonation . "<br />";
                mysql_query( $insertDonation );
            }
        }
    }
}
echo "done.";