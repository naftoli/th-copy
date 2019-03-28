<?
$cd = $_GET['cd'];
$code = $_GET['c'];

require_once 'db.php';
$sql = "select c.download_link, p.purchase_id  
		from cds c, purchase_details pd, purchases p 
		where pd.purchase_id = p.purchase_id 
		and pd.cd_id = c.id 
		and p.code = " . mysql_real_escape_string($code) . " 
		and c.id = " . mysql_real_escape_string($cd) . " 
		and pd.downloaded = 0";
$result = mysql_query($sql);
if (mysql_num_rows($result) > 0) {
	$row = mysql_fetch_assoc($result);
	$file = "../files/" . $row['download_link'];
	
	if (file_exists($file)) {
	    header('Content-Description: File Transfer');
	    header('Content-Type: application/force-download');
	    header('Content-Disposition: attachment; filename="../files/' . basename($file) . '"');
	    header('Expires: 0');
	    header('Cache-Control: must-revalidate');
	    header('Pragma: public');
	    header('Content-Length: ' . filesize($file));
	    readfile($file);
		
		$sql = "update purchase_details set downloaded = 1, download_date = now() where cd_id = " . mysql_real_escape_string($cd) . " 
			and purchase_id = " . mysql_real_escape_string($row['purchase_id']);
		mysql_query($sql) or die(mysql_error());
	    exit;
	}
}
?>