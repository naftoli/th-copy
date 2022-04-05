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

$awardTypes = [
    'yesod'     => 'certificate',
    'yediah'    => 'plaque',
    'havonah'   => 'plaque and medal',
    'iyun'      => 'plaque, medal, and glass trophy'
];

//echo "Users: " . count($users) . "<br />";
//echo "Chidon Users: " . count($chidonUsers) . "<br />";
//echo "Recruits: " . count($recruitments) . "<br />";
//echo "Surprise Gift: " . count($surpriseGifts) . "<br />";
//echo "Prizes: " . count($prizes) . "<br />";
//echo "Awards: " . count($awards) . "<br />";
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
        <div class="infobox2">
            Please uncheck items which have not been received so we can send it out.
            <br /><br />
            Please only uncheck an item if you have not received it. If you have received an item but it broke, got lost...
            please include all of the details in the comments section under each child and we will see if a replacement can be sent.
        </div>
        <?php
        foreach ($users as $school_id => $more) {
            if (isset($schools[$school_id])) {
                echo "<h2>" . $schools[$school_id] . "</h2>";
                foreach ($more as $user) {
                    // find out if we need to show this child
                    $chidon = false;
                    $surprise = false;
                    $recruitment = false;

                    // check if child in chidon
                    if (isset($chidonUsers[$user['user_id']])) {
                        $chidon = true;
                        $chidonInfo = $chidonUsers[$user['user_id']];
                    }

                    // check surprise gifts
                    if (array_search($user['user_id'], $surpriseGifts) !== false) $surprise = true;

                    // check recruitments
                    if (isset($recruitments[$user['user_id']])) $recruitment = $recruitments[$user['user_id']];
                    else if (isset($recruitments[$user['user_serial']])) $recruitment = $recruitments[$user['user_serial']];

                    if ($chidon || $surprise || $recruitment) {
                        $grade = $user['class_grade'] . ($user['class_sub'] ? '-' . $user['class_sub'] : '');
                        $name = $user['first'] . ' ' . $user['last'];
                        $school = $schools[$school_id];

                        echo "<div class='user' id='{$user['user_id']}'>";
                        echo "<b>Name: " . $name . "</b><br />";
                        echo "Serial: " . $user['user_serial'] . "<br />";
                        echo "School: " . $school . "<br />";
                        echo "Grade: " . $grade . "<br />";

                        if ($recruitment) {
                            $prize = $recruitmentPrizes[$recruitment];
                            echo "<br /><input type='checkbox' name='recruitment_prize' id='recruitment_prize:{$prize['chidon_credit_prize_id']}' checked /> 
                                    Recruitment Prize: " . $prize['prize_name'];
                            if ($recruitment == 3) {
                                if ($user['gender'] == 'M') echo " Navy";
                                else if ($user['gender'] == 'F') echo "Burgundy";
                            }
                            echo "<br />";
                        }

                        if ($surprise) {
                            echo "<br /><input type='checkbox' name='surprise_gift' id='surprise_gift' checked /> 
                                    Surprise Gift: Chavat Book<br />";
                        }

                        if ($chidon) {
                            echo "<br /><input type='checkbox' name='chidon_gift' id='chidon_gift' checked /> Gift: " .
                                getGift($user, $chidonInfo) . "<br />";
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
                                echo "<input type='checkbox' name='chidon_prize' id='chidon_prize:{$prize['prize_id']}' checked /> " .
                                    $desc . "<br />";
                            }

                            if (isset($awards[$user['user_id']])) {
                                echo "<br /><input type='checkbox' name='award' id='award' checked /> Award: " .
                                    $awardTypes[$awards[$user['user_id']]['award']] . "<br />";
                            }
                        }
                        echo "<br /></div>";
                    }
                }
            }
        }
        ?>
    </body>
    <script>
        $( function () {
            let missing = []
            $("input").click(function () {
                let user_id = $(this).parent().attr('id')
                let checked = $(this).is(':checked');
                let id = $(this).attr('id')

                let desc, prize_id
                if (id.includes(':')) {
                    let info = id.split(':')
                    desc = info[0]
                    prize_id = info[1]
                } else {
                    desc = id
                }
                if (! checked) {
                    if (! missing[user_id]) missing[user_id] = []
                    if (prize_id !== undefined) missing[user_id].push({desc, prize_id})
                    else missing[user_id].push({desc})
                    console.log(missing.toString())
                } else {
                    // remove if in missing array
                    if (missing[user_id].length) {
                        for (let i in missing[user_id]) {
                            let item = missing[user_id][i]
                            if (item.desc === desc) missing.user_id.splice(i, 1)
                        }
                    }
                }
            })
        })
    </script>
</html>
