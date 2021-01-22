<?php
ini_set('display_errors', 1);
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission to be here.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

function createFile($info, $name) {
    $fp = fopen($name, "w");
    fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) )); // utf8
    foreach ($info as $fields) {
        fputcsv($fp, $fields, "\t", ' ');
    }
    fclose($fp);
}

function createTextFile($file_name, $dates) {
    $fp = fopen($file_name, "w");
    fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) )); // utf8
    fputs($fp, "start date: " . $dates['start_he'] . "\nend date: " . $dates['end_he']);
    fclose($fp);
}

$rankNames = [
    'General'           =>  'general',
    '1* General'        =>  'one_star_general',
    '2* General'        =>  'two_star_general',
    '3* General'        =>  'three_star_general',
    '4* General'        =>  'four_star_general',
    '5* General'        =>  'five_star_general'
];

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.rankReport.php';
if (isset($_GET['prev']) && intval($_GET['prev'])) $r = new RankReport(true);
else $r = new RankReport();
$r->overrideDates(2459180,2459207);

$i = 0;
$info[$i++] = ['comp', 'comp_name', 'chayol_name', 'chayol_picture', 'school_name', 'school_logo'];

$boySchools =[269,176,112,105,63,81,615,49,89,55,106,470,5,21,4,86,263,60,185,483,80,110,412,659,517,
    3,39,480,19,9,471,614,61,577,255,542,48,180,84,643,427,87,663,33,11,58];
$girlsSchools = [269,54,162,45,30,2,7,112,81,613,192,50,37,265,42,61,40];

//$ords = [9,10,11,12,13,14];
$ords = [9];
foreach ($ords as $ord) {
    $r->setRanks('byRankFirst', $ord, "<br>", '', true); // make sure to add break in name between first name and last name and reverse he names
    $ranks = $r->getRanks();
    $users = $r->getUserInfo();
    $pics = $r->getUserPic();
    $picOnly = $r->getPicOnly();
    $logos = $r->getSchoolLogos();

    if (!empty($ranks)) {
//        $info[$i++] = ['promotions_intro', 'promotions_intro', '', '', $schools[$school], $logo_url]; // intro
        foreach ($ranks as $gender => $other) {
            foreach ($other as $rank => $details) {
                if (!isset($rankNames[$rank])) continue;
                $j = 1;
                $info[$i++] = [($rankNames[$rank] . '_intro'), ucwords(str_replace('_', ' ', ($rankNames[$rank] . '_' . $j++)))]; // rank intro

                // flag for myshliach / anash kinder
                $first_time = true;

                foreach ($details as $school_name => $more) {
                    // logo image
                    $school_id = array_search($school_name, $schools);
                    // myshliach / anash kinder will be split into two schools for boys / girls
                    if (in_array($school_id, [61, 269])) {
                        if ($first_time) {
                            $first_time = false;
                            $school_name = $school_name . "_Boys";
                            $logoContent = file_get_contents("http://mashpia.com/schoolLogos/" . rawurlencode($logos[$school_name]['logo_boys']));
                        } else {
                            $school_name = $school_name . "_Girls";
                            $logoContent = file_get_contents("http://mashpia.com/schoolLogos/" . rawurlencode($logos[$school_name]['logo_girls']));
                        }
                    } else {
                        if (in_array($school_id, $boySchools)) $logoContent = file_get_contents("http://mashpia.com/schoolLogos/" . rawurlencode($logos[$school_name]['logo_boys']));
                        else if (in_array($school_id, $girlsSchools)) $logoContent = file_get_contents("http://mashpia.com/schoolLogos/" . rawurlencode($logos[$school_name]['logo_girls']));
                    }
                    $logo_img = @imagecreatefromstring($logoContent);
                    $logo_url = 'images/' . $school_id . '.png';
                    $logo_image = @imagepng($logo_img, $logo_url);
//                    $images[] = $logo_image;

                    foreach ($more as $user_id) {
                        // create pic of child to add to zipArchive
                        // modify url to work with file_get_contents by enocding only the part that needs to be encoded
                        if (isset($picOnly[$user_id])) $url = "http://mashpia.com/mobile/reg/img/" . rawurlencode($picOnly[$user_id]);
                        else $url = "http://mashpia.com" . $pics[$user_id];
                        $contents = file_get_contents($url);
                        if ($contents) {
                            $new_img = @imagecreatefromstring($contents);
                            $img_url = 'images/' . $user_id . '.png';
                            $new_image = @imagepng($new_img, $img_url);
//                            if ($new_image && !in_array($img_url, $images)) $images[] = $img_url;
                        }

                        $info[$i++] = [
                            $rankNames[$rank],
                            ucwords(str_replace('_', ' ', ($rankNames[$rank] . '_' . $j++))),
                            $users[$user_id],
                            $new_image ? $img_url : '',
                            $school_name,
                            $logo_url
                        ];
                    }
                }
            }
        }
    }
}
$info[$i] = ['outro', 'outro']; // outro
$file_name = "generals.csv";
createFile($info, $file_name);

$dates = $r->getHeReportDates();
createTextFile("dates.txt", $dates);

echo json_encode([
    'success' => true
]);