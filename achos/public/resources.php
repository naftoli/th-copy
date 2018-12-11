<? 
$admin_auth = array('school','user'); 
require('header.php');

if ( isset( $_POST['submit'] ) ) {
    $month = ucwords( mysql_real_escape_string( $_POST['month'] ) );
    $link = mysql_real_escape_string( $_POST['link'] ); 
    $sql = "insert into resources values( null, '$month', '$link' )";
    mysql_query( $sql );
}
    
$resources = array(); 
$sql = "select * from resources";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $resources[] = $row;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
    th, td {
        padding: 5px;
        font-size: 12px;
    }
</style>
</head>

<body>
<? 
require_once('admin_header.php');
?>
<h1>Resources Links</h1>

<? if ( $admin->auth == 'super' ) { ?>
<form action="upload_resource.php" method="post">
    Month: <input type="text" name="month" /><br />
    Link: <input type="text" name="link" /><br />
    <input type="submit" name="submit" value="submit" />
</form>
<br />
<? } ?>

<? if ( count( $resources ) > 0 ) { ?>
    <table>
        <tr>
            <th>Month</th>
            <th>Link</th>
        </tr>
        <? 
        foreach ( $resources as $resource ) {
            echo "<tr><td>" . $resource['month'] . "</td><td><a href='" . $resource['link'] . "'>" . 
            $resource['link'] . "</a></td></tr>";
        }
        ?>
    </table>
<? } ?>
    
</body>
</html>