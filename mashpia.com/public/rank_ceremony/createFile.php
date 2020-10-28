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

// separate myshliach / anashKinder into separate boys/girls files
if (in_array($school, [61,269])) {
    generateFile( 'M' );
    generateFile( 'F' );
} else {
    generateFile();
}

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

function createFile($info, $name)
{
    $fp = fopen($name, "w");
    foreach ($info as $fields) {
        fputcsv($fp, $fields, "\t", ' ');
    }
    fclose($fp);
}

function generateFile( $gender = '' ) {
    global $school, $schools, $rankNames;
    $images = [];
    $r = new RankReport();
    $r->setSchoolId($school);
    if (empty($gender)) $r->setRanks('byGender', 0, "<br>"); // make sure to add break in name between first name and last name
    else $r->setRanks('byGender', 0, "<br>", $gender); // limit to gender for myshliach / anashKinder
    $ranks = $r->getRanks();
    $users = $r->getUserInfo();
    $pics = $r->getUserPic();
    $picOnly = $r->getPicOnly();
    $logos = $r->getSchoolLogos();

    if (!empty($ranks)) {
        if ($gender == 'M') $logoContent = file_get_contents("http://mashpia.com/file_view.php?id=" . $logos[$schools[$school]]['logo_boys']);
        else if ($gender == 'F') $logoContent = file_get_contents("http://mashpia.com/file_view.php?id=" . $logos[$schools[$school]]['logo_girls']);
        else $logoContent = file_get_contents("http://mashpia.com/file_view.php?id=" . $logos[$schools[$school]]['logo']);
        $logo_img = imagecreatefromstring($logoContent);
        $logo_url = 'images/' . $school . '.png';
        $logo_image = imagepng($logo_img, $logo_url);
        $images[] = $logo_image;
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
                                    $new_img = imagecreatefromstring($contents);
                                    $img_url = 'images/' . $user_id . '.png';
                                    $new_image = imagepng($new_img, $img_url);
                                    if ($new_image && !in_array($img_url, $images)) $images[] = $img_url;
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
        if (count($ranks)) {
            if ($gender == 'M') $file_name = str_replace(' ', '_', $schools[$school]) . "_Boys.csv";
            else if ($gender == 'F') $file_name = str_replace(' ', '_', $schools[$school]) . "_Girls.csv";
            else $file_name = str_replace(' ', '_', $schools[$school]) . ".csv";
            createFile($info, $file_name);
        }
    }
}

echo json_encode([
    'success' => true
]);