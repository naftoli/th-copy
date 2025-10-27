<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

$data = json_decode($_COOKIE['for_labels'], true);
$_POST = $data; // needed for medals and ranks in class.chayoleiShipping.php
// echo "<pre>"; print_r($data); echo "</pre>";
// $all = $_GET['all'] ?? false;

function checkForBreak() {
    global $i, $rows;
    if (($i % 3) != 0) {
        echo "<div class='space'></div>";
    } else {
        $i = 0; //reset i so that it will show new row
        $rows++; //add row
        if (($rows % 11) == 0) {
            $rows = 1; //reset rows counter and add space to top of new page
            echo "<div class='page-break'></div><div class='topSpace'></div>";
        }
    }
    $i++;
}

require 'class.chayoleiShipping.php';
require 'data.php';

$cs = new ChayoleiShipping();
$cs->setYear($data['year']);

$items_chosen = isset($data['items']) ? $data['items'] : [];
$cats = array_keys($items_chosen);
$fields_chosen = array_keys($data['fields']);
$item_details_chosen = isset($data['details']) ? array_keys($data['details']) : [];
$limit_to_status = isset($data['limit_to_status']) ? $data['limit_to_status'] : [];
$schools = isset($data['schools']) ? $data['schools'] : [];
$gender = isset($data['gender']) ? $data['gender'] : '';
$shipment_number = isset($data['shipment_number']) ? $data['shipment_number'] : 0;

$info = [];
foreach ($schools as $schoolID) {
    foreach ($items_chosen as $cat => $itemsPerCat) {
        $listOfItems = array_keys($itemsPerCat);
        $nameOfFunc = 'get' . str_replace(' ', '', ucwords($cat));
        if (!isset($info[$cat])) $info[$cat] = [];
        $info[$cat] += $cs->$nameOfFunc($gender, $schoolID, $listOfItems);
    }
}
if (empty($info)) {
    echo "No data found for current selection";
    exit;
}

$info['status'] = $cs->getStatus();
$labels = [];
foreach ($info as $cat => $more) {
    if ($cat == 'status') continue;
    foreach ($more as $user_id => $items) {
        foreach ($items as $idx => $item) {
            // find out how many of the same item we have
            if ($idx > 0 && $item['id'] == $items[$idx - 1]['id']) $item_num++;
            else $item_num = 0;                

            // get status and whether to show this item
            $show_item = false;
            $status = isset($info['status'][$user_id][$item['id']][$item_num]) ? $info['status'][$user_id][$item['id']][$item_num] : [];
            if (empty($limit_to_status)) $show_item = true;
            else {
                foreach ($limit_to_status as $idx) {
                    if ($idx == 0 && (empty($status) || $status['status'] == 0)) {
                        $show_item = true;
                        break;
                    }
                    else if (!empty($status) && $status['status'] == $idx) {
                        $show_item = true;
                        break;
                    }
                }
            }
            if ($show_item) {
                if ($shipment_number > 0 && isset($status['shipment_number']) && $status['shipment_number'] != $shipment_number) continue;
                $label = (isset($item['color']) && !empty($item['color']) ? $item['color'] . ' ' : '') . $item['item'];
                $labels[$user_id][] = $label;
            }
        }
    }
}

// get parent first, last, address, city, state, postal, country from user_id
$sql = "
    SELECT 
        a.admin_id, a.first as parent_first, a.last as parent_last, a.admin_address1, a.admin_address2, a.admin_city, a.admin_state, a.admin_postal, a.admin_country, 
        u.user_id, u.first, u.last, u.school_id
    FROM
        users u 
        join admin_auths aa on aa.id = u.user_id
        join admins a using (admin_id)
    WHERE
        aa.auth = 'user'
        AND u.user_id IN (" . implode(',', array_keys($labels)) . ")";
$stmt = $MASHPIA_DB->query($sql);
$rows = $stmt->fetchAll();
$user_info = [];
foreach ($rows as $row) {
    $user_info[$row['user_id']] = $row;
}

// put all info together into one array
$all_info = [];
$school_info = [];
$parent_info = [];
foreach ($labels as $user_id => $items) {
    if (!isset($user_info[$user_id])) continue;
    $user = $user_info[$user_id];
    $name = ucwords($user['first'] . " " . $user['last']);
    $admin_id = $user['admin_id'];
    $parent_name = $user['parent_first'] . " " . $user['parent_last'];
    $parent_address = $user['admin_address1'] . " " . $user['admin_address2'] . "<br />" . $user['admin_city'] . ", " . $user['admin_state'] . " " . 
            $user['admin_postal'] . "<br />" . $user['admin_country'];
    $all_info[$admin_id][$user_id] = [
        'name' => $name,
        'items' => $items,
        'school_id' => $user['school_id']
    ];
    $parent_info[$admin_id] = ['parent_name' => ucwords($parent_name), 'parent_address' => $parent_address];
}
// echo "<pre>"; print_r($all_info); echo "</pre>"; exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Labels</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style type="text/css">
        .label {
            width: 2.15in;
            height: 1in;
            font-size: 12px;
            padding: 5px;
            float: left;
        }

        .space {
            width: .35in;
            height: 1in;
            float: left;
            padding: 5px 20px;
        }

        .page-break {
            clear: both;
            page-break-after: always;
        }

        .medal {
            width: 1in;
            float: left;
            font-size: 9px;
        }

        .name {
            width: 2.15in;
            font-size: 14px;
        }

        .topSpace {
            height: 0.5in;
            width: 7in;
        }

        .instructions {
            width: 50%;
        }

        tr, th, td {
            font-family: "Arial", sans-serif;
            padding: 10px;
            font-size: 14px;
            border-bottom: 1px #f0f0f0 solid;
        }

        @media screen {
            #report_div {
                display: none;
            }

            .no-print {
                display: block;
            }
        }

        @media print {
            #report_div {
                display: block;
            }

            .no-print {
                display: none;
            }
        }

        select, button, input[type="button"], input[type="submit"] {
            padding: 5px 10px;
            font-size: 16px;
            cursor: pointer;
        }
    </style>
    <script type="text/javascript">
        function check() {
        if (confirm("Have you made sure to set your printer margins properly?\nIf not, please click 'cancel', set your margins, and then click 'print' again."))
            window.print();
        }
    </script>
</head>
<body>
<div class="no-print">
    <h1>Print Labels</h1>
    <div class="instructions">
    Please set your printer margins to the following:<br/>
    0.5 Top<br/>
    0.3 Left<br/>
    0.0 Right and Bottom<br/><br/>
    </div>
    <button id="printButton" onclick="check()">Print</button>
</div>

<div id="report_div" name="report_div">
<div class='topSpace'></div>
    <?php
    $i = 1; //counter for columns
    $rows = 1; //counter for rows
    foreach ($all_info as $admin_id => $more) {
        $shipping_name = $parent_info[$admin_id]['parent_name'];
        $shipping_address = $parent_info[$admin_id]['parent_address'];
        echo "<div class='label'>";
        echo "<span class='name'><b>" . $shipping_name . " (" . $admin_id . ")</b><br />" . $shipping_address . "</span>";
        echo "</div>";
        checkForBreak();
        foreach ($more as $user_id => $details) {
            $name = $details['name'];
            $items = $details['items'];
            $school_id = $details['school_id'];
            $school = $school_id == 61 ? 'MS' : 'AK';
            echo "<div class='label'>";
            echo "<span class='name'>" . $name . " (" . $school . " - " . $admin_id . ")</span><br />";
            $numItems = 1;
            foreach ($items as $item) {
                if ($numItems > 8) {
                    echo "</div>";
                    checkForBreak();
                    echo "<div class='label'>";
                    echo "<span class='name'>" . $name . " <strong>#2</strong> (" . $school . " - " . $admin_id . ")</span><br />";
                    $numItems = 1;
                }
                echo "<span class='medal'>" . $item . "</span>";
                $numItems++;
            }
            echo "</div>";
            checkForBreak();
        }
    }
    ?>
</div>
</body>
<script>
    window.onload = function() {
        document.getElementById('printButton').click();
    }
</script>
</html