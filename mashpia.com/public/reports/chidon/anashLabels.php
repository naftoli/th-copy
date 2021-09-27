<?php
ini_set('max_execution_time', 300);
set_time_limit( 300 );
ini_set('display_errors',1);
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

if (isset($_POST['fromDate']) && $_POST['fromDate'] && isset($_POST['toDate']) && $_POST['toDate']) {
    $from = mysql_real_escape_string( $_POST['fromDate'] );
    $to = mysql_real_escape_string( $_POST['toDate'] );
}

$info = [];
$sql = "SELECT 
            a.*
        FROM
            admins a
                JOIN
            admin_auths aa USING (admin_id)
        WHERE
            id IN (SELECT 
                    user_id
                FROM
                    registration_charges
                WHERE
                    year = $year
                        AND type IN ('chidon' , 'yahadus'))";

if (isset($from) && isset($to)) {
    $sql .= " AND date >= '" . $from ."' AND date <= '" . $to . "'";
}
$sql .= " GROUP BY admin_id";

$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}

$details = [];
foreach ($info as $row) {
    $admin_id = $row['admin_id'];
    $sql = "SELECT 
                rc.user_id, rc.type, u.first, c.class_grade
            FROM
                registration_charges rc
                    JOIN
                users u USING (user_id)
                    JOIN
                classes c USING (class_id)
            WHERE
                year = $year
                    AND type IN ('chidon' , 'yahadus')
                    AND user_id IN (SELECT 
                        id
                    FROM
                        admin_auths
                    WHERE
                        admin_id = $admin_id)";
    if (isset($from) && isset($to)) {
        $sql .= " AND date >= '" . $from ."' AND date <= '" . $to . "'";
    }
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $details[$admin_id][$row['type']][] = $row;
    }
}

//echo "<pre>"; print_r( $info ); echo "</pre>";
$cols = 1; //counter for columns
$rows = 1; //counter for rows
function checkForBreak() {
    global $cols, $rows;
    if (($cols % 3) != 0) {
        echo "<div class='space'></div>";
    } else {
        $cols = 0; //reset cols so that it will show new row
        $rows++; //add row
        if ( ($rows % 11) == 0 ) {
            $rows = 1; //reset rows counter and add space to top of new page
            echo "<div class='page-break'></div><div class='topSpace'></div>";
        }
    }
    $cols++;
}
//chdir( $_SERVER['DOCUMENT_ROOT'] );
?>
<!doctype html>
<html>
<head>
    <meta charset="UTF-8" />
    <link href="/admin_styles.css" rel="stylesheet" type="text/css">
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
        }
        .name {
            width: 2.15in;
            font-size: 14px;
        }
        .topSpace {
            height: 0.2in;
            width: 7in;
        }
        .instructions {
            width: 50%;
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
    </style>
    <script type="text/javascript">
        function check() {
            if ( confirm( "Have you made sure to set your printer margins properly?\nIf not, please click 'cancel', set your margins, and then click 'print' again." ) )
                window.print();
        }
    </script>
</head>

<body>
<?php
include($_SERVER['DOCUMENT_ROOT'].'/admin_header.php');
//chdir('reports/chidon/');
?>
<div class="no-print">
    <h1>Labels</h1>
    <form action="anashLabels.php" method="post">
        <p>
            From Date: <input type="date" name="fromDate" />
            To Date: <input type="date" name="toDate" />
            <input type="submit" name="submit" value="submit" />
        </p>
    </form>

    <div class='instructions'>
        <b>Printing Instructions</b><br />
        Set Scale to 90%<br />
        Set your printer margins to the following:<br />
        0.5 Top<br />
        0.3 Left<br />
        0.0 Right and Bottom<br /><br />
        <div align='center'>
            <input type='button' name='print' value='Print' onclick="check()" />
        </div>
    </div>
</div>

    <div id="report_div" name="report_div">
        <div class='topSpace'></div>
        <?php
        foreach ($info as $parent) {
            $name = $parent['first'] . ' ' . $parent['last'];
            $address = $parent['admin_address1'] . "<br />" . $parent['admin_city'] . ', ' . $parent['admin_state'] .
                " " . $parent['admin_postal'] . "<br />" . (empty($parent['admin_country']) ? 'USA' : $parent['admin_country']);

            echo "<div class='label'>";
            echo "<span class='name'>";
            echo "<b>" . $name . "</b><br />" . $address . "</span>";
            echo "</div>";
            checkForBreak();
            echo "<div class='label'>";
            echo "GUIDES:<br />";
            foreach ($details[$parent['admin_id']]['chidon'] as $row) {
                echo "<span class='name'>" . $row['first'] . " - " . (intval($row['class_grade']) - 3) . "</span><br />";
            }
            echo "BOOKS:<br />";
            foreach ($details[$parent['admin_id']]['yahadus'] as $row) {
                echo "<span class='name'>" . $row['first'] . " - " . (intval($row['class_grade']) - 3) . "</span><br />";
            }
            checkForBreak();
        }
        ?>
    </div>
</body>
</html>