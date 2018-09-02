<?php
require '../../../db.php';
require_once( __DIR__ . '/../../../class.globalSettings.php' );
$CHIDON_ACTIVE = true; // change to activate chidon

$admin = mysql_real_escape_string( $_POST['admin'] );
$year = mysql_real_escape_string( $_POST['year'] );

require 'encrypt.php';
$admin = encrypt_decrypt('decrypt', $admin);

require 'regFeeSchools.php';
require_once( dirname(__FILE__) . '/../../../raffles/yearly/classes/YearlyRaffle.php') ;
use raffles\yearly\YearlyRaffle as YearlyRaffle; // use the raffle class from its namespace
$yearly_raffle = new YearlyRaffle();

//setup json array of information to pass back to parent_detail page
$info = array();

$parent = array();
$sql = "SELECT father, mother, father_pic, mother_pic FROM admins WHERE admin_id = " . $admin;
$result = mysql_query( $sql );
$row = mysql_fetch_assoc( $result );

$parent['fatherPic'] = $row['father_pic'];
$parent['motherPic'] = $row['mother_pic'];
$parent['father'] = $row['father'];
$parent['mother'] = $row['mother'];

$info['parent'] = $parent;

$users = array();
$sql = "select id from admin_auths where admin_id = " . $admin . " and role_id = 1 and auth = 'user'";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc($result) ) {
	$users[] = $row['id'];
}

$children = array();
//need to have multiple result rows to get highest rank
$sql = "select s.school_name, s.school_city, s.school_era, s.reg_type, c.class_grade, u.user_id, u.first, u.last, "
	." u.first_he, u.last_he, u.lang_id, "
	." u.mobile_pic, u.user_photo_id, u.school_id, u.user_registered, r.rank_ord, r.rank_name, r.rank_image_id "
	." FROM users u "
    ." JOIN schools s USING (school_id) "
	." LEFT JOIN classes c ON c.class_id = u.class_id "
	." LEFT JOIN rank_marks rm USING (user_id) "
    ." LEFT JOIN ranks r USING (rank_ord) "
	." WHERE u.user_id IN (" . implode(',', $users) . ") "
    ." ORDER BY u.user_id, rank_ord";

$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc($result) ) {
	$reg_year = GlobalSettings::getRegistrationYear( $row['school_id'] );
	$chidon_year = GlobalSettings::getChidonYear( );
	$children[$row['user_id']]['first'] 	= $row['lang_id'] == 1 ? $row['first'] : $row['first_he'];
	$children[$row['user_id']]['last']  	= $row['lang_id'] == 1 ? $row['last'] : $row['last_he'];
	$children[$row['user_id']]['school'] 	= $row['school_name'];
	$children[$row['user_id']]['city'] 		= $row['school_city'];
	$children[$row['user_id']]['photo'] 	= empty( $row['user_photo_id'] ) ? null : $row['user_photo_id'];
	$children[$row['user_id']]['rank'] 		= $row['rank_name'] ? $row['rank_name'] : '';
	$children[$row['user_id']]['rankOrd']	= $row['rank_ord'] ? $row['rank_ord'] : 0;
	$children[$row['user_id']]['rankImg'] 	= $row['rank_image_id'] ? $row['rank_image_id'] : '';
	$children[$row['user_id']]['thumb'] 	= 0;
	$children[$row['user_id']]['mobile_pic']= empty( $row['mobile_pic'] ) ? 0 : $row['mobile_pic'];
	$children[$row['user_id']]['grade'] 	= $row['class_grade'];
	$children[$row['user_id']]['schoolRegistered'] = $row['school_era'] > 0 ? 0 : 1;
	$children[$row['user_id']]['anashkinder'] = $row['school_id'] == 269 ? 1 : 0;
	$children[$row['user_id']]['myshliach'] = $row['school_id'] == 61 ? 1 : 0;
	$children[$row['user_id']]['chidon'] = $CHIDON_ACTIVE && intval($row['class_grade']) > 3 ? 1 : 0;
	$children[$row['user_id']]['chidonRegistered'] = 0;
	$children[$row['user_id']]['chayolei'] = 1;
    $children[$row['user_id']]['user_registered'] = $row['user_registered'];
	$children[$row['user_id']]['reg_year'] = $reg_year;
	$children[$row['user_id']]['chidon_year'] = $chidon_year;
	$children[$row['user_id']]['school_id'] = $row['school_id'];
    
    $reg_query = mysql_query(
        "SELECT !ISNULL(tc.th_chidon_id) AS reg_chidon, !ISNULL(ur.user_reg_id) AS reg_chayolei, u.chayolei, "
        ."sri.date_paid AS registered FROM users u "
        ."LEFT JOIN th_chidon tc ON u.user_id = tc.user_id and year = $chidon_year "
        ."LEFT JOIN user_registration ur ON u.user_id = ur.user_id and ur.year = $reg_year "
        ."LEFT JOIN school_registrations sri ON u.school_id = sri.school_id AND sri.year = $reg_year "
        ."WHERE u.user_id = ".$row['user_id']
    );
    $row = array_merge( $row, mysql_fetch_assoc( $reg_query ) );
	$children[$row['user_id']]['schoolTypeRegistered'] = $row['registered'] > 0 ? 1 : 0;

	// after Nov 8, 2017 registration is closed
	//if (unixtojd() > 2458067 && !in_array($row['school_id'], array(61,269))) $children[$row['user_id']]['chidon'] = 0;
	
	//if (unixtojd() < 2457996) $children[$row['user_id']]['chidon'] = 0; // chidon registration only begins August 31, 2017
    
    // REGISTRATION 5779
    $children[$row['user_id']]['needsReg'] = 0;
    $children[$row['user_id']]['allowRemove'] = 0;
	$children[$row['user_id']]['reg_types'] = [];
	
    if ( !$row['reg_chayolei'] && $row['chayolei'] && // if not registered for chayolei
        // do not show for 7-8 grade who are registered in chidon 
        ( !(in_array( $row['class_grade'], [7, 8] ) && $row['reg_chidon'] ) ) 
    ) {
        $children[$row['user_id']]['needsReg'] = 1;
        $children[$row['user_id']]['reg_types']['chayolei'] = true;
    } 
    
    // chidon regustration
    if ( !$row['reg_chidon'] && // if not in chidon
		$row['class_grade'] >= 4 // and in grade 4+
		//&& in_array( $row['school_id'], $australia ) // and not in australia..
    ) {
        $children[ $row['user_id'] ]['needsReg'] = 1;
        $children[ $row['user_id'] ]['reg_types']['chidon'] = true;
    }
    
    $children[$row['user_id']]['enrollShabbaton'] = 0;
    $children[$row['user_id']]['shabbatonRegistered'] = 0;
    $children[$row['user_id']]['shabbatonEdit'] = 0;
    $children[$row['user_id']]['shabbatonConfirmed'] = 0;
    $cSql = "SELECT * FROM th_chidon "
        ." WHERE year = " . $year . " "
        ." AND user_id = " . $row['user_id'];
    $cRes = mysql_query($cSql);
    if (mysql_num_rows($cRes) > 0) {
        $cRow = mysql_fetch_assoc($cRes);
        if ($cRow['deleted'] == 0) {
            $children[$row['user_id']]['chidonRegistered'] = 1;
            $children[$row['user_id']]['allowRemove'] = 0;
            // make sure school indicated that child should enroll for shabbaton 
            if ($cRow['can_enroll'] && in_array($row['user_id'], [])) { // chidon registration is closed.
                // make sure school is registered to chidon
                $chapSql = "SELECT * FROM th_chidon_schools WHERE school_id = " . $row['school_id'] . " AND year = " . $year . " AND registered = 1";
                $chapRes = mysql_query( $chapSql );
                if (mysql_num_rows($chapRes) > 0) {
                    $children[$row['user_id']]['enrollShabbaton'] = 1;
                }
            }
        }
        if ($cRow['allow_edit']) {
            $children[$row['user_id']]['shabbatonEdit'] = 1;
        }
        if ($cRow['date_paid'] > 0) {
            $children[$row['user_id']]['shabbatonRegistered'] = 1;
        }
        if ($cRow['confirmed']) {
            $children[$row['user_id']]['shabbatonConfirmed'] = 1;
        }
    }
	
	$pSql = "select thumb from thumbs t 
			join users u on u.user_photo_id = t.file_id 
			where u.user_id = " . $row['user_id'];
	$pRes = mysql_query($pSql);
	if (mysql_num_rows($pRes) > 0) {
		$pRow = mysql_fetch_assoc($pRes);
		$children[$row['user_id']]['thumb']	= $pRow['thumb'];
	}
	
	// get number of days that tasks were done
	if ($row['user_registered']) {
        // set the eligibility for the user and get it back
        $yearly_raffle->set_user_eligibility( $row['user_id'] );
        $numTasks = $yearly_raffle->eligibility[ $row['user_id'] ];
        // send them a message with how many days left/done
		if ($numTasks >= 160) {
			$children[$row['user_id']]['auctionInfo'] = '160 days of tasks completed - eligible for yearly raffle';
		} else {
			$children[$row['user_id']]['auctionInfo'] = 160 - intval($numTasks) . " days of tasks to enter the yearly raffle";
		}
	}
	
	//if ($row['user_id'] == 26598) {
	//	$children[$row['user_id']]['chidonShow'] = 1;
	//}

	// find out if child is elligible for chidon shabbaton and if needs to pay for it
	//$children[$row['user_id']]['shabbaton'] = 0;
	//$children[$row['user_id']]['shabbatonEdit'] = 0;
	//include '../../chidon_shutdown_vars.php';
	//if (!$shutdown || in_array($row['school_id'], $exceptions)) {
	/*
		$cSql = "select * from th_chidon where user_id = " . $row['user_id'] . "
				and year = " . $year . "
				and deleted = 0
				and shabbaton = 1";
		$cResult = mysql_query($cSql);
		if (mysql_num_rows($cResult) > 0) {
			$cRow = mysql_fetch_assoc($cResult);
			if (intval($cRow['shabbaton']) || intval($cRow['contestant'])) {
				if ($cRow['paid'] > 0) $children[$row['user_id']]['shabbatonEdit'] = 1; 
				else $children[$row['user_id']]['shabbaton'] = 1;
				$children[$row['user_id']]['schoolChapReg'] = 0;
			}
			// check if the school has registered any chaperones
			$chapSql = "select * from th_chidon_schools tcs 
						join th_chidon_chaps using (school_id)  
						where tcs.registered = 1 and tcs.year = " . $year . " and tcs.school_id = " . $row['school_id'];
			$chapRes = mysql_query($chapSql);
			if (mysql_num_rows($chapRes) == 0) {
				$children[$row['user_id']]['schoolChapReg'] = 1;
			}
		}
	*/
	//}
	//if ($row['user_id'] == 8273) $children[$row['user_id']]['needsReg'] = 1;
	//if ($row['user_id'] == 5548) $children[$row['user_id']]['showStory'] = 1;
}
$info['children'] = $children;

echo json_encode( $info );
?>