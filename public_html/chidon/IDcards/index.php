<?php
ini_set('display_errors',1);
require '../../db.php';
$info = array();
$sql = "select * from th_chidon tc
        join schools s using (school_id)
        join users u on u.user_id = tc.user_id
        join th_chidon_bunks using (bunk_id)
        join th_chidon_teams tct on tct.team_id = tc.team_id  
        where tc.year = 5777
        and tc.paid > 0
        and tc.shabbaton = 1
        and u.gender = 'M'
        order by s.school_name, tc.grade, u.last, u.first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <title>ID Cards</title>
        <style>
            @font-face {
                font-family: gotham;
                src: url('fonts/GOTHAM-BOOK.OTF');
            }
            @font-face {
                font-family: gotham-med;
                src: url('fonts/GOTHAM-MEDIUM.OTF');
            }
            @font-face {
                font-family: heb;
                src: url('fonts/FbReforma-Medium.otf');
            }
            .card {
                width: 4.5in;
                height : 5.5in;
                margin: auto;
                text-align: center;
                font-family: gotham;
            }
            .topSec {
                width: 4.25in;
                height: 1in;
                text-align: center;
                vertical-align: middle;
                margin: auto;
                font-size: 24pt;
                padding-top: 10px;
            }
            .grade4 {
                background-color: #120941;
                color: #FFF;
            }
            .grade5 {
                background-color: #00270a;
                color: #FFF;
            }
            .grade6 {
                background-color: #e89221;
                color: #FFF;
            }
            .grade7 {
                background-color: #febe10;
                color: #FFF;
            }
            .grade8 {
                background-color: #382151;
                color: #FFF;
            }
            .personal {
                height: 0.7in;
                margin-top: 20px;
                font-size: 14pt;
            }
            .name {
                color: #7e0000;
                font-family: gotham-med;
                font-size: 14pt;
            }
            .team, .bunk, .grade {
                border: 5px groove #7e0000;
                padding: 5px;
                width: 1.2in;
                margin-top: 20px;
                margin-bottom: 20px;
            }
            .team {
                float: left;
                margin-left: .75in;
            }
            .teamName {
                font-family: heb;
                font-size: 18px;
            }
            .bunk {
                float: right;
                margin-right: .75in;
            }
            .grade {
                width: 1.2in;
                margin: auto;
                clear: both;
                margin-bottom: 20px;
            }
            .bottomSec {
                width: 4.25in;
                height: 0.5in;
                margin: auto;
                font-size: 14pt;
                padding-top: 10px;
            }
            .back .topSec {
                width: 3.25in;
                height: 0.35in;
                font-size: 14pt;
            }
            .back .middle {
                width: 4in;
                height: 2in;
                margin-top: 10px;
                margin-bottom: 20px;
                font-size: 9.5pt;
                text-align: left;
            }
            .back .host {
                float: left;
                margin-left: 0.65in;
                width: 1.75in;
            }
            .back .emerg {
                float: right;
            }
            .back .info {
                height: 1in;
                margin-top: 10px;
                margin-bottom: 10px;
                font-family: gotham-med;
                font-size: 9.5pt;
                margin-bottom: 20px;
            }
            .back .title  {
                font-family: gotham-med;
                font-size: 9.5pt;
                color: #5c1e2d;
            }
            .back .bus {
                float: left;
                width: 1.25in;
                margin-left: 0.65in;
            }
            .back .bus .desc {
                font-family: gotham;
            }
            .back .other {
                float: right;
                width: 1.25in;
                margin-right: 0.65in;
            }
            .back .other, .back .bus {
                border: 5px groove #5c1e2d;
                padding: 5px;
            }
        </style>
    </head>
    
    <body>
        <?php foreach ($info as $row) : ?>
        <div class="card">
            <div class="topSec grade<?=$row['grade']?>">
                <?php
                if ($row['contestant']) $str = "School Representative";
                else $str = "Contestant";
                echo strtoupper($str);
                ?>
            </div>
            <img src="chidon.png" />
            <div class="personal">
                <div class="name">
                    <?php
                    echo $row['first'] . ' ' . $row['last'] . ' #' . $row['th_chidon_id'];
                    ?>
                </div>
                <?php
                echo $row['school_name'] . "<br />";
                if (in_array($row['school_id'], array(61,269))) {
                    $s = "select admin_city, admin_state from admins
                          where admin_id = (
                            select admin_id from admin_auths
                            where id = " . $row['user_id'] . "
                            and auth = 'user'
                          )";
                    $r = mysql_query($s);
                    $admin = mysql_fetch_assoc($r);
                    echo ucwords(strtolower($admin['admin_city'])) . ", " . strtoupper($admin['admin_state']);
                } else {
                    echo ucwords(strtolower($row['school_city'])) . ", " . strtoupper($row['school_state']);
                }
                ?>
            </div>
            <div class="team">
                Team:
                <br />
                <div class="teamName">
                    <?=$row['team']?>
                </div>
            </div>
            <div class="bunk">
                Bunk:
                <br />
                <?=$row['bunk_name']?>
            </div>
            <div class="grade">
                Grade <?=$row['grade']?>
            </div>
            <div class="bottomSec grade<?=$row['grade']?> bottomText">
                <?php
                $history = $row['history'];
                if (!empty($history)) {
                    $arrHistory = explode(',', $history);
                } else {
                    $arrHistory = array();
                }
                $num = count($arrHistory);
                $year = '1st';
                switch ($num) {
                    case 1:
                        $year = '2nd';
                        break;
                    case 2:
                        $year = '3rd';
                        break;
                    case 3:
                        $year = '4th';
                        break;
                    case 4:
                        $year = '5th';
                        break;
                }
                ?>
                My <b><?=$year?></b> year on Shabbaton
            </div>
        </div>
        <div style="page-break-after: always"></div>
        <div class="card back">
            <div class="topSec grade<?=$row['grade']?>">
                Contacts
            </div>
            <div class="middle">
                <div class="host">
                    <div class="title">
                        Host
                    </div>
                    <?php
                    echo $row['host'] . "<br />" . $row['host_address1'] . " " . $row['host_address2'] . "<br />btw. " .
                        $row['between_streets'] . "<br />" . $row['host_number'];
                    ?>
                    <br /><br />
                    <div class="title">
                        Chaperone
                    </div>
                    <?php
                    $s = "select * from th_chidon_chaps where school_id = " . $row['school_id'] . " and show_id_cards = 1";
                    $r = mysql_query($s);
                    $chap = mysql_fetch_assoc($r);
                    echo $chap['name'] . "<br />" . $chap['phone'];
                    ?>
                    <br /><br />
                    
                    <div class="title">
                        Counselor
                    </div>
                    <?=$row['counselor']?><br />
                    <?=$row['c_number']?>
                    
                </div>
                <div class="emerg">
                    <div class="title">
                        Emergency
                    </div>
                    Hatzola: 718-387-1750<br />
                    Police / Fire: 911<br />
                    <br />
                    <div class="title">
                        Headquarters
                    </div>
                    Fraidy: 412-445-4745<br />
                    Mushka: 507-358-5824
                </div>
            </div>
            <div style="clear: both"></div>
            <div class="info">
                <div class="bus">
                    <div class="title">
                        Bus Numbers
                    </div>
                    <div class="desc">
                        Coach Bus <b>#<?=$row['coach_bus']?></b><br />
                        School Bus <b>#<?=$row['school_bus']?></b><br />
                        Double Decker <b>#<?=$row['double_decker']?></b><br />
                    </div>
                </div>
                <div class="other">
                    <div class="title">
                        Test Table
                    </div>
                    #<?=$row['test_table']?>
                    <br />
                    <div class="title">
                        Bowling Lane
                    </div>
                    #<?=$row['bowling_lane']?>
                </div>
            </div>
            <div style="clear: both"></div>
            <img src="award-ceremony.png" />
        </div>
        <div style="page-break-after: always"></div>
        <hr />
        <? endforeach; ?>
    </body>
</html>