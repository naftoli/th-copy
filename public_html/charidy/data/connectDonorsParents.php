<?php
ini_set('display_errors',1);
// load up list of charidy ppl
require '../../db.php';

$info = array();
$sql = "select * from charidy where parent_admin_id is null";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $info[$row['email']][] = $row;
}
//echo "<pre>"; print_r( $info ); echo "</pre>";

// functions to find out if any have a parent account by comparing email address / phone number / name / address
function matchByEmail( $email ) {
    if (!empty($email)) {
        $sql = "select * from admins where admin_email like '%" . mysql_real_escape_string($email) . "%'";
        $result = mysql_query( $sql );
        if (mysql_num_rows( $result) > 0) {
            $row = mysql_fetch_assoc( $result );
            return $row;
        } 
    } 
    return false;
}

function matchByPhone( $phone ) {
    if (!empty($phone)) {
        // remove all non numbers from phone
        $phone = filter_var($phone, FILTER_SANITIZE_NUMBER_INT);
        $phone = str_replace('+', '', $phone);
        $phone = str_replace('-', '', $phone);
        $sql = "select * from admins where phone1 = '" . $phone . "' or phone2 = '" . $phone . "' or phone3 = '" . $phone . "' or phone4 = '" . $phone . "'";
        $result = mysql_query( $sql );
        if (mysql_num_rows( $result) > 0) {
            $row = mysql_fetch_assoc( $result );
            return $row;
        } 
    }
    return false;
}

function matchByName( $first, $last ) {
    if ($first != '' && $last != '') {
        $sql = "select * from admins where LOWER(first) like '%" . mysql_real_escape_string(strtolower($first)) . "%' and LOWER(last) like '%" .
            mysql_real_escape_string(strtolower($last)) . "%'";
        $result = mysql_query( $sql );
        if (mysql_num_rows( $result) > 0) {
            $row = mysql_fetch_assoc( $result );
            return $row;
        }
    }
    return false;
}

function matchByAddress( $address ) {
    if (!empty($address)) {
        $sql = "select * from admins where LOWER(admin_address1) like '%" . mysql_real_escape_string(strtolower($address)) . "%'";
        $result = mysql_query( $sql );
        if (mysql_num_rows( $result) > 0) {
            $row = mysql_fetch_assoc( $result );
            return $row;
        }
    }
    return false;
}

function merge($charidy_id, $parent_id) {
    $sql = "update charidy set parent_admin_id = " . $parent_id . " where charidy_id = " . $charidy_id;
    //echo $sql . "<br />";
    //return false;
    return mysql_query($sql);
}

$merged = 0;
foreach ($info as $email => $other) {
    foreach ($other as $row) {
        $matched = array();
        if ($admin = matchByEmail($email)) {
            $matched[$admin['admin_id']] = 1;
            $emailMatched = true;
        }
        if ($admin = matchByPhone($row['phone'])) {
            if (isset($matched[$admin['admin_id']])) $matched[$admin['admin_id']]++;
            else $matched[$admin['admin_id']] = 1;
        }
        if ($admin = matchByName($row['fname'], $row['lname'])) {
            if (isset($matched[$admin['admin_id']])) $matched[$admin['admin_id']]++;
            else $matched[$admin['admin_id']] = 1;
        }
        if ($admin = matchByAddress($row['address'])) {
            if (isset($matched[$admin['admin_id']])) $matched[$admin['admin_id']]++;
            else $matched[$admin['admin_id']] = 1;
        }
            
        $ids = array_keys($matched);
        // see if all id's are the same
        if (count($ids) == 1) {
            $admin_id = $ids[0];
            
            // if email is matched and at least one other match with same admin id exists, or if matched id shows up 3 times, merge
            if (($emailMatched && $matched[$admin_id] > 1) || $matched[$admin_id] > 2) {
                // merge accounts
                if (merge($row['charidy_id'], $admin_id)) $merged++;
            }
        } 
    }
}
echo "Merged: " . $merged;