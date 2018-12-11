<?php
require '../../../db.php';

$admin = mysql_real_escape_string( $_POST['admin'] );
$year = mysql_real_escape_string( $_POST['year'] );

require 'encrypt.php';
$admin = encrypt_decrypt('decrypt', $admin);

function registration_rate( $user_id ){
    // the core registration information
    $rate                   = 55;       // base registration rate
    $tuition_rate           = 45;       // rate for schools which partially include it in tuition.
    $early_bird_discount    = 5;        // $5 early bird special
    $early_bird_deadline    = 2458011;  // deadline for early bird special
    $extended_deadline      = 2458018;  // extended deadline for some schools

    // SPECIAL CASES //
    // schools with the $extended_deadline
    $extended_schools = [
        2   =>  50, // Beth Rivkah Montreal
        4   =>  45, // Cheder Menachem LA
        9   =>  45, // Lubavitcher Yeshiva, Crown Heights
        21  =>  45, // Cheder Lubavitch Morristown Boys
        37  =>  45, // Cheder Lubavitch Morristown Girls
        162 =>  50, // Bais Chaya Mushka LA
        192 =>  50  // Cheder Chabad of Monsey Girls
    ];
    // schools with year round discounted rates
    $discount_schools = [
        265 =>   0, // Lubavitch Girls London
        61  =>  45, // MyShliach
        9   =>  45  // Lubavitcher Yeshiva CH
    ];

    // get the schools registration type
    $school_info_query = mysql_query(
        "SELECT reg_type, users.school_id FROM users JOIN schools USING (school_id) WHERE user_id = '$user_id'"
    );
    $school_info = mysql_fetch_assoc($school_info_query);
    $school_id  = $school_info['school_id'];

    // SPECIAL SCHOOLS
    if ( in_array( $school_id, array_keys( $discount_schools ) ) ) {
        return $discount_schools[ $school_id ];
    }

    // EARLY BIRD
    if ( unixtojd() < $early_bird_deadline ) {
        if ( $school_info['reg_type'] == 1) { // included in tuition
            return 0;
        } else if ( $school_info['reg_type'] == 2) { // discounted school rate
            return $tuition_rate;
        } else { // school does not provide any deeper discounts
            return $rate - $early_bird_discount;
        }
    // EXTENDED DEADLINE
    } else if ( unixtojd() < $extended_deadline && isset( $extended_schools[$school_id] ) ) {
        return $extended_schools[ $school_id ];
    }
    // return the default rate
    return $rate;
}

$users = array();
$users_query = mysql_query(
    "SELECT id FROM admin_auths WHERE admin_id = " . $admin . " AND role_id = 1 AND auth = 'user'"
);

while ( $row = mysql_fetch_assoc($users_query) ) {
	$users[] = $row['id'];
}

$children = array();
//need to have multiple result rows to get highest rank
$sql = "SELECT s.school_name, s.school_city, u.*, s.school_id, s.school_name, s.school_era, s.reg_type, c.class_grade, c.class_sub,
        r.rank_ord, r.rank_name, r.rank_image_id FROM users u 
		JOIN schools s USING (school_id) 
		LEFT JOIN rank_marks rm USING (user_id) 
		LEFT JOIN ranks r USING (rank_ord)
        JOIN classes c ON c.class_id = u.class_id 
		WHERE u.user_id IN (" . implode(',', $users) . ")";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc($result) ) {
	// make sure school registered and has type
	if ($row['school_era'] != 0 || $row['reg_type'] == 0) continue;
	if ($row['user_registered'] > '2017-08-20') continue;
	
	// find out if user already registered for new year
	$regSql = "SELECT * FROM user_registration WHERE year = " . $year . " AND user_id = " . $row['user_id'];
    $regRes = mysql_query( $regSql );
    if ( mysql_num_rows( $regRes ) != 0 ) continue;
    
    $children[$row['user_id']]['fee'] = registration_rate( $row['user_id'] );
    if ( $children[$row['user_id']]['fee'] == 0)
        $children[$row['user_id']]['fee'] = '0 (paid by school)';
    
	$children[$row['user_id']]['first'] 	= $row['first'];
	$children[$row['user_id']]['last']  	= $row['last'];
	$children[$row['user_id']]['school_id'] = $row['school_id'];
	$children[$row['user_id']]['school'] 	= $row['school_name'];
	$children[$row['user_id']]['city'] 		= $row['school_city'];
	$children[$row['user_id']]['photo'] 	= empty( $row['user_photo_id'] ) ? null : $row['user_photo_id'];
	$children[$row['user_id']]['rank'] 		= $row['rank_name'] ? $row['rank_name'] : '';
	$children[$row['user_id']]['rankOrd']	= $row['rank_ord'] ? $row['rank_ord'] : 0;
	$children[$row['user_id']]['rankImg'] 	= $row['rank_image_id'] ? $row['rank_image_id'] : '';
	$children[$row['user_id']]['thumb'] 	= 0;
	$children[$row['user_id']]['mobile_pic']= empty( $row['mobile_pic'] ) ? 0 : $row['mobile_pic'];
    $children[$row['user_id']]['fhname']    = $row['first_he'];
    $children[$row['user_id']]['lhname']    = $row['last_he'];
    $arrDob = explode('-', $row['dob']);
    $children[$row['user_id']]['dob']       = $arrDob[1] . '/' . $arrDob[2] . '/' . $arrDob[0];
    $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
	$children[$row['user_id']]['grade']     = $grade;
	
	$pSql = "select thumb from thumbs t 
			join users u on u.user_photo_id = t.file_id 
			where u.user_id = " . $row['user_id'];
	$pRes = mysql_query($pSql);
	if (mysql_num_rows($pRes) > 0) {
		$pRow = mysql_fetch_assoc($pRes);
		$children[$row['user_id']]['thumb']	= $pRow['thumb'];
	}
}

echo json_encode( ['children' => $children] );
?>