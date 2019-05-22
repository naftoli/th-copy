<?php
ini_set('display_errors',1);
$admin_auth = ['school']; 
require('../../header.php');

$info = [];
$sql = "select * from mashpia_charidy.donors d 
        join mashpia_charidy.donations dd using (donor_id) 
        left join admins a on a.admin_id = d.parent_admin_id 
        where amount >= 18 
        and (parent_admin_id > 0 or (address != '' and address is not null))";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $info[] = $row;
}
?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8" />
		<link href="../../admin_styles.css" rel="stylesheet" type="text/css">
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
	<? include('../../admin_header.php'); ?>
	<div class="no-print">
        <h1>Donor Address Labels</h1>        
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
      
      foreach ( $info as $row ) { 
        // if we have admin info, use that, else use donor db
        if ( isset( $row['admin_id'] ) ) {
          $name = $row['first'] . ' ' . $row['last'];
          $address = $row['admin_address1'] . "<br />" . $row['admin_city'] . ', ' . $row['admin_state'] . 
           			" " . $row['admin_postal'] . "<br />" . (empty($row['admin_country']) ? 'USA' : $row['admin_country']);
        } else {
          $name = $row['first_name'] . ' ' . $row['last_name'];
          $address = $row['address'] . "<br />" . $row['city'] . ', ' . $row['state'] . " " . $row['zip'] . "<br />" . $row['country'];
        }
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
	</body>
</html>