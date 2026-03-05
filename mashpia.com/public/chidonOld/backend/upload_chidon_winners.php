<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    die('Access Denied');
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

if (isset($_POST['submit'])) {
    if (isset($_FILES['winners'])) {
        $file = $_FILES['winners']['tmp_name'];
        if (($handle = fopen($file, "r")) !== false) {
            $stmt = $MASHPIA_DB->prepare("
                insert into th_chidon_winners
                set year = :year,
                serial = :serial,
                school = :school,
                grade = :grade,
                gender = :gender,
                name = :name,
                team = :team,
                trophy = :trophy,
                khk_trophy = :khk_trophy,
                blue_trophy = :blue_trophy
            ");
            $headers = ['serial', 'school', 'grade', 'gender', 'name', 'team', 'trophy', 'khk_trophy', 'blue_trophy'];
            $first = true;
            $success = true;
            $MASHPIA_DB->beginTransaction();
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                if ($first) {
                    $first = false;
                    continue;
                }
                foreach ($headers as $index => $header) {
                    $$header = $data[$index];
                }
                $res = $stmt->execute([
                    ':year' => $year,
                    ':serial' => $serial,
                    ':school' => $school,
                    ':grade' => $grade,
                    ':gender' => $gender,
                    ':name' => $name,
                    ':team' => $team,
                    ':trophy' => $trophy,
                    ':khk_trophy' => $khk_trophy,
                    ':blue_trophy' => empty($blue_trophy) ? 0 : 1
                ]);
                if (!$res) {
                    $success = false;
                    echo "Error: " . $stmt->errorInfo()[2];
                    break;
                }
            }
            if ($success) {
                $MASHPIA_DB->commit();
                echo "Winners uploaded successfully";
            } else {
                $MASHPIA_DB->rollBack();
                echo "Error uploading winners";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload Chidon Winners</title>
</head>
<body>
    <form action="upload_chidon_winners.php" method="post" enctype="multipart/form-data">
        <input type="file" name="winners" id="winners">
        <input type="submit" value="Upload" name="submit">
    </form>
</body>
</html>