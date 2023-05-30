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

$user_ids = [];
generateFileByGrade();
generateRestOfChildren();

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

function generateFileByGrade() {
    global $school, $schools, $user_ids;

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

    $r = new RankReport();
    $r->overrideDates($_GET['start'], $_GET['end']);
    $r->setSchoolId($school);
    $r->setRanks('byGradeOnlyRank', 0, "<br>", ''); // make sure to add break in name between first name and last name
    $ranks = $r->getRanks();
    $users = $r->getUserInfo();
    $pics = $r->getUserPic();
    $picOnly = $r->getPicOnly();
    $logos = $r->getSchoolLogos();

    if (count($ranks)) {
        foreach ($ranks as $school_name => $other) {
            // OT wants the files to be separated into 3, one for grade 1, another for grades 2-5, and another for grades 6-7
            $needed = [[1], [2, 3, 4, 5], [6, 7]];
            foreach ($needed as $grades) {
                $logoContent = file_get_contents("http://mashpia.com/schoolLogos/" . rawurlencode($logos[$schools[$school]]['logo_boys']));
                $logo_img = @imagecreatefromstring($logoContent);
                $logo_url = 'images/' . $school . '.png';
                if ($logo_img) @imagepng($logo_img, $logo_url);
                else $logo_url = '';

                $i = 0;
                $info = [];
                $info[$i++] = ['comp', 'comp_name', 'chayol_name', 'chayol_picture', 'school_name', 'school_logo'];
                $info[$i++] = ['promotions_intro', 'promotions_intro', '', '', $schools[$school], $logo_url];

                foreach ($rankNames as $rank => $desc) {
                    foreach ($grades as $grade) {
                        if (isset($other[$grade][$rank])) {
                            $j = 1;
                            $info[$i++] = [($desc . '_intro'), ucwords(str_replace('_', ' ', ($desc . '_' . $j++))), '', '', $school_name, $logo_url]; // rank intro

                            foreach ($other[$grade][$rank] as $user_id) {
                                $user_ids[] = $user_id;
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

                $info[$i] = ['outro', 'outro', '', '', $schools[$school], $logo_url]; // outro
                if (count($ranks)) {
                    $file_name = $schools[$school] . " " . implode('-', $grades) . ".csv";
                    createFile($file_name, $info);

                    $dates = $r->getHeReportDates();
                    $str = "Schools By Grade:\nStart Date: " . $dates['start_he'] . "\nEnd Date: " . $dates['end_he'];
                    createFile("dates2.txt", $str);
                }
            }
        }
    }
}

function generateRestOfChildren() {
    global $school, $schools, $user_ids;

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

    $r = new RankReport();
    $r->setSchoolId($school);
    $r->setOtherChildren("<br>", $user_ids); // make sure to add break in name between first name and last name
    $ranks = $r->getRanks();
    $users = $r->getUserInfo();
    $pics = $r->getUserPic();
    $picOnly = $r->getPicOnly();
    $logos = $r->getSchoolLogos();

    if (count($ranks)) {
        foreach ($ranks as $school_name => $other) {
            // OT wants the files to be separated into 3, one for grade 1, another for grades 2-5, and another for grades 6-7
            $needed = [[1], [2, 3, 4, 5], [6, 7]];
            foreach ($needed as $grades) {
                $logoContent = file_get_contents("http://mashpia.com/schoolLogos/" . rawurlencode($logos[$schools[$school]]['logo_boys']));
                $logo_img = @imagecreatefromstring($logoContent);
                $logo_url = 'images/' . $school . '.png';
                if ($logo_img) @imagepng($logo_img, $logo_url);
                else $logo_url = '';

                $i = 0;
                $info = [];
                $info[$i++] = ['comp', 'comp_name', 'chayol_name', 'chayol_picture', 'school_name', 'school_logo'];
                $info[$i++] = ['promotions_intro', 'promotions_intro', '', '', $schools[$school], $logo_url];

                foreach ($rankNames as $rank => $desc) {
                    foreach ($grades as $grade) {
                        if (isset($other[$grade][$rank])) {
                            $j = 1;
                            $info[$i++] = [($desc . '_intro'), ucwords(str_replace('_', ' ', ($desc . '_' . $j++))), '', '', $school_name, $logo_url]; // rank intro

                            foreach ($other[$grade][$rank] as $user_id) {
                                $user_ids[] = $user_id;
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

                $info[$i] = ['outro', 'outro', '', '', $schools[$school], $logo_url]; // outro
                if (count($ranks)) {
                    $file_name = $schools[$school] . "_rest_of_kids_" . implode('-', $grades) . ".csv";
                    createFile($file_name, $info);
                }
            }
        }
    }
}

echo json_encode([
    'success' => true
]);