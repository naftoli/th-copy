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
$sort_by = $_POST['sort_by'] ?? 'rank';
if ($sort_by == 'rank') {
    $sql = "SELECT u.*, MAX(rm.rank_ord) as rank FROM `users` u  
    JOIN `classes` c ON u.class_id = c.class_id 
    JOIN rank_marks rm ON u.user_id = rm.user_id 
    WHERE u.`school_id` = :school_id";
    if (isset($_POST['reg']) && $_POST['reg'] == 'reg_only') $sql .= " AND u.user_registered > 0";
    $sql .= " GROUP BY u.user_id 
            ORDER BY rank DESC, u.last, u.first";
} else {
    $sql = "SELECT * FROM `users` u  
    JOIN `classes` c ON u.class_id = c.class_id 
    WHERE u.`school_id` = :school_id ";
    if (isset($_POST['reg']) && $_POST['reg'] == 'reg_only') $sql .= " AND u.user_registered > 0";
    $sql .= " ORDER BY u.last, u.first";
}
$stmt = $MASHPIA_DB->prepare($sql);
foreach ($schools as $school_id => $school_name) {
    $stmt->execute([
        'school_id' => $school_id,
    ]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $info[$school_id] = $students;
}

$lang = $_POST['lang'] ?? 'en';
$grade = $_POST['grade'] ?? false;
$picture = $_POST['picture'] ?? true;
$picture_size = $_POST['picture_size'] ?? 100;
$reg = $_POST['reg'] ?? 'all';
$add_space = $_POST['add_space'] ?? false;

$student_height = $picture_size + 60;
if (isset($_POST['grade'])) $student_height += 10;
if (isset($_POST['add_space'])) $student_height += 25; // for adding stuff to the bottom
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
        <h1 style="color: #2c3e50; text-align: center; margin-bottom: 30px; text-shadow: 2px 2px 4px rgba(0,0,0,0.1);">Picture Report</h1>
        <form action="custom_report.php" method="post">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <h2 style="margin: 0 0 15px 0; font-size: 1.2em; text-align: center; font-weight: 500;">Report Configuration</h2>
                <p style="margin: 0; text-align: center; font-size: 1.1em; line-height: 1.5;">
                    Please choose which fields you would like to include in the report. Customize the display options below to create the perfect report for your needs.
                </p>
            </div>
            <div style="display: flex; gap: 40px; margin: 20px 0;">
                <div style="flex: 1;">
                    <h3 style="margin-bottom: 15px; color: #333;">Student Filter</h3>
                    <label style="display: block; margin: 10px 0; cursor: pointer;">
                        <input type="radio" name="reg" value="all"
                        <?php if ($reg == 'all') echo 'checked'; ?>
                        style="margin-right: 8px;">
                        All students (including unregistered)
                    </label>
                    <label style="display: block; margin: 10px 0; cursor: pointer;">
                        <input type="radio" name="reg" value="reg_only"
                        <?php if ($reg == 'reg_only') echo 'checked'; ?>
                        style="margin-right: 8px;">
                        Only registered students
                    </label>
                </div>
                
                <div style="flex: 1;">
                    <h3 style="margin-bottom: 15px; color: #333;">Name Language</h3>
                    <label style="display: block; margin: 10px 0; cursor: pointer;">
                        <input type="radio" name="lang" value="en"
                        <?php if ($lang == 'en') echo 'checked'; ?>
                        style="margin-right: 8px;">
                        English Name
                    </label>
                    <label style="display: block; margin: 10px 0; cursor: pointer;">
                        <input type="radio" name="lang" value="he"
                        <?php if ($lang == 'he') echo 'checked'; ?>
                        style="margin-right: 8px;">
                        Hebrew Name
                    </label>
                </div>
            </div>
            
            <div style="display: flex; gap: 40px; margin: 20px 0;">
                <div style="flex: 1;">
                    <h3 style="margin-bottom: 15px; color: #333;">Display Options</h3>
                    <label style="display: block; margin: 10px 0; cursor: pointer;">
                        <input type="checkbox" name="grade" value="grade"
                        <?php if ($grade) echo 'checked'; ?>
                        style="margin-right: 8px;">
                        Show Grade
                    </label>
                    <label style="display: block; margin: 10px 0; cursor: pointer;">
                        <input type="checkbox" name="add_space" value="add_space"
                        <?php if ($add_space) echo 'checked'; ?>
                        style="margin-right: 8px;">
                        Add extra space under each student
                    </label>
                </div>
                
                <div style="flex: 1;">
                    <h3 style="margin-bottom: 15px; color: #333;">Picture Settings</h3>
                    <label style="display: block; margin: 10px 0; cursor: pointer;">
                        <input type="checkbox" name="picture" value="picture"
                        <?php if ($picture) echo 'checked'; ?>
                        style="margin-right: 8px;">
                        Show Picture
                    </label>
                    <div style="margin-top: 10px;">
                        <label style="margin-bottom: 5px; font-weight: bold;">Picture Size:</label>
                        <select name="picture_size" style="padding: 5px; border-radius: 3px; border: 1px solid #ccc;">
                            <option value="100" <?php if ($picture_size == 100) echo 'selected'; ?>>Small (100px)</option>
                            <option value="150" <?php if ($picture_size == 150) echo 'selected'; ?>>Medium (150px)</option>
                            <option value="200" <?php if ($picture_size == 200) echo 'selected'; ?>>Large (200px)</option>
                            <option value="250" <?php if ($picture_size == 250) echo 'selected'; ?>>Extra Large (250px)</option>
                        </select>
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 40px; margin: 20px 0;">
                <div style="flex: 1;">
                    <h3 style="margin-bottom: 15px; color: #333;">Sorting Options</h3>
                    <label style="display: block; margin: 10px 0; cursor: pointer;">
                        <input type="radio" name="sort_by" value="rank"
                        <?php if (!isset($_POST['sort_by']) || $_POST['sort_by'] == 'rank') echo 'checked'; ?>
                        style="margin-right: 8px;">
                        Sort by Rank
                    </label>
                    <label style="display: block; margin: 10px 0; cursor: pointer;">
                        <input type="radio" name="sort_by" value="alphabetical"
                        <?php if (isset($_POST['sort_by']) && $_POST['sort_by'] == 'alphabetical') echo 'checked'; ?>
                        style="margin-right: 8px;">
                        Sort Alphabetically
                    </label>
                </div>
            </div>
            <div style="text-align: center; margin: 30px 0;">
                <input type="submit" name="submit" value="Generate Report" style="
                    background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
                    color: white;
                    padding: 10px 20px;
                    font-size: 14px;
                    font-weight: bold;
                    border: none;
                    border-radius: 25px;
                    cursor: pointer;
                    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
                    transition: all 0.3s ease;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(76, 175, 80, 0.4)'" 
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(76, 175, 80, 0.3)'" />
             </div>
        </form>
        <div id="report">
            <?php
            if (isset($_POST['submit'])) { ?>
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
                let picture = img ? img.src : '';
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