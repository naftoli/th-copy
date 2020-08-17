<?php
ini_set('display_errors',1);
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.rankReport.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';  
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

function createSchoolFile($info) {

}

function createFile($info, $name) {
    $fp = fopen($name, "w");
    foreach ($info as $fields) {
        fputcsv($fp, $fields, "\t", ' ');
    }
    fclose($fp);
}

function createZip($files, $images, $filename) {
    $zip = new ZipArchive;
    $success = $zip->open($filename, ZipArchive::CREATE);
    if ($success !== true) {
        exit("cannot open <$filename>\n");
    }
    foreach($files as $file) {
        $zip->addFromString($file, file_get_contents($file));
        unlink($file);
    }
    foreach ($images as $img) {
        $zip->addFromString($img, file_get_contents($img));
        unlink($img);
    }
    $zip->close();
}

$rankNames = [
    'Sergeant'          =>  'sergeant',
    'Sergeant Major'    =>  'sergeant_major',
    'Second Lieutenant' =>  'second_lieutenant',
    'First Lieutenant'  =>  'first_lieutenant',
    'Captain'           =>  'captain',
    'Major'             =>  'major',
    'Colonel'           =>  'colonel',
    'General'           =>  'general',
    '1* General'        =>  'one_star_general',
    '2* General'        =>  'two_star_general',
    '3* General'        =>  'three_star_general',
    '4* General'        =>  'four_star_general',
    '5* General'        =>  'five_star_general'
];

$files = [];
$images = [];
$r = new RankReport();
foreach ($schools as $id => $school) {
    if ($id == 612) continue;
    $r->setSchoolId(2);
    // $r->setSchoolId($id);
    $r->setRanks('byRank', 0, "<br>"); // make sure to add break in name between first name and last name
    $ranks = $r->getRanks();
    $users = $r->getUserInfo();
    $pics = $r->getUserPic();
    $logos = $r->getSchoolLogos();
    $grades = $r->getGrades();

    foreach ($grades as $g) { // for oholei torah and beis rivka we will have a separate sheet for each grade
        $i = 0;
        $info[$i++] = ['comp', 'comp_name', 'chayol_name', 'chayol_picture', 'school_name', 'school_logo'];
        $info[$i++] = ['promotions_intro', 'promotions_intro', '', '', '', $school, $logos[$school]['logo_id']]; // intro
        foreach ($ranks as $school => $other) {
            foreach ($other as $rank => $more) {
                $j = 1;
                $info[$i++] = [($rankNames[$rank] . '_intro'), ucwords(str_replace('_', ' ', ($rankNames[$rank] . '_' . $j++)))]; // rank intro
                foreach ($more as $teacher => $other) {
                    foreach ($other as $grade => $more) {
                        foreach ($more as $user_id) {
                            // create pic of child to add to zipArchive
                            $url = 'http://mashpia.com' . $pics[$user_id];
                            $img_url = $user_id . '.png';
                            $new_img = imagecreatefromstring(file_get_contents($url));
                            $new_image = imagepng($new_img, $img_url);
                            if ($new_image && !in_array($img_url, $images)) $images[] = $img_url;

                            $info[$i]['comp'] = $rankNames[$rank];
                            $info[$i]['comp_name'] = ucwords(str_replace('_', ' ', ($rankNames[$rank] . '_' . $j++)));
                            $info[$i]['chayol_name'] = $users[$user_id];
                            $info[$i]['chayol_picture'] = $new_image ? $img_url : '';
                            $info[$i]['school_name'] = $school;
                            $info[$i]['school_logo'] = $logos[$school]['logo_id'];
                            $i++;
                        }
                    }
                }
            }
        }
        $info[$i] = ['outro', 'outro']; // outro
        if (count($ranks)) {
            $file_name = "TSV_Report_" . $id . ".csv";
            if (in_array($id, [54,255])) {
                $file_name = "TSV_Report_" . $id . "_" . $g . ".csv";
            }
            createFile($info, $file_name);
            $files[] = $file_name;
            break;
        }
        if (!in_array($id, [54,255])) {
            break;
        }
    }
} 
$filename = "tsv.zip";
createZip($files, $images, $filename);

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'.basename($filename).'"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filename));
flush(); // Flush system output buffer
readfile($filename);
unlink($filename);
?>