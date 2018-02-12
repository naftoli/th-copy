<? // start the script after the catch all title
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

/***************** RAFFLE IMPORTS **********************/
require_once(dirname(__FILE__).'/../classes/Raffle.php');
require_once(dirname(__FILE__).'/functions/getWinners.php');
use raffles\weekly\Raffle as Raffle; // use the raffle from its namespace

// format any debugging info
if($debug) echo "<pre>";

/***************** GET POST PARAMATERS **********************/
include(dirname(__FILE__).'/parts/report_post.php'); // load up all the $winners and $prize_counts

//include(dirname(__FILE__).'/functions/get_tracking_number.php'); // load the get_tracking_number.php

//if($debug) print_r($winners);

// stop formatting any debugging info
if($debug) echo "</pre>";

/***************** RENDER THE TABLE **********************/
foreach($schools as $school_id => $school_name){?>
    <h2><?=$school_name?></h2>
    <? if (!$winners[$school_id] || count($winners[$school_id]) == 0) { // make sure the school has winners
        echo "<p class='no-winners'>No Winners</p>"; continue;
    }?>
    <p>
        <strong>Toggle All Shipped: </strong>
        <label class="slider-container">
            <input type="checkbox" data-school_id="<?=$school_id?>" class="mark_shipped_bulk"/>
            <span class="slider-span"></span>
        </label>
    </p>
    <table id="table_shipping_<?=$school_id?>">
        <thead>
            <th>Shipped</th><th>Name</th><th>Grade</th><th>Prize</th>
        </thead>
        <tbody>
            <? foreach($winners[$school_id] as $winner){ ?>
            <tr>
                <td class="shipped-td">
                    <label class="fancy-check-container">
                        <input type="checkbox" data-user_id="<?=$winner['user_id']?>" data-raffle_id="<?=$winner['raffle_id']?>"
                        data-prize_id="<?=$winner['prize_id']?>" class="mark_shipped" <?= $winner['shipped'] == 1 ? "checked" : ""?>/>
                        <span class="fancy-check"></span>
                    </label>
                    
                </td>
                <td><?= $winner['last']?>, <?= $winner['first']?></td>
                <td><?= $winner['class_grade']. ($winner['class_sub'] ? " - ". $winner['class_sub'] : "")?></td>
                <td><?= $winner['prize']?></td>
            </tr>
            <? } // end winners foreach?>
        </tbody>
    </table>
    <br/>
    <p>
        <strong>Item shipping totals: </strong>
    </p>
    <table>
        <thead><th>Prize</th><th>Total</th></thead>
        <tbody>
            <?  $total_prizes = 0;
            foreach($prize_counts[$school_id] as $prize_count){
                $total_prizes += $prize_count['total']?>
            <tr>
                <td><?=$prize_count['prize']?></td>
                <td><?=$prize_count['total']?></td>
            </tr>
            <? } // end for each prize total?>
            <tr>
                <th>Grand Total</th>
                <th><?=$total_prizes?></th>
            </tr>
        </tbody>
    </table>
<?} // end foreach school?>
