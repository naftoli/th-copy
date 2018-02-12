<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
// only allow schools here
$admin_auth = array('school'); 
require_once $_SERVER["DOCUMENT_ROOT"].'/header.php';

/***************** IMPORTS **********************/
require_once(dirname(__FILE__).'/../classes/Raffle.php');
// namespace fixing
use raffles\weekly\Raffle as Raffle; // use the raffle from its namespace

/***************** GET POST DATA *********************/
// get the type from the post request
$type = mysql_real_escape_string($_POST['type']);
$ran_only = $_POST['ran_only'] == "true" ? true : false;

$filter = ""; // sorting
// load all the raffles
if($type !== "") $filter .= "WHERE type='$type' "; // add the where clause before the order_by\
if($type !== "" && $ran_only) {
    $filter .= "AND date_ran IS NOT NULL ";
} else if ($ran_only) {
    $filter .= "WHERE date_ran IS NOT NULL ";
}

$filter .= "ORDER BY run_date, type";

$raffles = Raffle::loadAll($filter);

/***************** RENDER SELECT BOX *********************/
?>
<select name="raffle_id" id="raffle_id">
    <option value="" disabled selected >Select a Raffle</option>
    <? foreach($raffles as $raffle){ // render an option for each raffle?>
        <option value="<?=$raffle->raffle_id?>"><?=$raffle->name?></option>
    <?}?>
</select>