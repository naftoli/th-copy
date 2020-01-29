<?php
$admin_auth = ['school'];
require_once '../header.php';
require_once '../class.rankReport.php';
$r = new RankReport;
$r->setRanks('byGenerals', 10);
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
                background-color: #000000;
            }
            <?php if ( $_POST['rows'] == 1 ) : ?>
                .row:not(.inner) {
                    padding-top: 570px;
                }
            <?php elseif ( $_POST['rows'] == 2 ) : ?>
                .row:not(.inner) {
                    padding-top: 180px;
                }
            <?php endif; ?>
        </style>
    </head>
    <body>
    <?php if ( !isset( $_POST['rows'] ) ) : ?>
            <form action="promotion_pic_general.php" method="post">
                How many rows do you want to show in each screen?<br />
                <select name="rows">
                    <option value='1'>One</option>
                    <option value='2'>Two</option>
                </select><br /><br />
                <input type="submit" name="submit" value="submit" />
            </form>
        <?php else : ?>
        <?php require "promotion_general.php" ?>
        <?php endif; ?>
    </body>
</html>