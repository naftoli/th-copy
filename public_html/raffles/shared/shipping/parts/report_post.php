<? // fragment to be included in report.php and report_print.php to handle post data
/***************** GET POST OR GET PARAMATERS **********************/
$school_id = $_POST['school_id'] ? $_POST['school_id'] : $_GET['school_id'];
$limit = $_POST['limit'] ? $_POST['limit'] : $_GET['limit'];
$sort = $_POST['sort'] ? $_POST['sort'] : $_GET['sort'];
$filter = $_POST['filter'] ? $_POST['filter'] : $_GET['filter'];

// get the params that are reliant on each other
if($limit == "dates"){// when limit is dates
    $start = $_POST['start'] ? $_POST['start'] : $_GET['start'];
    $end = $_POST['end'] ? $_POST['end'] : $_GET['end'];
} else if ($limit == "raffles") { // when limit is raffles
    $raffle_ids = $_POST['raffle_ids'] ? $_POST['raffle_ids'] : $_GET['raffle_ids'];
}
/***************** GET SCHOOLS **********************/
if(!$school_id) {// if there was no school id
    // load some more dependencies
    require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';
    require_once $_SERVER["DOCUMENT_ROOT"].'/class.schoolsUsers.php';
    // get all the schools for that provided user
    $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
    $schools = $as->getSchools(); // get the users schsools
} else {
    $school_name_sql = "SELECT school_name FROM schools WHERE school_id='$school_id';"; // get the school name
    $row = mysql_fetch_assoc(mysql_query($school_name_sql));
    $schools = [$school_id => $row['school_name']]; // set the school to the one selected
}
/***************** LOAD THE RAFFLE WINNERS **********************/
if($limit == "raffles"){
    $data = getWinners::get_winners_raffles($raffle_ids, $sort, $filter, $school_id);
}
if($limit == "dates"){
    $data = getWinners::get_winners_dates($start, $end, $sort, $filter, $school_id);
}

$winners = $data[0];
$prize_counts = $data[1];

// if($debug) print_r($prize_counts);

// go on to render page..