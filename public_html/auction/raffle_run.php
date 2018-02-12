<?
ini_set('display_errors', TRUE);
ini_set('max_execution_time', 300);

// issues that may cause raffle script to bomb (or work incorrectly) include:
// 1. having more prizes than schools
// 2. having more schools than prizes
// 3. having school with no children that are eligible (have earned at least 50 points)
// 4. having school that needs lets say 4 winners but only 3 children are eligible 

require_once 'raffle.class.php';
$auction_id = 72;
$prize_id = 1;
$raffle = new Raffle($auction_id, $prize_id);

$start = 2457700;
$end = 2457732;

//$raffle->setPrizes();
//$p = $raffle->getPrizes();

$raffle->setSchoolsSpecificPrize(array(
	269,269,269,269,176,176,162,162,162,45,45,45,30,30,54,54,54,54,54,54,54,54,54,54,54,54,2,2,7,7,7,7,7,112,112,112,112,66,66,105,105,63,63,81,81,49,49,
    89,55,106,106,5,5,50,21,37,4,4,4,4,60,264,33,185,185,185,80,110,110,110,194,3,39,19,19,19,19,19,19,42,42,42,42,42,42,265,265,9,9,9,9,9,9,9,9,9,
    263,61,61,61,61,61,61,61,61,61,61,61,255,255,255,255,255,255,255,255,255,255,255,255,48,58,58,58,58,87,427,11,40,86
));
//$s = $raffle->getSchoolsSpecificPrize();

//$raffle->setSchools(array(61,61,61,61,61,61,9,9,9,9,54,54,54,42,42,4,4,110,110,19,19,30,105,50,45,112,2,58,162,185,7,49,194,55,81,21,89));
//$raffle->setSchools(array(
//	176,162,162,45,54,54,54,54,2,7,7,7,52,105,63,49,106,5,50,21,4,4,4,185,185,
//	110,110,110,39,19,19,42,42,42,9,9,9,9,9,61,61,61,61,61,61,61,61,61,48,58
//));
//$s = $raffle->getSchools();

//$raffle->setBarcodes();
//$raffle->setNewPoints();

$raffle->setDates( $start, $end );
$raffle->setChildren();
//iterate over schools and set children for schools that don't have children that have more than 50 points
//foreach ($s as $school) {
//    $raffle->checkSchoolChildren($school);
//}
//$c = $raffle->getChildren();

$delete = true;
$raffle->setWinners($delete);
$w = $raffle->getWinners();

//$up = $raffle->getUsedPrizes();

$raffle->updateAuction();

echo "<pre>";
//echo "Prizes:<br />";
//print_r($p);

//echo "<br />Special Prize:<br />";
//print_r($sp);
//echo "<br />Schools:<br />";
//print_r($s);
//print_r($c);
//foreach ($c as $school => $users) {
//	echo $c . '-' . count($users) . "<br />";
//}
//echo "<br />User Prizes Given Out:<br />";
//print_r($up);

echo "<br />Winners:<br />";
print_r($w);
print_r($raffle->getBadSchools());
echo "</pre>";
?>
