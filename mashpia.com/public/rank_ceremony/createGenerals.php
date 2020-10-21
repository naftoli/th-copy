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
    foreach ($info as $fields) {
        fputcsv($fp, $fields, "\t", ' ');
    }
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
$files = [];
$images = [];
$r = new RankReport();

$i = 0;
$info[$i++] = ['comp', 'comp_name', 'chayol_name', 'chayol_picture', 'school_name', 'school_logo'];

$ords = [9,10,11,12,13,14];
foreach ($ords as $ord) {
    $r->setRanks('byRankFirst', $ord, "<br>"); // make sure to add break in name between first name and last name
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

                foreach ($details as $school_name => $more) {
                    // logo image
                    $school_id = array_search($school_name, $schools);
                    $logoContent = file_get_contents("http://mashpia.com/file_view.php?id=" . $logos[$school_id]['logo_boys']);
                    $logo_img = imagecreatefromstring($logoContent);
                    $logo_url = 'images/' . $school_id . '.png';
                    $logo_image = imagepng($logo_img, $logo_url);
                    $images[] = $logo_image;

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

                        //                        $info[$i]['comp'] = ;
                        //                        $info[$i]['comp_name'] = ;
                        //                        $info[$i]['chayol_name'] = ;
                        //                        $info[$i]['chayol_picture'] = ;
                        //                        $info[$i]['school_name'] = ;
                        //                        $info[$i]['school_logo'] = ;
                        //                        $i++;
                    }
                }
            }
        }
    }
}
$info[$i] = ['outro', 'outro']; // outro
$file_name = "generals.csv";
createFile($info, $file_name);

echo json_encode([
    'success' => true
]);