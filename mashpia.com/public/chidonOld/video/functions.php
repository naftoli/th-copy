<?php
function getChildren($school_id, $gender) {
    if ($gender == 'boys') $gender = 'M';
    else if ($gender == 'girls') $gender = 'F';

    $children = [];
    $sql = "SELECT 
                u.user_id,
                u.first,
                u.last,
                u.gender,
                u.chidon_pic_5782,
                u.chidon_pic_5781,
                u.mobile_pic,
                c.class_grade,
                s.school_id,
                s.school_name,
                tci.highest_track
            FROM
                users u
                    JOIN
                schools s USING (school_id)
                    JOIN
                classes c ON c.class_id = u.class_id
                    JOIN
                th_chidon_info tci ON tci.user_id = u.user_id
                    JOIN
                th_chidon tc ON tc.user_id = u.user_id
            WHERE
                tc.year = 5782 AND tc.date_paid > 0 
                    AND u.gender = '$gender'";
    if ($school_id) $sql .= " AND u.school_id = " . $school_id;
    $sql .= " ORDER BY u.school_id, highest_track , class_grade , last , first";
//    echo $sql . "<br />";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $children[] = $row;
    }
    return $children;
}

function getUserPrizes() {
    $prizes = [];
    $sql = "SELECT 
                user_id, prize_id
            FROM
                chidon_user_prizes 
            WHERE
                year = 5782";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $prizes[$row['user_id']][] = $row['prize_name'];
    }
    return $prizes;
}

function createFile($name, $info) {
    $fp = fopen($name, "w");
    fputs($fp, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF))); // utf8
    if (is_array($info)) {
        foreach ($info as $fields) {
            fputcsv($fp, $fields, "\t", ' ');
        }
    } else {
        fputs($fp, $info);
    }
    fclose($fp);
}

function createSpreadSheet($children) {
    global $prizes;

    $info = [];
    foreach ($children as $child) {
        $info[$child['highest_track']][] = $child;
    }
    $tracks = ['yesod', 'yediah', 'havonah', 'iyun'];

    $i = 0;
    $sheet = [];

    $sheet[$i++] = ['comp', 'chayol_name', 'chayol_picture', 'grade', 'school_name', 'school_logo',
        'prize_1', 'prize_2', 'prize_3', 'prize_4', 'prize_5', 'prize_6', 'prize_amount', 'track'];
    $sheet[$i++] = ['intro', '', '', '', '', '', '', '', '', '', '', '', '', ''];
    $sheet[$i++] = ['school_intro', '', '', '', '', '', '', '', '', '', '', '', '', ''];

    $school_id = 0;
    foreach ($tracks as $track) {
        if (isset($info[$track])) {
            $sheet[$i++] = [$track . '_rewards_awards_intro', '', '', '', '', '', '', '', '', '', '', '', '', ''];
            foreach ($info[$track] as $child) {
                $school_id = $child['school_id'];
                $name = $child['first'] . "<br />" . $child['last'];
                $img = 'http://mashpia.com/mobile/reg/' . (empty($child['chidon_pic_5782']) ? empty($child['chidon_pic_5781']) ?
                        $child['mobile_pic'] : $child['chidon_pic_5781'] : $child['chidon_pic_5782']);
                $img_url = '';
//                $contents = @file_get_contents($img);
//                if ($contents) {
//                    $new_img = @imagecreatefromstring($contents);
//                    $img_url = 'images/' . $child['user_id'] . '.png';
//                    if ($new_img) @imagepng($new_img, $img_url);
//                }
                $school_name = $child['school_name'];
                $school_logo = '';
                $track = $child['highest_track'];

                // prizes
                $prize_amount = 0;
                // initialize prize vars
                $prize_1 = '';
                $prize_2 = '';
                $prize_3 = '';
                $prize_4 = '';
                $prize_5 = '';
                $prize_6 = '';
                if (isset($prizes[$child['user_id']])) {
                    $prize_amount = count($prizes[$child['user_id']]);
                    foreach ($prizes[$child['user_id']] as $idx => $prize) {
                        $key = $idx + 1;
                        ${'prize_' . $key} = "Prizes/Prize_" . $prize . ".png";
                    }
                }

                $sheet[$i++] = [$track, $name, $img_url, $child['class_grade'], $school_name, $school_logo,
                    $prize_1, $prize_2, $prize_3, $prize_4, $prize_5, $prize_6, $prize_amount, $track];
            }
            $sheet[$i++] = ['outro', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        }
    }
    return $sheet;
}

function extractFiles($list) {
    $files = [];
    foreach ($list as $name) {
        if (is_dir($name)) continue;
        if ($name === '.' || $name === '..' || strpos($name, '.php') !== false) continue;
        else $files[] = $name;
    }
    return $files;
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
        $img = 'images/' . $img;
        $zip->addFromString($img, file_get_contents($img));
        unlink($img);
    }
    $zip->close();
}
