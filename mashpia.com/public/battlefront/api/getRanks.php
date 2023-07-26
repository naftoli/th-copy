<?php
$admin_auth = ['school'];
require_once( $_SERVER["DOCUMENT_ROOT"] . '/header.php' );

// make sure only super admins can access
if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

function dateToJd( $date ){
    $date = explode( "-", $date );
    return gregoriantojd($date[1], $date[2], $date[0]);
}

$info = json_decode(file_get_contents('php://input'), true);
$start_date = dateToJd($info['start']);
$end_date = dateToJd($info['end']);

$ranks = [];
$sql = "select * from ranks";
$ranks_query = mysql_query($sql);
while($rank_info = mysql_fetch_assoc($ranks_query)){
    $ranks[$rank_info['rank_ord']] = $rank_info['rank_name'];
}

$starGenerals = [];
$generals = [];
$lowerRanks = [];

$qry = " SELECT IFNULL(s.shorthand, s.school_id) as school, u.first, u.last, u.user_serial, u.mobile_pic, u.user_photo_id, u.gender, "
        ." r.rank_name, rm.rank_ord FROM rank_marks rm "
        ." JOIN users u USING ( user_id ) "
        ." JOIN ranks r USING ( rank_ord ) "
        ." JOIN schools s USING ( school_id ) "
        ." WHERE rm.date_promoted >= '$start_date' "
        ." AND rm.date_promoted <= '$end_date' "
        ." AND rm.rank_ord > 1 "
        ." AND s.test_school = 0 "
        ." ORDER BY gender, school, last, first";

//$qry2 =  " SELECT IFNULL(s.shorthand, s.school_id) as school, u.first, u.last, u.user_serial, u.mobile_pic, u.user_photo_id, u.gender "
//        ." r.rank_name, rm.rank_ord FROM rank_marks rm "
//        ." JOIN users u USING ( user_id ) "
//        ." JOIN ranks r USING ( rank_ord ) "
//        ." JOIN schools s USING ( school_id ) "
//        ." WHERE rm.date_promoted >= '$start_date' "
//        ." AND rm.date_promoted <= '$end_date' "
//        ." AND rm.rank_ord = 9 "
//        ." AND s.test_school = 0 "
//        ." ORDER BY gender, school, last, first";
//
//$qry3 =  " SELECT IFNULL(s.shorthand, s.school_id) as school, u.first, u.last, u.user_serial, u.mobile_pic, u.user_photo_id, u.gender "
//        ." r.rank_name, rm.rank_ord FROM rank_marks rm "
//        ." JOIN users u USING ( user_id ) "
//        ." JOIN ranks r USING ( rank_ord ) "
//        ." JOIN schools s USING ( school_id ) "
//        ." WHERE rm.date_promoted >= '$start_date' "
//        ." AND rm.date_promoted <= '$end_date' "
//        ." AND rm.rank_ord < 9 "
//        ." AND s.test_school = 0 "
//        ." ORDER BY gender, school, last, first";

$result = mysql_query($qry);
while ($row = mysql_fetch_assoc($result)) {
    if (intval($row['rank_ord']) > 9) $starGenerals[$row['rank_ord']][] = $row;
    else if (intval($row['rank_ord']) == 9) $generals[] = $row;
    else $lowerRanks[$row['rank_ord']][] = $row;
}
// order ranks by highest rank first
krsort($starGenerals);
krsort($lowerRanks);

echo json_encode([
    'ranks'         => $ranks,
    'starGenerals'  => $starGenerals,
    'generals'      => $generals,
    'lowerRanks'    => $lowerRanks
]);