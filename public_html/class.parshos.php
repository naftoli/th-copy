<? 
class Parshos {
	public static function getParshos($year) {
		$sql = "select * from parshos where year = " . $year;
		$result = mysql_query($sql);
		$parshos = array();
		while ($row = mysql_fetch_assoc($result)) {
			$parshos[] = $row;
		}
		return $parshos;
	}
}
?>