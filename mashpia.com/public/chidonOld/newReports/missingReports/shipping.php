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

$items = getAllMissingItems();
$users = getMissingUsers($items);
$itemsBySchool = getItemsBySchool($items, $users);
$recruitmentPrizesById = getRecruitmentPrizesById();
$chidonPrizes = getChidonPrizes();

$itemTypes = [
    'recruitement_prize'    => 'Recruitment Prize',
    'surprise_gift'         => 'Surprise Gift',
    'chidon_gift'           => 'Chidon Gift',
    'award'                 => 'Award',
    'celeb_item'            => 'Celebration Item',
    'rebbe_pic_5781'        => 'Rebbe Picture 5781'
];
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Chidon Missing Items Shipping Report</title>
    <style>
        body {
            font-family: Arial, Verdana, sans-serif;
            font-size: 14px;
        }
        tr, th, td {
            font-size: 14px;
            padding: 10px;
            border-bottom: 1px solid grey;
        }
    </style>
</head>
<body>
    <h1>Chidon Missing Items Shipping Report</h1>
    <?php
    foreach ($itemsBySchool as $school_id => $schoolItems) {
        echo "<h2>" . $schools[$school_id] . "</h2>";
        $info = getItemSummary($schoolItems);
        ?>
        <table>
            <tr>
                <th>Item Type</th>
                <th>Item</th>
                <th>Number of Items</th>
            </tr>
            <?php
            foreach ($info as $desc => $more) {
                foreach ($more as $item => $total) {
                    switch ($desc) {
                        case 'recruitment_prize':
                            $item = $recruitmentPrizesById[$item]['prize'];
                            break;
                        case 'surprise_gift':
                            $item = "Surprise Gift: Chavat Book";
                            break;
                        case 'chidon_gift':
                            $item = "Chidon Gift";
                            break;
                        case 'chidon_prize':
                            $prize = $chidonPrizes[$item];
                            $item = $prize['prize_name'] . ' ' . $prize['size'] . ' ' . $prize['color'];
                            break;
                        case 'celeb_item':
                            $celeb_item = getCelebItem($item);
                            if ($celeb_item['name'] == 'sweater') $desc = ucwords($celeb_item['type'] . " " . $celeb_item['name'] . " " . $celeb_item['size']);
                            else $desc = $celeb_item['name'];
                            $item = $desc;
                            break;
                        case 'rebbe_pic_5781':
                            $item = 'Rebbe Picture 5781';
                            break;
                    }
                    echo "<tr><td>" . $desc . "</td><td>" . $item . "</td><td>" . $total . "</td></tr>";
                }
            }
            ?>
        </table>
        <?php
    }
    ?>
</body>
</html>