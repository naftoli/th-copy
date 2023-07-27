<?php
if ($_SERVER['DOCUMENT_ROOT'] != 'mashpia.com') {
    ini_set('display_errors', 1);
    ini_set('error_reporting', E_ALL);
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

//require '../../../db.php';
//require_once( __DIR__ . '/../../../class.globalSettings.php' );
//require_once( __DIR__ . '/../../../chidonTests/class.chidonTests.php');

$CHIDON_ACTIVE = true; // change to activate chidon

$admin = mysql_real_escape_string( $_POST['admin'] );
require 'encrypt.php';
$admin = encrypt_decrypt('decrypt', $admin);

$australian = [ 55, 66, 110, 112, 180, 256, 643, 709, 713 ];

//require 'regFeeSchools.php';
// require_once( dirname(__FILE__) . '/../../../raffles/yearly/classes/YearlyRaffle.php') ;
// use raffles\yearly\YearlyRaffle as YearlyRaffle; // use the raffle class from its namespace
// $yearly_raffle = new YearlyRaffle();

// needed for checking about mivtzoim purchases
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mivtzoim_purchases/classes/MivtzoimSetting.php';

//setup json array of information to pass back to parent_detail page
$info = array();

$parent = array();
$sql = "SELECT father, mother, father_pic, mother_pic FROM admins WHERE admin_id = " . $admin;
$result = mysql_query( $sql );
$row = mysql_fetch_assoc( $result );

$parent['id'] = $admin;
$parent['fatherPic'] = $row['father_pic'];
$parent['motherPic'] = $row['mother_pic'];
$parent['father'] = $row['father'];
$parent['mother'] = $row['mother'];

$parent['gear'] = 0;
$sqlG = "select * from family_raised where admin_id = " . $admin;
$resultG = mysql_query( $sqlG );
if (mysql_num_rows($resultG) > 0) {
    $rowG = mysql_fetch_assoc( $resultG );
    $parent['gear'] = intval($rowG['amount']);
}

$info['parent'] = $parent;

$users = array();
$sql = "select id from admin_auths where admin_id = " . $admin . " and role_id = 1 and auth = 'user'";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc($result) ) {
    $users[] = $row['id'];
}

if ( !empty( $users ) ) {
    $children = [];
    $sql = "select s.school_name, s.school_name_he, s.school_city, s.school_era, s.reg_type, s.shipping_method, s.school_country, c.class_grade, "
        ." u.user_id, u.first, u.last, u.first_he, u.last_he, u.lang_id, u.chayolei, u.chidon, u.user_serial, u.school_type_id, u.hachayol, "
        ." u.mobile_pic, u.user_photo_id, u.school_id, u.user_registered, s.school_id, c.class_id "
        ." FROM users u "
        ." JOIN schools s USING (school_id) "
        ." LEFT JOIN classes c ON c.class_id = u.class_id "
        ." WHERE u.user_id IN (" . implode(',', $users) . ") ";
//    echo $sql;

    $result = mysql_query( $sql );
    while ( $row = mysql_fetch_assoc($result) ) {
        $reg_year = GlobalSettings::getRegistrationYear( $row['school_id'] );
        $chidon_year                                = isset($_POST['year']) ? $_POST['year'] : GlobalSettings::getChidonRegYear();
        if ($admin == 195942) $chidon_year = 5784;
        $children[$row['user_id']]['user_id']       = $row['user_id'];
        $children[$row['user_id']]['first'] 	    = $row['lang_id'] == 1 ? $row['first'] : $row['first_he'];
        $children[$row['user_id']]['last']  	    = $row['lang_id'] == 1 ? $row['last'] : $row['last_he'];
        $children[$row['user_id']]['school'] 	    = (isset($_COOKIE['lang']) && $_COOKIE['lang'] == 'he' ? $row['school_name_he'] : $row['school_name']);
        $children[$row['user_id']]['city'] 		    = $row['school_city'];
        $children[$row['user_id']]['photo'] 	    = empty( $row['user_photo_id'] ) ? null : $row['user_photo_id'];
        $children[$row['user_id']]['thumb'] 	    = 0;
        $children[$row['user_id']]['mobile_pic']    = empty( $row['mobile_pic'] ) ? 0 : $row['mobile_pic'];
        $children[$row['user_id']]['grade'] 	    = $row['class_grade'];
        $children[$row['user_id']]['schoolRegistered'] = $row['school_era'] > 0 ? 0 : 1;
        $children[$row['user_id']]['anashkinder']   = $row['school_id'] == 269 ? 1 : 0;
        $children[$row['user_id']]['myshliach']     = $row['school_id'] == 61 ? 1 : 0;
        $children[$row['user_id']]['chidon']        = $CHIDON_ACTIVE && $row['chidon'] && (intval($row['class_grade']) >= 3) && (intval($row['class_grade']) <= 8) ? 1 : 0;
        $children[$row['user_id']]['chidonRegistered'] = 0;
        $children[$row['user_id']]['chayolei']      = 1;
        $children[$row['user_id']]['user_registered'] = $row['user_registered'];
        $children[$row['user_id']]['reg_year']      = $reg_year;
        $children[$row['user_id']]['chidon_year']   = $chidon_year;
        $children[$row['user_id']]['school_id']     = $row['school_id'];
        $children[$row['user_id']]['shipping']      = $row['shipping_method'];
        $children[$row['user_id']]['chayoleiRegistered'] = false;
        $children[$row['user_id']]['new_day_school'] = intval($row['school_type_id']) == 50 ? true : false;
        $children[$row['user_id']]['school_country'] = $row['school_country'];
        $children[$row['user_id']]['user_serial']    = $row['user_serial'];
        $children[$row['user_id']]['hachayol']       = $row['hachayol'];
        $children[$row['user_id']]['admin_id']       = $admin;

        // find out highest rank achieved
        $sqlRank = "select r.rank_ord, r.rank_name, r.rank_image_id 
					from ranks r 
					join rank_marks rm using (rank_ord) 
					where rm.user_id = " . $row['user_id'] . " 
					order by rank_ord desc limit 1";
        $resRank = mysql_query( $sqlRank );
        $rowRank = mysql_fetch_assoc( $resRank );
        $children[$row['user_id']]['rank'] 		= $rowRank['rank_name'] ? $rowRank['rank_name'] : '';
        $children[$row['user_id']]['rankOrd']	= $rowRank['rank_ord'] ? $rowRank['rank_ord'] : 0;
        $children[$row['user_id']]['rankImg'] 	= $rowRank['rank_image_id'] ? $rowRank['rank_image_id'] : '';

        $qry = "
            SELECT 
                ! ISNULL(tc.th_chidon_id) AS reg_chidon,
                tc.th_chidon_id,
                tc.invite_used, 
                ! ISNULL(ur.user_reg_id) AS reg_chayolei,
                sri.date_paid AS registered,
                u.chayolei,
                u.chidon, 
                tc.ultimate_trip, 
                tc.school_rep, 
                tc.regional_rep, 
                tc.intl_rep 
            FROM
                users u
                    LEFT JOIN
                th_chidon tc ON u.user_id = tc.user_id
                    AND tc.year = " . $chidon_year . "
                    LEFT JOIN
                user_registration ur ON u.user_id = ur.user_id
                    AND ur.year = " . $reg_year . "
                    LEFT JOIN
                school_registrations sri ON u.school_id = sri.school_id
                    AND sri.year = " . $reg_year . "
            WHERE
                u.user_id = ".$row['user_id'];
//        echo $qry . "<br />";

        $reg_query = mysql_query( $qry );
        $row = array_merge( $row, mysql_fetch_assoc( $reg_query ) );
        $children[$row['user_id']]['schoolTypeRegistered'] = $row['registered'] > 0 ? 1 : 0;
        if ( intval( $row['reg_chidon'] ) ) $children[$row['user_id']]['chidonRegistered'] = 1;
        if ( intval( $row['th_chidon_id'] ) ) $children[$row['user_id']]['th_chidon_id'] = $row['th_chidon_id'];
        $children[$row['user_id']]['ultimate_trip'] = intval($row['ultimate_trip']);
        $children[$row['user_id']]['rep'] = intval($row['school_rep']) ? 1 : intval($row['regional_rep']) ? 1 : intval($row['intl_rep']) ? 1 : 0;

        //mivtza lulav
//        $lulavSchools = [];
//        $sqlLulav = "select ls.*
//                     from lulav_settings ls
//                     join schools s using (school_id)
//                     where school_country in ('United States','US','USA','U.S.A.','Canada','canada')
//                     and year = " . $reg_year;
//        $resLulav = mysql_query( $sqlLulav );
//        while ( $rowLulav = mysql_fetch_assoc( $resLulav ) ) {
//           if ( intval( $rowLulav['allow_lulav'] ) ) $lulavSchools[$rowLulav['school_id']] = $rowLulav['lulav_shipping'];
//        }

//         $children[$row['user_id']]['mivtzaLulav'] = 0;
//         if ( $children[$row['user_id']]['schoolRegistered']
//         	&& $children[$row['user_id']]['schoolTypeRegistered']
//         	&& $children[$row['user_id']]['user_registered']
//         	&& in_array( $row['school_id'], array_keys( $lulavSchools ) )
//         	) {
//         	$children[$row['user_id']]['lulavPurchased'] = 0;
//         	$children[$row['user_id']]['mivtzaLulav'] = 1;
//         	$children[$row['user_id']]['lulav_shipping'] = intval( $lulavSchools[$row['school_id']] );
//         }
//
//         // find out if user already purchases a set
//         if ( intval( $children[$row['user_id']]['mivtzaLulav'] ) ) {
//         	$sqlPurchased = "select * from mashpia_purchases.purchase_details
//                            join mashpia_purchases.purchases using (purchase_id)
//                            where item_id = 1 and user_id = " . $row['user_id'] . " and year = " . $reg_year;
//         	$resPurchased = mysql_query( $sqlPurchased );
//         	if ( mysql_num_rows( $resPurchased ) ) {
//         		$children[$row['user_id']]['lulavPurchased'] = 1;
//         	}
//         }

//         // mivtza chanuka
//         $children[$row['user_id']]['menorah'] = 0;
//         $children[$row['user_id']]['brochure'] = 0;
//         $chanukaSchools = MivtzoimSetting::getEnabledSchools( $chidon_year, [2, 3] );
//         foreach ( $chanukaSchools as $school ) {
//         	$school_id = $school['school_id'];
//         	if (
//         		$row['school_id'] == $school_id &&
//         		$children[$row['user_id']]['schoolRegistered'] &&
//         		$children[$row['user_id']]['schoolTypeRegistered'] &&
//         		$children[$row['user_id']]['user_registered']
//         	) {
//         		if ( $school['item_id'] == 2 ) {
//         			$children[$row['user_id']]['menorah'] = 1;
//         			$children[$row['user_id']]['menorah_purchased'] = 0;
//         			$children[$row['user_id']]['menorah_shipping'] = $school['shipping_charge'];
//         		} else if ( $school['item_id'] == 3 ) {
//         			$children[$row['user_id']]['brochure'] = 1;
//         			$children[$row['user_id']]['brochure_purchased'] = 0;
//         			$children[$row['user_id']]['brochure_shipping'] = $school['shipping_charge'];
//         		}
//         	}
//         }

        // selling game sets
//        $item_id = 4;
//        $sqlPurchased = "select * from mashpia_purchases.purchase_details
//                        join mashpia_purchases.purchases using (purchase_id)
//                        where item_id = " . $item_id . " and user_id = " . $row['user_id'] . " and year = " . $reg_year;
//        $resPurchased = mysql_query( $sqlPurchased );
//        if ( mysql_num_rows( $resPurchased ) ) {
//            $children[$row['user_id']]['gamePurchased'] = 1;
//        } else {
//            $children[$row['user_id']]['gamePurchased'] = 0;
//        }

        // REGISTRATION
        $children[$row['user_id']]['needsReg'] = 0;
        $children[$row['user_id']]['allowRemove'] = 0;
        $children[$row['user_id']]['reg_types'] = [];

        if ( !$row['reg_chayolei'] && $row['chayolei'] ) {
            $children[$row['user_id']]['needsReg'] = 1;
            $children[$row['user_id']]['reg_types']['chayolei'] = true;
        } else if ( $row['reg_chayolei'] && $row['chayolei'] ) {
            $children[$row['user_id']]['chayoleiRegistered'] = true;
        }

        // add remove reg button for children that are tuition school and parents registered but school didn't register
        $children[$row['user_id']]['removeRegButton'] = 0;
//        $sqlRemove = "select * from reg_confirmations where user_id = " . $row['user_id'] . " and year = " . $reg_year;
//        $resRemove = mysql_query($sqlRemove);
//        $confirmed = mysql_num_rows($resRemove);
//        if ( $confirmed ) {
//            $children[$row['user_id']]['removeRegButton'] = 1;
//        }

        // if school is tuition type, and school has registered child, we still need parent to confirm info if coming from parent acct
        // only if not australian schools
        $children[$row['user_id']]['confirmationOnly'] = 0;
//        if ( intval($row['reg_type']) == 1 && $children[$row['user_id']]['chayoleiRegistered'] && !$confirmed && !GlobalSettings::isAustralian($row['school_id']) ) {
//            $children[$row['user_id']]['chayoleiRegistered'] = false;
//            $children[$row['user_id']]['reg_types']['chayolei'] = true;
//            $children[$row['user_id']]['needsReg'] = 1;
//            $children[$row['user_id']]['confirmationOnly'] = 1;
//        }

        // for testing
        // if ( in_array( $row['user_id'], [ 8273, 13159, 19274, 22722, 50814, 50836 ] ) ) {
        // 	$children[$row['user_id']]['needsReg'] = 1;
        // 	$children[$row['user_id']]['reg_types']['chayolei'] = true;
        // }

        // if tuition school, turn off registration
//        if (intval($row['reg_type']) == 1 && !$row['reg_chayolei'] && $row['chayolei']) $children[$row['user_id']]['reg_types']['chayolei'] = false;

        // chidon registration
         $exceptions = [482,544,583];
         $sqlNextChidon = "select * from th_chidon where user_id = " . $row['user_id'] . " and year = " . $chidon_year;
         $resNextChidon = mysql_query($sqlNextChidon);
         $children[$row['user_id']]['next_year_chidon'] = $chidon_year;
         if (mysql_num_rows($resNextChidon)) {
             $row['reg_chidon'] = true;
             $children[$row['user_id']]['chidon5783'] = true;
         } else {
             $row['reg_chidon'] = false;
             $children[$row['user_id']]['chidon5783'] = false;
         }
         if ( !$row['reg_chidon'] // if not in chidon
         	&& intval( $row['class_grade'] ) >= 3 // and in grade 4+
         	&& intval( $row['class_grade'] ) <= 8 // not in grade 8
         	&& $row['chidon'] // make sure the kid is in chidon
         	&& !in_array( intval( $children[$row['user_id']]['school_id'] ), $exceptions ) // make sure not one of these schools
         	//&& in_array( $row['school_id'], $australia ) // and not in australia..
         ) {
         	$children[ $row['user_id'] ]['needsReg'] = 1;
         	$children[ $row['user_id'] ]['reg_types']['chidon'] = true;
         }

        // if school hasn't registered, turn off chayolei, chidon registration
        if ( !$children[$row['user_id']]['schoolTypeRegistered'] ) {
            $children[ $row['user_id'] ]['reg_types'] = [];
        }

        // shut down chidon reg
        if (isset($children[ $row['user_id'] ]['reg_types']['chidon'])) $children[ $row['user_id'] ]['reg_types']['chidon'] = false;

        // chidon experience registration
        $children[$row['user_id']]['shabbatonPaid'] = 0;
        $cSql = "SELECT * FROM th_chidon WHERE date_paid > 0 and year = " . $chidon_year . " AND user_id = " . $row['user_id'];
        $cRes = mysql_query($cSql);
        if (mysql_num_rows($cRes) > 0) {
            $cRow = mysql_fetch_assoc($cRes);
            $children[$row['user_id']]['chidonRegistered'] = 1;
            $children[$row['user_id']]['shabbatonPaid'] = 1;
            $children[$row['user_id']]['chidon_id'] = $cRow['th_chidon_id'];
        }

//        $trackSql = "select * from th_chidon where date_paid > 0 and year = " . $chidon_year . " and user_id = " . $row['user_id'];
//        $trackRes = mysql_query($trackSql);
//        if (mysql_num_rows($trackRes) > 0) {
//            $trackRow = mysql_fetch_assoc($trackRes);
//            $ct = new ChidonTests();
//            $tracks = $ct->getTypes();
//            $child = [
//                'user_id' => $row['user_id'],
//                'class_id' => $row['class_id'],
//                'school_id' => $row['school_id'],
//                'test_type' => $trackRow['test_type'],
//                'reward_type' => $trackRow['reward_type'],
//                'th_chidon_id' => $trackRow['th_chidon_id']
//            ];
//            $highestTrack = $ct->getHighestTrackPassed($child)['highest_track'];
//            $rewardType = $child['reward_type'];
//            if ($rewardType != 'highest track passed') {
//                if ($highestTrack == '') $highestTrack = $rewardType;
//                else {
//                    $indexes = array_keys($tracks);
//                    $key1 = array_search($highestTrack, $indexes);
//                    $key2 = array_search($rewardType, $indexes);
//                    if ($key2 > $key1) $highestTrack = $rewardType;
//                }
//            }
//            if ($highestTrack == '') $highestTrack = 0;
//            $children[$row['user_id']]['track'] = $highestTrack;
//        }

        $pSql = "select thumb from thumbs t 
				join users u on u.user_photo_id = t.file_id 
				where u.user_id = " . $row['user_id'];
        $pRes = mysql_query($pSql);
        if (mysql_num_rows($pRes) > 0) {
            $pRow = mysql_fetch_assoc($pRes);
            $children[$row['user_id']]['thumb']	= $pRow['thumb'];
        }

        // // get number of days that tasks were done
        // if ($row['user_registered']) {
        //     // set the eligibility for the user and get it back
        //     $yearly_raffle->set_user_eligibility( $row['user_id'] );
        //     $numTasks = $yearly_raffle->eligibility[ $row['user_id'] ];
        //     // send them a message with how many days left/done
        //     if ($numTasks >= 160) {
        //         $children[$row['user_id']]['auctionInfo'] = '160 days of tasks completed - eligible for yearly raffle';
        //     } else {
        //         $children[$row['user_id']]['auctionInfo'] = 160 - intval($numTasks) . " days of tasks to enter the yearly raffle";
        //     }
        // }

        // find out if child bought a game set
        $children[$row['user_id']]['gameSet'] = 0;
        $sqlGame = "select * from mashpia_purchases.purchases 
                    join mashpia_purchases.purchase_details using (purchase_id)
                    where item_id = 4 
                    and year = $chidon_year 
                    and user_id = " . $row['user_id'];
        $resGame = mysql_query($sqlGame);
        if (mysql_num_rows($resGame) > 0) $children[$row['user_id']]['gameSet'] = 1;
    }
} else {
    $children = [];
}
$info['children'] = $children;

echo json_encode( $info );
?>