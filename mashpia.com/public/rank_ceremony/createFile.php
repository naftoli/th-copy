<?php
ini_set('display_errors', 1);
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission to be here.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$school = $_POST['school'];
if (!$school) exit;

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.rankReport.php';

$boySchools =[269,176,112,105,63,81,615,49,89,55,106,470,5,21,4,86,263,60,185,483,80,110,412,659,517,
    3,39,480,19,9,471,614,61,577,255,542,48,180,84,643,427,87,663,33,11,58];
$girlsSchools = [269,54,162,45,30,2,7,112,81,613,192,50,37,265,42,61,40];

// separate myshliach / anashKinder into separate boys/girls files
if (in_array($school, [61,269])) {
    generateFile( 'boys', 'M' );
    generateFile( 'girls', 'F' );
} else {
    if (array_search($school, $boySchools) !== false) generateFile('boys');
    else if (array_search($school, $girlsSchools) !== false) generateFile('girls');
    else exit;
}

function createFile($info, $name)
{
    $fp = fopen($name, "w");
    foreach ($info as $fields) {
        fputcsv($fp, $fields, "\t", ' ');
    }
    fclose($fp);
}

function generateFile( $logoType = '', $limitTo = '' ) {
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

    $r = new RankReport();
    $r->setSchoolId($school);
    if (empty($limitTo)) $r->setRanks('byGender', 0, "<br>", '', '', true); // make sure to add break in name between first name and last name
    else $r->setRanks('byGender', 0, "<br>", '', $limitTo, true); // limit to gender for myshliach / anashKinder
    $ranks = $r->getRanks();
    $users = $r->getUserInfo();
    $pics = $r->getUserPic();
    $picOnly = $r->getPicOnly();
    $logos = $r->getSchoolLogos();
    if (in_array($school, [61,269])) {
        echo "<pre>";
        print_r($users);
        echo "</pre>";
        return;
    }

    if (!empty($ranks)) {
        if ($logoType == 'boys') $logoContent = file_get_contents("http://mashpia.com/schoolLogos/" . rawurlencode($logos[$schools[$school]]['logo_boys']));
        else if ($logoType == 'girls') $logoContent = file_get_contents("http://mashpia.com/schoolLogos/" . rawurlencode($logos[$schools[$school]]['logo_girls']));
        $logo_img = @imagecreatefromstring($logoContent);
        $logo_url = 'images/' . $school . '.png';
        if ($logo_img) @imagepng($logo_img, $logo_url);
        else $logo_url = '';
        $i = 0;
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
        $info[$i] = ['outro', 'outro']; // outro
//        echo "<pre>"; print_r($info); echo "</pre>"; return;
        if (count($ranks)) {
            if ($limitTo == 'M') $file_name = str_replace(' ', '_', $schools[$school]) . "_Boys.csv";
            else if ($limitTo == 'F') $file_name = str_replace(' ', '_', $schools[$school]) . "_Girls.csv";
            else $file_name = str_replace(' ', '_', $schools[$school]) . ".csv";
            createFile($info, $file_name);
        }
    }
}

echo json_encode([
    'success' => true
]);