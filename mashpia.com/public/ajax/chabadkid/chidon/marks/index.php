<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$ct = new ChidonTests();
$year = GlobalSettings::getChidonYear();

function getUserID($chidon_id) {
    $sql = "select user_id from th_chidon where th_chidon_id = " . mysql_real_escape_string($chidon_id);
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    return $row['user_id'];
}

$test_num = intval($_POST['test_num']);
if ($test_num == 4) {
    $levels = [
        'maven'   => 1,
        'pro'     => 2,
        'expert'  => 3,
        'genius'  => 4
    ];

    $qrys = [];
    $info = $_POST['scores'];
    foreach ($info as $id => $more) {
        $user_id = getUserID($id);
        foreach ($more as $test_num => $scores) {
            foreach ($scores as $type => $mark) {
                $mark = intval($mark);
                $level = 'level_' . $levels[$type];
                $sql = "insert into th_chidon_finals 
                        set year = $year, 
                        user_id = $user_id, 
                        $level = $mark
                        on duplicate key update $level = $mark";
                $qrys[] = $sql;
            }
        }
    }
//    echo "<pre>"; print_r($qrys); echo "</pre>"; exit;
    $success = true;
    mysql_query('set autocommit=0');
    mysql_query('begin');
    foreach ($qrys as $qry) {
        if (! mysql_query($qry)) {
            $success = false;
            break;
        }
    }
    if ($success) mysql_query('commit');
    else mysql_query('rollback');
    mysql_query('set autocommit=1');
} else {
    $marks = [];
    $info = $_POST['scores'];
    foreach ($info as $id => $more) {
        foreach ($more as $test_num => $scores) {
            foreach ($scores as $type => $mark) {
                $marks[$id][$test_num]["$type"] = $mark;
            }
        }
    }
    $success = $ct->insertScores($marks);
}
?>
<!DOCTYPE html>
<html>
    <head>
        <script>
            let success = <?= $success ? 1 : 0 ?>;
            let test = <?= $test_num ?>;
            if (success) {
                alert('Marks saved.')
                location.href = "https://mashpia.com/chidonTests/marks.php?test_num=" + test;
            }
            else alert('Error saving marks.')
        </script>
    </head>
</html>
