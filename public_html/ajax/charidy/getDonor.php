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
header("Access-Control-Allow-Origin: *");
require '../../db.php';

$key = mysql_real_escape_string($_POST['key']);
if ($key == 'cth5778!') {
    
    $info = array();
    $email = mysql_real_escape_string($_POST['email']);
    
    $sql = "select * from charidy_donors where email = '" . trim($email) . "'";
    $result = mysql_query($sql);
    if ($row = mysql_fetch_assoc($result)) {
        $donor_id = $row['donor_id'];
        $parent_id = $row['parent_admin_id'];
        $phone = $row['phone'];
        $address = $row['address'];
        
        $info['children'] = array();
        if ($parent_id) {
            $sql2 = "select u.user_id, u.first, u.last, u.school_id, s.school_name, u.mobile_pic 
                    from users u
                    join admin_auths aa on aa.id = u.user_id
                    join admins a using (admin_id)
                    where a.admin_id = " . $parent_id;
            $result2 = mysql_query($sql2);
            while ($row2 = mysql_fetch_assoc($result2)) {
                $info['children'][] = array(
                    'user_id'       =>  $result2['user_id'],
                    'name'          =>  $result2['first'] . ' ' . $result2['last'],
                    'school'        =>  $result2['school_name'],
                    'school_id'     =>  $result2['school_id'], 
                    'picture'       =>  'https://mashpia.com/' . $result2['mobile_pic']
                );
            }
        }
        
        $info['donor_id'] = $donor_id;
        $info['parent_id'] = $parent_id;
        $info['phone_number'] = $phone;
        $info['address'] = $address;
        $info['donation_info'] = array(
            'last_yr'   =>  0,
            'this_yr'   =>  0
        );
    } else {
        $info  =  array(
            'donor_id'  =>  0,
            'parent_id' =>  0,
            'phone'     =>  '',
            'address'   =>  '',
            'donation_last_yr'   =>  0,
            'donation_this_yr'   =>  0,
            'children'  =>  array()
        );
    }
    
    echo json_encode( $info );
}
?>