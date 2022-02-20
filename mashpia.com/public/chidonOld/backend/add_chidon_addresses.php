<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

if (isset($_POST['submit'])) {
    if (isset($_FILES['user_prizes'])) {
        $qrys = [];
        $first = true;
        $file = $_FILES['user_prizes']['tmp_name'];
        if (($handle = fopen($file, "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                // skip first row
                if ($first) {
                    $first = false;
                    continue;
                }
                $price = $data[0];
                $admin_id = $data[1];
                $usa = strtolower($data[2]) == 'no' ? 0 : 1;
                $sql = "insert ignore into chidon_parent_shipping 
                        set parent_id = $admin_id, 
                        year = $year, 
                        cost = $price, 
                        usa = $usa 
                        on duplicate key update cost = $price";
                $qrys[] = $sql;
            }
        }

        mysql_query('set autocommit=0');
        mysql_query("begin");
        $success = true;
        foreach ($qrys as $qry) {
            if (! mysql_query($qry)) {
                $success = false;
                echo "Error: " . mysql_error() . "<br />" . $qry;
                break;
            }
        }
        if ($success) mysql_query("commit");
        else mysql_query("rollback");
        mysql_query("set autocommit=1");
        echo "Done.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8">
    <title>Add to user prizes</title>
</head>
<body>
<?php if (! isset($_POST['submit'])) : ?>
    <form action="" method="post" enctype="multipart/form-data">
        Select file to upload: <input type="file" name="user_prizes" />
        <input type="submit" name="submit" value="submit">
    </form>
<?php endif; ?>
</body>
</html>
