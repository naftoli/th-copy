<?
$admin_auth = array('school'); 
require('header.php');

$types = array('chayolei', 'tuition', 'tanya', 'tehillim', 'chidon');

if (isset($_POST['submit'])) {
	
	foreach ($types as $type) {
		${$type} = array();		
	}
	
	foreach ($types as $type) {
		if (isset($_POST[$type])) {
			foreach ($_POST[$type] as $k => $v) {
				${$type}[] = $k;
			}
		}
	}
	
	$info = array();
	foreach ($types as $type) {
		foreach (${$type} as $val) {
			$info[$val][] = $type;
		}
	}
	
	//echo "<pre>"; print_r($info); echo "</pre>"; exit;
	$sqls = array();
	foreach ($info as $school => $types) {
		$sql = "update schools set ";
		$num = count( $types );
		for ($i = 1; $i <= $num; $i++) {
			if ($i == $num) $sql .= $types[$i-1] . " = 1 ";
			else $sql .= $types[$i-1] . " = 1, ";
		}
		$sql .= "where school_id = " . $school;
		$sqls[] = $sql;
	}
	//echo "<pre>"; print_r( $sqls ); echo "</pre>"; exit;
	
	//first delete all previously saved info
	$sql = "update schools set chayolei = 0, tuition = 0, tanya = 0, tehillim = 0, chidon = 0";
	mysql_query( $sql );
	
	//then update based on form submission
	foreach ($sqls as $sql) {
		mysql_query( $sql );
	}
}

$schools = array();
$sql = "select * from schools order by school_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$schools[] = $row;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style type="text/css">
            th, td {
            	padding: 5px;
            	font-size: 12px;
            }
        </style>
    </head>
    
    <body>
    	<? include('admin_header.php'); ?>
    	<h1>Types of Schools Setup</h1>
    	
    	<form action="types_of_schools.php" method="post">
    		<div align="center">
    			<input type="submit" name="submit" value="save" />
    		</div>
    		
    		<p>Please check off all that apply to each school.</p>
    		
	    	<table>
	    		<tr>
					<th>School ID</th>
					<th>Base Number</th>
	    			<th>School</th>
	    			<th>Chayolei</th>
					<th>Tuition</th>
	    			<th>Tanya/Mishna</th>
					<th>Tehillim</th>
					<th>Chidon</th>
	    		</tr>
	    		<?
				$types = array('chayolei', 'tuition', 'tanya', 'tehillim', 'chidon');
	    		foreach ($schools as $school) {
	    			echo "<tr><td>" . $school['school_id'] . "</td><td>" . $school['school_number'] . "</td><td>" .	$school['school_name'] . "</td>";
					foreach ($types as $type) {
						echo "<td><input type='checkbox' name='" . $type . "[" . $school['school_id'] . "]'";
						if ($school[$type]) echo " checked";
						echo " /></td>";
					}
					echo "</tr>";
	    		}
	    		?>
	    	</table>
	    	
	    </form>
    </body>
</html>