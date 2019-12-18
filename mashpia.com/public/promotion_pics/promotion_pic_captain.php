<?php
$admin_auth = ['school'];
require_once '../header.php';
require_once '../class.rankReport.php';
$r = new RankReport;
$r->setRanks('byRankFirst', 6);
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
                background-color: #0054a6;
            }
        </style>
    </head>
    <body>
    <?php require "promotion_pic.php" ?>
    </body>
</html>