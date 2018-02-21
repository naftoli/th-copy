<?php
/***************** DEBUGGING SETTINGS **********************/
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
// only superusers can use this page
if ($admin_user['auth'] != 'super') {
    echo "Sorry you don't have the privilege(s) necessary to view this page.";
    exit;
}
/***************** IMPORTS **********************/
require_once(dirname(__FILE__).'/../classes/Raffle.php');
require_once(dirname(__FILE__).'/../classes/Prize.php');
require_once(dirname(__FILE__).'/../functions.php');
use raffles\weekly\Raffle as Raffle; // use the raffle from its namespace
use raffles\weekly\Prize as Prize; // use the raffle from its namespace

$filter = "";

if($_POST["type"]){
    $type = mysql_real_escape_string($_POST['type']);
    $filter = "WHERE type='$type' ";
}

$filter .= "ORDER BY run_date";

$raffles = Raffle::loadAll($filter);

?>
<table>
    <thead>
        <tr>
            <th>Name</th><th>Prizes</th><th>Type</th><th>Run Date</th><th>Starts On</th><th>Ends On</th><th>Ran On</th><th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <? foreach($raffles as $raffle) { // generate a row for each raffle
            $raffle->get_prizes();?>
            <tr>
                <td><?=$raffle->name?></td>
                <td><? if($raffle->type == "weekly" && count($raffle->prizes) > 0) {
                    foreach($raffle->prizes as $prize) {?>
                        <a href="https://mashpia.com/raffles/weekly/forms/prize_form.php?action=edit&prize_id=<?=$prize->prize_id?>"><?=$prize->name?></a><br/>
                    <? }
                } elseif ($raffle->type == "weekly" && count($raffle->prizes) == 0) {
                    echo "N/A";
                } elseif ($raffle->type == "monthly" ) {
                    echo count($raffle->prizes)." prizes set";
                }
                ?></td>
                <td><?=$raffle->type?></td>
                <td><?=$raffle->run_date->format('m/d/Y')?></td>
                <td><?=formatJdToDate($raffle->start_date, "m/d/Y")?></td>
                <td><?=formatJdToDate($raffle->end_date, "m/d/Y")?></td>
                <td><?=($raffle->date_ran ? $raffle->date_ran->format('m/d/Y') : "N/A")?></td>
                <td>
                    <a href="raffle_form.php?action=edit&raffle_id=<?=$raffle->raffle_id.($debug ? "&debug=true" : "")?>" class='button'>View/Edit</a>
                    <form method="POST" style="display: inline-block">
                        <input type="hidden" name="action" value="destroy"/>
                        <input type="hidden" name="raffle_id" value="<?=$raffle->raffle_id?>"/>
                        <!--<input type="submit" value="Delete" />-->
                    </form>
                </td>
            </tr>
        <?}?>
    </tbody>
</table>