<? 
$admin_auth = array('school','user'); 
require('header.php');

require_once 'class.fileUploader.php';
$fu = new FileUploader();
    
if ( isset( $_POST['submit'] ) ) {
    $fu->upload( $_FILES['file'] );
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
    th, td {
        padding: 3px;
        margin-right: 20px;
    }
</style>
</head>

<body>
<? 
require_once('admin_header.php');
?>
<h1>File Uploader</h1>
<form action="upload_file.php" method="post" enctype="multipart/form-data">
    <input type="file" name="file" /><br />
    <input type="submit" value="upload" name="submit" />
</form>
<h2>Uploaded files</h2>
<table>
    <tr>
        <th>Name</th>
        <th>Link</th>
    </tr>
<? $fu->showFiles() ?>
</table>
</body>
</html>