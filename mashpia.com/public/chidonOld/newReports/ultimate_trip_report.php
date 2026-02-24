<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$superAdmin = $admin_user['auth'] == 'super';

$sql = "SELECT 
            *
        FROM
            th_chidon tc
                JOIN
            users u USING (user_id)
                JOIN
            schools s ON s.school_id = u.school_id
                JOIN
            classes c ON c.class_id = u.class_id
        WHERE
            tc.year = :year AND tc.ultimate_trip = 1 
                AND u.school_id in (" . implode(',', array_keys($schools)) . ") 
        ORDER BY u.school_id , class_grade , class_sub , last , first";
$stmt = $MASHPIA_DB->prepare($sql);
$stmt->execute(['year' => $year]);
$info = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta charset="UTF-8">
    <title>Ultimate Trip Info</title>
    <style>
    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin: 25px 0;
        font-size: 14px;
        font-family: Arial, sans-serif;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }

    .data-table thead tr {
        background-color: #009879;
        color: #ffffff;
        text-align: left;
        position: sticky;
        top: 0;
    }

    .data-table th,
    .data-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #dddddd;
        white-space: nowrap;
    }

    .data-table tbody tr {
        border-bottom: 1px solid #dddddd;
    }

    .data-table tbody tr:nth-of-type(even) {
        background-color: #f3f3f3;
    }

    .data-table tbody tr:last-of-type {
        border-bottom: 2px solid #009879;
    }

    .data-table tbody tr:hover {
        background-color: #f5f5f5;
        cursor: default;
    }

    /* Container for table with horizontal scroll */
    .table-container {
        max-width: 100%;
        overflow-x: auto;
        margin: 20px 0;
        padding: 0 10px;
    }

    /* Additional styles for better readability */
    body {
        margin: 0;
        padding: 20px;
        font-family: Arial, sans-serif;
    }

    h1 {
        color: #009879;
        margin-bottom: 20px;
    }

    .download-csv {
        background-color: #009879;
        color: #ffffff;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
    }

    .download-csv:hover {
        background-color: #007259;
    }

    .download-csv:active {
        background-color: #005e46;
    }

    .download-csv:disabled {
        background-color: #cccccc;
        cursor: not-allowed;
    }
    </style>
</head>
<body>
    <h1>Ultimate Trip Info</h1>
    <button class="download-csv" onclick="downloadAsCsv()">Download CSV</button>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>School</th>
                    <th>Grade/Class</th>
                    <th>Student</th>
                    <th>Serial Number</th>
                    <th>Gender</th>
                    <th>Sandwich</th>
                    <th>Height</th>
                    <th>Weight</th>
                    <th>Ski/Snowboard</th>
                    <th>Skill Level</th>
                    <th>Outerwear</th>
                    <th>Shoe Size</th>
                    <th>Allergies</th>
                    <th>Trip Option</th>
                    <th>In Walking Zone</th>
                    <th>Host</th>
                    <th>Host Phone Number</th>
                    <th>Street Number</th>
                    <th>Street Number Suffix</th>
                    <th>Street Name</th>
                    <th>Apt. #</th>
                    <th>Host Cross Street 1</th>
                    <th>Host Cross Street 2</th>
                    <th>Thursday Walking</th>
                    <th>Motzei Shabbos Walking</th>
                    <th>Zone ID</th>
                    <th>Comments</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($info as $row) {
                    $chidon_id = $row['th_chidon_id'];
                    $school = $row['school_name'];
                    $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
                    $student = $row['first'] . ' ' . $row['last'];
                    $serial = $row['user_serial'];
                    $gender = strtolower($row['gender']) == 'm' ? 'boys' : 'girls';
                    $shoe = $row['shoe_size'];
                    $sandwich = $row['sandwich'];
                    $allergies = $row['allergies'];
                    $in_zone = $row['in_zone'];
                    $host = $row['host'];
                    $host_phone = $row['host_number'];
                    $street_num = $row['host_street_num'];
                    $suffix = $row['host_street_num_suffix'];
                    $street = $row['host_street'];
                    $apt = $row['host_street_apt'];
                    $zone = $row['walking_zone'];
                    $cross1 = $row['between_streets1'];
                    $cross2 = $row['between_streets2'];
                    $poll = $row['poll'];
                    $thurs_walking = $row['thurs_walking'];
                    $ms_walking = $row['ms_walking'];
                    $height = $row['height'];
                    $weight = $row['weight'];
                    $ski = $row['ski'];
                    $skill = $row['skill'];
                    $outerwear = $row['outerwear'];
                    $trip_option = intval($row['trip_option']);

                    switch ($thurs_walking) {
                        case 0:
                            $thurs = 'child walking alone';
                            break;
                        case 1:
                            $thurs = 'parent picking up';
                            break;
                        case 2:
                            $thurs = 'NEEDS TO BE DROPPED OFF';
                            break;
                    }

                    switch ($ms_walking) {
                        case 0:
                            $ms = 'child walking alone';
                            break;
                        case 1:
                            $ms = 'parent picking up';
                            break;
                        case 2:
                            $ms = 'NEEDS TO BE DROPPED OFF';
                            break;
                    }

                    echo "<tr class='' id='" . $chidon_id . "'><td>" . $school . "</td><td>" . $grade . "</td><td>" . $student . "
                        </td><td>" . $serial . "</td><td>" . $gender . "</td>
                        <td><input type='text' class='sandwhich' value='" . $sandwich . "' /></td>
                        <td><input type='text' class='height' value='" . $height . "' /></td>
                        <td><input type='text' class='weight' value='" . $weight . "' /></td>
                        <td><input type='text' class='ski' value='" . $ski . "' /></td>
                        <td><input type='text' class='skill' value='" . $skill . "' /></td>
                        <td><input type='text' class='outerwear' value='" . $outerwear . "' /></td>
                        <td><input type='text' class='shoe' value='" . $shoe . "' /></td>
                        <td><input type='text' class='alergies' value='" . $allergies . "' /></td>
                        <td><select name='trip_option' class='trip_option'>
                        <option value='0'>Select Option</option>
                        <option value='1'" . ($trip_option == 1 ? ' selected' : '') . "
                        >Option 1</option><option value='2'" . ($trip_option == 2 ? ' selected' : '') . "
                        >Option 2</option><option value='3'" . ($trip_option == 3 ? ' selected' : '') . "
                        >Option 3</option><option value='4'" . ($trip_option == 4 ? ' selected' : '') . "
                        >Option 4</option><option value='5'" . ($trip_option == 5 ? ' selected' : '') . "
                        >Option 5</option></select></td>
                        <td><select name='in_zone' class='in_zone'><option value='yes'" . ($in_zone ? ' selected' : '') . "
                        >yes</option><option value='no'" . (!$in_zone ? ' selected' : '') . ">no</option></select></td> 
                        <td><input type='text' class='host' value='" . $host . "' /></td>
                        <td><input type='text' class='host_phone' value='" . $host_phone . "' /></td>
                        <td><input type='text' class='street_num' value='" . $street_num . "' size='3'  /></td>
                        <td><input type='text' class='suffix' value='" . $suffix . "' size='2' /></td>
                        <td><input type='text' class='street' value='" . $street . "' /></td>
                        <td><input type='text' class='apt' value='" . $apt . "' size='3' /></td> 
                        <td><input type='text' class='cross1' value='" . $cross1 . "' /></td>
                        <td><input type='text' class='cross2' value='" . $cross2 . "' /></td>
                        <td>" . $thurs . "</td><td>" . $ms . "</td>
                        <td>" . $zone . "</td><td>" . $poll . "</td><td>";
                    if ($superAdmin) echo "<button class='save'>Save</button>";
                    echo "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
<script src="https://code.jquery.com/jquery-3.6.3.min.js"
        integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU="
        crossorigin="anonymous"></script>
<script>
    const superAdmin = <?= intval($superAdmin) ?>;
    $(function () {
      if (! superAdmin) $("input").attr('disabled', true)

      $(".save").click( async function () {
        let row = $(this).parent().parent()
        let chidon_id = $(row).attr('id')
        let fields = ['sandwhich', 'height', 'weight', 'ski', 'skill', 'outerwear', 'shoe', 'allergies', 'in_zone', 
            'host', 'host_phone', 'street_num', 'suffix', 'street', 'apt', 'cross1', 'cross2', 'trip_option']
        let info = {
          chidon_id: chidon_id
        }
        for (let field of fields) {
          let elem = '.' + field
          let val = $(row).find(elem).val()
          info[field] = val
        }
        console.log(info)

        const res = await fetch('api/updateChidonInfo.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(info)
        })

        const data = await res.json()
        console.log(data)
        if (! data.success) alert('Error saving data')
      })
    })

    // function to download as csv
    function downloadAsCsv() {
      const headers = ['School', 'Grade/Class', 'Student', 'Serial Number', 'Gender', 'Sandwich', 'Height', 'Weight', 'Ski/Snowboard', 'Skill Level', 'Outerwear', 'Shoe Size', 'Allergies', 'In Walking Zone', 'Host', 'Host Phone Number', 'Street Number', 'Street Number Suffix', 'Street Name', 'Apt. #', 'Host Cross Street 1', 'Host Cross Street 2', 'Thursday Walking', 'Motzei Shabbos Walking', 'Zone ID', 'Comments']
      const rows = getRows()
      const universalBOM = "\uFEFF";
      let csvContent = `${ headers.join(',') }\n`;
      // Add each row to the CSV content and encode it for unicode in excel
      rows.forEach( row => { csvContent += `${row.join(',')}\n` } );
      csvContent = encodeURIComponent( universalBOM + csvContent );
      // create and click the download link
      let link = document.createElement('a');
      link.href = `data:text/csv;charset=utf-8,${csvContent}`;
      // link.target = '_blank';
      link.download = 'ultimate_trip_report.csv';
      link.click();
    }

    function getRows() {
      const rows = []
      $('.data-table tbody tr').map(function () {
        const row = []
        const cells = $(this).find('td')
        cells.each(function () {
          const cell = $(this)
          const text = cell.text().trim()
          row.push(text)
        })
        rows.push(row)
      }).get()
      return rows
    }
</script>
</html>