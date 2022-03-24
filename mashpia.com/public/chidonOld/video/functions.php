<?php
function getFinalMarks() {
    global $year;

    $final_marks = [];
    $sql = "select * from th_chidon_finals where year = " . $year;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $final_marks[$row['user_id']] = $row;
    }
    return $final_marks;
}

function getChildren($school_id, $gender) {
    global $year;

    if ($gender == 'boys') $gender = 'M';
    else if ($gender == 'girls') $gender = 'F';

    $children = [];
    $sql = "SELECT 
                u.user_id,
                u.user_serial, 
                u.first,
                u.last,
                u.gender,
                u.chidon_pic_5782,
                u.chidon_pic_5781,
                u.mobile_pic,   
                u.non_th_school_id, 
                u.non_th_school, 
                u.non_th_city, 
                u.non_th_state,
                c.class_grade,
                s.school_id,
                s.school_name,
                s.school_city,
                s.school_state,
                tc.th_chidon_id, 
                tc.khk_reg,
                tc.khk_trip, 
                tc.rep_type, 
                tc.trophy_type,
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
                tc.year = $year AND tc.date_paid > 0 
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

function getAward($child) {
    global $final_marks;

    $tracks = [
        1   => 'yesod',
        2   => 'yediah',
        3   => 'havonah',
        4   => 'iyun'
    ];
    $finals = [
        'yesod'     => 20,
        'yediah'    => 40,
        'havonah'   => 60,
        'iyun'      => 80
    ];
    $needed = [
        'yesod'     => 60,
        'yediah'    => 70,
        'havonah'   => 80,
        'iyun'      => 90
    ];
    $awards = [
        'yesod'     => 'certificate',
        'yediah'    => 'plaque',
        'havonah'   => 'medal / plaque',
        'iyun'      => 'trophy / medal / plaque'
    ];

    $highest_track = $child['highest_track'];
    // find out if award is same as before final or not
    $award = false;
    $key = array_search($highest_track, $tracks);
    if ($key !== false) {
        // go down from key to find where the child is holding
        if (isset($final_marks[$child['user_id']])) {
            $row = $final_marks[$child['user_id']];
            $score = 0;
            for ($i = 1; $i <= $key; $i++) {
                $level = 'level_' . $i;
                if ($row[$level]) {
                    $score += $row[$level];
                }
            }
            for ($i = 1; $i <= $key; $i++) {
                $divide_by = $finals[$tracks[$i]];
                $final_score = number_format(($score / $divide_by) * 100, 2);
                if ($final_score >= $needed[$tracks[$i]]) {
                    $award = $tracks[$i];
                }
            }
        }
    }
    if ($award) return array_search($award, $tracks);
    else return '';
}

function getMarks() {
    $marks = [];
    $sql = "select * from th_khk_marks";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $marks[$row['th_chidon_id']][$row['test_number']] = $row['mark'];
    }
    return $marks;
}

function getUserPrizes() {
    global $year;

    $prizes = [];
    $sql = "SELECT 
                user_id, prize_id
            FROM
                chidon_user_prizes 
            WHERE
                year = " . $year;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $prizes[$row['user_id']][] = $row['prize_id'];
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

    $khk = [];
    $bronze = [];
    $silver = [];
    $gold = [];

    $i = 0;
    $sheet = [];

    $sheet[$i++] = ['comp', 'chayol_name', 'chayol_picture', 'grade', 'school_name', 'school_location', 'school_logo', 'award', 'trip',
        'prize_1', 'prize_2', 'prize_3', 'prize_4', 'prize_5', 'prize_6', 'prize_amount'];
    $sheet[$i++] = ['intro', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];

    // get school name, school location
    $school_name = '';
    $school_location = '';
    $school_logo = '';

    // school info
    foreach ($tracks as $track) {
        if (isset($info[$track])) {
            foreach ($info[$track] as $child) {
                $school_name = preg_replace('/\s+/', ' ', $child['school_name']);
                $school_location = preg_replace('/\s+/', ' ', ($child['school_city'] . ",<br>" . $child['school_state']));
                $school_logo = 'School_' . $child['school_id'];
                if ($child['gender'] == 'M') $school_logo .= '_b';
                else if ($child['gender'] == 'F') $school_logo .= '_g';
                break 2;
            }
        }
    }
    $sheet[$i++] = ['school_intro', '', '', '', $school_name, $school_location, $school_logo, '', '', '', '', '', '', '', '', ''];

    // tracks
    foreach ($tracks as $track) {
        if (isset($info[$track])) {
            $sheet[$i++] = [$track . '_intro', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
            foreach ($info[$track] as $child) {
                $sheet[$i++] = addToSheet($child);
                if (intval($child['khk_reg']) && passedKhk($child['th_chidon_id'])) $khk[] = $child;
                if ($child['trophy_type']) {
                    ${$child['trophy_type']}[$child['class_grade']][$child['rep_type']][] = $child;
                }
            }
        }
    }

    // khk
    if (! empty($khk)) {
        $sheet[$i++] = ['khk_intro', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        foreach ($khk as $child) {
            $sheet[$i++] = addToSheet($child, true);
        }
    }

    // trophies
    $sheet[$i++] = ['trophies_intro', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
    if (! empty($bronze)) {
        foreach ($bronze as $grade => $reps) {
            foreach ($reps as $type => $more) {
                foreach ($more as $child) {
                    $sheet[$i++] = addToSheet($child, false, true);
                }
            }
        }
    }

    $sheet[$i++] = ['outro', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
    return $sheet;
}

function passedKhk($id) {
    global $marks;

    if (isset($marks[$id])) {
        $user_marks = $marks[$id];
        $total = 0;
        foreach ($user_marks as $mark) $total += intval($mark);
        $total /= 4;
        if ($total >= 70) return true;
    }
    return false;
}

function addToSheet($child, $khk = false, $trophy = false) {
    global $prizes;

    $name = preg_replace('/\s+/', ' ', ($child['first'] . ' ' . $child['last']));
    $img_url = $child['user_serial'] . '.png';
    $track = $khk ? 'khk' : $child['highest_track'];
    $award = getAward($child);
    $trip = intval($child['khk_trip']) ? 2 : 1;
    $grade = 'Grade ' . $child['class_grade'];

    if ($trophy) {
        $track = $child['rep_type'] . '_trophy';
    }

    $school_name = '';
    $school_location = '';
    $school_logo = '';
    if ($child['school_id'] == 269) {
        $school_name = preg_replace('/\s+/', ' ', $child['non_th_school']);
        $school_location = preg_replace('/\s+/', ' ', ($child['non_th_city'] . ",<br>" . $child['non_th_state']));
        $school_logo = 'School_' . $child['non_th_school_id'];
        if ($child['gender'] == 'M') $school_logo .= '_b';
        else if ($child['gender'] == 'F') $school_logo .= '_g';
    }

    if ($khk || $trophy) {
        return [$track, $name, $img_url, $grade, $school_name, $school_location, $school_logo, '', '', '', '', '', '', '', '', ''];
    } else {
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
            foreach ($prizes[$child['user_id']] as $idx => $prize_id) {
                $key = $idx + 1;
                ${'prize_' . $key} = "Prize_" . $prize_id . ".png";
            }
        }

        return [$track, $name, $img_url, $grade, $school_name, $school_location, $school_logo, $award, $trip,
            $prize_1, $prize_2, $prize_3, $prize_4, $prize_5, $prize_6, $prize_amount];
    }
}

//function createImages($children) {
//    foreach ($children as $child) {
//        $img = 'http://mashpia.com/mobile/reg/' . (empty($child['chidon_pic_5782']) ? empty($child['chidon_pic_5781']) ?
//                $child['mobile_pic'] : $child['chidon_pic_5781'] : $child['chidon_pic_5782']);
//        $contents = @file_get_contents($img);
//        if ($contents) {
//            $new_img = @imagecreatefromstring($contents);
//            $img_url = 'images/' . $child['user_serial'] . '.png';
//            if ($new_img) @imagepng($new_img, $img_url);
//        }
//    }
//}

function extractFiles($list) {
    $files = [];
    foreach ($list as $name) {
        if (is_dir($name)) continue;
        if ($name === '.' || $name === '..' || strpos($name, '.php') !== false) continue;
        else $files[] = $name;
    }
    return $files;
}

function createZip($files, $filename) {
    $zip = new ZipArchive;
    $success = $zip->open($filename, ZipArchive::CREATE);
    if ($success !== true) {
        exit("cannot open <$filename>\n");
    }
    foreach($files as $file) {
        $zip->addFromString($file, file_get_contents($file));
        unlink($file);
    }
    $zip->close();
}
