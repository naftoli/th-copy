<?
class Parshos {
    public function getParshos($start = null, $end = null) {
        if (is_null($start) && is_null($end)) {
        	//get latest year
        	$sql = "select year from parshos group by year order by year desc limit 1";
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
			$year = $row['year'];
			
            $parshos = array();
            $sql = "select * from parshos where year = $year";
            $result = mysql_query($sql);
            while ($row = mysql_fetch_assoc($result)) {
                $parshos[] = $row;
            } 
            return $parshos;
        } else {
            $sql = "select * from parshos where start = " . $start . " and end = " . $end;
            $result = mysql_query($sql);
            $row = mysql_fetch_assoc($result);
            return $row;
        }
    }
	
	public function getParshosByYear( $year ) {
		$parshos = array();
		$sql = "select * from parshos where year = " . $year;
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$parshos[] = $row;
		} 
		return $parshos;
	}
}
?>
