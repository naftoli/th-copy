<?php
include_once(dirname(__FILE__) . "/header.php");

require dirname(__FILE__) . "/../../class.globalSettings.php";
$year = GlobalSettings::getCurrentYear();

//echo json_encode( $_POST );

$donor_id = intval( mysql_real_escape_string( $_POST['donor_id'] ) );
$donation_amount = doubleval( mysql_real_escape_string( $_POST['amount'] ) );
$date = mysql_real_escape_string( $_POST['date_time'] );
$user_serial = isset($_POST['dedication_user_id']) && $_POST['dedication_user_id'] > 0 ?  mysql_real_escape_string( $_POST['dedication_user_id'] ) : 0;

// get user_id
if ($user_serial > 0) {
    $sql = "select user_id from users where user_serial = " . $user_serial;
    $result = mysql_query( $sql );
    $row = mysql_fetch_assoc( $result );
    $user_id = $row['user_id'];
} else {
    $user_id = 0;
}

// if ( $donor_id == 0 ) {
//     // create donor
//     $name = mysql_real_escape_string( $_POST['name'] );
//     $arrName = explode(' ', $name);
//     $last_name = $arrName[count($arrName) - 1];
//     $first_name = '';
//     for ( $i = 0; i < count($arrName) - 2; $i++ ) {
//         $first_name .= $arrName[$i] + ' ';
//     }
//     $phone = mysql_real_escape_string( $_POST['phone'] );
//     $address = mysql_real_escape_string( $_POST['address'] );
//     $email = mysql_real_escape_string( $_POST['email'] );
//     $parent_id = mysql_real_escape_string( $_POST['parent_id'] );

//     $sql = "insert into mashpia_charidy.donors 
//             set first_name = \"" . $first_name . "\", 
//             last_name = \"" . $last_name . "\", 
//             address = \"" . $address . "\", 
//             phone = '" . $phone . "', 
//             email = '" . $email . "'";
//     $result = mysql_query( $sql );
//     if ( $result ) $donor_id = mysql_insert_id();
// }

if ($donor_id > 0) {
    if ( $donation_amount ) {
        $sql = "insert into mashpia_charidy.donations
                set donor_id = " . $donor_id . ",
                year = " . $year . ",
                amount = " . $donation_amount . ",
                donation_date = '" . $date . "',
                user_id = " . $user_id;
        $response['query'] = $sql;
        if (mysql_query( $sql )) {
            $response['success'] = true;
        } else {
            $response['success'] = false;
            $response['error'] = mysql_error();
        }
    }
    //echo json_encode( $response );
    
    $children = $_POST['children'];
    if (!empty($children)) {
        foreach ($children as $child) {
            if ($child['amount']) {
                $user_id = mysql_real_escape_string( $child['user_id'] );
                $amount = mysql_real_escape_string( $child['amount'] );
                $sql = "insert into mashpia_charidy.donations
                        set donor_id = " . $donor_id . ",
                        year = " . $year . ",
                        amount = " . $amount . ",
                        donation_date = '" . $date . "',
                        user_id = " . $user_id . ",
                        child_only_donation = 1";
                mysql_query( $sql );
            }
        }
    }
} 
    