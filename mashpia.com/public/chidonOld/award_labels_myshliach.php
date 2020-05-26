<?php
ini_set('display_errors',1);
$admin_auth = array('school'); 
require('../header.php'); 
require '../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$info = [];
$totals = [];
$sql = "
    SELECT 
        tc.award_type,
        tc.trophy,
        tc.cert_number,
        u.first,
        u.last,
        a.admin_id,  
        a.first as parent_first, 
        a.last as parent_last, 
        a.admin_address1, 
        a.admin_address2, 
        a.admin_city, 
        a.admin_state, 
        a.admin_city, 
        a.admin_postal, 
        a.admin_country
    FROM
        th_chidon tc
            JOIN
        users u USING (user_id)
            JOIN
        admin_auths aa on aa.id = u.user_id 
			JOIN
		admins a using (admin_id) 
    WHERE 
        tc.award_type != '' AND tc.year = " . $year . " 
            AND
        u.school_id = 61 
    ORDER BY 
        a.admin_id, u.last, u.first";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $info[] = $row;
}
// echo "<pre>"; print_r($info); echo "</pre>"; exit;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

    <HEAD>
        <TITLE>Award Labels Report</TITLE>
        <LINK href="../admin_styles.css" rel="stylesheet" type="text/css">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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
                font-size: 11px;
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
    </HEAD>   
    
    <BODY>
        <?php include('../admin_header.php'); ?>
        
        <div class="no-print">
        <h1>Award Labels Report</h1>        
            <div class='instructions'>
                <b>Printing Instructions</b><br />
                Please set your printer margins to the following:<br />
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
            $cols = 1; //counter for columns
            $rows = 1; //counter for rows 

            $admin_id = 0;
            $showAddress = false;
            foreach ( $info as $r ) {  
                if ($admin_id != $r['admin_id']) {
                    $showAddress = true;
                    $admin_id = $r['admin_id'];
                } else {
                    $showAddress = false;
                }
                createLabel( $r, $showAddress );
                if (strtolower($r['award_type']) == 'medal') {
                    $r['award_type'] = 'Plaque';
                    createLabel( $r );
                }
            }

            function createLabel( $r, $showAddress = false ) {
                $shippingName = $r['parent_first'] . ' ' . $r['parent_last'];
                $shipping = empty($r['admin_address2']) ? '' : $r['admin_address2'] . "<br />";
                $shippingAddress = $r['admin_address1'] . "<br />" . $shipping . $r['admin_city'] . 
                        ", " . $r['admin_state'] . " " . $r['admin_postal'] . "<br />" . $r['admin_country'];
                
                if ( $showAddress ) {
                    echo "<div class='label'>";
                    echo "<span class='name'>" . $shippingName . "<br />" . $shippingAddress . "</span>";
                    echo "</div>";
                    checkForBreak();
                }
                echo "<div class='label'>";
                echo "<span class='name'>" . $r['first'] . ' ' . $r['last'] . "</span><br /><br />";
                echo "<b>Cert #:</b> " . $r['cert_number'];
                echo "<b>Award:</b> " . $r['award_type'];
                echo "</div>";
                checkForBreak();
            }

            function checkForBreak() {
                global $cols, $rows;
                if (($cols % 3) != 0) {
                    echo "<div class='space'></div>";
                } else {
                    $cols = 0; //reset i so that it will show new row
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
        
    </BODY>
</HTML>
