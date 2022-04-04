<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . "/header.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/class.adminSchools.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/class.globalSettings.php";

$year = GlobalSettings::getChidonYear();
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

require 'functions.php';

$users = getUsers();
$chidonUsers = getChidonUsers();
$usersLastYr = getChidonUsersLastYr();
$recruitments = getRecruitments();
$recruitmentPrizes = getRecruitmentPrizes();
$surpriseGifts = getSurpriseGifts();
$prizes = getPrizes();
$awards = getAwards();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Full Chidon Report</title>
        <link href="../../../admin_styles.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <?php include('../../../admin_header.php'); ?>
        <h1>Full Chidon Report</h1>
        <?php
        foreach ($users as $school_id => $more) {
            if (isset($schools[$school_id])) {
                echo "<h2>" . $schools[$school_id] . "</h2>";
                foreach ($more as $user) {
                    // find out if we need to show this child
                    $chidon = false;
                    $recruitment = false;
                    $surprise = false;

                    // check if child in chidon
                    $key = array_search($user['user_id'], $chidonUsers);
                    if ($key !== false) {
                        $chidon = true;
                        $userInfo = $chidonUsers[$key];
                    }

                    // check recruitments
                    $ids = array_keys($recruitments);
                    if (array_search($user['user_id'], $ids) !== false) $recruitment = $recruitments[$user['user_id']];
                    else if (array_search($user['user_serial'], $ids) !== false) $recruitment = $recruitments[$user['user_serial']];

                    // check surprise gifts
                    if (array_search($user['user_id'], $surpriseGifts) !== false) $surprise = true;

                    if ($chidon || $surprise || $recruitment) {
                        $grade = $user['class_grade'] . ($user['class_sub'] ? '-' . $user['class_sub'] : '');
                        $name = $user['first'] . ' ' . $user['last'];
                        $school = $schools[$school_id];

                        echo "<b>Name: " . $name . "</b><br />";
                        echo "Serial: " . $user['user_serial'] . "<br />";
                        echo "School: " . $school . "<br />";

                        if ($recruitment) {
                            $prize = $recruitmentPrizes[$recruitment];
                            echo "<br /><input type='checkbox' name='' id='' checked /> Recruitment Prize: " . $prize;
                            if ($recruitment == 3) {
                                if ($user['gender'] == 'M') echo " Navy";
                                else if ($user['gender'] == 'F') echo "Burgundy";
                            }
                            echo "<br />";
                        }

                        if ($surprise) {
                            echo "<br /><input type='checkbox' name='' id='' checked /> Surprise Gift: Chavat Book<br />";
                        }

                        if ($chidon) {
                            echo "<br /><input type='checkbox' name='' id='' checked /> Chidon Gift: " . getGift($user) . "<br />";
                            // prizes
                            echo "<br />Prizes:<br />";
                            foreach ($prizes[$user['user_id']] as $prize) {
                                $pName = $prize['prize_name'];
                                $pColor = $prize['color'];
                                $pSize = $prize['size'];
                                $desc = $pName;
                                if ($pColor) $desc .= ' ' . $pColor;
                                if ($pSize) $desc .= ' ' . $pSize;
                                if ($prize['he_name']) $desc .= ' ' . $prize['he_name'];
                                echo "<input type='checkbox' name='' id='' checked /> " . $desc . "<br />";
                            }

                            if ($awards[$user['user_id']]) {
                                echo "<br /><input type='checkbox' name='' id='' checked /> Award: " . $awards[$user['user_id']] . "<br />";
                            }
                        }
                        echo "<br /><br />";
                    }
                }
            }
        }
        ?>
    </body>
</html>
