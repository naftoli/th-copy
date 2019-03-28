<?
require 'db.php';
$info = array();
$sql = "select * from schools where school_id = 82";
$result = mysql_query( $sql );
$row = mysql_fetch_assoc( $result );
$info = $row;

$names = array(
'חי מושקא',
'בתי\'ה מרים',
'אמונה ברכה',
'חיים בן ציון פירסאן',
'לוי מישולבין',
'יהודה לייב האנדווערגער',
'יוכבד חי האנדווערגער',
'שיינא נחמה האנדווערגער',
'משה ליבערזאהן',
'מושקא ליבערזאהן',
'משפחת מישולבין',
'הרב ישראל ארי\' ליב רייצעס',
'הרב זבולון גוטלייזער',
'משה גליקן',
'שלום מנחם מענדל הלוי טויבער',
'יעקב צבי וואגעל',
'יחיאל וואגעל',
'יוסף מרדכי בורסטאן',
'לוי יצחק הכהן כהן',
'יוסף יצחק דייטש',
'מנחם מענדל קליין',
'מנחם מענדל מענדלאוו',
'ברכה וואגעל',
'רייזל פרענקל',
'רבקה מלכה בורסטאן',
'חיה שרה פרענקל',
'מירל וואגעל',
'מילי ראזענבלוה',
'משפחת מארקאוויטש',
'משפחת ווייס',
'חי\'ה מושקא מישולבין',
'בתי\'ה מישולבין',
'מנחם מענדל מישולבין',
'לוי יצחק מטוסוב',
'ישראל שערמאן',
'יצחק אייזיק קמחין',
'מנחם מענדל קמחין',
'דובער קמחין',
'ברוך שלום קמחין',
'אליהו קמחין'
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
		echo "Order #: " . $info['school_number'] . "<br />";
		echo "Total Number of Pushkas: " . count($names) . "<br />";

		foreach ($names as $name) {
			echo "<div class='outer'><div class='label'><span class='heading'>לה׳ הארץ ומלואה</span>
				<br /><span class='name'>" . $name . "</span></div></div>";
		}
		echo "<div style='clear: both; page-break-after: always'></div><br /><br />";
	?>
	</body>
</html>