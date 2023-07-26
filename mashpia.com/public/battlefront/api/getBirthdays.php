<?php
$admin_auth = ['school'];
require_once $_SERVER["DOCUMENT_ROOT"] . '/header.php';

// make sure only super admins can access
if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

$_POST['dob'] = 'en';
$_POST['start_date'] = unixtojd() - 150;
$_POST['end_date'] = unixtojd();

require_once $_SERVER["DOCUMENT_ROOT"] . '/class.adminSchools.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/class.schoolsUsers.php';

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
$schoolsUsers = [];
$heDates = [];

foreach ( $schools as $id => $school ) {
    $s = new SchoolsUsers( $id );
    //get all users and filter out correct ones
    $users = $s->getUsers();
    $temp = array();
    $dob = array();
    foreach ( $users as $user ) {
        if ( empty( $user['dob'] ) )
            continue;

        //find out what day birthday is in current year
        //english dob is in format yy-mm-dd
        $arrDOB = explode( '-', $user['dob'] );
        $yy = date('Y');
        $en_birthday = gregoriantojd( $arrDOB[1], $arrDOB[2], $yy );

        //check if hebrew dob should be one day further
        if ( $user['dob_he_offset'] ) {
            //add one to dob
            $date = new DateTime( $user['dob'] );
            $date->add( new DateInterval( 'P1D' ) );
            $newDate = $date->format( 'Y-m-d' );
            $arrDOB = explode('-', $newDate);
        }

        //find out what day hebrew birthday is in current year
        $jd = gregoriantojd( $arrDOB[1], $arrDOB[2], $arrDOB[0] );
        $jDate = jdtojewish($jd);
        $arrJDate = explode("/", $jDate);
        $hMonth = $arrJDate[0];
        $hDay = $arrJDate[1];

        //find out if user born in leap year
        if (((7 * $arrJDate[2] + 1) % 19) < 7) {
            $bornInLeap = true;
        } else {
            $bornInLeap = false;
        }

        //find out if current year is leap year
        $jNow = jdtojewish(unixtojd());
        $arrJNow = explode('/', $jNow);
        $hYear = $arrJNow[2];
        if (((7 * $hYear + 1) % 19) < 7) {
            $leap = true;
        } else {
            $leap = false;
        }

        //if born in regular year and current year is leap year,
        //and month is adar, then month needs to be changed to adar II
        if (!$bornInLeap && $leap && $hMonth == 6) {
            $hMonth++;
        }

        if ( $arrJNow[0] == 13 && $hMonth == 1 ) $hYear += 1;

        $he_birthday = jewishtojd($hMonth, $hDay, $hYear);
        $he_birthday_str = jdtojewish($he_birthday, true, CAL_JEWISH_ADD_GERESHAYIM);
        $he_birthday_str = iconv('WINDOWS-1255', 'UTF-8', $he_birthday_str);
        if (! in_array($he_birthday, array_keys($heDates))) $heDates[$he_birthday] = $he_birthday_str;

        if ($he_birthday >= $_POST['start_date'] && $he_birthday <= $_POST['end_date']) {
            if (! in_array($he_birthday, $dob)) $dob[] = $he_birthday;
            $schoolsUsers[$he_birthday][] = [
                'name' => $user['first'] . ' ' . $user['last'],
                'school' => $user['shorthand'],
                'school_id' => $id
            ];
        }

//        if ( $_POST['dob'] == 'en' ) {
//            if ( $en_birthday >= $_POST['start_date'] && $en_birthday <= $_POST['end_date'] ) {
//                $temp[] = $user;
//                $dob[] = $en_birthday;
//            }
//        } else if ( $_POST['dob'] == 'he' ) {
//            if ( $he_birthday >= $_POST['start_date'] && $he_birthday <= $_POST['end_date'] ) {
//                $temp[] = $user;
//                $dob[] = $he_birthday;
//            }
//        }
    }
//    array_multisort( $dob, SORT_ASC, $temp );
//    $schoolsUsers[$id] = $temp;
}
array_multisort( $dob, SORT_ASC, $schoolsUsers); // sort by hebrew date
echo json_encode([
    'heDates'   => $heDates,
    'birthdays' => $schoolsUsers
]);