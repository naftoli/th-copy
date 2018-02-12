<?
$divs = $_GET['divs'];

if (isset($_GET['day']))
	$day = $_GET['day'];
else
	$day = date("j");

if (isset($_GET['month']))
	$month = $_GET['month'];
else
	$month = date("n");

if (isset($_GET['year']))
	$year = $_GET['year'];
else
	$year = date("Y");

if (isset($_GET['form_name']))
	$form_name = $_GET['form_name'];
else
	$form_name = "";
	
if ($form_name == "tasks_form")
	$onchange = " calculate_max_times(); ";
else
	$onchange = "";
	
$echo_string = "";

$months = array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
$month_days = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
if ($year % 4 == 0) 
	$month_days[1] == 29;


$month_select1 = "<select id='start_month' name='start_month' onchange='get_date_divs(\"start_date\", \"\", \"" . $form_name . "\"); " . $onchange . "'>";
for ($cntr = 0; $cntr < 12; $cntr++) {
	if ($cntr == $month - 1)
		$month_select1 = $month_select1 . "<option selected value='" . ($cntr + 1). "'>" . $months[$cntr] . "</option>";
	else
		$month_select1 = $month_select1 . "<option value='" . ($cntr + 1). "'>" . $months[$cntr] . "</option>";
}
$month_select1 = $month_select1 . "</select>";
$month_select1 = $month_select1 . "&nbsp";

$month_select2 = "<select id='end_month' name='end_month' onchange='get_date_divs(\"\", \"end_date\", \"" . $form_name . "\");" . $onchange . "'>";
for ($cntr = 0; $cntr < 12; $cntr++) {
	if ($cntr == $month - 1)
		$month_select2 = $month_select2 . "<option selected value='" . ($cntr + 1). "'>" . $months[$cntr] . "</option>";
	else
		$month_select2 = $month_select2 . "<option value='" . ($cntr + 1). "'>" . $months[$cntr] . "</option>";
}
$month_select2 = $month_select2 . "</select>";
$month_select2 = $month_select2 . "&nbsp";


$day_select1 = "<select id='start_day' name='start_day' onchange='" . $onchange . "'>";
for ($cntr = 0; $cntr < $month_days[$month - 1]; $cntr++) {
	if (($cntr + 1)== $day)
		$day_select1 = $day_select1 . "<option selected value='" . ($cntr + 1) . "'>" . ($cntr + 1) . "</option>";
	else
		$day_select1 = $day_select1 . "<option value='" . ($cntr + 1) . "'>" . ($cntr + 1) . "</option>";
}
$day_select1 = $day_select1 . "</select>";
$day_select1 = $day_select1 . "&nbsp";

$day_select2 = "<select id='end_day' name='end_day' onchange='" . $onchange . "'>";
for ($cntr = 0; $cntr < $month_days[$month - 1]; $cntr++) {
	if (($cntr + 1)== $day)
		$day_select2 = $day_select2 . "<option selected value='" . ($cntr + 1) . "'>" . ($cntr + 1) . "</option>";
	else
		$day_select2 = $day_select2 . "<option value='" . ($cntr + 1) . "'>" . ($cntr + 1) . "</option>";
}
$day_select2 = $day_select2 . "</select>";
$day_select2 = $day_select2 . "&nbsp";


$year_select1 = "<select id='start_year' name='start_year' onchange='" . $onchange . "'>";
$year_select1 = $year_select1 . "<option value='" . $year . "'>" . $year . "</option>";
$year_select1 = $year_select1 . "</select>";

$year_select2 = "<select id='end_year' name='end_year'>";
$year_select2 = $year_select2 . "<option value='" . $year . "'>" . $year . "</option>";
$year_select2 = $year_select2 . "</select>";

$echo_string = $month_select1 . $day_select1 . $year_select1 . "[SPLIT]" . $month_select2 . $day_select2 . $year_select2;
echo $echo_string;
?>
