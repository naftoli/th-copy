<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

if (isset($_POST['submit'])) {
    $qrys = [];
    if (($handle = fopen($_FILES['schoolList']['tmp_name'], "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $school = $data[0];
            // find out if school exists
            $sql = "select * from non_th_schools where school_name like '%" . $school . "%'";
            $result = mysql_query($sql);
            if (mysql_num_rows($result)) {
                if ($school == 'Ateres Chaya') {
                    $qrys[] = "insert into non_th_schools set school_name = '" . addslashes($school) . "'";
                }
            } else {
                $qrys[] = "insert into non_th_schools set school_name = '" . addslashes($school) . "'";
            }
        }
    }
    foreach ($qrys as $qry) {
        if (! mysql_query($qry)) {
            echo "Error in qry: " . $qry . "<br />";
        }
    }
    echo "done.";

    // get all non th schools with ids and update users db
    $schools = [];
    $sql = "select * from non_th_schools";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $schools[$row['non_th_school_id']] = $row['school_name'];
    }

    $updated = 0;
    foreach ($schools as $id => $school) {
        $sql = "update users set non_th_school_id = $id where non_th_school = \"" . $school . "\"";
        if (mysql_query($sql)) $updated++;
        else echo $sql . "<br />";
    }
    echo "Updated: " . $updated;
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
