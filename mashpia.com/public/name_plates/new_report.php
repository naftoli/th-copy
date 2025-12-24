<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$admin_auth = ['school'];
require_once '../header.php';
require_once '../api/header/db.php';
require_once '../class.adminSchools.php';
require_once '../class.globalSettings.php';

if ($admin_user['auth'] != 'super') {
    die('Access Denied');
}

$start_date = 2457540; // Rosh Chodesh Sivan 5785

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();
$year = GlobalSettings::getRegistrationYear();

getNewlyRegistered();
getPromoted(); // adds promoted to info
getFuturePromoted(); // adds future promoted to info

function getNewlyRegistered() {
    global $MASHPIA_DB, $year, $schools, $info;
    $sql = "SELECT s.school_id, s.school_name, c.class_grade, c.class_sub, u.user_id, u.first, u.last, u.first_he, u.last_he, u.user_serial 
            FROM users u 
            JOIN schools s ON s.school_id = u.school_id
            JOIN classes c ON c.class_id = u.class_id
            JOIN user_registration ur USING (user_id) 
            WHERE ur.year = ? 
            AND u.user_id NOT IN (
                SELECT user_id FROM name_plates WHERE year = ? AND school_id IN (" . implode(',', array_keys($schools)) . ")
            )
            AND u.school_id IN (" . implode(',', array_keys($schools)) . ")";
    $stmt = $MASHPIA_DB->prepare($sql);
    $stmt->execute([$year, $year - 1]);
    // $stmt->debugDumpParams();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $info['new'][$row['school_id']][$row['class_grade']][$row['class_sub']][] = $row;
    }
}

function getPromoted() {
    global $MASHPIA_DB, $start_date, $schools, $info;
    $sql = "SELECT s.school_id, s.school_name, c.class_grade, c.class_sub, u.user_id, u.first, u.last, u.first_he, u.last_he, u.user_serial, rm.rank_ord  
            FROM users u 
            JOIN rank_marks rm USING (user_id)
            JOIN schools s ON s.school_id = u.school_id
            JOIN classes c ON c.class_id = u.class_id
            WHERE rm.date_promoted >= ?
            AND rm.rank_ord IN (9, 12) 
            AND u.school_id IN (" . implode(',', array_keys($schools)) . ")";
    $stmt = $MASHPIA_DB->prepare($sql);
    $stmt->execute([$start_date]);
    // $stmt->debugDumpParams();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $info['promoted'][$row['school_id']][$row['class_grade']][$row['class_sub']][] = $row;
    }
}

function getFuturePromoted() {
    global $MASHPIA_DB, $year, $schools, $info;
    $info['future'] = [];
}

$reasons = [
    'new' => 'New Registration',
    'promoted' => 'Promotion (General / 3* General)',
    'future' => 'Future Promotion (General / 3* General)',
];

echo '<pre>'; print_r($info); echo '</pre>'; exit;
/**
 Name Plate Report should include the following Information for each child: 
- school
- class
- serial
- English name
- Hebrew name
- Reason

The following children should come up in the report. With the reason written in the report. 
- New Registration
> A child that registered this year that didnt register last year. 

- Promotion (General)
> A child who was promoted to general from Rosh Chodesh Sivan 5785 until today

- Promotion (3* General)
> A child who was promoted to 3* general from Rosh Chodesh Sivan 5785 until today

- Future Promotion 
> A child who will be  promoted to general/3* general from today until 26 iyar 5786
*/
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="../admin_styles.css" rel="stylesheet" type="text/css">
    <title>Name Plate Report</title>
    <style>
        .infobox {
            line-height: 1.5;
        }
        tr, th, td {
            padding: 5px;
            font-size: 12px;
            border-bottom: 1px solid #ccc;
        }
        .reason {
            font-size: 10px;
        }
    </style>
</head>
<body>
    <?php include '../admin_header.php'; ?>
    <h1>Name Plate Report</h1>
    <button onclick="downloadReport()">Download Report</button><br /><br />
    <table>
        <thead>
            <tr>
                <th>School</th>
                <th>Class</th>
                <th>Serial</th>
                <th>English Name</th>
                <th>Hebrew Name</th>
                <th>Reason</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($info as $reason => $more) {
                foreach ($more as $school_id => $other) {
                    foreach ($other as $class_grade => $more_sub) {
                        foreach ($more_sub as $class_sub => $rows) {
                            foreach ($rows as $row) {
                                echo '<tr>';
                                echo '<td>' . $schools[$school_id] . '</td>';
                                echo '<td>' . $class_grade . ($class_sub ? '-' . $class_sub : '') . '</td>';
                                echo '<td>' . $row['user_serial'] . '</td>';
                                echo '<td>' . $row['first'] . ' ' . $row['last'] . '</td>';
                                echo '<td>' . $row['first_he'] . ' ' . $row['last_he'] . '</td>';
                                echo '<td>' . $reasons[$reason] . '</td>';
                                echo '</tr>';
                            }
                        }
                    }
                }
            }
            ?>
        </tbody>
    </table>
    <script>
        function downloadReport() {
            const universalBOM = "\uFEFF";
            let csvContent = `School,Class,Serial,English Name,Hebrew Name,Reason\n`;
            // add the data
            const rows = []
            $("tr").each(function() {
                const row = []
                $(this).find("td").each(function() {
                    row.push($(this).text())
                })
                rows.push(row)
            })
            rows.forEach(row => {
                csvContent += `${row.join(',')}\n`
            })
            csvContent = encodeURIComponent(universalBOM + csvContent);
            const link = document.createElement('a');
            link.href = `data:text/csv;charset=utf-8,${csvContent}`;
            link.download = 'name_plate_report.csv';
            link.click();
        }
    </script>
</body>
</html>