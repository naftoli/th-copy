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
                AND u.school_id in (" . implode(',', array_keys($schools)) . ") ";
if (isset($_GET['gender'])) $sql .= " AND u.gender = :gender";
$sql .= " ORDER BY u.school_id , class_grade , class_sub , last , first";
$stmt = $MASHPIA_DB->prepare($sql);
if (isset($_GET['gender'])) $stmt->bindValue(':gender', $_GET['gender']);
$stmt->bindValue(':year', $year);
$stmt->execute();
$info = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta charset="UTF-8">
    <title>Ultimate Trip Info</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
    :root {
        --bg: #f8fafc;
        --card: #ffffff;
        --border: #e2e8f0;
        --text: #1e293b;
        --text-muted: #64748b;
        --accent: #2563eb;
        --accent-hover: #1d4ed8;
        --success: #22c55e;
    }

    * { box-sizing: border-box; }

    body {
        margin: 0;
        padding: 24px;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        background: var(--bg);
        color: var(--text);
        line-height: 1.5;
    }

    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
    }

    h1 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text);
    }

    .actions {
        display: flex;
        gap: 12px;
    }

    .btn {
        padding: 10px 18px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        font-family: inherit;
        cursor: pointer;
        transition: background 0.15s;
    }

    .btn-primary {
        background: var(--accent);
        color: white;
    }
    .btn-primary:hover { background: var(--accent-hover); }

    .btn-secondary {
        background: var(--card);
        color: var(--text);
        border: 1px solid var(--border);
    }
    .btn-secondary:hover { background: #f1f5f9; }

    .table-card {
        background: var(--card);
        border-radius: 12px;
        border: 1px solid var(--border);
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .table-container {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .data-table thead {
        background: #f1f5f9;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .data-table th {
        padding: 12px 14px;
        text-align: left;
        font-weight: 500;
        color: var(--text-muted);
        white-space: nowrap;
        border-bottom: 1px solid var(--border);
    }

    .data-table td {
        padding: 10px 14px;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }

    .data-table tbody tr:hover {
        background: #f8fafc;
    }

    .data-table input,
    .data-table select {
        padding: 6px 10px;
        border: 1px solid var(--border);
        border-radius: 6px;
        font-size: 13px;
        font-family: inherit;
        min-width: 60px;
    }

    .data-table input:focus,
    .data-table select:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 2px rgba(37,99,235,0.15);
    }

    .data-table .save {
        padding: 6px 12px;
        font-size: 12px;
        background: var(--success);
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
    }
    .data-table .save:hover { filter: brightness(1.05); }

    .count-badge {
        display: block;
        font-size: 13px;
        color: var(--text-muted);
        font-weight: 400;
        margin-top: 4px;
    }
    </style>
</head>
<body>
    <div class="page-header">
        <div>
            <h1>Ultimate Trip Info</h1>
            <span class="count-badge"><?= count($info) ?> participants</span>
        </div>
        <div class="actions">
            <button class="btn btn-secondary" onclick="downloadAsCsv()">Download CSV</button>
        </div>
    </div>
    <div class="table-card">
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
      const headers = ['School', 'Grade/Class', 'Student', 'Serial Number', 'Gender', 'Sandwich', 'Height', 'Weight', 'Ski/Snowboard', 'Skill Level', 'Outerwear', 'Shoe Size', 'Allergies', 
      'Trip Option', 'In Walking Zone', 'Host', 'Host Phone Number', 'Street Number', 'Street Number Suffix', 'Street Name', 'Apt. #', 'Host Cross Street 1', 'Host Cross Street 2', 
      'Thursday Walking', 'Motzei Shabbos Walking', 'Zone ID', 'Comments']
      const rows = getRows()
      const universalBOM = "\uFEFF";
      let csvContent = `${ headers.join(',') }\n`;
      // Add each row to the CSV content and encode it for unicode in excel
      rows.forEach( row => { csvContent += row.join(',') + '\n' } );
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
      $('.data-table tbody tr').each(function () {
        const row = []
        $(this).find('td').each(function () {
          const cell = $(this)
          const input = cell.find('input, select')
          const text = input.length ? input.val() : cell.text().trim()
          row.push('"' + String(text).replace(/"/g, '""') + '"')
        })
        rows.push(row)
      })
      return rows
    }
</script>
</html>