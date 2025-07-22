<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

$data = json_decode($_COOKIE['for_labels'], true);
$_POST = $data;
//echo "<pre>"; print_r($data); echo "</pre>";

if (empty($data)) {
  echo "No data provided";
  exit;
}

function getSubjects() {
    global $MASHPIA_DB;
    $sql = "SELECT subject_id, subject_name FROM subjects";
    $stmt = $MASHPIA_DB->query($sql);
    $rows = $stmt->fetchAll();
    $subjects = [];
    foreach ($rows as $row) {
        $subjects[$row['subject_id']] = $row['subject_name'];
    }
    return $subjects;
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

$info = [];
foreach ($schools as $schoolID) {
    foreach ($items_chosen as $cat => $itemsPerCat) {
        $listOfItems = array_keys($itemsPerCat);
        $nameOfFunc = 'get' . str_replace(' ', '', ucwords($cat));
        $info[$cat] = $cs->$nameOfFunc($gender, $schoolID, $listOfItems);
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
                $labels[$user_id][] = $item['item'];
            }
        }
    }
}

// get school, school_address, class, first and last name from user_id
$sql = "
    SELECT 
        s.*, c.class_grade, c.class_sub, u.first, u.last
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
foreach ($labels as $user_id => $items) {
    
}
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
    <button onclick="check()">Print</button>
</div>

<div id="report_div" name="report_div">
  <div class='topSpace'></div>
    
</div>
</body>
</html>