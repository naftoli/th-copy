<?php
/*
 * Populate Donors Database based on charidy table as well as admins table
 */
require '../../db.php';

function getAdmins() {
    // create list of admins to make donor accounts from
    // all admins with unique email will have their own donor account
    // if same email address exists more than once, find the one that has children associated with it
    // if multiple accounts with same email has children associated with it, choose most recent one
    
    $donors = array();
    $admins = array();    
    $sql = "select * from admins where admin_email is not null";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc( $result )) {
        // make sure email is valid
        $email = $row['admin_email'];
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // make sure email isn't already in database
            $sql2 = "select * from donors where email = '" . $email . "'";
            $result2 = mysql_query( $sql2 );
            if (mysql_num_rows( $result2 ) == 0) {
                $admins[$email][] = $row;
            }
        }
    }

    foreach ($admins as $email => $rows) {
        if (count($rows) > 1) {
            $info = array();
            $children = array();
            foreach ($rows as $row) {
                $admin_id = $row['admin_id'];
                // make sure to keep copy of info
                $info[$admin_id] = $row;
                // find out which id's have children connected to it
                $sqlChildren = "select * from admin_auths where auth = 'user' and admin_id = " . $admin_id . " order by admin_id";
                $resultChildren = mysql_query( $sqlChildren );
                if (mysql_num_rows( $resultChildren )) {
                    while ($rowChildren = mysql_fetch_assoc( $resultChildren )) {
                        $children[$admin_id][] = $rowChildren['id'];
                    }
                }
            }
            
            // find out how many admin ids have children connected to it
            if (!empty($children)) {
                $ids = array_keys($children);
                // if only one admin id has children connected, then make that id the donor id
                if (count($ids) == 1) {
                    $donors[] = $info[$ids[0]];
                } else {
                    // find highest id and make it the donor id
                    $donors[] = $info[$ids[count($ids)-1]];
                }
            } else {
                // find out if there's a school or class connected to admin
                $foundAuth = false;
                foreach ($rows as $row) {
                    $sql = "select * from admin_auths where auth in ('school', 'class') admin_id = " . $row['admin_id'];
                    $result = mysql_query( $sql );
                    if (mysql_num_rows( $result )) {
                        $donors[] = $info[$row['admin_id']];
                        $foundAuth = true;
                        break;
                    }
                }
                
                if (!$foundAuth) {
                    // find highest id and make it the donor id
                    $ids = array_keys($info);
                    $donors[] = $info[$ids[count($ids)-1]];
                }
            }
        } else {
            $donors[] = $rows[0];
        }
    }

    return $donors;
}

function getDonations() {
    $donations = array();
    $sql = "select * from charidy";
    $result = mysql_query( $sql );
    while ($row = mysql_fetch_assoc( $result )) {
        $donations[] = $row;
    }
    
    return $donations;
}

function createDonorFromAdmin( $admin ) {
    $phone = $admin['phone3'] ? $admin['phone3'] : ($admin['phone4'] ? $admin['phone4'] : ($admin['phone1'] ? $admin['phone1'] : ($admin['phone2'] ? $admin['phone2'] : '')));
    $sql = "insert IGNORE into charidy_donors
            set first_name = '" . $admin['first'] . "',
            last_name = '" . $admin['last'] . "',
            address = \"" . $admin['admin_address1'] . "\",
            city = '" . $admin['admin_city'] . "',
            state = '" . $admin['admin_state'] . "',
            zip = '" . $admin['admin_postal'] . "',
            country = '" . $admin['admin_country'] . "',
            phone = '" . $phone . "',
            email = '" . trim($admin['admin_email']) . "',
            parent_admin_id = " . $admin['admin_id'];
    echo $sql . "<br />";
    $id = 1111111;
}

function createDonorFromDonation( $donation ) {
    // find out if email already exist from parent accounts or from entered donation
    $email = trim($donation['email']);
    $sql = "select donor_id from charidy_donors where email = '" . $email . "'";
    $result = mysql_query( $sql );
    if (mysql_num_rows( $result )) {
        $row = mysql_fetch_assoc( $result );
        $id = $row['donor_id'];
    } else {
        $sql = "insert IGNORE into charidy_donors
                set first_name = '" . $donation['fname'] . "',
                last_name = '" . $donation['lname'] . "',
                address = \"" . $donation['address'] . "\"
                city = '" . $donation['city'] . "',
                state = '" . $donation['state'] . "',
                zip = '" . $donation['zip'] . "',
                country = '" . $donation['country'] . "',
                phone = '" . $donation['phone'] . "',
                email = '" . $email . "'";
        echo $sql . "<br />";
        $id = 1111111;
    }
    
    $sql = "insert into charidy_donations
            set donor_id = " . $id . ",
            year = " . $donation['year'] . ",
            amount = " . $donation['amount'] . ",
            donation_date = '" . $donation['donation_date'] . "'";
    echo $sql . "<br />";
}

$num = 0;
$admins = getAdmins();
foreach ($admins as $admin) {
    createDonorFromAdmin( $admin );
    $num++;
}

$donations = getDonations();
foreach ($donations as $donation) {
    createDonorFromDonation( $donation );
    $num++;
}

echo "Number of donors: " . $num;