<?php $debug = false;
/***************** DEBUGGING **********************/
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
    echo "<pre>";
    print_r($_POST);
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

/***************** GET OR POST **********************/
function get_param($key){
    if (isset($_POST[$key])) { return $_POST[$key];}
    else if (isset($_GET[$key])) { return $_GET[$key];}
    else { return false; }
}

/***************** POST PARAMS **********************/
$order = get_param('sort_desc') == "true" ? "DESC" : "ASC";
$school_id = mysql_real_escape_string(get_param('school_id'));
$shipping_status = mysql_real_escape_string(get_param('shipping_status'));
$sort = mysql_real_escape_string(get_param('sort'));
// cast the shipments to booleans
$shipments = [];
$shipments['raffles_weekly'] = get_param('shipments')['raffles_weekly'] == "true" ? true : false;
$shipments['raffles_monthly'] = get_param('shipments')['raffles_monthly'] == "true" ? true : false;
$shipments['gifts'] = get_param('shipments')['gifts'] == "true" ? true : false;
$shipments['ranks'] = get_param('shipments')['ranks'] == "true" ? true : false;
$shipments['medals'] = get_param('shipments')['medals'] == "true" ? true : false;
$shipments['hachayols'] = get_param('shipments')['hachayols'] == "true" ? true : false;
/***************** HANDLE DATES **********************/
$start_date = get_param('start_date');
if($start_date){ // if we have a start date
    $start_date = new DateTime($start_date);
    $start_date = $start_date->format("Y-m-d"); // formmat it for mysql
}
$end_date = get_param('end_date');
if($end_date){ // if we have an end date
    $end_date = new DateTime($end_date);
    $end_date = $end_date->format("Y-m-d 24:59:59"); // format it for mysql
}
// if we are on the medals and rank cards we want to use the predetermined report dates
$report_dates = get_param('report_dates');
if($report_dates) {
    require_once($_SERVER["DOCUMENT_ROOT"]."/class.report.php");
    $report = new Report(); // set the previous dates to true if the toggle is pointing to "previous"
    if ($report_dates == "previous") $report->setPreviousDates();
    $greg_start = $report->getReportDates()['start'];
    $greg_end = $report->getReportDates()['end'];
} else {
    // gregorian versions of the dates provided (if there is no "report_dates" set)
    $greg_start = explode("-", $start_date); $greg_end = explode("-", $end_date); // split the dates for the converison
    $greg_start = gregoriantojd($greg_start[1], $greg_start[2], $greg_start[0]);
    $greg_end = gregoriantojd($greg_end[1], explode(" ", $greg_end[2])[0], $greg_end[0]);
}
// if($debug) print_r($shipments);

/***************** SCHOOLS **********************/
if($school_id) {
    $school_name = mysql_fetch_assoc(mysql_query("SELECT school_name FROM schools WHERE school_id = $school_id;"))['school_name'];
    $schools = [$school_id => $school_name];
} else {
    require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php'; // load the class
    $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] ); // get the schools
    $schools = $as->getSchools(); // and set them to the $schools paramater
}

/***************** GET THE RAFFLE SHIPMENTS **********************/
$prizes = [];
if($shipments['raffles_weekly']){
    require_once(dirname(__FILE__)."/../functions/get_prizes.php");
    $prizes = get_prizes($start_date, $end_date, $shipping_status, $school_id, "weekly");
    //if($debug) print_r($prizes);
}
if($shipments['raffles_monthly']){
    require_once(dirname(__FILE__)."/../functions/get_prizes.php");
    $prizes_monthly = get_prizes($start_date, $end_date, $shipping_status, $school_id, "monthly");
    // manually add the prizes to the array in the correct location. array_merge_recursive does not work as it resets the keys.
    foreach($prizes_monthly as $prize_school_id => $student){
        foreach($student as $student_id => $monthly_prizes){
            foreach($monthly_prizes as $prize){
                $prizes[$prize_school_id][$student_id][] = $prize;
            }
        }
    }
}

//if($debug) print_r($prizes);

/***************** GET THE GIFT SHIPMENTS **********************/
$gifts = [];
if($shipments['gifts']){
    require_once(dirname(__FILE__)."/../functions/get_gifts.php");
    if($school_id){
        $gifts = get_gifts($school_id, $start_date, $end_date, $debug);
    } else {
        foreach($schools as $gift_school_id => $school){
            $tmp = get_gifts($gift_school_id, $start_date, $end_date, $debug);
            $gifts['students'][$gift_school_id]  = $tmp['students'][$gift_school_id];
            $gifts['staff'][$gift_school_id]     = $tmp['staff'][$gift_school_id];
        }
    }
    //if($debug) print_r([$gifts, $school_id]);
}

/***************** GET THE RANK CARD SHIPMENTS **********************/
$ranks = [];
if($shipments['ranks']) {
    require_once(dirname(__FILE__)."/../functions/get_ranks.php");
    $ranks = get_ranks($school_id, $greg_start, $greg_end, $debug);
    //if($debug) print_r($ranks);
}

/***************** GET THE MEDAL SHIPMENTS **********************/
$medals = [];
if($shipments['medals']) {
    require_once(dirname(__FILE__)."/../functions/get_medals.php");
    $medals = get_medals($school_id, $greg_start, $greg_end, $debug);
    //if($debug) print_r($medals);
}

$hachayols = [];
if($shipments['hachayols']) {
    require_once(dirname(__FILE__)."/../functions/get_hachayols.php");
    $hachayols = get_hachayols($school_id, $greg_start, $greg_end);
}

/***************** GET THE USERS **********************/
require_once(dirname(__FILE__)."/../functions/get_students_shipping.php");
$schoolsUsers = array();
//$schoolsStaff = array();
// for each school get its users
foreach ( $schools as $id => $school ) {
    $schoolsUsers[$id] = get_students_shipping($id, $sort, $order); // use this to sort the students....
    //$schoolsStaff[$id] = $gifts['staff'][$id];
}

/***************** SORTING FUNCTIONS **********************/
function sortByShipment($a, $b) {
    return $a['shipment'] > $b['shipment'];
}

function sortByItem($a, $b) {
    return $a['item'] > $b['item'];
}

/***************** SCHOOL TYPES **********************/
$school_gender_types = array(
    2	=> 'girls', 3	=> 'boys',  4	=> 'boys',  5	=> 'boys',  7	=> 'girls', 9	=> 'boys',  11	=> 'boys',  19	=> 'boys',
    21	=> 'boys',  30	=> 'girls', 33	=> 'boys',  37	=> 'girls', 39	=> 'mixed', 40	=> 'girls', 42	=> 'girls', 45	=> 'girls',
    49	=> 'boys',  48	=> 'boys',  50	=> 'girls', 54	=> 'girls', 55	=> 'mixed', 58	=> 'boys',  60	=> 'boys',  61	=> 'mixed',
    63	=> 'boys',  66	=> 'girls', 80	=> 'mixed', 81	=> 'mixed', 84	=> 'mixed', 87	=> 'mixed', 89	=> 'mixed', 105	=> 'girls',
    106	=> 'mixed', 110	=> 'mixed', 112	=> 'boys',  162	=> 'girls', 176	=> 'girls', 185	=> 'mixed', 192	=> 'girls', 194	=> 'mixed',
    255	=> 'boys',  263	=> 'mixed', 264	=> 'boys',  265	=> 'girls', 269	=> 'mixed', 471	=> 'boys',  427	=> 'mixed', 82 => 'test'
);

/***************** GET FULL SCHOOL INFO FOR SHIPPING **********************/
function get_school_shipping_info($school_id){
    $school_sql = "SELECT * FROM schools WHERE school_id = $school_id;";
    return mysql_fetch_assoc(mysql_query($school_sql));
}

/***************** GET THE FIRST ADMIN FOR THE SCHOOL **********************/
function get_school_admin($school_id) {
    $admin_sql = "SELECT * FROM admins JOIN admin_auths aa USING (admin_id) WHERE aa.auth = 'school' AND id = $school_id;";
    return mysql_fetch_assoc(mysql_query($admin_sql));
}

function get_school_hachayol_name($school_id) {
    $school_sql = "SELECT hachayol_name FROM schools WHERE school_id = $school_id;";
    return mysql_fetch_assoc(mysql_query($school_sql))['hachayol_name'];
}

function filter_shipping_status($shipments, $shipping_status){
    if($shipping_status != "all") {
        $result = [];
        foreach($shipments as $shipment){ // for each shipment
            if($shipping_status == "shipped" && !$shipment['shipped']) continue; // if it is not shipped and we only want shipped. skip it
            if($shipping_status == "not-shipped" && $shipment['shipped']) continue; // if it is shipped and we only want not shipped. skip it
            $result[] = $shipment; // it made it this far so add it to the filtered list
        }
        return $result;
    }
    return $shipments;
}

if($debug) echo "</pre>";
