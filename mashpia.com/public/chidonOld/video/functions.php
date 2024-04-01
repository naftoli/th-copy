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
                c.class_sub, 
                s.school_id,
                s.school_name,
                s.school_city,
                s.school_state,
                tc.th_chidon_id, 
                tc.khk_reg,
                tc.khk_trip, 
                tc.rep_type, 
                tc.trophy_type,
                tc.ultimate_trip, 
                tci.highest_track 
            FROM
                users u
                    JOIN
                schools s USING (school_id)
                    JOIN
                classes c ON c.class_id = u.class_id
                    JOIN
                th_chidon tc ON tc.user_id = u.user_id 
                    JOIN
                th_chidon_info tci ON tci.user_id = u.user_id AND tci.year = tc.year 
            WHERE
                tc.year = $year AND tc.date_paid > 0 
                    AND u.gender = '$gender'";
    if ($school_id) $sql .= " AND u.school_id = " . $school_id;
    $sql .= " GROUP BY u.user_id";
    $sql .= " ORDER BY u.school_id, class_grade , last , first";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $award = getAward($row);
        $row['award_track'] = $award;
        $children[] = $row;
    }
    return $children;
}

function getAllChildrenByGender($gender) {
    global $year;

    $children = [];
    $sql = "SELECT 
                u.user_id,
                u.first,
                u.last,
                u.non_th_school_id, 
                u.non_th_school, 
                u.non_th_city, 
                u.non_th_state,
                s.school_id,
                s.school_name,
                s.school_city,
                s.school_state,
                tc.th_chidon_id, 
                tci.highest_track, 
                a.admin_city, 
                a.admin_state       
            FROM
                users u
                    JOIN
                schools s USING (school_id)
                    JOIN
                classes c ON c.class_id = u.class_id 
                    JOIN
                th_chidon tc ON tc.user_id = u.user_id 
                    JOIN
                th_chidon_info tci ON tci.user_id = u.user_id AND tci.year = tc.year 
                    JOIN 
                admin_auths aa on aa.id = u.user_id 
                    JOIN 
                admins a using (admin_id)
            WHERE
                tc.year = $year AND tc.date_paid > 0 
                    AND u.gender = '$gender'";
    $sql .= " GROUP BY u.user_id";
    $sql .= " ORDER BY s.school_name, u.last, u.first";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $row['award_track'] = getAward($row);
        $children[] = $row;
    }
    return $children;
}

function getAward($child) {
    global $cs, $final_marks;

    // awards based off the final marks
    if (isset($final_marks[$child['user_id']])) {
        $child += $final_marks[$child['user_id']];
        $award = $cs->getAwardTrack($child);
        return $award;
    }
    return '';
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

function createFile($name, $info, $csv = false) {
    $fp = fopen($name, "w");
    fputs($fp, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF))); // utf8
    if (is_array($info)) {
        foreach ($info as $fields) {
            if ($csv) fputcsv($fp, $fields);
            else fputcsv($fp, $fields, "\t", ' ');
//            else fputcsv($fp, $fields, "\t", ' ');
        }
    } else {
        fputs($fp, $info);
    }
    fclose($fp);
}

function createSpreadSheet($children, $type = 'ht') {
    $info = [];
    foreach ($children as $child) {
        $track = $type == 'ht' ? $child['highest_track'] : $child['award_track'];
        if (empty($track)) continue;
        $info[$track][] = $child;
    }

    $tracks = ['yesod', 'yediah', 'havonah', 'iyun'];

    $khk = [];
//    $bronze = [];
//    $silver = [];
//    $gold = [];

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
                $school_location = preg_replace('/\s+/', ' ', ($child['school_city'] . ", " . $child['school_state']));
                $school_logo = 'School_' . $child['school_id'];
                if ($child['gender'] == 'M') $school_logo .= '_b';
                else if ($child['gender'] == 'F') $school_logo .= '_g';
                $school_logo .= '.png';
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
                $sheet[$i++] = addToSheet($child, false, false);
                if (intval($child['khk_reg']) && passedKhk($child)) $khk[] = $child;
//                if ($child['trophy_type']) {
//                    ${$child['trophy_type']}[$child['class_grade']][$child['rep_type']][] = $child;
//                }
            }
        }
    }

    // khk
    if (! empty($khk)) {
        // first sort by last name, first name
        foreach ($khk as $key => $row) {
            $first[$key] = $row['last'];
            $last[$key] = $row['last'];
        }
        array_multisort($last, SORT_ASC, $first, SORT_ASC, $khk);

        $sheet[$i++] = ['khk_intro', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        foreach ($khk as  $child) {
            $track = $type == 'ht' ? $child['highest_track'] : $child['award_track'];
            $sheet[$i++] = addToSheet($child, true, false);
        }
    }

    // trophies
//    if (count($bronze) || count($silver) || count($gold)) {
//        $sheet[$i++] = ['trophies_intro', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
//        foreach (['bronze', 'silver', 'gold'] as $trophy) {
//            if (!empty($$trophy)) {
//                foreach ($$trophy as $reps) {
//                    foreach ($reps as $type) {
//                        foreach ($type as $child) {
//                            $sheet[$i++] = addToSheet($child, false, true);
//                        }
//                    }
//                }
//            }
//        }
//    }

    $sheet[$i++] = ['outro', '', 'end.png', '', '', '', '', '', '', '', '', '', '', '', '', ''];
    return $sheet;
}

function passedKhk($child) {
    global $final_marks;

    if (isset($final_marks[$child['user_id']])) {
       $mark = intval($final_marks[$child['user_id']]['khk']);
       if ($mark >= 140) return true;
    }
    return false;

//    if (isset($marks[$id])) {
//        $user_marks = $marks[$id];
//        $total = 0;
//        foreach ($user_marks as $mark) $total += intval($mark);
//        $total /= 4;
//        if ($total >= 70) return true;
//    }
//    return false;
}

function addToSheet($child, $khk = false, $trophy = false) {
    global $prizes;

    $tracks = [
        1 => 'yesod',
        2 => 'yediah',
        3 => 'havonah',
        4 => 'iyun'
    ];

    $name = trim($child['first']) . ' ' . trim($child['last']);
    $img_url = $child['user_serial'] . '.png';
    $award_num = array_search($child['award_track'], $tracks);
    if (in_array($child['highest_track'], ['yesod', 'yediah'])) $trip = 0;
    else $trip = isset($child['ultimate_trip']) && intval($child['ultimate_trip']) == 1 ? 2: 1;
    $grade = 'Grade ' . $child['class_grade'];

//    if ($trophy) {
//        $track = $child['trophy_type'] . '_trophy';
//    }

    $school_name = '';
    $school_location = '';
    $school_logo = '';
    if ($child['school_id'] == 269) {
        $school_name = preg_replace('/\s+/', ' ', $child['non_th_school']);
        $school_location = preg_replace('/\s+/', ' ', ($child['non_th_city'] . ", " . $child['non_th_state']));
        $school_logo = 'School_' . $child['non_th_school_id'];
        if ($child['gender'] == 'M') $school_logo .= '_b';
        else if ($child['gender'] == 'F') $school_logo .= '_g';
    }

    $show_track = $child['highest_track'];
    if ($khk || $trophy) {
        if ($khk) $show_track = 'khk';
        return [$show_track, $name, $img_url, $grade, $school_name, $school_location, $school_logo, $award_num, $trip, '', '', '', '', '', '', ''];
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
        if ($child['highest_track'] != 'yesod' && isset($prizes[$child['user_id']])) {
            $prize_amount = count($prizes[$child['user_id']]);
            foreach ($prizes[$child['user_id']] as $idx => $prize_id) {
                $key = $idx + 1;
                ${'prize_' . $key} = "Prize_" . $prize_id . ".png";
            }
        }

        return [$show_track, $name, $img_url, $grade, $school_name, $school_location, $school_logo, $award_num, $trip,
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

function createAwardCeremonyData($children) {
    $sheets = [];

    // first sort by schools
    $sorted = [];
    foreach ($children as $child) {
        $sorted[$child['school_name']][] = $child;
    }

    $total = 14;
    foreach ($sorted as $school => $more) {
        $sheet = [];
        $names = [];
        $i = 0;
        $numChildren = count($more);
        foreach ($more as $idx => $child) {
            $name = trim($child['first']) . ' ' . trim($child['last']);
            $names[$i++] = $name;
            // we create another row once we get the total amount of children allowed per row or if we are at the last child
            if (($idx + 1) == $numChildren || $i == $total) {
                $school_name = preg_replace('/\s+/', ' ', $child['school_name']);
                $school_location = preg_replace('/\s+/', ' ', ($child['school_city'] . ", " . $child['school_state']));
                $school_name_other = preg_replace('/\s+/', ' ', $child['non_th_school']);
                $child_location = preg_replace('/\s+/', ' ', ($child['admin_city'] . ", " . $child['admin_state']));

                if ($child['school_id'] == 61) {
                    $j = 0;
                    $sheet[$j++] = $school_name;
                    $sheet[$j++] = $school_name_other;
                    $sheet[$j++] = $child_location;
                    foreach ($names as $name) $sheet[$j++] = $name;
                } else if ($child['school_id'] == 269) {
                    $j = 0;
                    $sheet[$j++] = $school_name_other;
                    $sheet[$j++] = '';
                    $sheet[$j++] = $child_location;
                    foreach ($names as $name) $sheet[$j++] = $name;
                } else {
                    $j = 0;
                    $sheet[$j++] = $school_name;
                    $sheet[$j++] = '';
                    $sheet[$j++] = $school_location;
                    foreach ($names as $name) $sheet[$j++] = $name;
                }
                $sheets[] = $sheet;

                // reset vars
                $sheet = [];
                $names = [];
                $i = 0;
            }
        }
    }

    return $sheets;
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

function downloadFile() {
    // loop through dir to get files
    $dir = getcwd();
    $list = scandir($dir);
    $files = extractFiles($list);

    $filename = "Chidon.zip";
    createZip($files, $filename);

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filename));
    flush(); // Flush system output buffer
    readfile($filename);
    unlink($filename);
}

function getSchoolReps($school, $gender) {
    global $year;

    $reps = [];
    $sql = "select * from users u 
            join th_chidon tc using (user_id) 
            where tc.year = $year 
            and u.school_id = $school 
            and school_rep = 1";
    if ($gender == 'boys') $sql .= " and u.gender = 'M'";
    else if ($gender == 'girls') $sql .= " and u.gender = 'F'";
    $sql .= " order by tc.book, u.last, u.first";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $reps[] = $row;
    }
    return $reps;
}

function createRepsSheet($children, $school, $gender) {
    $info = [];
    foreach ($children as $child) {
        $info[$child['book']][] = $child;
    }

    $school_logo = 'School_' . $school;
    if ($gender == 'boys') $school_logo .= '_b';
    else if ($gender == 'girls') $school_logo .= '_g';
    $school_logo .= '.png';

    $sheet = [];
    $sheet[] = ['comp', 'logo', 'image', 'name', 'team name'];
    $sheet[] = ['intro', $school_logo, '', '', ''];

    foreach ($info as $book => $more) {
        $sheet[] = [('book ' . $book . ' intro'), '', '', '', ''];
        foreach ($more as $child) {
            $image = $child['user_serial'] . '.png';
            $name = trim($child['first']) . ' ' . trim($child['last']);
            $team = $child['school_team'];
            $sheet[] = [('book ' . $book), '', $image, $name, $team];
        }
    }
    return $sheet;
}

function getPrizesInfo() {
    global $year;

    $prizes = [];
    $sql = "select * from chidon_prizes where year = " . $year;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $prizes[$row['prize_id']] = $row;
    }
    return $prizes;
}