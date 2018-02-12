<? 
$gender = 'boys';
$str = substr($_SERVER['SCRIPT_URI'], 4, 1);
if ($str != 's') {
	header("Location: https://mashpia.com/chidon_reg_boys.php");
}

$text = "<p>You can pay for students individually, all Chidon reservations must be paid by: Friday, Chof Vov Iyar (May 15).</p>";
$terms = "
	<b>Chidon Starts</b><br />
	Thursday, Yud Zayin Sivan (June 4)<br /><br />
	<b>Chidon Ends</b><br />
	Sunday, Chof Sivan (June 7)
	<br /><br />
";

$schools = array(
	'Chabad Youth Boys',
	'Cheder at the Ohel'.
	'Cheder Chabad Baltimore',
	'Cheder Chabad Monsey',
	'Cheder Chabad Sydney',
	'Cheder Chabad Toronto',
	'Cheder Lubavitch Chicago',
	'Cheder Lubavitch Morristown',
	'Cheder Menachem LA',
	'Cheder Menachem Wilkes Barre',
	'Darchai Menachem',
	'Kesser Torah College',
	'Lubavitch Boys Junior School',
	'Lubavitch Cheder Day School',
	'Lubavitcher Yeshiva',
	'MyShliach',
	'Ohr Menachem',
	'OYY Lubavitch',
	'Oholei Torah',
	'ULY Flatbush',
	'Yeshiva Tomchei Temimim Lubavitch',
	'Yeshiva Schools of Pittsburgh'
);

include "chidon_reg.php";
?>