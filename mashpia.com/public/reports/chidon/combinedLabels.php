<?php
ini_set('max_execution_time', 300);
set_time_limit( 300 );
$admin_auth = ['school']; 
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php'; 
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php'; 

if ( isset( $_POST['date'] ) && $_POST['date'] ) {
    if ( $_POST['date'] == 1 ) {
        $from = '2019-06-01';
        $to = '2019-08-12';
    } else if ( $_POST['date'] == 2 ) {
        $from = '2019-08-13';
        $to = '2019-09-17';
    } else if ( $_POST['date'] == 3 ) {
        $from = '2019-09-18';
        $to = '2019-09-25';
    } else if ( $_POST['date'] == 4 ) {
        $from = '2019-09-26';
        $to = '2019-10-11';
    } 

    $info = [];
    $sql = "
        SELECT 
            u.first,
            u.last,
            a.admin_address1,
            a.admin_address2,
            a.admin_city,
            a.admin_state,
            a.admin_postal,
            a.admin_country,
            a.admin_email
        FROM
            registration_charges rc
                JOIN
            users u USING (user_id)
                JOIN
            admin_auths aa ON aa.id = u.user_id
                JOIN
            admins a ON a.admin_id = aa.admin_id
        WHERE
            type IN ('yahadus' , 'chidon')
                AND rc.year = 5780
                AND rc.school_id in (61, 269) 
                AND rc.date >= '" . $from . " 00:00:00' 
                AND rc.date <= '" . $to . " 23:59:59'
        GROUP BY rc.user_id
        ORDER BY first , last , date
    ";
    $result = mysql_query( $sql );
    while ( $row = mysql_fetch_assoc( $result ) ) {
        $info[] = $row;
    }
}
//echo "<pre>"; print_r( $info ); echo "</pre>";
?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8" />
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
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
	<? include('admin_header.php'); ?>
	<div class="no-print">
        <h1>Hachayol Report</h1>    
        <?php if ( !isset( $_POST['date'] ) ) : ?>
        <form action="combinedLabels.php" method="post">
            <select name="date">
                <option value="0">Choose Batch Number</option>
                <option value="1" 
                <?php if ( isset( $_POST['date'] ) && $_POST['date'] == 1 ) echo "selected" ?>
                >1st Batch (until August 12)</option>
                <option value="2"
                <?php if ( isset( $_POST['date'] ) && $_POST['date'] == 2 ) echo "selected" ?>
                >2nd Batch (from August 13 until Sept 17)</option>
                <option value="3"
                <?php if ( isset( $_POST['date'] ) && $_POST['date'] == 3 ) echo "selected" ?>
                >3rd Batch (from Sept 18 to Sept 25)</option>
                <option value="4"
                <?php if ( isset( $_POST['date'] ) && $_POST['date'] == 4 ) echo "selected" ?>
                >4th Batch (from Sept 26 to Oct 11)</option>
            </select><br /><br />
            <input type="submit" name="submit" value="submit" />
        </form>
        <?php else : ?>
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
            <?
            $cols = 1; //counter for columns
            $rows = 1; //counter for rows
            
			foreach ($info as $parent) {
                $name = $parent['first'] . ' ' . $parent['last'];
                $address = $parent['admin_address1'] . "<br />" . $parent['admin_city'] . ', ' . $parent['admin_state'] . 
                    " " . $parent['admin_postal'] . "<br />" . (empty($parent['admin_country']) ? 'USA' : $parent['admin_country']);
                
                echo "<div class='label'>";
                echo "<span class='name'>";
                echo "<b>" . $name . "</b><br />" . $address . "</span></div>";
                checkForBreak();
			}

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
			?>
        </div>
        <?php endif; ?>
	</body>
</html>