<? 
$gender = 'girls';
$str = substr($_SERVER['SCRIPT_URI'], 4, 1);
if ($str != 's') {
	header("Location: https://mashpia.com/chidon_reg_girls.php");
}

$text = "<p>You can pay for students individually, all Chidon reservations must be paid by: Friday, Yud Tes Iyar (May 8).</p>";
$terms = "
	<b>Chidon Starts</b><br />
	Thursday, Yud Sivan (May 28)<br /><br />
	<b>Chidon Ends</b><br />
	Sunday, Yud Gimmel Sivan (May 31)
	<br /><br />
";

$schools = array(
	'Bais Chaya Mushka LA',
	'Bais Chaya Mushka Toronto',
	'Bais Chaya Mushka Crown Heights',
	'Bais Rivka Crown Heights',
	'Bais Rivka Montreal',
	'Bnos Menachem',
	'Cheder Lubavitch Chicago',
	'Cheder Menachem NJ',
	'Cheder Chabad Monsey',
	'Cheder Menachem Wilkes Barre',
	'Lubavitch Cheder Day School',
	'Maimonides Hebrew Day School',
	'MyShliach',
	'Yeshiva Schools of Pittsburgh'
);

include "chidon_reg.php";
?>