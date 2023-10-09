<?
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
$admin_auth = ['school'];
/***************** IMPORTS **********************/
require_once( $_SERVER["DOCUMENT_ROOT"].'/header.php' ); // load the db so that the raffle can do its thing
require_once( $_SERVER["DOCUMENT_ROOT"].'/class.globalSettings.php' );
require_once( dirname(__FILE__).'/../classes/Raffle.php' );
// namespace fixing
use raffles\weekly\Raffle as Raffle; // use the raffle from its namespace

// enforce admins only
if ( isset( $_COOKIE['admin_id'] ) ){
    $is_parent_query = mysql_query(
        "SELECT auth FROM admin_auths WHERE auth != 'user' AND admin_id = " . mysql_escape_string( $_COOKIE['admin_id'] ) . ";"
    );
    if ( mysql_num_rows( $is_parent_query ) === 0 ){
        http_response_code( 401 );
        echo json_encode( [ "success" => false, "msg" => "Invalid Credentials" ] );
        die();
    };
}
//else {
//    http_response_code( 401 );
//    echo json_encode( [ "success" => false, "msg" => "Invalid Credentials" ] );
//    die();
//}

$school_id = isset($_POST['school_id']) ? $_POST['school_id'] : false;
$raffle_id = isset($_POST['raffle_id']) ? $_POST['raffle_id'] : false;
$seperate_genders = isset($_POST['single_list']) ? false : true;

// find out if we are a super admin or not
$is_super = $admin_user['auth'] == 'super';

// if no single raffle was given, get all of them
if(!$raffle_id && isset( $_GET['v'] ) && $_GET['v'] == 2){
    $raffle_query = mysql_query("SELECT r.* "
                                ."FROM raffles r"
                                ." ORDER BY date_ran DESC, type "
                                .( isset($_GET['latest']) ? "LIMIT 1 " : "LIMIT 10 ") );
    $raffles = [];
    while($raffle_info = mysql_fetch_assoc($raffle_query)){
        $raffle = Raffle::loadFromRow($raffle_info);
        $raffles[] = $raffle;
    }
} elseif(!$raffle_id) {
    $raffles = Raffle::loadAll("WHERE year = " . GlobalSettings::getCurrentYear() . " ORDER BY date_ran desc, type"); // show the most recent raffles with weekly having a higher priority then monthly to maintain order
} else {
    $raffles = []; // create a raffles array
    $raffles[] = Raffle::load($raffle_id); // and add the raffle they asked for
}
// the result to return
$return_array = [];
$sorting = isset($_POST['sorting']) ? $_POST['sorting'] : "name";
// for each raffle
foreach($raffles as $raffle){
    // only add raffles if there's permission to view them
    if ($is_super && !$raffle->show_for_hq) continue; // if we are a super admin and the raffle is not for hq, skip it
    else if (!$is_super && !$raffle->show_for_bc) continue;
    $winners_info = $raffle->get_winner_info($school_id, $seperate_genders, $sorting); // get the winners for the school (if given, will be false and all the schools will be returned otherwise)
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