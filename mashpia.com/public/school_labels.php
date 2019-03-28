<?php
$admin_auth = array('school'); 
require('header.php');

require_once 'class.adminSchools.php';       
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], false);
$schools = $as->getSchools(); 

$schoolInfo = array();
foreach ($schools as $id => $name) {
    $sql = "select shipping_first, shipping_last, shipping_address1, shipping_address2, 
            shipping_city, shipping_state, shipping_postal, shipping_country 
            from schools where school_id = $id 
            and school_era is null";   
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $schoolInfo[$name] = $row;
    }
}
ksort($schoolInfo);
echo "<pre>";
//print_r($schoolInfo);
echo "</pre>";
//exit;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

    <HEAD>
        <TITLE>School Labels</TITLE>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <LINK href="admin_styles.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
        <script type="text/javascript" src="scripts/jquery.styleselect.js"></script>
        <style type="text/css">
            .label {
                width: 2.3in;
                height: 1in;
                font-size: 10px;
                padding: 5px;
                float: left;
            }
            .space {
                width: .35in;
                height: 1in;
                float: left;
                padding: 5px;
            }
            .page-break {
                clear: both;
                page-break-after: always;
            }
            .name {
                width: 2.2in;
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
            fieldset {
                border: 1px solid white;
                padding: 10px;
                padding-top: 0px;
                -moz-border-radius: 10px;
                -webkit-border-radius: 10px;
                border-radius: 10px;
                font-size: 14px;
            }
            legend {
                margin-left: 20px;
                padding: 5px;
                color: purple;
                font-size: 16px;
            }
            .parshos {
                width: 25%;
                float: left;
            }
        </style>
        <script type="text/javascript">
            function check() {
                if ( confirm( "Have you made sure to set your printer margins properly?\nIf not, please click 'cancel', set your margins, and then click 'print' again." ) ) 
                    window.print();
            }
            $( function() {
                $(".sSelect").sSelect();
                $("#toggleParshos").click( function() {
                    $(".parshos input").trigger('click');
                });
            });
        </script>
    </HEAD>   
    
    <BODY>
        <?php include('admin_header.php'); ?>
        <h1 class="no-print">School Labels</h1>     
            <div class="no-print">
                <p>Printing Instructions:<br />
                Step 1: Set the Orientation to <u>Portrait</u><br />
                Step 2: Check 'Shrink to fit Page Width'<br />
                Step 3: In Options check 'Print Background (colors & images)'<br />
                Step 4: In the second tab set all Margins to 0.0 inches (All Sides)<br />
                Step 5: Set all Headers & Footers to Blank</p>
                <p class='print'>
                    <input type="button" value="Print" onclick="window.print()" />
                </p>
            </div>
            <div id="report_div" name="report_div">
                <div class='topSpace'></div>
                <? 
                $c = 1; //counter for columns
                $i = 1; //counter for pages
                foreach ($schoolInfo as $name => $info) {
                    echo "<div class='label'>";
                    echo $info['shipping_first'] . ' ' . $info['shipping_last'] . "<br />";
                    echo $name . "<br />";
                    echo $info['shipping_address1'] . "<br />";
                    if (!empty($info['shipping_address2']))
                        echo $info['shipping_address2'] . "<br />";
                    echo $info['shipping_city'] . ", " . $info['shipping_state'] . ' ' . $info['shipping_postal'];
                    if (!empty($info['shipping_country']))
                        echo "<br />" . $info['shipping_country'];
                    echo "</div>";

                    if (++$c == 4) {
                        $c = 1;
                    } else {
                        echo "<div class='space'></div>";
                    }
                    if (++$i == 31) {
                        echo "<div class='topSpace'></div>";
                        $i = 1;
                    }
                }
                ?>
            </div>
     </BODY>
</HTML>
