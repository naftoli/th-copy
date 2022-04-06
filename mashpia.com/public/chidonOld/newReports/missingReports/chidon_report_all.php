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

$lastYrPrize = [7754682,7754010,7752096,7763443,7758861,7748948,7753225,7748737,7752130,7753071,7746984,7754697,7753730,
7748773,7755768,7749743,7764867,7760035,7764916,7756308,7756824,7749838,7752995,7750084,7759623,7754585,7760545,7756413,
7760809,7749068,7756462,7757183,7758181,7758708,7758056,7760921,7764289,7756164,7753347,7772673,7761591,7753094,7749826,
7766087,7753789,7772982,7754181,7742515,7753093,7772511,7753435,7746921,7755492,7755493,7755624,7750813,7775611,7744977,
7747817,7758009,7758025,7770747,7752534,7749170,7749211,7760401,7759977,7756213];

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
                        if (! ['surprise_gift_5782', 'glass trophy'].includes(desc)) missing[user_id].push({ desc, value })
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
                    $rebbePic = false;

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

                    // check last yrs rebbe picture prize
                    if (in_array($user['user_serial'], $lastYrPrize)) $rebbePic = true;

                    if ($chidon || $surprise || $recruitment || $rebbePic) {
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
                            echo "<br />Recruitment Prize(s):";
                            for ($i = 1; $i <= $recruitment; $i++) {
                                $prize = $recruitmentPrizes[$i];
                                echo "<br /><input type='checkbox' name='recruitment_prize' id='recruitment_prize:{$prize['chidon_credit_prize_id']}'";
                                if (!isMissing($missing, 'recruitment_prize', $prize['chidon_credit_prize_id'])) echo " checked";
                                echo " /> " . $prize['prize'];
                                if ($i == 3) {
                                    if ($user['gender'] == 'M') echo " Navy";
                                    else if ($user['gender'] == 'F') echo "Burgundy";
                                }
                            }
                            echo "<br />";
                        }

                        if ($surprise) {
                            echo "<br /><input type='checkbox' name='surprise_gift' id='surprise_gift'";
                            if (! isMissing($missing, 'surprise_gift')) echo " checked";
                            echo " /> Surprise Gift 5781: Chavat Book<br />";
                            if ($chidon) echo "<input type='checkbox' name='surprise_gift_5782' id='surprise_gift_5782' />
                                    Surprise Gift 5782<br />";
                        }

                        if ($rebbePic) {
                            echo "<br /><input type='checkbox' name='rebbe_pic_5781' id='rebbe_pic_5781' checked /> 
                                Rebbe Picture 5781<br />";
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
                                $award = $awardDesc[$key];
                                if ($award == 'certificate') {
                                    echo "<input type='checkbox' name='award' id='award:{$award}'";
                                    if (! isMissing($missing, 'award', $award)) echo " checked";
                                    echo " /> $award<br />";
                                } else {
                                    for ($a = 1; $a <= $key; $a++) {
                                        $award = $awardDesc[$a];
                                        echo "<input type='checkbox' name='award' id='award:{$award}'";
                                        if ((!isMissing($missing, 'award', $award)) && $award != 'glass trophy') echo " checked";
                                        echo " /> $award<br />";
                                    }
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
