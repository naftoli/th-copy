<? 
$admin_auth = array(); 

require('header.php'); 

if ($lines = gra('lines')) {
	while ($lines && $lines[count($lines)]['text'] === '') 
		array_pop($lines);
		
	$page = 1;
	$perek = '?';
	
	for($i = 1; $i <= count($lines); $i++) {
		if(intval($lines[$i]['page'])) $page = intval($lines[$i]['page']);
		if($lines[$i]['perek'] !== '') $perek = $lines[$i]['perek'];
		mq("INSERT INTO tanya_lines (line, page, perek, text) VALUES ($i, $page, " . ms($perek) . ', ' . ms($lines[$i]['text']) . ") ON DUPLICATE KEY UPDATE page = $page, perek = " . ms($perek) . ', text = ' . ms($lines[$i]['text']));
	}
	
	mq("DELETE FROM tanya_lines WHERE line >= $i");
}

function get_body() {
	$sql = 'SELECT line, page, perek, text FROM tanya_lines ORDER BY line';
	$query = mysql_query($sql);
	
	while ($row = mysql_fetch_assoc($query)) {
		echo '<TR><TH>' . $line = $row['line'] . '</TH>';
		echo '<TD><INPUT type="text" name="lines[' . $row['line'] . '][page]" value="' . $row['page'] . '" size="5" maxlength="5" onChange="if(this.value != \'\') this.value = Math.max(1, Math.min(parseInt(\'0\'+this.value, 10), 65535));"></TD>';
		echo '<TD><INPUT type="text" name="lines[' . $row['line'] . '][perek]" value="' . es($row['perek']) . '" size="2" maxlength="2"></TD>';
		echo '<TD><INPUT type="text" name="lines[' . $row['line'] . '][text]" value="' . es($row['text']) . '" size="80"></TD>';
		echo '</TR>';
	}
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
   
<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Tanya Lines'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
	</HEAD>
	
	<BODY>
	
		<?include('admin_header.php');?>
		
		<DIV CLASS="body">
			<H1><?=T_('Tanya Lines')?></H1>
			
			<FORM action="admin_tanya_lines.php#new" method="post" accept-charset="UTF-8">
				<P>
					<?=T_('Note: You only have to enter a Page # and Perek when it changes. If you leave it blank it will copy the entry above it when you save.')?>
				</P>
				
				<TABLE class="pretty">
					<THEAD>
						<TR>
						  <TH><?=T_('Line')?></TH>
						  <TH><?=T_('Page')?></TH>
						  <TH><?=T_('Perek')?></TH>
						  <TH><?=T_('Text')?></TH>
						</TR>
					</THEAD>
								
					<TBODY>	
						<? get_body(); ?>
					</TBODY>				
				</TABLE>
				
				<P>
					<INPUT type="submit" value="<?=T_('Save')?>">
				</P>			
			</FORM>
		
		</DIV>
		
		<? include('admin_footer.php'); ?>
	</BODY>
	
</HTML>
