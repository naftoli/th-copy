<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';

$adminSchools = new adminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $adminSchools->getSchools();

$info = [];
$stmt = $MASHPIA_DB->prepare("
    SELECT * FROM `users` u  
    JOIN `classes` c ON u.class_id = c.class_id 
    WHERE u.`school_id` = :school_id 
");
foreach ($schools as $school_id => $school_name) {
    $stmt->execute([
        'school_id' => $school_id,
    ]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $info[$school_id] = $students;
}
?>
<DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <title>Picture Report</title>
        <style>
            .student {
                width: 50px;
                padding: 10px;
                float: left;
            }
            img.pic {
                width: 50px;
            }
            .name {
                font-size: 12px;
                text-align: center;
            }
            .grade {
                font-size: 10px;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'; ?>
        <h1>Picture Report</h1>
        <form action="custom_report.php" method="post">
            <p>
                Please choose which fields you would like to include in the report:
            </p>
            <p>
                <input type="radio" name="lang" value="en" checked> English Name<br />
                <input type="radio" name="lang" value="he"> Hebrew Name<br />
                <input type="checkbox" name="grade" value="grade"> Grade<br />
                <input type="checkbox" name="picture" value="picture"> Picture<br />
            </p>
            <p>
                <input type="submit" name="submit" value="Generate Report">
            </p>
        </form>
        <div id="report">
            <?php
            if (isset($_POST['submit'])) {
                $lang = $_POST['lang'];
                $grade = $_POST['grade'] ?? false;
                $picture = $_POST['picture'] ?? false;
                ?>
                <h2>Report</h2>
                <?php
                foreach ($info as $school_id => $students) { 
                    foreach ($students as $student) {
                        if ($lang == 'en') $name = $student['first'] . ' ' . $student['last'];
                        else if ($lang == 'he') $name = $student['first_he'] . ' ' . $student['last_he'];
                        $pic = $student['mobile_pic'] ? '/mobile/reg/' . $student['mobile_pic'] : '/file_view.php?id=' . $student['user_photo_id'];
                        if ( !$pic ) $pic = '/mobile/reg/images/profile-photo-default.jpg';
                        ?>
                        <div class='student'>
                            <div class='pic'>
                            <?php if ($picture) echo "<img src='{$pic}' alt='{$name}'>"; ?>
                            </div>
                            <div class='name'>
                                <?php echo $name; ?>
                            </div>
                            <div class='grade'>
                                <?php echo $student['class_grade'] . ($student['class_sub'] ? '-' . $student['class_sub'] : ''); ?>
                            </div>
                        </div>
                        <?php 
                    }
                }
            }
            ?>
        </div>
    </body>
</html>