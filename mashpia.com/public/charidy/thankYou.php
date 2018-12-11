<?php
ini_set('display_errors',1,1);
require '../db.php';

$list = array();
$sql = "select * from charidy_final_list order by mailing_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $list[] = $row;
}

$ranks = array();
$sql = "select rank_ord, rank_name from ranks";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    switch ($row['rank_ord']) {
        case 10:
            $row['rank_name'] = "one-star general";
            break;
        case 11:
            $row['rank_name'] = "two-star general";
            break;
        case 12:
            $row['rank_name'] = "three-star general";
            break;
        case 13:
            $row['rank_name'] = "four-star general";
            break;
        case 14:
            $row['rank_name'] = "five-star general";
            break;
    }
    $ranks[$row['rank_ord']] = strtolower($row['rank_name']);
}
echo "<pre>";
//print_r($list);
//print_r($ranks);
echo "</pre>";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            @font-face {
                font-family: proxima;
                src: url('fonts/proxima.otf');
            }
            @font-face {
                font-family: proximab;
                src: url('fonts/proxima-bold.otf');
            }
            
            .postcard {
                text-align: center;
                height: 7.5in;
                width: 6.25in;
                position: relative;
            }
            
            .postcard img {
                max-height: 100%;
                max-width: 100%;
            }
            
            p {
                font-family: proxima;
                font-size: 14px;
            }
            
            .rank {
                font-family: proximab;
                width: 2.5in;
                margin-top: -10px;
            }
            
            .rank img {
                margin-bottom: -20px;
            }
            
            .email {
                position: absolute;
                top: 6.4in;
                font-size: 18px;
                width: 6.25in;
                left: 0;
                margin: auto;
            }
            
            .mailingID {
                position: absolute;
                top: 6.9in;
                font-size: 12px;
                width: 6.25in;
                text-align: right;
                color: #fff;
                right: 25px;
            }
        </style>
    </head>
    <body>
        <?php foreach ($list as $row) : ?>
            <div class="postcard">
                <img src="Charidy-5777.jpg" />
            </div>
            <div style="page-break-after: always;"></div>
            
            <div class="postcard">
                <p>
                    Dear <?=ucwords(strtolower($row['name']))?>,
                </p>
                
                <p>
                    Wow, what a year it's been at Tzivos Hashem,<br />
                    thanks to your generous contribution.
                </p>
                
                <p>
                    Read on to see the impact your investment has made on our soldiers.
                </p>
                
                <p>
                    This year, we are challenging ourselves to do even more.
                </p>
                
                <img src="Charidy-5777-3.jpg" style="margin-bottom: -20px;" />
    
                <p class="rank" style="float: left; margin-left: 50px;">
                    <img src="logos/<?=$row['rank_5776']?>.png" />
                    Last year, you gave the generous gift of $<?=number_format($row['donation_5776'])?> earning you the honor of <?=$ranks[$row['rank_5776']]?>.
                </p>
                
                <p class="rank" style="float: right; margin-right: 50px;">
                    <img src="logos/<?=$row['rank_5777']?>.png" />
                    This year, can you grow your rank to <?=$ranks[$row['rank_5777']]?> by giving $<?=number_format($row['projected_5777'])?>?
                </p>
                
                <img src="Charidy-5777-2.jpg" style="margin-top: 20px;" />
                
                <p class="email">
                    <?=$row['email']?>
                </p>
                
                <p class="mailingID">
                    <?=$row['mailing_id']?>
                </p>
            </div>
            <div style="page-break-after: always;"></div>
        <?php endforeach; ?>
        <!--
        <?php for ($i = 0; $i < 200; $i++) : ?>
            <div class="postcard">
                <img src="Charidy-5777.jpg" />
            </div>
            <div style="page-break-after: always;"></div>
            
            <div class="postcard">
                <p>
                    Dear &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;,
                </p>
                
                <p>
                    Wow, what a year it's been at Tzivos Hashem,<br />
                    thanks to your generous contribution.
                </p>
                
                <p>
                    Read on to see the impact your investment has made on our soldiers.
                </p>
                
                <p>
                    This year, we are challenging ourselves to do even more.
                </p>
                
                <img src="Charidy-5777-3.jpg" style="margin-bottom: -20px;" />
    
                <p class="rank" style="float: left; margin-left: 50px;">
                    <img src="" width="250" height="250" style="margin-bottom: 0; border: none;" />
                    Last year, you gave the generous gift of $ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; earning you the honor of
                </p>
                
                <p class="rank" style="float: right; margin-right: 50px;">
                    <img src="" width="250" height="250" style="margin-bottom: 0; border: none;" />
                    This year, can you grow your rank to &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; by giving $ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;?
                </p>
                
                <img src="Charidy-5777-2.jpg" style="margin-top: 20px;" />
                
                <p class="email">
                    
                </p>
                
                <p class="mailingID">
                    
                </p>
            </div>
            <div style="page-break-after: always;"></div>
        <?php endfor; ?>
        -->
    </body>
</html>