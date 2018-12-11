<?php
$admin_auth = array();  
require('header.php');
?>
<html>
<head>
    <style>
        body, table {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }
        th, td {
            padding: 5px;
            border: 1px solid black;
        }
        .red {
            color: red;
        }
    </style>
</head>
<body>
<?
/*
    create shipping report that calculates the following:
    number of new kids
    number of old kids

    for each new kid give:
    sticker book
    1st set of sticker boards
    1 pack of scratch-off cards

    for each old kid give:
    sticker book
    2nd set of sticker boards
    1 pack of scratch-off cards


$values = array('id', 'type', 'date', 'old', 'new', 'lastYear');
foreach ($values as $val) { 
    if (!array_key_exists($val, $_GET)) {
        header("Location: admin_shipping_report_new.php");
        exit;
    }
}

//set variables
foreach ($values as $val) {
    $$val = $_GET["$val"];
}
 * 
 */

//print_r($_GET);
foreach ($_GET as $arr) {
    foreach ($arr as $k => $v) {
        $$k = $v;
    }
    
    //get school info
    $sql = "select * from schools where school_id = $id";
    $r = mysql_query($sql);
    $school = mysql_fetch_assoc($r);
    
    //get kids from school
    $children = array();
    $qry = "select user_id, class_grade, class_sub, first, last 
            from users as u, classes as c
            where u.class_id = c.class_id 
            and u.user_registered > 0 
            and u.school_id = $id 
            order by class_grade, class_sub, last, first";
    $result = mysql_query($qry);
    while ($row = mysql_fetch_assoc($result)) {
        $children[] = $row;
    }
    
    //get add_ons
    $add_ons = array();
    $sel = "select title, needs_size from school_add_ons order by school_add_on_id";
    $res = mysql_query($sel);
    while ($row = mysql_fetch_row($res)) {
        $add_ons[$row[0]] = $row[1];
    }
?>
    <p>
        <?=date("F j, Y");?>
    </p>
    <p>
        <?
        echo $school['school_name'] . "<br />";
        echo $school['school_address1'] . "<br />";
        echo $school['school_city'] . ", " . $school['school_state'] . " " . $school['school_postal'] . "<br />";
        ?>
    </p>
    
    <p>
        Totals:<br/>
        Sticker Books: <?=$new + $old;?><br />
        Sticker Board Binder: <?=$new;?><br />
        2nd Set: <?=$lastYear;?><br />
        Charge Cards: <?=$new + $old;?>
    </p>
    
    <p class='red'>* This is for ALL chayolim that registered this year.<br />
    ** This is for FIRST TIME registered chayolim<br />
    *** This is only for chayolim that registered in 5772</p>
            
    <table>
        <tr>
            <th>Name</th>
            <th>Grade</th>
            <th>Sticker Books <span class='red'>*</span></th>
            <th>Sticker Board Binder <span class='red'>**</span></th>
            <th>2nd Set <span class='red'>***</span></th>
            <th>Charge cards <span class='red'>*</span></th>

    <?  
    
    foreach ($add_ons as $k => $v) {
        if ($k == 'Album') 
            $k = 'Album and Rebbe pictures'; 
        echo "<th>$k</th>";
    }
    echo "</tr>";
    
    //variable to find children added to system this year
    $jd = 2456171; //August 31, 2012
    $jdBeg = 2455804; //beginning of last year for 2nd set
    $jdEnd = 2456104; //end of last year for 2nd set
    
    foreach ($children as $child) { 
        if ($type == 'all') {
            //get new kids
            $sql1 = "
                SELECT u.user_id 
                FROM users AS u, schools AS s
                WHERE u.school_id = s.school_id
                AND s.school_id = $id  
                AND u.user_registered > 0 
                AND u.user_start_date > $jd 
                AND u.user_id = $child[user_id]";
            //echo $sql1;
            $result1 = mysql_query($sql1);
            $newNum = mysql_num_rows($result1);
            
            //get old kids
            $sql2 = "
                SELECT u.user_id 
                FROM users AS u, schools AS s
                WHERE u.school_id = s.school_id
                AND s.school_id = $id  
                AND u.user_registered > 0 
                AND u.user_start_date < $jd
                AND u.user_id = $child[user_id]";
            //echo $sql2;
            $result2 = mysql_query($sql2);
            $oldNum = mysql_num_rows($result2);
            
            //get kids registered last year only (not before)
            $sql3 = "
                SELECT u.user_id 
                FROM users AS u, schools AS s
                WHERE u.school_id = s.school_id
                AND s.school_id = $id  
                AND u.user_registered > 0 
                AND u.user_start_date > $jdBeg 
                AND u.user_start_date < $jdEnd 
                AND u.user_id = $child[user_id]";
            //echo $sql3;
            $result3 = mysql_query($sql3);
            $lastYearNum = mysql_num_rows($result3);
        }
        
        else if ($type == 'new') {
            //get new kids 
            $sql1 = "
                SELECT u.user_id 
                FROM users AS u, schools AS s
                WHERE u.school_id = s.school_id
                AND s.school_id = $id  
                AND u.user_registered > '$date'  
                AND u.user_start_date > $jd 
                AND u.user_id = $child[user_id]";
            //echo $sql1;
            $result1 = mysql_query($sql1);
            $newNum = mysql_num_rows($result1);
            
            //get old kids
            $sql2 = "
                SELECT u.user_id 
                FROM users AS u, schools AS s
                WHERE u.school_id = s.school_id
                AND s.school_id = $id  
                AND u.user_registered > '$date' 
                AND u.user_start_date < $jd 
                AND u.user_id = $child[user_id]";
            //echo $sql2;
            $result2 = mysql_query($sql2);
            $oldNum = mysql_num_rows($result2);
            
            //get kids registered last year only (not before)
            $sql3 = "
                SELECT u.user_id 
                FROM users AS u, schools AS s
                WHERE u.school_id = s.school_id
                AND s.school_id = $id   
                AND u.user_registered > 0 
                AND u.user_start_date > $jdBeg 
                AND u.user_start_date < $jdEnd";
            //echo $sql3;
            $result3 = mysql_query($sql3);
            $lastYear = mysql_num_rows($result3);
        }
        if (!$oldNum)
            $oldNum = '';
        if (!$newNum)
            $newNum = '';
        if (!$lastYearNum)
            $lastYearNum = '';
        
        ?>
        <tr>
            <td><?=$child['first'] . " " . $child['last'];?></td>
            <td>&nbsp;
            <? 
                echo $child['class_grade'];
                if ($child['class_sub'] != '') 
                    echo "-" . $child['class_sub'];
            ?>
            </td>
            <td>&nbsp;
            <?  
                if ($newNum || $oldNum) {
                    echo '1';
                }                       
            ?>
            </td>
            <td>&nbsp;<?=$newNum;?></td>
            <td>&nbsp;<?=$lastYearNum;?></td>
            <td>&nbsp;
            <?  
                if ($newNum || $oldNum) {
                    echo '1';
                }                       
            ?>
            </td>
        <?
        
        foreach ($add_ons as $j => $m) { 
            echo "<td>&nbsp;";
            if ($m == 1) {
                $sql = "SELECT size 
                        FROM user_add_ons AS ua, school_add_ons AS sa, users AS u, schools AS s
                        WHERE ua.school_add_on_id = sa.school_add_on_id
                        AND u.user_id = ua.user_id
                        AND s.school_id = u.school_id
                        AND s.school_id = $id
                        AND sa.title = '$j'
                        AND u.user_registered > 0 
                        AND u.user_id = $child[user_id] ";
                if ($type == 'new') $sql .= "AND u.user_registered > '$date' ";
            } else { 
                $sql = "SELECT title 
                        FROM user_add_ons AS ua, school_add_ons AS sa, users AS u, schools AS s
                        WHERE ua.school_add_on_id = sa.school_add_on_id
                        AND u.user_id = ua.user_id
                        AND s.school_id = u.school_id
                        AND s.school_id = $id
                        AND sa.title = '$j'
                        AND u.user_registered > 0 
                        AND u.user_id = $child[user_id] ";
                if ($type == 'new') $sql .= "AND u.user_registered > '$date'";
            }
            $result = mysql_query($sql);
            while ($row = mysql_fetch_assoc($result)) {
                if ($m == 1) 
                    echo strtoupper($row['size']);
                else 
                    echo "1";
            }
            echo "</td>";
        }
        ?>
        </tr>
    <? } ?>
    </table> 
    <br />
    <br />
    <hr />
    <br />
<? } ?>
</body>
</html>