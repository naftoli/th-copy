<?php
/*
Information we need to provide:

Email address 
First Name 
Last Name  
Amount donated last year
Rank Last year
Suggest Amount for this year
Suggested Rank this Year
Address 
Phone

Child/ren
User ID
First Name
Last Name 
Picture (url link to picture)
School 
*/
include_once(dirname(__FILE__) . "/header.php");
require_once dirname(__FILE__) . "/../../class.globalSettings.php";

$key = mysql_real_escape_string( $_POST['key'] );

if ( $key != 'cth5778!' ) {
    die(); // send an error message?
}

function build_json( $row ) {
    $response = array();
    $year = GlobalSettings::getCurrentYear();
    $amounts = array(1,126,180,300,504,773,1008,1800,3600,5400,10800,18000,27000,36000);

    $ranks = array(
        1    =>  'Private',
        126  =>  'Sergeant',
        180  =>  'Sergeant Major',
        300  =>  'Second Lieutenant',
        504  =>  'First Lieutenant',
        773  =>  'Captain',
        1008 =>  'Major',
        1800 =>  'Colonel',
        3600 =>  'General',
        5400     =>  '1* General',
        10800    =>  '2* General',
        18000    =>  '3* General',
        27000    =>  '4* General',
        36000    =>  '5* General'
    );
     
    $donor_id = $row['donor_id'];
    $parent_id = $row['parent_admin_id'];
    $phone = $row['phone'];
    $address = $row['address'];
    
    $response['children'] = array();
    if ($parent_id) {
        $sql2 = "select u.user_id, u.first, u.last, u.school_id, s.school_name, u.mobile_pic 
                from users u
                join schools s using (school_id) 
                join admin_auths aa on aa.id = u.user_id
                join admins a using (admin_id) 
                where aa.auth = 'user' 
                and a.admin_id = " . $parent_id;
        $result2 = mysql_query($sql2);
        while ($row2 = mysql_fetch_assoc($result2)) {
            $response['children'][] = array(
                'user_id'       =>  $row2['user_id'],
                'name'          =>  $row2['first'] . ' ' . $row2['last'],
                'school'        =>  $row2['school_name'],
                'school_id'     =>  $row2['school_id'], 
                'picture'       =>  'https://mashpia.com/mobile/reg/' . $row2['mobile_pic']
            );
        }
    }
    
    $donation_qry = "select sum(amount) as total from charidy_donations where donor_id = " . $donor_id . " and year = " . --$year;
    $donation_result = mysql_query( $donation_qry );
    $donation_row = mysql_fetch_assoc( $donation_result );
    
    $response['donor_id'] = $donor_id;
    $response['parent_id'] = $parent_id;
    $response['phone_number'] = $phone;
    $response['address'] = $address;

    // figure out which rank was done for last yr
    // show next rank for this yr
    $rankDone = 0;
    if ($donation_row['total']) {
        $numAmounts = count( $amounts );
        for ($i = $numAmounts; $i > 0; $i--) {
            if (intval($donation_row['total']) >= $amounts[$i-1]) {
                $rankDone = $i-1;
                break;
            }
        }
        $response['donation_last_yr'] = (int)$donation_row['total'];
        $response['rank_last_yr'] = $ranks[$amounts[$rankDone]];
        $response['donation_this_yr'] = (string)$amounts[$i];
        $response['rank_this_yr'] = $ranks[$amounts[$i]];
    } else {
        $response['donation_last_yr'] = 0;
        $response['rank_last_yr'] = 0;
        $response['donation_this_yr'] = '126';
        $response['rank_this_yr'] = "Sergeant";
    }
    
    return $response;
}

// blank response
$response = [];
$email = mysql_real_escape_string( trim( $_POST['email'] ) );

$donor_query = mysql_query(
    "SELECT * FROM charidy_donors WHERE email = '" . $email . "' "
);
// if we have a result...
if ( $row = mysql_fetch_assoc($donor_query) ) {
    $response = build_json( $row );
} else {
    // find out if email exists in charidy_donors_extra table
    //$sql = "select cd.* from charidy_donors cd
    //        join charidy_donors_extra_info using (donor_id) 
    //        where type = 'email'
    //        and info = '" . $email . "'";
    //$result = mysql_query( $sql );
    //if (mysql_num_rows( $result ) > 0) {
    //    $row = mysql_fetch_assoc( $row );
    //    $response = build_json( $row );
    //} else {
        $response  =  array(
            'donor_id'  =>  0,
            'parent_id' =>  0,
            'phone'     =>  '',
            'address'   =>  '',
            'donation_last_yr'   =>  0,
            'rank_last_yr'      =>  0,
            'donation_this_yr'   =>  '126',
            'rank_this_yr'      =>  'Sergeant',
            'children'  =>  array()
        );
    //}
}

echo json_encode( $response );
?>