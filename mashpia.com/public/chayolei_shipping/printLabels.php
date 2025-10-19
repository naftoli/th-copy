<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

$data = json_decode($_COOKIE['for_labels'], true);
$_POST = $data;
$all = $_GET['all'] ?? false;
// echo "<pre>"; print_r($data); echo "</pre>";

if (empty($data)) {
    echo "No data provided";
    exit;
}

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

$items_chosen = $data['items'];
$gender = $data['gender'];
$schools = $data['schools'];
$type = $_GET['type'] ?? '';

if ($type == 'hachayols') {
    if (in_array(61, $schools) && in_array(269, $schools)) {
        header('Location: /myShliachHachayolLabels.php?ak=1');
    } else if (in_array(61, $schools)) {
        header('Location: /myShliachHachayolLabels.php');
    } else if (in_array(269, $schools)) {
        header('Location: /anashHachayolLabels.php');
    }
    exit;
}

$info = [];
if ($all) {
    // show all schools for all items
    foreach ($items_chosen as $cat => $itemsPerCat) {
        $listOfItems = array_keys($itemsPerCat);
        $nameOfFunc = 'get' . str_replace(' ', '', ucwords($cat));
        $info[$cat] = $cs->$nameOfFunc($gender, 0, $listOfItems);
    }
} else {
    foreach ($schools as $schoolID) {
        foreach ($items_chosen as $cat => $itemsPerCat) {
            $listOfItems = array_keys($itemsPerCat);
            $nameOfFunc = 'get' . str_replace(' ', '', ucwords($cat));
            $info[$cat] = $cs->$nameOfFunc($gender, $schoolID, $listOfItems);
        }
    }
}

$getStatus = false;
foreach ($info as $cat => $items) {
    if (!empty($items)) {
        $getStatus = true;
        break;
    }
}
if ($getStatus) {
    $info['status'] = $cs->getStatus();
}

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
                $label = (isset($item['color']) && !empty($item['color']) ? $item['color'] . ' ' : '') . $item['item'];
                $labels[$user_id][] = $label;
            }
        }
    }
}

// get school, school_address, class, first and last name from user_id
$sql = "
    SELECT 
        s.*, c.class_grade, c.class_sub, u.user_id, u.first, u.last
    FROM
        users u
            JOIN
        schools s USING (school_id)
            JOIN
        classes c ON c.class_id = u.class_id
    WHERE
        user_id IN (" . implode(',', array_keys($labels)) . ")";
$stmt = $MASHPIA_DB->query($sql);
$rows = $stmt->fetchAll();
$user_info = [];
foreach ($rows as $row) {
    $user_info[$row['user_id']] = $row;
}

// put all info together into one array
$all_info = [];
$school_info = [];
foreach ($labels as $user_id => $items) {
    $user = $user_info[$user_id];
    $school = $user['school_name'];
    $class = $user['class_grade'] . (empty($user['class_sub']) ? '' : "-" . $user['class_sub']);
    $name = $user['first'] . " " . $user['last'];
    $all_info[$school][$class][$name] = $items;
    $school_info[$user['school_id']] = $user;
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
    $totalnumItems = 0;
    $i = 1; //counter for columns
    $rows = 1; //counter for rows
    $tempSchool = '';
    $schoolChanged = false; //variable to find out when school changes
    $shippingName = '';
    $shippingAddress = '';
    $tempGrade = '';
    $gradeChanged = false; //variable to find out when grade changes
    $firstTime = true;
    foreach ($all_info as $school => $grades) {
        if ($tempSchool != $school) {
            $qry = "select * from schools where school_name = '" . $school . "'";
            $res = mysql_query($qry);
            $r = mysql_fetch_assoc($res);
            $school_id = $r['school_id'];
            $shippingName = $r['shipping_first'] . " " . $r['shipping_last'];
            $shippingAddress = $r['shipping_address1'] .
                (empty($r['shipping_address2']) ? '' : ' ' . $r['shipping_address2']) . "<br />" .
                $r['shipping_city'] . ", " . $r['shipping_state'] . " " . $r['shipping_postal'] .
                "<br />" . $r['shipping_country'];
            $schoolChanged = true;
        }
        $tempSchool = $school;
        foreach ($grades as $grade => $names) {
            if ($tempGrade != $grade) {
                $gradeChanged = true;
            }
            $tempGrade = $grade;
            foreach ($names as $name => $items) {
                $numItems = 1;
                if ($schoolChanged || $gradeChanged) {
                    if ($schoolChanged) {
                        if (!$firstTime) {
                            echo "<div class='page-break'></div><div class='topSpace'></div>";
                            $i = 1;
                        }
                        else $firstTime = false;
                        echo "<div class='label'>";
                        echo "<span class='name'><b>" . $school . "</b><br />" . $shippingName . "<br />" . $shippingAddress . "</span>";
                        $schoolChanged = false;
                    } else if ($gradeChanged) {
                        echo "<div class='label'>";
                        echo "<span class='name'><b>" . $school . "</b><br />" . $grade . "</span>";
                        $gradeChanged = false;
                    }
                    //put current user info on new label so that we don't lose this user
                    echo "</div>";
                    checkForBreak();
                    echo "<div class='label'>";
                    echo "<span class='name'>" . $school . "<br />" . $name . " (Grade: " . $grade . ")</span><br />";
                    foreach ($items as $item) {
                        if ($numItems > 8) {
                            echo "</div>";
                            checkForBreak();
                            echo "<div class='label'>";
                            echo "<span class='name'>" . $school . "<br />" . $name . " (Grade: " . $grade . ") <strong>#2</strong></span><br />";
                            $numItems = 1;
                        }
                        echo "<span class='medal'>" . $item . "</span>";
                        $numItems++;
                        $totalnumItems++;
                    }
                } else {
                    echo "<div class='label'>";
                    echo "<span class='name'>" . $school . "<br />" . $name . " (Grade: " . $grade . ")</span><br />";
                    foreach ($items as $item) {
                        if ($numItems > 8) {
                            echo "</div>";
                            checkForBreak();
                            echo "<div class='label'>";
                            echo "<span class='name'>" . $school . "<br />" . $name . " (Grade: " . $grade . ") <strong>#2</strong></span><br />";
                            $numItems = 1;
                        }
                        echo "<span class='medal'>" . $item . "</span>";
                        $numItems++;
                        $totalnumItems++;
                    }
                }
                echo "</div>";
                checkForBreak();
            }
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