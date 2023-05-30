<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('max_execution_time', 300);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission to be here.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$school = isset($_REQUEST['school']) ? $_REQUEST['school'] : 0;
if (!$school) exit;

$months = array(
    0	=>	'Tishrei',  1   =>  'Cheshvon', 2   =>  'Kisleiv',  3   =>  'Teves',
    4   =>  'Shevat',   5   =>  'Adar',     6   =>  'Adar II',  7   =>  'Nissan',
    8   =>  'Iyar',     9   =>  'Sivan',    10  =>  'Tamuz',    11  =>  'Av',
    12  =>  'Elul'
);

$heMonths = array(
    0  =>  'תשרי',      1   =>  'חשון',     2   =>  'כסלו',     3   =>  'טבת',
    4   =>  'שבט',      5   =>  'אדר',      6   =>  'אדר ב',    7   =>  'ניסן',
    8   =>  'אייר',     9   =>  'סיון',     10  =>  'תמוז',     11  =>  'אב',
    12  =>  'אלול'
);

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.rankReport.php';

//$boySchools =[269,176,112,105,63,81,615,49,89,55,106,470,5,21,4,86,263,60,185,483,80,110,412,659,517,
//    3,39,480,19,9,471,614,61,577,255,542,48,180,84,643,427,87,663,33,11,58,472];
$girlSchools = [269,54,162,45,30,2,7,112,81,613,192,50,37,265,42,61,40];

// separate myshliach / anashKinder into separate boys/girls files
if (in_array($school, [61,81,269])) {
    generateFile('boys', 'M');
    generateFile('girls', 'F');
} else if (in_array($school, [54,106,255])) {
    generateFileByGrade($school);
} else {
    if (array_search($school, $girlSchools) !== false) generateFile('girls');
    else generateFile('boys');
}

function createFile($name, $info) {
    $fp = fopen($name, "w");
    fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) )); // utf8
    if (is_array($info)) {
        foreach ($info as $fields) {
            fputcsv($fp, $fields, "\t", ' ');
        }
    } else {
        fputs($fp, $info);
    }
    fclose($fp);
}

function generateFile( $logoType = '', $limitTo = '' ) {
    global $school, $schools, $months, $heMonths;

    $rankNames = [
        'Sergeant' => 'sergeant',
        'Sergeant Major' => 'sergeant_major',
        'Second Lieutenant' => 'second_lieutenant',
        'First Lieutenant' => 'first_lieutenant',
        'Captain' => 'captain',
        'Major' => 'major',
        'Colonel' => 'colonel',
        'General' => 'general',
        '1* General' => 'one_star_general',
        '2* General' => 'two_star_general',
        '3* General' => 'three_star_general',
        '4* General' => 'four_star_general',
        '5* General' => 'five_star_general'
    ];

//    if (isset($_GET['prev']) && intval($_GET['prev'])) $r = new RankReport(true);
//    else $r = new RankReport();
//    $r->overrideDates(2459180,2459207);
    $r = new RankReport();
    $r->overrideDates($_GET['start'], $_GET['end']);
    $r->setSchoolId($school);
    if (empty($limitTo)) $r->setRanks('byGender', 0, "<br>", ''); // make sure to add break in name between first name and last name
    else $r->setRanks('byGender', 0, "<br>", $limitTo); // limit to gender for myshliach / anashKinder
    $ranks = $r->getRanks();
    $users = $r->getUserInfo();
    $pics = $r->getUserPic();
    $picOnly = $r->getPicOnly();
    $logos = $r->getSchoolLogos();

    if (count($ranks)) {
        if ($logoType == 'boys') $logoContent = file_get_contents("http://mashpia.com/schoolLogos/" . rawurlencode($logos[$schools[$school]]['logo_boys']));
        else if ($logoType == 'girls') $logoContent = file_get_contents("http://mashpia.com/schoolLogos/" . rawurlencode($logos[$schools[$school]]['logo_girls']));
        $logo_img = @imagecreatefromstring($logoContent);
        imageinterlace($logo_img, true);
        $logo_url = 'images/' . $school . '.png';
        if ($logo_img) @imagepng($logo_img, $logo_url);
        else $logo_url = '';
//        if ($logoType == 'boys') $logo_url = "/logos/" . $logos[$schools[$school]]['logo_boys'];
//        else if ($logoType == 'girls') $logo_url = "/logos/" . $logos[$schools[$school]]['logo_girls'];

        $i = 0;
        $info = [];
        $info[$i++] = ['comp', 'comp_name', 'chayol_name', 'chayol_picture', 'school_name', 'school_logo'];
        $info[$i++] = ['promotions_intro', 'promotions_intro', '', '', $schools[$school], $logo_url]; // intro

        foreach ($ranks as $gender => $more) {
            foreach ($more as $school_name => $other) {
                foreach ($other as $rank => $more) {
                    if (!isset($rankNames[$rank])) continue;
                    $j = 1;
                    $info[$i++] = [($rankNames[$rank] . '_intro'), ucwords(str_replace('_', ' ', ($rankNames[$rank] . '_' . $j++))), '', '', $school_name, $logo_url]; // rank intro
                    foreach ($more as $teacher => $other) {
                        foreach ($other as $grade => $more) {
                            foreach ($more as $user_id) {
                                // create pic of child to add to zipArchive
                                // modify url to work with file_get_contents by enocding only the part that needs to be encoded
                                if (isset($picOnly[$user_id])) $url = "http://mashpia.com/mobile/reg/img/" . rawurlencode($picOnly[$user_id]);
                                else $url = "http://mashpia.com" . $pics[$user_id];
                                $contents = file_get_contents($url);
                                if ($contents) {
                                    $new_img = @imagecreatefromstring($contents);
                                    $img_url = 'images/' . $user_id . '.png';
                                    if ($new_img) @imagepng($new_img, $img_url);
                                    else $img_url = '';
                                }
                                $info[$i++] = [
                                    $rankNames[$rank],
                                    ucwords(str_replace('_', ' ', ($rankNames[$rank] . '_' . $j++))),
                                    $users[$user_id],
                                    $img_url,
                                    $school_name,
                                    $logo_url
                                ];
                            }
                        }
                    }
                }
            }
        }
        $info[$i] = ['outro', 'outro', '', '', $schools[$school], $logo_url]; // outro
        if (count($ranks)) {
            if ($limitTo == 'M') $file_name = $schools[$school] . " Boys.csv";
            else if ($limitTo == 'F') $file_name = $schools[$school] . " Girls.csv";
            else $file_name = $schools[$school] . ".csv";
            createFile($file_name, $info);

            $dates = $r->getHeReportDates();
            $startArr = explode(" ", $dates['start_he']);
            $endArr = explode(" ", $dates['end_he']);
            $str = "Regular Schools:\nStart Date: " . $startArr[1] . ' ' . $startArr[2] . "\nEnd Date: " . $endArr[1] . ' ' . $endArr[2];
            createFile("dates.txt", $str);

            $rDates = $r->getReportDates();
            $heDateArr = explode('/', jdtojewish($rDates['end']));
            $heMonth = $heDateArr[0];
            $heYear = $heDateArr[2];
            $dateStr = "Date\n" . $months[$heMonth-1] . ' ' . $heYear . "\n" . $heMonths[$heMonth-1] . ' ' . mb_substr($endArr[2], 1);
            createFile("dateInfo.txt", $dateStr);
        }
    }
}

function generateFileByGrade() {
    global $school, $schools;

    $rankNames = [
        'Sergeant' => 'sergeant',
        'Sergeant Major' => 'sergeant_major',
        'Second Lieutenant' => 'second_lieutenant',
        'First Lieutenant' => 'first_lieutenant',
        'Captain' => 'captain',
        'Major' => 'major',
        'Colonel' => 'colonel',
        'General' => 'general',
        '1* General' => 'one_star_general',
        '2* General' => 'two_star_general',
        '3* General' => 'three_star_general',
        '4* General' => 'four_star_general',
        '5* General' => 'five_star_general'
    ];

//    if (isset($_GET['prev']) && intval($_GET['prev'])) $r = new RankReport(true);
//    else $r = new RankReport();
//    $r->overrideDates(2459180,2459207);
    $r = new RankReport();
    $r->overrideDates($_GET['start'], $_GET['end']);
    $r->setSchoolId($school);
    $r->setRanks('byGradeOnlyRank', 0, "<br>", '', true); // make sure to add break in name between first name and last name
    $ranks = $r->getRanks();
    $users = $r->getUserInfo();
    $pics = $r->getUserPic();
    $picOnly = $r->getPicOnly();
    $logos = $r->getSchoolLogos();

    if (count($ranks)) {
        foreach ($ranks as $school_name => $more) {
            foreach ($more as $grade => $other) {
                if (in_array($school, [106, 255])) $logoContent = file_get_contents("http://mashpia.com/schoolLogos/" . rawurlencode($logos[$schools[$school]]['logo_boys']));
                else if ($school == 54) $logoContent = file_get_contents("http://mashpia.com/schoolLogos/" . rawurlencode($logos[$schools[$school]]['logo_girls']));
                $logo_img = @imagecreatefromstring($logoContent);
                $logo_url = 'images/' . $school . '.png';
                if ($logo_img) @imagepng($logo_img, $logo_url);
                else $logo_url = '';
//                    if ($school == 255) $logo_url = "/logos/" . $logos[$schools[$school]]['logo_boys'];
//                    else if ($school == 54) $logo_url = "/logos/" . $logos[$schools[$school]]['logo_girls'];

                $i = 0;
                $info = [];
                $info[$i++] = ['comp', 'comp_name', 'chayol_name', 'chayol_picture', 'school_name', 'school_logo'];
                $info[$i++] = ['promotions_intro', 'promotions_intro', '', '', $schools[$school], $logo_url]; //

                foreach ($other as $rank => $more) {
                    if (!isset($rankNames[$rank])) continue;
                    $j = 1;
                    $info[$i++] = [($rankNames[$rank] . '_intro'), ucwords(str_replace('_', ' ', ($rankNames[$rank] . '_' . $j++))), '', '', $school_name, $logo_url]; // rank intro

                    foreach ($more as $user_id) {
                        // create pic of child to add to zipArchive
                        // modify url to work with file_get_contents by enocding only the part that needs to be encoded
                        if (isset($picOnly[$user_id])) $url = "http://mashpia.com/mobile/reg/img/" . rawurlencode($picOnly[$user_id]);
                        else $url = "http://mashpia.com" . $pics[$user_id];
                        $contents = file_get_contents($url);
                        if ($contents) {
                            $new_img = @imagecreatefromstring($contents);
                            $img_url = 'images/' . $user_id . '.png';
                            if ($new_img) @imagepng($new_img, $img_url);
                            else $img_url = '';
                        }

                        $info[$i++] = [
                            $rankNames[$rank],
                            ucwords(str_replace('_', ' ', ($rankNames[$rank] . '_' . $j++))),
                            $users[$user_id],
                            $img_url,
                            $school_name,
                            $logo_url
                        ];
                    }

                    $info[$i] = ['outro', 'outro', '', '', $schools[$school], $logo_url]; // outro
                    if (count($ranks)) {
                        $file_name = $schools[$school] . " " . $grade . ".csv";
                        createFile($file_name, $info);

                        $dates = $r->getHeReportDates();
                        $str = "Schools By Grade:\nStart Date: " . $dates['start_he'] . "\nEnd Date: " . $dates['end_he'];
                        createFile("dates2.txt", $str);
                    }
                }
            }
        }
    }
}

echo json_encode([
    'success' => true
]);