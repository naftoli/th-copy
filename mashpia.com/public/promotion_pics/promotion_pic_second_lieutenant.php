<?php
$admin_auth = ['school'];
require_once '../header.php';
require_once '../class.rankReport.php';
$r = new RankReport(true); // true param gets previous report
$r->setRanks('byRankFirst', 4);
$ranks = $r->getRanks();
$logos = $r->getSchoolLogos();
//echo "<pre>"; print_r( $ranks ); echo "</pre>";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <link rel="stylesheet" type="text/css" href="promotion_pics.css" />
        <style>
            html {
                background-color: #f0c62e;
            }
        </style>
    </head>
    <body>
    <?php require "promotion_pic.php" ?>
    </body>
</html>