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
$recruitments = getRecruitments();
$recruitmentPrizes = getRecruitmentPrizes();
$surpriseGifts = getSurpriseGifts();
$prizes = getPrizes();
$awards = getAwards();
$celebItems = getCelebrationItems();

$awardTypes = ['yesod', 'yediah', 'havonah', 'iyun'];
$awardDesc = ['certificate', 'plaque', 'medal', 'glass trophy'];
$awardNames = ['Certificate', 'Plaque', 'Plaque and Medal', 'Plaque, Medal and Glass Trophy'];

function isMissing($missing, $desc, $value = '') {
    if (empty($missing)) return false;
    foreach ($missing as $item) {
        // for comments, return comment
        if ($item->desc == 'comments' && $desc == 'comments') return $item->value;
        if ($item->desc == $desc && $item->value == $value) return true;
    }
    return false;
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Full Chidon Report</title>
        <link href="../../../admin_styles.css" rel="stylesheet" type="text/css">
        <style>
            button {
                padding: 5px 10px;
            }
        </style>
        <script>
            window.onload = e => {
                var missing = {}
                $("input").click( function () {
                    let desc, value
                    let user_id = $(this).parent().attr('id')
                    let checked = $(this).is(':checked');
                    let id = $(this).attr('id')
                    if (id.includes(':')) {
                        let info = id.split(':')
                        desc = info[0]
                        value = info[1]
                    } else {
                        desc = id
                        value = ''
                    }

                    if (! checked) {
                        if (! missing[user_id]) missing[user_id] = []
                        missing[user_id].push({ desc, value })
                    } else {
                        // remove if in missing array
                        if (missing[user_id].length) {
                            for (let i in missing[user_id]) {
                                let item = missing[user_id][i]
                                if (item.desc === desc && item.value === value) missing[user_id].splice(i, 1)
                            }
                        }
                    }
                })

                $(".comments").blur( function () {
                    let value = $.trim($(this).val())
                    if (value) {
                        let user_id = $(this).parent().attr('id')
                        if ( !missing[user_id]) {
                            missing[user_id] = []
                        }
                        // add comment to user id
                        missing[user_id].push({ desc: 'comments', value })
                    }
                })

                $("#save").click( function (e) {
                    e.preventDefault()
                    console.log(missing)
                    $.post('saveMissing.php', { missing }, function (success) {
                        if (parseInt(success)) alert('Saved.')
                        else alert('Error Saving.')
                    })
                })
            }
        </script>
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
        <div>
            <button id="save">Save</button>
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
                        $missing = getMissingItems($user['user_id']);

                        echo "<div class='user' id='{$user['user_id']}'>";
                        echo "<b>Name: " . $name . "</b><br />";
                        echo "Serial: " . $user['user_serial'] . "<br />";
                        echo "School: " . $school . "<br />";
                        echo "Grade: " . $grade . "<br />";
                        if ($chidon) {
                            echo "<b>Highest track passed</b> <i>determining rewards</i>: " . $chidonInfo['highest_track'] . "<br />";
                            $key = array_search($awards[$user['user_id']]['award'], $awardTypes);
                            echo "<b>Awards earned based on Final:</b> " . $awardNames[$key] . "<br />";
                        }

                        if ($recruitment) {
                            $prize = $recruitmentPrizes[$recruitment];
                            echo "<br /><input type='checkbox' name='recruitment_prize' id='recruitment_prize:{$prize['chidon_credit_prize_id']}'";
                            if (! isMissing($missing, 'recruitment_prize', $prize['chidon_credit_prize_id'])) echo " checked";
                            echo " />Recruitment Prize: " . $prize['prize'];
                            if ($recruitment == 3) {
                                if ($user['gender'] == 'M') echo " Navy";
                                else if ($user['gender'] == 'F') echo "Burgundy";
                            }
                            echo "<br />";
                        }

                        if ($surprise) {
                            echo "<br /><input type='checkbox' name='surprise_gift' id='surprise_gift'";
                            if (! isMissing($missing, 'surprise_gift')) echo " checked";
                            echo " /> Surprise Gift: Chavat Book<br />";
                        }

                        if ($chidon) {
                            $user['yarmulka'] = $chidonInfo['yarmulka'];
                            echo "<br /><input type='checkbox' name='chidon_gift' id='chidon_gift'";
                            if (! isMissing($missing, 'chidon_gift')) echo " checked";
                            echo " /> Gift: " . getGift($user) . "<br />";
                            // prizes
                            if ($chidonInfo['highest_track'] != 'yesod') {
                                echo "<br />Prizes:<br />";
                                foreach ($prizes[$user['user_id']] as $prize) {
                                    $pName = $prize['prize_name'];
                                    $pColor = $prize['color'];
                                    $pSize = $prize['size'];
                                    $desc = $pName;
                                    if ($pColor) $desc .= ' ' . $pColor;
                                    if ($pSize) $desc .= ' ' . $pSize;
                                    if ($prize['he_name']) $desc .= ' ' . $prize['he_name'];
                                    echo "<input type='checkbox' name='chidon_prize' id='chidon_prize:{$prize['prize_id']}'";
                                    if (! isMissing($missing, 'chidon_prize', $prize['prize_id'])) echo " checked";
                                    echo " /> " . $desc . "<br />";
                                }
                            }

                            if (isset($awards[$user['user_id']])) {
                                echo "<br />Awards:<br />";
                                $key = array_search($awards[$user['user_id']]['award'], $awardTypes);
                                for ($a = 0; $a <= $key; $a++) {
                                    $actualAward = $awardDesc[$a];
                                    echo "<input type='checkbox' name='award' id='award:{$actualAward}'";
                                    if (! isMissing($missing, 'award', $actualAward)) echo " checked";
                                    echo " /> $actualAward<br />";
                                }
                            }

                            if (isset($celebItems[$user['user_id']])) {
                                echo "<br />Celebration Items:<br />";
                                foreach ($celebItems[$user['user_id']] as $item) {
                                    for ($j = 0; $j < $item['amount']; $j++) {
                                        echo "<input type='checkbox' name='celeb_item' id='celeb_item:{$item['purchase_id']}'";
                                        if (! isMissing($missing, 'celeb_item', $item['purchase_id'])) echo " checked";
                                        echo " /> ";
                                        if ($item['item'] == 'celeb_box') echo "Celebration Box";
                                        else echo ucwords($item['type_of_sweater'] . " " . $item['item'] . " " . $item['size']);
                                        if (isset($item['address'])) {
                                            $address = $item['address'] . " " . $item['city'] . ", " . $item['state'] .
                                                " " . $item['zip'] . " " . $item['country'];
                                        } else {
                                            // get school address
                                            $address = $chidonInfo['school_address1'] . ' ' . $chidonInfo['school_city'] .
                                                ', ' . $chidonInfo['school_state'] . ' ' . $chidonInfo['school_postal'] .
                                                ' ' . $chidonInfo['school_country'];
                                        }
                                        echo " Shipped To: " . $address . "<br />";
                                    }
                                }
                            }
                        }
                        echo "<br />Comments:<br /><textarea cols='50' rows='5' class='comments'>";
                        $comments = isMissing($missing, 'comments');
                        if ($comments) echo $comments;
                        echo "</textarea><br /><br /></div>";
                    }
                }
            }
        }
        ?>
    </body>
</html>
