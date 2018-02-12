<?
require 'db.php';
$info = array();
$sql = "select * from schools where school_id = 82";
$result = mysql_query( $sql );
$row = mysql_fetch_assoc( $result );
$info = $row;

$names = array(
'משפחת הארליג',
'נאוה פנינה שמוטקין',
'חייקי סילווער',
'יעקב יהודה רפאפורט',
'לוי רפאפורט',
'מושקא ווילשנסקי',
'חנה בתי\' גאלדבערג',
'משׁפּחת וויטקעס',
'משׁפּחת וויטקעס',
'מושקא ליבערמאן',
'שטערנא שרה לאבקאווסקי',
'הדס זולדן',
'חי\' מושקא סודאק',
'אוראלי\'ה דבורה ריימונד',
'רוזא לאה פרוינדליך',
'רוזי וואגעל',
'חנה ליובא קייטל',
'לינדה יעל פחה',
'אילת עודזה אבגי',
'שושנה גרינבלט',
'לאה מחלה ערדווין',
'פרומא דהאן',
'חנה איזה דהאן',
'מוטי קיי',
'העניא רחל שפירא',
'צבי הירש ריכטר',
'נחמה ווילענסקי',
'מנחם מענדל סקורי'
);
//echo "<pre>"; print_r( $info ); echo "</pre>"; exit;
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Pushka Names Report</title>
		<meta charset="UTF-8" />
		<style>
			@font-face {
			    font-family: heb;
			    src: url('fonts/Adobe Hebrew Regular.otf');
			}
			.label {
				background-color: black;
				color: white;
				text-align: center;
				width: 7.4cm;
				height: 1.3cm;
				line-height: 1.1;
				padding-top: 6px;
				page-break-inside: avoid;
			}
			.heading {
				font-family: heb;
				font-size: 15px;
			}
			.name {
				font-size: 20px;
			}
			.outer {
				padding: 5px;
				background-color: white;
				float: left;
			}
		</style>
	</head>
	
	<body>
	<?
		echo "Order #: " . ($info['school_number'] + 139454) . "<br />";
		echo "Total Number of Pushkas: " . count($names) . "<br />";

		foreach ($names as $name) {
			echo "<div class='outer'><div class='label'><span class='heading'>לה׳ הארץ ומלואה</span>
				<br /><span class='name'>" . $name . "</span></div></div>";
		}
		echo "<div style='clear: both; page-break-after: always'></div><br /><br />";
	?>
	</body>
</html>