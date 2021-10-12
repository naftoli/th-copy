<?php
$admin_auth = ['school'];
require_once '../header.php';
require_once '../class.rankReport.php';

if (isset($_POST['submit'])) {
    $from = explode('-', $_POST['fromDate']);
    $to = explode('-', $_POST['toDate']);
    $start = gregoriantojd($from[1], $from[2], $from[0]);
    $end = gregoriantojd($to[1], $to[2], $to[0]);
    // $r = new RankReport(true); // true param gets previous report
    $r = new RankReport();
    $r->overrideDates($start, $end);
    $r->setRanks('byGenerals', 13);
    $ranks = $r->getRanks();
    $logos = $r->getSchoolLogos();
    $userInfo = $r->getUserInfo();
    $userSchool = $r->getUserSchool();
}
//echo "<pre>"; print_r( $ranks ); echo "</pre>";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <link rel="stylesheet" type="text/css" href="promotion_general.css" />
        <style>
            html {
                background-color: #d6dbde;
            }
            .name {
                color: #000;
            }
        </style>
    </head>
    <body>
        <?php
        if (isset($_POST['submit'])) {
            require "promotion_general.php";
        } else {
            ?>
            <form action="" method="post">
                From date: <input type="date" name="fromDate" /><br />
                To date: <input type="date" name="toDate" />
                <input type="submit" name="submit" value="submit" />
            </form>
            <?php
        }
        ?>
    </body>
</html>