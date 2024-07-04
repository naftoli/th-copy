<?php
//error_reporting(E_ALL);
ini_set("display_errors", 1);
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
$ran_only = isset( $_POST['ran_only'] ) && $_POST['ran_only'] == "true";
$all = isset( $_POST['all'] ) && $_POST['all'] == "true" ? true : false;

$filter = []; // sorting
// load all the raffles
if ( $type !== "" )
    $filter[] = "type='$type'"; // add the where clause before the order_by\
if ( $ran_only )
    $filter[] = "date_ran IS NOT NULL";

// for BC only show winners for raffles marked as show_for_bc
if ( $admin_user['auth'] !== 'super' && $ran_only ) $filter[] = "show_for_bc = 1";
else $filter[] = "show_for_hq = 1";

//if ( $admin_user['auth'] !== 'super' )
    //$filter[] = 'year = '.GlobalSettings::getCurrentYear(); // only show raffles from this year

if ( count( $filter ) > 0 ) {
    $filter = 'WHERE '.implode( ' AND ', $filter ).' ORDER BY run_date DESC, type';
} else {
    $filter = 'ORDER BY run_date DESC, type';
}
//echo "<pre>"; print_r( $filter ); echo "</pre>";

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