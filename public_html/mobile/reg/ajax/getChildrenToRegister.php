<?php
require '../../../db.php';

$admin = mysql_real_escape_string( $_POST['admin'] );
$year = mysql_real_escape_string( $_POST['year'] );

require 'encrypt.php';
$admin = encrypt_decrypt('decrypt', $admin);

require 'regFeeSchools.php';

$info = array();
$users = array();
$sql = "select id from admin_auths where admin_id = " . $admin . " and role_id = 1 and auth = 'user'";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc($result) ) {
	$users[] = $row['id'];
}

$children = array();
//$schools45 = array(54,48,81,61,269,7,263,58,2,21,37,4,49,192);
//need to have multiple result rows to get highest rank
$sql = "select s.school_name, s.school_city, u.*, s.school_id, s.school_name, s.school_era, s.reg_type, c.class_grade, c.class_sub,
        r.rank_ord, r.rank_name, r.rank_image_id from users u 
		join schools s using (school_id) 
		left join rank_marks rm using (user_id) 
		left join ranks r using (rank_ord)
        join classes c on c.class_id = u.class_id 
		where u.user_id in (" . implode(',', $users) . ")";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc($result) ) {
	// make sure school registered and has type
	if ($row['school_era'] != 0 || $row['reg_type'] == 0) continue;
	if ($row['user_registered'] > '2017-08-20') continue;
	
	// find out if user already registered for new year
	$regSql = "select * from user_registration where year = " . $year . " and user_id = " . $row['user_id'];
    $regRes = mysql_query( $regSql );
    if ( mysql_num_rows( $regRes ) != 0 ) continue;
    
    //if (!in_array($row['school_id'], $showRegister)) continue;
	//if (in_array($row['school_id'], $tuitionSchools)) continue;
	$children[$row['user_id']]['tuitionSchoolNoPay'] = 0;
	$children[$row['user_id']]['tuitionSchool'] = 0;
	$children[$row['user_id']]['fee'] = $userFee;
		
	if (in_array($row['school_id'], $tuitionSchoolsNoPay)) {
		$children[$row['user_id']]['tuitionSchoolNoPay'] = 1;
		$children[$row['user_id']]['fee'] = '0 (paid by school)';
	} else if (in_array($row['school_id'], $tuitionSchools)) {
		$children[$row['user_id']]['tuitionSchool'] = 1;
		$children[$row['user_id']]['fee'] = '45';
	} else if (unixtojd() < 2458018 && in_array($row['school_id'], array_keys($extended))) {
		if ($extended[$row['school_id']] == 0) {
			$children[$row['user_id']]['tuitionSchoolNoPay'] = 1;
			$children[$row['user_id']]['fee'] = '0 (paid by school)';
		} else {
			$children[$row['user_id']]['fee'] = $extended[$row['school_id']];
		}
	}
	
	// myshliach fee is always 45
	if ($row['school_id'] == 61) $children[$row['user_id']]['fee'] = '45';
	
	//// day school of houston gets 45 until yom kippur
	//if (unixtojd() < 2458027 && in_array($row['school_id'], array(84,9))) {
	//	$children[$row['user_id']]['fee'] = '45';
	//}
	//
	//// Bais Chaya Mushka LA has until erev erev yom kippur
	//if (unixtojd() < 2458025 && $row['school_id'] == 162) {
	//	$children[$row['user_id']]['fee'] = '50';
	//}
	
	// Lubavitcher Yeshiva CH has 45 entire year
	if ($row['school_id'] == 9) $children[$row['user_id']]['fee'] = '45';
    
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
    //$children[$row['user_id']]['laSchool']  = $row['school_id'] == 162 ? 1 : 0;
	//$children[$row['user_id']]['fortyFive'] = unixtojd() < 2457665 && in_array($row['school_id'], $schools45) ? 1 : 0;
	//$children[$row['user_id']]['fortyFive'] = $row['school_id'] == 61 ? 1 : 0;
    $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
	$children[$row['user_id']]['grade']     = $grade;
	$children[$row['user_id']]['australia'] = in_array($row['school_id'], $australia) ? 1 : 0;
	
	$pSql = "select thumb from thumbs t 
			join users u on u.user_photo_id = t.file_id 
			where u.user_id = " . $row['user_id'];
	$pRes = mysql_query($pSql);
	if (mysql_num_rows($pRes) > 0) {
		$pRow = mysql_fetch_assoc($pRes);
		$children[$row['user_id']]['thumb']	= $pRow['thumb'];
	}
}
$info['children'] = $children;

echo json_encode( $info );
?>