<?php
$admin_auth = array('school'); 
require('header.php');

require_once 'class.adminSchools.php';       
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], false);
$schools = $as->getSchools(); 

$jYear = 5776;
$parshos = array();
$sql1 = "select * from parshos where year = " . $jYear;
$result1 = mysql_query($sql1);
while ($row1 = mysql_fetch_assoc($result1)) {
    $parshos[$row1['name']] = array( 
        'start' => $row1['start'], 
        'end'   => $row1['end']
    );
}

if (isset($_POST['school'])) {
    echo "<pre>";
    //print_r($_POST);
    echo "</pre>";
    //exit;
    
    $school = $_POST['school'];
    $parshas = $_POST['parshas'];

    $names = array();
    $sql = "select s.school_name, u.user_id, u.dob  
            from users u 
            join schools s on (s.school_id = u.school_id) 
            where u.user_registered > 0 
            and u.dob > 0 ";
     if ($school > 0)
        $sql .= "and u.school_id = " . $school;
     else 
        $sql .= "order by s.school_name";
     //echo $sql;
    
    $result = mysql_query($sql);
    if (mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_assoc($result)) {
            //check users dob to see if within dates range
            //get month and day of hebrew birthday
            $dob = explode( '-', $row['dob'] );
            if ($dob[1] == 0 || $dob[2] == 0) continue;
            $jd = gregoriantojd($dob[1], $dob[2], $dob[0]);
            $jewish = jdtojewish($jd);
            $j = explode('/', $jewish);
            $jMonth = $j[0];
            $jDay = $j[1];
            //find this year's jd equivalent of hebrew birthday
            $jdNow = jewishtojd($jMonth, $jDay, $jYear);
            
            $names[$row['school_name']][][$row['user_id']] = $jdNow;
        }
    }
    
    $schoolBDays = array();
    foreach ($parshas as $parsha) {
        $dates = explode(":", $parsha);
        $start = $dates[0];
        $end = $dates[1];
        $name = $dates[2];
        
        foreach ($names as $school => $info) {
            foreach ($info as $users) {
                foreach ($users as $user => $bday) { 
                    if (($bday >= $start) && ($bday <= $end)) {
                        if (isset($schoolBDays[$name][$school])) {
                            $schoolBDays[$name][$school]++;
                        } else {
                            $schoolBDays[$name][$school] = 1;
                        }                    
                    }
                }
            }
        }
    }
    echo "<pre>";
    //print_r($schoolBDays);
    echo "</pre>";
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

    <HEAD>
        <TITLE>Birthday Cards Envelopes</TITLE>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <LINK href="admin_styles.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
        <script type="text/javascript" src="scripts/jquery.styleselect.js"></script>
        <style type="text/css">
            .label {
                width: 2.0in;
                height: .895in;
                font-size: 10px;
                padding: 5px;
                float: left;
            }
            .space {
                width: .35in;
                height: .895in;
                padding: 5px;
                float: left;
            }
            .page-break {
                clear: both;
                page-break-after: always;
            }
            .name {
                width: 2.0in;
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
        <h1 class="no-print">Birthday Cards Envelopes</h1>     
        <?
        if (isset($_POST['school'])) {
            if (empty($names)) {
                echo "Sorry there are no names that meet your criteria, please go back and revise the options.";
                exit;
            }
            ?>
            <div class="no-print">
                <p>Printing Instructions:<br />
                Step 1: Set the Orientation to <u>Portrait</u><br />
                Step 2: Set Scale to 103<br />
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
                foreach ($schoolBDays as $parsha => $info) {
                    foreach ($info as $school => $num) {
                        echo "<div class='label'>Birthday Cards for<br />";
                        echo "<div class='name'>" . $school . "</div>";
                        echo "Number of Cards: " . $num . "<br />";
                        echo $parsha;
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
                    if ($_POST['school'] < 1) {
                        echo "<div class='page-break'></div><div class='topSpace'></div>";
                        $c = 1;
                        $i = 1;
                    } 
                }
                ?>
            </div>
        <? } else { ?>
            <form action="school_bday_labels.php" method="post">
                <select name="school" class="sSelect">
                    <?
                    if (count($schools) > 1) {
                        echo "<option value='0'>Select School</option>";
                        echo "<option value='-1'>All</option>";
                    }
                    foreach ($schools as $id => $school) {
                        echo "<option value=$id>$school</option>";
                    }
                    ?>
                </select><br /><br />
                
                <fieldset>
                    <legend>
                        Parshos
                    </legend>
                    <?
                    $num = count($parshos);
                    $cutoff = (int)($num / 4 + 1);
                    $i = 0;
                    echo "<div class='parshos'>";
                    foreach ($parshos as $name => $dates) {                           
                        if (++$i > $cutoff) {
                            echo "</div><div class='parshos'>";
                            $i = 1;
                        }
                        echo "<input type='checkbox' name='parshas[]' value=" . 
                            $dates['start'] . ":" . $dates['end'] . ":" . $name . " checked>" . $name . "<br />";
                    }
                    echo "</div>";
                    ?>
                    <div style='clear: both'></div>
                    <div align='center'>
                        <br /><input type='button' id='toggleParshos' value='Toggle' />
                    </div>
                </fieldset>
                            
                <br />
                <p align='center'>
                    <input type='submit' name='submit' id='submit' value='Submit' />
                </p>
            </form>
        <? } ?>        
    </BODY>
</HTML>
