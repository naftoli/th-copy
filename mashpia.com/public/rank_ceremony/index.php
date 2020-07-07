<?php
ini_set('display_errors',1);
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.rankReport.php';

$r = new RankReport();
$r->setSchoolId(2);
$dates = $r->getReportDates();
$r->setRanks('byRank');
$ranks = $r->getRanks();
$user = $r->getUserInfo();
$pic = $r->getUserPic();
$logos = $r->getSchoolLogos();
?>
<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <meta charset="utf8" />
        <style>
            tr, th, td {
                font-family: Arial, Helvetica, sans-serif;
                padding: 5px;
                font-size: 14px;
            }
        </style>
    </head>
    <body>
        <?php
        echo "<table><tr><th>comp</th><th>comp_name</th><th>chayol_name</th><th>chayol_picture</th><th>school_name</th><th>school_logo</th></tr>";
        foreach ($ranks as $school => $other) {
            foreach ($other as $rank => $more) {
                $i = 1; // number the users within rank
                foreach ($more as $teacher => $other) {
                    foreach ($other as $grade => $more) {
                        foreach ($more as $user_id) {
                            echo "<tr><td>" . $rank . "</td><td>" . ($rank . '_' . $i++) . "</td><td>" . $user[$user_id] . "</td><td>" . $pic[$user_id] . "</td><td>" . 
                                $school . "</td><td>" . $logos[$school]['logo_id'] . "</td></tr>";
                        }
                    }
                }
            }
        }
        ?>
    </body>
</html>