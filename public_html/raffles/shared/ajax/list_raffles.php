<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
// only allow schools here
$admin_auth = array('school'); 
require_once $_SERVER["DOCUMENT_ROOT"].'/header.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/class.globalSettings.php';

/***************** IMPORTS **********************/
require_once(dirname(__FILE__).'/../classes/Raffle.php');
require_once(dirname(__FILE__).'/../classes/Prize.php');
// namespace fixing
use raffles\weekly\Raffle as Raffle; // use the raffle from its namespace
use raffles\weekly\Prize as Prize; // use the raffle from its namespace

/***************** GET POST DATA *********************/
// get the type from the post request
$type = mysql_real_escape_string($_POST['type']);
$ran_only = $_POST['ran_only'] == "true" ? true : false;
$all = isset( $_POST['all'] ) && $_POST['all'] == "true" ? true : false;

$filter = ""; // sorting
// load all the raffles
if($type !== "") $filter .= "WHERE type='$type' "; // add the where clause before the order_by\
if($type !== "" && $ran_only) {
    $filter .= "AND date_ran IS NOT NULL ";
} else if ($ran_only) {
    $filter .= "WHERE date_ran IS NOT NULL ";
}
$filter .= 'AND year = '.GlobalSettings::getCurrentYear(); // only show raffles from this year

$filter .= "ORDER BY run_date DESC, type";

$raffles = Raffle::loadAll($filter);

/***************** RENDER SELECT BOX *********************/
?>
<select name="raffle_id" id="raffle_id">
    <option value="" disabled selected >Select a Raffle</option>
    <?php if ( $all ) { ?>
        <option value="">All Raffles</option>
    <?php } ?>
    
    <? foreach($raffles as $raffle){ // render an option for each raffle?>
        <?
        // show prize name next to weekly raffles
        if ($raffle->type == "weekly") {
            $prizes = $raffle->get_prizes();
            if ($prizes) {
                foreach($prizes as $prize) {
                    $raffle->name .= ' - ' . $raffle->year . " (" . $prize->name . ")";
                }
            }
        }
        ?>
        <option value="<?=$raffle->raffle_id?>"><?=$raffle->name?></option>
    <?}?>
</select>