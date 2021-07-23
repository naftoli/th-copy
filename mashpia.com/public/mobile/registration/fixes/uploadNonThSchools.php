<?php
if (isset($_POST['submit'])) {
    $qrys = [];
    if (($handle = fopen($_FILES['schoolList']['tmp_name'], "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $school = $data[0];
            $qrys[] = "insert into non_th_schools set school_name = '$school'";
        }
    }
    echo "<pre>"; print_r($qrys); echo "</pre>";
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
