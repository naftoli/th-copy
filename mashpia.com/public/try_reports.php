<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<html>

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Birthday Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
    </head>
    
	<body>
		<? include('admin_header.php'); ?>
		<h1>Reports Generator</h1>
		
		<?
		require 'class.reportGenerator.php';
		$r = new ReportGenerator();
		
		if ( isset( $_POST['submit'] ) ) {
			$r->setSelectedFields( $_POST['fields'] );
			$r->createQuery();
			$r->runQuery();
		} else {			
			$fields = $r->getFields( 'users' );
			?>
			<form action="try_reports.php" method="post">
				<? 
				foreach ( $fields as $key => $field ) { 
					echo "<input type='checkbox' name='fields[]' . value=" . $key . " />" . $field . "<br />";
				}
				?>
				<input type="submit" name="submit" value="submit" />
			</form>
		<? } ?>
	</body>
</html>