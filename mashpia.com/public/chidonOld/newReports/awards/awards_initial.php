<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

$year = GlobalSettings::getChidonYear();

// qry to get all kids that should get the award
$sql = "
    SELECT
        s.school_name, u.user_id, u.class_id, u.school_id, u.user_serial, u.first_he, u.last_he, u.gender, 
        c.class_grade, c.class_sub, tc.parent_id, tc.th_chidon_id, tc.test_type, tc.reward_type 
    FROM 
        users u 
        join schools s using (school_id) 
        join classes c on c.class_id = u.class_id 
        join th_chidon tc using (user_id) 
        join th_chidon_marks tcm using (th_chidon_id) 
    WHERE
        tc.year = $year 
    GROUP BY 
        u.user_id 
    ORDER BY
        s.school_id, c.class_grade, c.class_sub, u.last, u.first";
//echo $sql . "<br />"; exit;
$stmt = $MASHPIA_DB->query($sql);
$rows = $stmt->fetchAll();
$info = [];
foreach ($rows as $row) {
    $row['award'] = 'cert'; // default to 'cert'
    $info[$row['school_id']][] = $row;
}

// find out order of kids for admins
$admins = [];
$sql = "select aa.admin_id, aa.id from admin_auths aa 
        join users u on u.user_id = aa.id 
        join th_chidon tc using (user_id) 
        where tc.year = $year 
        and u.school_id in (61, 269) 
        group by id 
        order by u.dob";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $admins[$row['admin_id']][] = $row['id'];
}

$adminsSorted = [];
foreach ($info as $school => $children) {
    if (in_array($school, [61, 269])) {
        foreach ($admins as $admin_id => $more) {
            foreach ($more as $user_id) {
                $adminsSorted[$admin_id][] = $user_id;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Certificate Report</title>
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
<h1>Certificate Report</h1>
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
                <th>Type of Award</th>
            </tr>
            <?php
            $i = 0;
            $previousGrade = '';
            foreach ($info as $school_id => $users) {
                $total = count($users);
                $colspan = 4;
                echo "<tr><td>" . $schools[$school_id] . " (" . $total . ")</td><td colspan='$colspan'></td></tr>";
                foreach ($users as $row) {
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
                        $code = $row['parent_id'] . '-';
                        // find number of child in admins array
                        if (isset($adminsSorted[$row['parent_id']])) {
                            $key = array_search($row['user_id'], $adminsSorted[$row['parent_id']]);
                            if ($key !== false) $code .= $key + 1;
                        }
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
                        "</td><td>" . $template . "</td><td>" . $row['award'] . "</td></tr>";
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
