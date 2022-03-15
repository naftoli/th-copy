<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

if (isset($_POST['submit'])) {
    $qrys = [];
    if (($handle = fopen($_FILES['schoolList']['tmp_name'], "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $school = $data[0];
            // find out if school exists
            $sql = "select * from non_th_schools where school_name like '%" . $school . "%'";
            echo $sql . "<br />";
            $result = mysql_query($sql);
            if (mysql_num_rows($result)) {
                echo "non th school: " . $school . ", school found: " . mysql_fetch_assoc($result)['school_name'] . "<br />";
            }
//            $qrys[] = "insert into non_th_schools set school_name = '" . addslashes($school) . "'";
        }
    }
//    foreach ($qrys as $qry) {
//        mysql_query($qry);
//    }
    echo "done.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Upload Non TH Schools</title>
</head>
<body>
<form action="" method="post" enctype="multipart/form-data">
    <input type="file" name="schoolList">
    <input type="submit" name="submit" value="Upload" />
</form>
</body>
</html>
