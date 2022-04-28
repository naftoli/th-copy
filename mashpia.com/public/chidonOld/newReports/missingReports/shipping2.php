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

require 'functions2.php';

$items = getAllMissingItems();
$users = getMissingUsers($items);
$itemsBySchool = getItemsBySchool();
$recruitmentPrizesById = getRecruitmentPrizesById();
$chidonPrizes = getChidonPrizes();
$userPrizes = getPrizes();
$parentItems = getCelebItemsForParents();

$itemTypes = [
    'recruitement_prize'    => 'Recruitment Prize',
    'surprise_gift'         => 'Surprise Gift',
    'chidon_gift'           => 'Chidon Gift',
    'award'                 => 'Award',
    'celeb_item'            => 'Celebration Item',
    'rebbe_pic_5781'        => 'Rebbe Picture 5781'
];

$keys = array_keys($users);
sort($keys);
echo "<pre>";
print_r($keys);
print_r($itemsBySchool);
echo "</pre>";
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
        @media print {
            h1 {
                display: none;
            }
        }
    </style>
</head>
<body>
<h1>Chidon Missing Items Shipping Report</h1>
<?php
foreach ($itemsBySchool as $school_id => $schoolItems) {
    echo "<h2>" . $schools[$school_id] . "</h2>";
    echo "<h3>Summary</h3>";
    $info = getItemSummary($schoolItems);
    $details = getItemDetails($school_id);
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
                $item = getItemDesc($item, $desc);
                echo "<tr><td>" . $desc . "</td><td>" . $item . "</td><td>" . $total . "</td></tr>";
            }
        }
        ?>
    </table>
    <div style="page-break-after: always"></div>
    <?php echo "<h2>" . $schools[$school_id] . "</h2>"; ?>
    <h3>Details</h3>
    <table>
        <tr>
            <th>User Serial</th>
            <th>Grade</th>
            <th>Student</th>
            <th>Item(s)</th>
        </tr>
        <?php
        foreach ($details as $grade => $more) {
            foreach ($more as $user_id => $other) {
                $user = $users[$user_id];
                echo "<tr><td>" . $user['user_serial'] . "</td><td>" . $grade . "</td><td>" .
                    ($user['first'] . ' ' . $user['last']) . "</td><td>";
                foreach ($other as $item) {
                    echo $item . "<br />";
                }
                echo "</td></tr>";
            }
        }
        ?>
    </table>
    <br />
    <div style="page-break-after: always"></div>
    <?php
}
if (count($parentItems)) {
    echo "<h2>Items That need to be Shipped to Parents</h2>";
    foreach ($parentItems as $admin => $more) {
        $info = $parentItems[$admin][0];
        $address = $info['address'] . ' ' . $info['city'] . ', ' . $info['state'] . ' ' . $info['zip'] . ' ' . $info['country'];
        echo "Admin ID: " . $admin . "<br />";
        echo "Name: " . $info['first'] . ' ' . $info['last'] . "<br />";
        echo "Address: " . $address . "<br />";
        foreach ($more as $item) {
            echo "Item: ";
            if ($item['item'] == 'celeb_box') {
                echo "Celebration Box<br />";
            } else {
                $desc = ucwords($item['type_of_sweater'] . ' Sweater ' . $item['size']);
                echo $desc . "<br />";
            }
        }
        echo "<br />";
    }
}
?>
</body>
</html>