<?php
ini_set('display_errors',1);
require '../../db.php';
$info = array();
$sql = "select * from th_chidon_chaps tcc 
        join schools s using (school_id)
        where tcc.year = 5777
        and tcc.school_id in (269,105,63,81,49,89,55,106,5,21,4,60,86,430,185,80,3,434,19,9,220,428,61,176,255,13,48,84,427,11,58)";
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
                margin-top: 30px;
                font-size: 14pt;
            }
            .name {
                color: #5c1e2d;
                font-face: gotham-med;
                font-size: 14pt;
            }
            .bottomSec {
                width: 4.25in;
                height: 0.5in;
                margin: auto;
                font-size: 14pt;
                background-color: #000;
                margin-top: 1in;
            }
        </style>
    </head>
    
    <body>
        <?php foreach ($info as $row) : ?>
        <div class="card">
            <div class="topSec">
                Chaperone
            </div>
            <br />
            <img src="chidon.png" width="400" />
            <div class="personal">
                <div class="name">
                    <?php
                    echo ucwords(strtolower($row['name']));
                    ?>
                </div>
                <?=$row['school_name']?><br />
                <?=ucwords(strtolower($row['school_city'])) . ', ' . strtoupper($row['school_state'])?>
            </div>
            <div class="bottomSec"></div>
        </div>
        <div style="page-break-after: always"></div>
        <? endforeach; ?>
    </body>
</html>