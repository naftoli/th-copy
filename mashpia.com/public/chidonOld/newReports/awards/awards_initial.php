<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

require 'afterTest1.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Awards Report</title>
    <link href="../../../admin_styles.css" rel="stylesheet" type="text/css">
    <style>
      fieldset {
        width: 200px;
        border: 1px solid grey;
        border-radius: 15px;
        padding: 15px;
      }
      legend {
        left: 30px;
        padding-left: 5px;
        padding-right: 5px;
      }
      #submit {
        padding: 10px;
        font-size: 16px;
      }
      tr, th, td {
        padding: 5px;
        font-size: 14px;
      }
    </style>
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
<h1>Awards Report</h1>
    <div>
        <table>
            <tr>
                <th>School Name</th>
                <th>Serial Number</th>
                <th>Full Hebrew Name</th>
                <th>Code Part 1</th>
                <th>Code Part 2</th>
                <th>Code Part 3</th>
                <th>Template Code</th>
            </tr>
            <?php
            $i = 0;
            $previousGrade = '';
            foreach ($info as $school_id => $users) {
                $total = count($users);
                $colspan = 4;
                echo "<tr><td>" . $schools[$school_id] . " (" . $total . ")</td><td colspan='$colspan'></td></tr>";
                foreach ($users as $idx => $row) {
                    $school = $row['school_name'];
                    $serial = $row['user_serial'];
                    $he_name = $row['first_he'] . ' ' . $row['last_he'];
                    $template = '';
                    if ($row['gender'] == 'M') {
                        $template = 'B-' . $row['class_grade'];
                    } else if ($row['gender'] == 'F') {
                        $template = 'G-' . $row['class_grade'];
                    }
                    $code = '';
                    if (in_array($row['school_id'], [61, 269])) {
                        // find number of child in admins array
                        $key = array_search($row['user_id'], $admins[$row['parent_id']]);
                        $code = $row['parent_id'] . '-' . ($key + 1);
                    } else {
                        $grade = $row['school_id'] . '-' . $row['class_grade'];
                        if ($previousGrade != $grade) {
                            $i = 1;
                            $previousGrade = $grade;
                        }
                        $code = $row['school_id'] . '-' . $row['class_grade'] . '-' . $i;
                        $i++;
                    }

                    $arrCode = explode('-', $code);
                    echo "<tr><td></td><td>" . $serial . "</td><td>" . $he_name . "</td><td>" . $arrCode[0] .
                        "</td><td>" . $arrCode[1] . "</td><td>" . (isset($arrCode[2]) ? $arrCode[2] : '') .
                        "</td><td>" . $template . "</td>";
                    echo "</tr>";
                }
            }
            ?>
        </table>
    </div>
</body>
<script>
  $("#awardForm").submit( function(e) {
    let type = $(".award_type").is(":checked")
    let final = $(".final").is(":checked")
    if (! (type && final)) {
      alert('You must make a choice in both sections.')
      e.preventDefault()
    }
  })

  function downloadCSV(headers, rows) {
    // generate the csv content
    const universalBOM = "\uFEFF";
    let csvContent = `${headers.join(',')}\n`;
    // Add each row to the CSV content and encode it for unicode in excel
    rows.forEach(row => {
      csvContent += `${row.join(',')}\n`
    });
    csvContent = encodeURIComponent(universalBOM + csvContent);
    // create and click the download link
    let link = document.createElement('a');
    link.href = `data:text/csv;charset=utf-8,${csvContent}`;
    console.log(link.href)
    link.download = `awards.csv`;
    link.click();
  }
</script>
</html>
