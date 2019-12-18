<?php
$admin_auth = ['school'];
require_once '../header.php';
require_once '../class.rankReport.php';
$r = new RankReport;
$r->setRanks('byGenerals', 9);
$ranks = $r->getRanks();
$logos = $r->getSchoolLogos();
$userInfo = $r->getUserInfo();
$userSchool = $r->getUserSchool();
//echo "<pre>"; print_r( $ranks ); echo "</pre>";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <link rel="stylesheet" type="text/css" href="promotion_general.css" />
        <style>
            html {
                background-color: #848786;
            }
        </style>
    </head>
    <body>
        <?php require "promotion_general.php" ?>
    </body>
</html>