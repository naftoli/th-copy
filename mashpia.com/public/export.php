<?
function csv_escape($str) 
{
	$str = str_replace(array('"', ',', "\n", "\r"), array('""', ',', "\n", "\r"), $str, &$count);
	
	if ($count) 
	{
		return "\"$str\"";
	} 
	else 
	{
		return $str;
	}
}

function export($sql, $filename) 
{
	$result = mqu($sql);
	header("Content-type: text/comma-separated-values");
	header("Content-Disposition: inline; filename=\"{$filename}.csv\"");

	$fields = mysql_num_fields($result);

	echo csv_escape(mysql_field_name($result, 0));
	
	for($i = 1; $i < $fields; $i++) 
	{
		echo ',', csv_escape(mysql_field_name($result, $i));
	}
	
	echo "\r\n";

	while($row = mysql_fetch_row($result)) 
	{
		echo csv_escape($row[0]);
		
		for($i = 1; $i < $fields; $i++) 
		{
			echo ',', csv_escape($row[$i]);
		}
		
		echo "\r\n";
	}
	
}
?>
