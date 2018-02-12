<?php
require '../../../db.php';
$admin = mysql_real_escape_string($_POST['admin']);

require '../../reg/ajax/encrypt.php';
$admin = encrypt_decrypt('decrypt', $admin);

chdir('../../../');
define('WP_USE_THEMES', false);
require('blog/wp-blog-header.php');
    
$sql = "select * from mashpiadb.admins where admin_id = " . $admin;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
if (is_null($row['wp_id'])) {
    // create user in wordpress
    $userdata = array(
        'user_login'    =>  $row['username'],
        'user_pass'     =>  $row['password'],
        'user_email'    =>  $row['admin_email'],
        'display_name'  =>  $row['first'] . ' ' . $row['last'],
        'first_name'    =>  $row['first'],
        'last_name'     =>  $row['last']
    );
    $user_id = wp_insert_user( $userdata ) ;
    if ( ! is_wp_error( $user_id ) ) {
        $sql = "update mashpiadb.admins set wp_id = " . $user_id . " where admin_id = " . $admin;
        mysql_query($sql);
        
        update_user_meta( $user_id, "billing_first_name", $row['first'] );
        update_user_meta( $user_id, "billing_last_name", $row['last'] );
        update_user_meta( $user_id, "billing_address_1", $row['admin_address1'] );
        update_user_meta( $user_id, "billing_address_2", $row['admin_address2'] );
        update_user_meta( $user_id, "billing_city", $row['admin_city'] );
        update_user_meta( $user_id, "billing_postcode", $row['admin_postal'] );
        update_user_meta( $user_id, "billing_country", $row['admin_country'] );
        update_user_meta( $user_id, "billing_state", $row['admin_state'] );
        update_user_meta( $user_id, "billing_email", $row['admin_email'] );
        update_user_meta( $user_id, "billing_phone", $row['admin_phone_home'] );
        
        update_user_meta( $user_id, "billing_first_name", $row['first'] );
        update_user_meta( $user_id, "shipping_last_name", $row['last'] );
        update_user_meta( $user_id, "shipping_address_1", $row['admin_address1'] );
        update_user_meta( $user_id, "shipping_address_2", $row['admin_address2'] );
        update_user_meta( $user_id, "shipping_city", $row['admin_city'] );
        update_user_meta( $user_id, "shipping_postcode", $row['admin_postal'] );
        update_user_meta( $user_id, "shipping_country", $row['admin_country'] );
        update_user_meta( $user_id, "shipping_state", $row['admin_state'] );
        echo 0;
    } else {
        echo $user_id->get_error_message();
    }
} else {
    // get wordpress user id and login user
    $creds = array();
	$creds['user_login'] = $row['username'];
	$creds['user_password'] = $row['password'];
	$creds['remember'] = true;
	$user = wp_signon( $creds, false );
	if ( is_wp_error($user) ) {
		echo $user->get_error_message();
	} else {
        echo 0;
    }
}
?>