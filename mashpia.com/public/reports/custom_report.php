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
$picture_size = 100;
if ( isset($_POST['picture_size']) ) $picture_size = $_POST['picture_size'];

$student_height = $picture_size + 55;
if (isset($_POST['grade'])) $student_height += 10;
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
                width: <?php echo $picture_size + 20; ?>px;
                height: <?php echo $student_height; ?>px;
                padding: 5px;
                float: left;
                text-align: center;
                border: 1px solid #000;
                border-radius: 5px;
                margin: 10px;
            }
            .name {
                font-size: 16px;
                font-weight: bold;
                text-align: center;
                margin-top: 5px;
            }
            .grade {
                font-size: 12px;
                text-align: center;
                margin-top: 5px;
            }
            img {
                max-width: <?php echo $picture_size; ?>px;
                max-height: <?php echo $picture_size; ?>px;
                border-radius: 5px;
                margin: 5px;
            }
            button {
                padding: 10px;
                margin: 10px;
                font-size: 14px;
                border-radius: 5px;
                border: 1px solid #000;
                cursor: pointer;
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
                <input type="checkbox" name="grade" value="grade" checked> Grade<br />
                <input type="checkbox" name="picture" value="picture" checked> Picture 
                <select name="picture_size">
                    <option value="100">Small (100px)</option>
                    <option value="150">Medium (150px)</option>
                    <option value="200">Large (200px)</option>
                    <option value="250">Extra Large (250px)</option>
                </select>
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
                $picture_size = $_POST['picture_size'] ?? 100;
                ?>
                <h2>Report</h2>
                <button onclick="window.print()">Print</button>
                <button onclick="downloadAsCSV()">Download as CSV</button>
                <?php
                foreach ($info as $school_id => $students) { 
                    echo "<h3>{$schools[$school_id]}</h3>";
                    echo "<div class='students'>";
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
                                <?php if ($grade) echo $student['class_grade'] . ($student['class_sub'] ? '-' . $student['class_sub'] : ''); ?>
                            </div>
                        </div>
                        <?php 
                    }
                    echo "</div><div style='clear: both;'></div><br /><br />";
                }
            }
            ?>
        </div>
    </body>
    <script>
        function downloadAsCSV() {
            let csvContent = "data:text/csv;charset=utf-8,";
            csvContent += "Name,Grade,Picture\n";
            
            const rows = document.querySelectorAll("#report .student");
            
            if (rows.length === 0) {
                alert("No data to export");
                return;
            }
            
            rows.forEach(row => {
                const name = row.querySelector(".name").textContent.trim();
                const grade = row.querySelector(".grade").textContent.trim();
                const img = row.querySelector(".pic img");
                
                let picture = '';
                if (img) {
                    picture = img.src;
                }
                
                csvContent += `"${name}","${grade}","${picture}"\n`;
            });
            
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "student_report.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</html>