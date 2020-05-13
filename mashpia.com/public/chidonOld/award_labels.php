<?php
ini_set('display_errors',1);
$admin_auth = array('school'); 
require('../header.php'); 
require '../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$info = [];
$sql = "
    SELECT 
        tc.award_type,
        tc.trophy,
        s.school_name,
        s.shipping_first,
        s.shipping_last,
        s.shipping_address1,
        s.shipping_address2,
        s.shipping_city,
        s.shipping_state,
        s.shipping_postal,
        s.shipping_country,
        c.class_grade, 
        c.class_sub,
        c.class_teacher,
        u.first,
        u.last
    FROM
        th_chidon tc
            JOIN
        users u USING (user_id)
            JOIN
        schools s ON s.school_id = u.school_id
			JOIN
		classes c ON c.class_id = u.class_id 
    WHERE 
        tc.award_type != '' AND tc.year = " . $year;
$result = mysql_query( $sql );
while ( $row = mysql_query( $sql ) ) {
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
            $i = 1; //counter for columns
            $rows = 1; //counter for rows 
            $tempSchool = '';
            $tempGrade = '';
            $firstTime = true;

            foreach ( $info as $r ) {
                $school = $r['school_name'];
                $grade = $r['school_grade'] . '-' . $r['school_sub'];
                $teacher = $r['class_teacher'];

                $shippingName = $r['shipping_first'] . " " . $r['shipping_last'];
                $shipping = empty($r['shipping_address2']) ? '' : $r['shipping_address2'] . "<br />";
                $shippingAddress = $r['shipping_address1'] . "<br />" . $shipping . $r['shipping_city'] . 
                        ", " . $r['shipping_state'] . " " . $r['shipping_postal'] . "<br />" . $r['shipping_country'];

                $schoolChanged = false;
                $gradeChanged = false; 

                if ($school != $tempSchool) {
                    $tempSchool = $school;
                    $schoolChanged = true;
                }                
                if ( $tempGrade != $grade ) {
                    $tempGrade = $grade;
                    $gradeChanged = true;
                } 
                 
                if ( $schoolChanged || $gradeChanged ) {
                    if ( $schoolChanged ) {
                        //echo "</div>";
                        //checkForBreak();
                        //echo "<div class='page-break'></div><div class='topSpace'></div><div class='label'>";
                        if (!$firstTime) {
                            echo "<div class='page-break'></div><div class='topSpace'></div>";
                            $i = 1;
                        } else $firstTime = false;
                        echo "<div class='label'>";
                        echo "<span class='name'><b>" . $school . "</b><br />" . $shippingName . "<br />" . $shippingAddress . "</span>";
                        $schoolChanged = false;
                    } else if ( $gradeChanged ) {
                        echo "<div class='label'>";
                        echo "<span class='name'><b>" . $school . "</b><br />" . $teacher . "<br />" . $grade . "</span>"; 
                        $gradeChanged = false;
                    }
                    //put current user info on new label so that we don't lose this user
                    echo "</div>";
                    checkForBreak();
                    echo "<div class='label'>";
                    echo "<span class='name'>" . $school . "<br />" . $userInfo[$user] . " (Grade: " . $grade . ")</span><br />";
                    echo "</div>";
                    echo "<div class='label'>";
                    echo "<span class='medal'>Award: " . $r['award_type'] . "</span>";
                    echo "</div>";
                } else {
                    echo "<div class='label'>";
                    echo "<span class='name'>" . $school . "<br />" . $userInfo[$user] . " (Grade: " . $grade . ")</span><br />";
                    echo "</div>";
                    echo "<div class='label'>";
                    echo "<span class='medal'>Award: " . $r['award_type'] . "</span>";
                    echo "</div>";
                }
                // echo "</div>"; 
                checkForBreak();
            }

            function checkForBreak() {
                global $i, $rows;
                if (($i % 3) != 0) {
                    echo "<div class='space'></div>";
                } else {
                    $i = 0; //reset i so that it will show new row
                    $rows++; //add row
                    if ( ($rows % 11) == 0 ) {
                        $rows = 1; //reset rows counter and add space to top of new page
                        echo "<div class='page-break'></div><div class='topSpace'></div>"; 
                    }
                }
                $i++;
            }
            ?>
            
        </div>
        
    </BODY>
</HTML>
