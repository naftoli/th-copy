<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Chidon Registration Report 5775</title>
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
            td {
            	vertical-align: top;
            	text-align: center;
            }
            .newPage {
                page-break-after: always;
            }
        </style>
    </HEAD>

    <BODY>
        <h1>Chidon Registration Report 5775</h1>
        
        <?
        $schools = array();
        $sql = "select * from chidon_schools cs 
        		join schools s using (school_name) 
        		where year = 5775 order by gender desc, school_name asc";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$schools[$row['chidon_schools_id']] = $row;
		}
		
		$details1 = array();
		foreach ($schools as $id => $arr) {
			$sql = "select * from chidon_reg 
				where chidon_schools_id = $id 
				and grade in ('4','5') 
				order by last_name, name";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$details1[$id][] = $row;
			}
		}
		
		$details2 = array();
		foreach ($schools as $id => $arr) {
			$sql = "select * from chidon_reg 
				where chidon_schools_id = $id 
				and grade in ('6','7','8') 
				order by last_name, name";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$details2[$id][] = $row;
			}
		}
		
		
		//echo "<pre>"; print_r($details); echo "</pre>";
		$num = 1;
		$reset = false;
		foreach ($schools as $id => $school) {			
			if (isset($details1[$id])) {
				foreach ($details1[$id] as $row) {
					echo $row['name'] . $row['last_name'] . "<br />" . 
						$school['school_name'] . "<br />" . 
						$school['school_city'] . ', ' . $school['school_state'] . 
						' ' . $school['school_country'] . "<br /><br />";
				} 
			} 
		} 
			
		$num = 1;
		$reset = false;
		foreach ($schools as $id => $school) {			
			if (isset($details2[$id])) {
				foreach ($details1[$id] as $row) {
					echo $row['name'] . $row['last_name'] . "<br />" . 
						$school['school_name'] . "<br />" . 
						$school['school_city'] . ', ' . $school['school_state'] . 
						' ' . $school['school_country'] . "<br /><br />";
				} 
			} 
		} 
		?>
    </body>
</html>