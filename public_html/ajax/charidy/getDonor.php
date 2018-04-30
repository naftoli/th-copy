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

$key = mysql_real_escape_string( $_POST['key'] );

if ( $key != 'cth5778!' ) {
    die(); // send an error message?
}

// blank response
$response = [];
$email = mysql_real_escape_string( $_POST['email'] );

$donor_query = mysql_query(
    "SELECT * FROM charidy_donors WHERE email = '" . trim($email) . "'"
);
// if we have a result...
if ( $row = mysql_fetch_assoc($donor_query) ) {
    $donor_id = $row['donor_id'];
    $parent_id = $row['parent_admin_id'];
    $phone = $row['phone'];
    $address = $row['address'];
    
    $response['children'] = array();
    if ($parent_id) {
        $sql2 = "select u.user_id, u.first, u.last, u.school_id, s.school_name, u.mobile_pic 
                from users u
                join admin_auths aa on aa.id = u.user_id
                join admins a using (admin_id)
                where a.admin_id = " . $parent_id;
        $result2 = mysql_query($sql2);
        while ($row2 = mysql_fetch_assoc($result2)) {
            $response['children'][] = array(
                'user_id'       =>  $result2['user_id'],
                'name'          =>  $result2['first'] . ' ' . $result2['last'],
                'school'        =>  $result2['school_name'],
                'school_id'     =>  $result2['school_id'], 
                'picture'       =>  'https://mashpia.com/' . $result2['mobile_pic']
            );
        }
    }
    
    $response['donor_id'] = $donor_id;
    $response['parent_id'] = $parent_id;
    $response['phone_number'] = $phone;
    $response['address'] = $address;
    $response['donation_info'] = array(
        'last_yr'   =>  0,
        'this_yr'   =>  0
    );
} else {
    $response  =  array(
        'donor_id'  =>  0,
        'parent_id' =>  0,
        'phone'     =>  '',
        'address'   =>  '',
        'donation_last_yr'   =>  0,
        'donation_this_yr'   =>  0,
        'children'  =>  array()
    );
}

echo json_encode( $response );
?>