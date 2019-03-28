<?php
ini_set('display_errors',1);
require '../../db.php';
$info = array();
$sql = "select * from th_chidon_bunks where year = 5777";
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
                color: #fff;
                background-color: #000;
            }
            .personal {
                height: 0.7in;
                margin-top: 20px;
                font-size: 14pt;
            }
            .name {
                color: #7e0000;
                font-face: gotham-med;
                font-size: 14pt;
            }
            .bunk {
                border: 5px groove #7e0000;
                padding: 5px;
                width: 1.2in;
                margin: auto;
                margin-bottom: 40px;
                margin-top: 20px;
            }
            .bottomSec {
                width: 4.25in;
                height: 0.5in;
                margin: auto;
                font-size: 14pt;
                padding-top: 10px;
                background-color: #000;
            }
            .back .topSec {
                width: 3.25in;
                height: 0.35in;
                font-size: 14pt;
                background-color:#5c1e2d;
            }
            .back .middle {
                width: 4in;
                height: 1in;
                margin-top: 30px;
                margin-bottom: 30px;
                font-size: 10pt;
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
                width: 2in;
                margin: auto;
                font-family: gotham-med;
                font-size: 10pt;
                margin-bottom: 60px;
            }
            .back .title  {
                font-family: gotham-med;
                font-size: 10pt;
                color: #5c1e2d;
            }
            .back .bus .desc {
                font-family: gotham;
            }
            .back .bus {
                border: 5px groove #5c1e2d;
                padding: 5px;
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
        </style>
    </head>
    
    <body>
        <?php foreach ($info as $row) : ?>
        <div class="card">
            <div class="topSec grade<?=$row['grade']?>">
                Counselor
            </div>
            <img src="chidon.png" width="400" />
            <div class="personal">
                <div class="name">
                    <?php
                    echo $row['counselor'];
                    ?>
                </div>
                Chidon Shabbaton Staff<br />
                Brooklyn, NY
            </div>
            <div class="bunk">
                Bunk:
                <br />
                <?=$row['bunk_name']?>
            </div>
            <div class="bottomSec grade<?=$row['grade']?>"></div>
        </div>
        <div style="page-break-after: always"></div>
        <div class="card back">
            <div class="topSec">
                Contacts
            </div>
            <div class="middle">
                <div class="host">
                    <div class="title">
                        Emergency
                    </div>
                    Hatzola: 718-387-1750<br />
                    Police / Fire: 911
                </div>
                <div class="emerg">
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
                        Coach Bus <b>#<?=$row['c_coach_bus']?></b><br />
                        Dropoff <b>#<?=$row['c_dropoff']?></b><br />
                        School Bus <b>#<?=$row['c_school_bus']?></b><br />
                        Double Decker <b>#<?=$row['c_double_decker']?></b><br />
                    </div>
                </div>
            </div>
            <img src="gradient-background.png" width="400" />
        </div>
        <div style="page-break-after: always"></div>
        <? endforeach; ?>
    </body>
</html>