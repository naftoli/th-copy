<? // start the script after the catch all title
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
// non super users are told that it is under construction
//if ($admin_user['auth'] != 'super' || !$debug) {
//    echo "<p>Under construction, please come back soon</p>";
//    die();
//}

/***************** RAFFLE IMPORTS **********************/
require_once(dirname(__FILE__).'/../classes/Raffle.php');
require_once(dirname(__FILE__).'/functions/getWinners.php');
use raffles\weekly\Raffle as Raffle; // use the raffle from its namespace

/***************** GET POST PARAMATERS **********************/
include(dirname(__FILE__).'/parts/report_post.php');
// hardcoded array of the type for shipping
$types = array(
    2	=> 'girls', 3	=> 'boys',  4	=> 'boys',  5	=> 'boys',  7	=> 'girls', 9	=> 'boys',  11	=> 'boys',
    19	=> 'boys',  21	=> 'boys',  30	=> 'girls', 33	=> 'boys',  37	=> 'girls', 39	=> 'mixed', 40	=> 'girls',
    42	=> 'girls', 45	=> 'girls', 49	=> 'boys',  48	=> 'boys',  50	=> 'girls', 54	=> 'girls', 55	=> 'mixed', 58	=> 'boys',
    60	=> 'boys',  61	=> 'mixed', 63	=> 'boys',  66	=> 'girls', 80	=> 'mixed', 81	=> 'mixed', 84	=> 'mixed', 87	=> 'mixed', 89	=> 'mixed',
    105	=> 'girls', 106	=> 'mixed', 110	=> 'mixed', 112	=> 'boys',  162	=> 'girls', 176	=> 'girls', 185	=> 'mixed', 192	=> 'girls', 194	=> 'mixed',
    255	=> 'boys',  263	=> 'mixed', 264	=> 'boys',  265	=> 'girls', 269	=> 'mixed', 471	=> 'boys',  427	=> 'mixed'
);

function get_school_shipping_info($school_id){
    $school_sql = "SELECT * FROM schools WHERE school_id = $school_id;";
    return mysql_fetch_assoc(mysql_query($school_sql));
}

function get_school_admin($school_id) {
    $admin_sql = "SELECT * FROM admins JOIN admin_auths aa USING (admin_id) WHERE aa.auth = 'school' AND id = $school_id;";
    return mysql_fetch_assoc(mysql_query($admin_sql));
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Raffle Shipping Report Printout</title>
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        <style>
            .page-break {page-break-after: always;}
            h3 {font-size: 1.2em; margin: .4em .5em; font-weight: normal;}
            .quarter {min-width: 10%;display: inline-block;}
            body { max-width: 780px; margin: 0 auto; }
            img {border-radius: 5px; width:50px}
            th {text-align: left;}      td-img{width: 60px;}
            th.grand_total {font-size: 1.3em;}  .shipping_details {width: 90%;}
            span.checkbox-new{font-size: 1.6em; margin-right: 3%;}
            span.checkbox {display: inline-block;border: 1px solid;height: 15px;width: 15px;margin-right: 10px;}
            div.shipping_box {display: inline-block;width: 30%;margin: 1%;}
            div.shipping_inner_box {display: flex;align-items: center;justify-content: center;}
        </style>
    </head>
    <body onload='window.print();'>
        <? foreach($schools as $school_id => $school_name){
            if (!$winners[$school_id] || count($winners[$school_id]) == 0) continue; // if the school does not have any winners then skip it
            $school = get_school_shipping_info($school_id);
            $admin = get_school_admin($school_id);?>
            <h1><?=$school_name?></h1>
            <h2>
                <?=$school['school_address1']?><br/>
                <?=$school['school_city'].", ".$school['school_state']." ".$school['school_postal'].", ".$school['school_country']?>
            </h2>
            <h3 class="quarter"><strong>Method:</strong> <?=$school['shipping_method'] == "deliver" ? "Delivery" : "Pickup"?></h3>
            <h3 class="quarter"><strong>Type:</strong> <?=$types[$school_id]?></h3>
            <h3 class="quarter"><strong>Principal:</strong> <?=$school['principal']?></h3>
            <h3 class="quarter"><strong>Admin:</strong> <?=$admin['first']." ".$admin['last']?></h3>
            <?if ($school['shipping_requests']) { ?><h3><strong>Requests:</strong> <?=$school['shipping_requests']?></h3><? }?>
            
            <h2>Totals</h2>
            <table style="min-width: <?=count($prize_counts[$school_id]) > 1 ? "100" : "50"; ?>%;">
<!--                <thead>
                    <th colspan=2>Prize</th><th colspan=2>Total</th>
                    <?if (count($prize_counts[$school_id]) > 1) {?><th colspan=2>Prize</th><th colspan=2>Total</th><?}?>
                </thead>-->
                <tbody>
                    <tr>
                    <?  $total_prizes = 0;
                    foreach($prize_counts[$school_id] as $index => $prize_count){
                        $total_prizes += $prize_count['total'];
                        // get the correct url for the image
                        $picture = is_numeric($prize_count['picture']) ? "/file_view.php?id=".$prize_count['picture'] : $prize_count['picture'];
                        if($index % 2 == 0) echo "<tr>";?>
                        <td class="td-img"><img src="<?=$picture?>"/></td>
                        <td><?=$prize_count['prize']?></td>
                        <td><?=$prize_count['total']?></td>
                        <td><span class="checkbox-new"><i class="fa fa-square-o" aria-hidden="true"></i></span></td>
                        <?if($index % 2 != 0) echo "</tr>";?>
                    <? } // end for each prize total?>
                    </tr>
                    <tr>
                        <td></td>
                        <th class="grand_total">Grand Total:</th>
                        <th class="grand_total"><?=$total_prizes?></th>
                        <td><span class="checkbox-new"><i class="fa fa-square-o" aria-hidden="true"></i></span></td>
                    </tr>
                </tbody>
            </table>
            
            <h2>Shipments</h2>
            
            <? foreach($winners[$school_id] as $winner){ ?>
                <div class="shipping_box">
                    <div class="shipping_inner_box">
                        <span class="checkbox-new">
                            <i class="fa fa-square-o" aria-hidden="true"></i>
                            <!--<i class="fa fa-<?=$winner['shipped'] == 1 ? "check-" : ""?>square-o" aria-hidden="true"></i>-->
                        </span>
                        <!--<span class="checkbox"></span>-->
                        <div class="shipping_details">
                            <strong><?= $winner['last']?>, <?= $winner['first']?></strong><br/>
                            Grade: <strong><?= $winner['class_grade']. ($winner['class_sub'] ? " - ". $winner['class_sub'] : "")?></strong><br/>
                            Raffle: <strong><?= $winner['raffle']?></strong><br/>
                            Prize: <strong><?= $winner['prize']?></strong>
                        </div>
                    </div>
                </div>
            <? } // end foreach winners ?>
            
            <div class="page-break"></div>
        <? } // end foreach school ?>
    </body>
</html>
