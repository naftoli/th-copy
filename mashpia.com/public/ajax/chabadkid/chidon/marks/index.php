<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$ct = new ChidonTests();
$year = GlobalSettings::getChidonYear();

$marks = [];
$info = $_POST['scores'];
foreach ($info as $id => $more) {
    foreach ($more as $test_num => $scores) {
        foreach ($scores as $type => $mark) {
            $marks[$id][$test_num]["$type"] = $mark;
        }
    }
}

if ($ct->insertScores($marks)) echo "Marks Saved.";
else echo "Error saving marks.";