<?php
//ini_set('display_errors',1);
chdir("../../../");
require 'db.php';

$admin_id = mysql_real_escape_string( $_POST['admin'] );
$chidon_reg_id = mysql_real_escape_string( $_POST['reg_id'] );
$year = mysql_real_escape_string( $_POST['year'] );

require 'mobile/reg/ajax/encrypt.php';
$admin_id = encrypt_decrypt('decrypt', $admin_id);

if (intval($chidon_reg_id)) {
    $sql = "update th_chidon  
            set paid = 0,
            date_paid = now(),
            paid_by = " . $admin_id . ",
            parent_id = " . $admin_id . " 
            where th_chidon_id = " . intval($chidon_reg_id);
    //echo $sql;
    if (!@mysql_query( $sql )) {
        $to = "naftolir@gmail.com";
        $subject = "Error in chidon registration.";
        $msg = $sql . " - " . mysql_error();
        @mail($to, $subject, $msg);
    }
        
    // get all chidon info in db for child to send to hq
    $sql = "select tc.*, u.first as uFirst, u.last as uLast, u.first_he, u.last_he, a.* 
            from th_chidon tc 
            join users u using (user_id)
            join admin_auths aa on aa.id = u.user_id
            join admins a using (admin_id)
            where aa.auth = 'user'
            and aa.role_id = 1 
			and tc.parent_id = a.admin_id 
            and th_chidon_id = " . $chidon_reg_id;
    $result = mysql_query( $sql );
    $row = mysql_fetch_assoc( $result );
    
    $name = $row['uFirst'] . ' ' . $row['uLast'];
    $he_name = $row['first_he'] . ' ' . $row['last_he'];
    $address = $row['admin_address1'] . (empty($row['admin_address2']) ? '' : "<br />" . $row['admin_address2']) . "<br />" .
        $row['admin_city'] . ', ' . $row['admin_state'] . ' ' . $row['admin_postal'];
    if (empty($row['admin_country']) && strtolower($row['admin_country']) != 'usa') $address .= "<br />" . $row['admin_country'];
    
    $grade = $row['grade'];
    $book = $row['book'];
    $parent_name = $row['first'] . ' ' . $row['last'];
    $parent_email = $row['admin_email'];
    $father_cell = $row['admin_phone_mobile'];
    $mother_cell = $row['admin_phone_mobile2'];
    $home_phone = $row['admin_phone_home'];
    
    $sweater_size = $row['size'];
    $shoe_size = $row['shoe_size'];
    $sandwich = $row['sandwich'];
    $allergies = $row['allergies'];
    
    $walk_day = $row['walk_day'];
    $walk_night = $row['walk_night'];
            
    $hostInfo = $row['host'] . "<br />" . $row['host_number'] . " " . $row['host_address1'] . (empty($row['host_address2']) ? '' : "<br />" . $row['host_address2']);
    $between_streets = $row['between_streets'];
    
    $msg = "Parent " . $parent_name . " has just enrolled for the Chidon Shabbaton.<br />";
    $msg .= "Here's the info we have about the child:<br />";
    $msg .= "Name: " . $name . "<br />" .
        "Hebrew Name: " . $he_name . "<br />" .
        "Address: " . $address . "<br />" .
        "Grade: " . $grade . "<br />" .
        "Book: " . $book . "<br />" .
        "Parent Name: " . $parent_name . "<br />" .
        "Parent Email: " . $parent_email . "<br />" .
        "Father Cell: " . $father_cell . "<br />" .
        "Mother Cell: " . $mother_cell . "<br />" .
        "Home Phone: " . $home_phone . "<br />" .
        "Sweater Size: " . $sweater_size . "<br />" .
        "Shoe Size: " . $shoe_size . "<br />" .
        "Sandwich: " . $sandwich . "<br />" .
        "Allergies: " . $allergies . "<br />" .
        "Walking Alone Permission: ";
        if ($walk_night) $msg .= "always<br />";
        else if ($walk_day) $msg .= "only by day<br />";
        else $msg .= "never<br />";
        $msg .= "Host: " . $hostInfo . "<br />" .
        "Between Streets: " . $between_streets . "<br />";    
    
    $to = "chidon@tzivoshashem.org";
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=UTF-8';
    $headers[] = 'From: Chidon Tzivos Hashem <chidon@tzivoshashem.org>';
    $headers[] = 'CC: naftolir@gmail.com';

    @mail($to, $subject, $msg, implode("\r\n", $headers));
	echo 1;
} else {
	echo "Missing ID.";
}
?>