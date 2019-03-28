<?
$admin_auth = array('school'); 
require('header.php');
?>
<html>
    <head>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
    	<pre>
    	<?
    	$admins = array();
    	$parents = array();
		$children = array();
    	$sql = "SELECT * 
				FROM admin_auths aa
				JOIN admins a
				USING ( admin_id ) 
				RIGHT JOIN users u ON ( aa.id = u.user_id ) 
				WHERE u.school_id =82";
		//echo $sql . "<br />";
		$result = mysql_query( $sql ) or die( mysql_error() );
		while ( $row = mysql_fetch_assoc( $result ) ) {
			//print_r( $row );
			$admin = $row['admin_id'] ? $row['admin_id'] : 0;
			$child = $row['user_id'];
			if ( $admin ) {
				$admins[$admin] = $row['admin_first'] . ' ' . $row['admin_last'];
			}
			$parents[$admin][] = $child;
			
		}
		print_r( $parents );
    	?>
    	</pre>
    </body>
</html>    	