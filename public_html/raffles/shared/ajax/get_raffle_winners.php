<?
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
/***************** IMPORTS **********************/
require_once( $_SERVER["DOCUMENT_ROOT"].'/db.php' ); // load the db so that the raffle can do its thing
require_once( dirname(__FILE__).'/../classes/Raffle.php' );
// namespace fixing
use raffles\weekly\Raffle as Raffle; // use the raffle from its namespace

$shipping = false;
if ( isset( $_COOKIE['admin_id'] ) ){
    $is_parent_query = mysql_query(
        "SELECT auth FROM admin_auths WHERE auth != 'user' AND admin_id = " . mysql_escape_string( $_COOKIE['admin_id'] ) . ";"
    );
    $shipping = mysql_num_rows( $is_parent_query ) > 0;
}

$school_id = isset($_POST['school_id']) ? $_POST['school_id'] : false;
$raffle_id = isset($_POST['raffle_id'])? $_POST['raffle_id'] : false;
$seperate_genders = isset($_POST['single_list']) ? false : true;

// if no single raffle was given, get all of them
if(!$raffle_id && $_GET['v'] == 2){
    $raffle_query = mysql_query("SELECT r.* "
                                ."FROM raffles r WHERE show_on_mobile = 1 "
                                ." ORDER BY date_ran DESC, type "
                                .(isset($_GET['latest']) ? "LIMIT 1 " : "LIMIT 10 "));
    $raffles = [];
    while($raffle_info = mysql_fetch_assoc($raffle_query)){
        $raffle = Raffle::loadFromRow($raffle_info);
        // $raffle->raffle_number = $raffle_info['raffle_num'];
        $raffles[] = $raffle;
    }
} elseif(!$raffle_id) {
    $raffles = Raffle::loadAll("WHERE show_on_mobile = 1 ORDER BY date_ran desc, type"); // show the most recent raffles with weekly having a higher priority then monthly to maintain order
} else {
    $raffles = []; // create a raffles array
    $raffles[] = Raffle::load($raffle_id); // and add the raffle they asked for
}
// the result to return
$return_array = [];
$sorting = isset($_POST['sorting']) ? $_POST['sorting'] : "name";
// for each raffle
foreach($raffles as $raffle){
    $winners_info = $raffle->get_winner_info($school_id, $seperate_genders, $sorting, $shipping); // get the winners for the school (if given, will be false and all the schools will be returned otherwise)
    $raffle_from = explode(' ', iconv('WINDOWS-1255', 'UTF-8', jdtojewish($raffle->start_date, true, CAL_JEWISH_ADD_GERESHAYIM)));
    $raffle_from = $raffle_from[0] . ' ' . $raffle_from[1];
    
    $raffle_to = explode(' ', iconv('WINDOWS-1255', 'UTF-8', jdtojewish($raffle->end_date, true, CAL_JEWISH_ADD_GERESHAYIM)));
    $raffle_to = $raffle_to[0] . ' ' . $raffle_to[1];
    /*$he = iconv('WINDOWS-1255', 'UTF-8', jdtojewish($temp, true, CAL_JEWISH_ADD_GERESHAYIM));
    $heArr = explode(' ', $he);
    $this->heDates[] = $heArr[0] . ' ' . $heArr[1];*/
    
    $return_array[] = ["raffle_name" => $raffle->name." (".$raffle->type.")",
                       "raffle_type" => $raffle->type,
                       "raffle_from" => $raffle_from,
                       "raffle_to" => $raffle_to,
                    //    "raffle_number" => $raffle->raffle_number,
                       "winners" => $winners_info]; // add the info to the array
}

echo json_encode($return_array); // return as a plain array to maintain order when parsed in browser