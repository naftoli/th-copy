<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

/***************** DEBUGGING SETTINGS **********************/
$debug = false;
if (isset( $_GET['debug'] ) && $_GET['debug']) {
    //error_reporting(E_ALL);
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

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();

/***************** IMPORTS **********************/
require_once(dirname(__FILE__).'/../classes/Prize.php');
require_once(dirname(__FILE__).'/../classes/Raffle.php');
require_once(dirname(__FILE__).'/../functions.php');
//namespacing
use raffles\weekly\Prize as Prize; // use the raffle from its namespace

/***************** FILTER CONTENT **********************/
$filter = "";

if($_POST["type"]){
    $type = mysql_real_escape_string($_POST['type']);
    $filter = "WHERE type_of_prize='$type'";
}

$prizes = Prize::loadAll($filter);

?>
<table>
    <thead>
        <tr>
          <th>Prize ID</th><th>Shipping ID</th><th>Name</th><th>Type</th><th>Created On</th><th>Thumbnail</th>
<!--          <th>Parsho(s)</th>-->
          <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <? foreach($prizes as $prize) { // generate a row for each raffle
            echo "<tr>"; // start the row
            echo "<td>".$prize->prize_id."</td>";
            echo "<td>".$prize->shipping_code."</td>"; // show the shipping id
            echo "<td>".$prize->name."</td>"; // show the name
            echo "<td>".$prize->type_of_prize."</td>"; // show the type
            echo "<td>".$prize->date_created->format('m/d/Y')."</td>"; // show the date created
            echo "<td><img src='".$prize->thumbnail."' height='50'></td>"; // show the thumbnail at the full size
            // get parshos
//            $parshos = '';
//            $raffles = $prize->get_raffles($year);
//            foreach ($raffles as $raffle) {
//                $parshos .= $raffle->name.'<br />';
//            }
////            // remove last comma
//            echo "<td>".$parshos."</td>"; // show the parshos
            // show the view/edit button
            echo "<td>".
                "<a href='prize_form.php?action=edit&prize_id=".$prize->prize_id.($debug ? "&debug=true" : "")."' class='button'>View/Edit</a>".
                //"<a href='prize_form.php?action=destroy&prize_id=".$prize->prize_id.($debug ? "&debug=true" : "")."' class='button'>Delete</a>".
                "</td>";
            echo "</tr>"; // end the row
        }?>
    </tbody>
</table>