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

$test_num = $_POST['test_num'];
$success = $ct->insertScores($marks);
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
