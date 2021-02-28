<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
$ct = new ChidonTests();

function getNeededMark($school_id, $class_id, $type = '') {
    if (in_array($school_id, [5])) return 85;
    if (in_array($school_id, [106]) && $type != 'maven') return 80;
    if (in_array($school_id, [255]) && $type != 'maven') return 75;
    if (in_array($school_id, [255]) && $type == 'maven') return 80;
    if (in_array($school_id, [4])) return 77;
    if (in_array($class_id, [6088, 6089, 6090])) return 75;
    if (in_array($class_id, [6061, 6260, 6376, 6579, 6821])) return 80;
    return 70;
}

$info = [];
$marks = [];
foreach ($schools as $id => $school) {
    $ct->setStudents($id);
    $info[$id] = $ct->getStudents();
    $ct->setScores();
    $ct->calculateMarks();
    $marks += $ct->getMarks();
}
//echo "<pre>"; print_r($marks); echo "</pre>"; exit;

$types = $ct->getTypes();
$types['trophy'] = 'Trophy';
$types['trophy_extra'] = '';

$eligibility = [];
foreach ($info as $school => $children) {
    if (empty($children)) continue;
    foreach ($children as $child) {
        $id = $child['th_chidon_id'];

        // figure out marks
        $child_marks = [];
        foreach ($types as $type => $value) {
            $total = 0;
            for ($i = 1; $i <= 4; $i++) {
                $mark = isset($marks[$id][$i][$type]) ? $marks[$id][$i][$type] : 0;
                $total += $mark;
            }
            $final = round($total / 4, 2);
            $child_marks[$type] = $final;
        }
//        echo "<pre>"; print_r($child_marks); echo "</pre>";

        $pro_elig = '';
        $expert_elig = '';
        foreach ($child_marks as $type => $final) {
            if ($type == 'trophy_extra') continue;
            switch ($type) {
                case 'maven':
                    if ($final >= getNeededMark($child['school_id'], $child['class_id'], $type)) $eligible = 'sweater';
                    else $eligible = '';
                    break;
                case 'pro':
                    if ($final >= getNeededMark($child['school_id'], $child['class_id'], $type)) $eligible = 'gifts';
                    else $eligible = '';
                    if ($eligible == 'gifts') $pro_elig = 'yes';
                    break;
                case 'expert':
                    if ($final >= getNeededMark($child['school_id'], $child['class_id'], $type)) $eligible = 'trips';
                    else $eligible = '';
                    if ($eligible == 'trips') $expert_elig = 'yes';
                    if ($child['test_type'] == 'pro' && $pro_elig == 'yes') $eligible = 'trips';
                    break;
                case 'trophy':
                    // output 3 parts Avg
                    if (
                        ($expert_elig == 'yes' && $final >= 80) || ($child_marks['trophy_extra'] >= 80)
                    ) $eligible = 'trophy';
                    else $eligible = '';
                    break;
            }
            if (!empty($eligible)) {
                $eligibility[$id] = $eligible;
            }
        }
    }
}
//echo "<pre>"; print_r($eligibility); echo "</pre>";
$qrys = [];
foreach ($eligibility as $id => $value) {
    switch ($value) {
        case 'sweater':
            $qrys[] = "update th_chidon set shabbaton_maven = 1 where th_chidon_id = $id";
            break;
        case 'gifts':
            $qrys[] = "update th_chidon set shabbaton_pro = 1 where th_chidon_id = $id";
            break;
        case 'trips':
            $qrys[] = "update th_chidon set shabbaton_expert = 1 where th_chidon_id = $id";
            break;
        case 'trophy':
            $qrys[] = "update th_chidon set shabbaton_trophy = 1 where th_chidon_id = $id";
            break;
    }
}
//echo "<pre>"; print_r($qrys); echo "</pre>";
$success = true;
mysql_query('set autocommit = 0');
mysql_query('begin');
foreach ($qrys as $sql) {
    if (!mysql_query($sql)) {
        $success = false;
        break;
    }
}
if ($success)
    mysql_query('commit');
else {
    mysql_query('rollback');
    echo mysql_error();
}
mysql_query('set autocommit = 1');
echo 'done.';