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
        tr, th, td {
          font-size: 14px;
          padding: 10px;
          border-bottom: 1px solid grey;
          font-family: Arial, Helvetica, sans-serif;
        }
    </style>
</head>
<body>
    <h1>Ultimate Trip Info</h1>
    <table>
        <tr>
            <th>School</th>
            <th>Grade/Class</th>
            <th>Student</th>
            <th>Serial Number</th>
            <th>Shoe Size</th>
            <th>Sandwich</th>
            <th>Allergies</th>
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
        <?php
        foreach ($info as $row) {
            $chidon_id = $row['th_chidon_id'];
            $school = $row['school_name'];
            $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
            $student = $row['first'] . ' ' . $row['last'];
            $serial = $row['user_serial'];
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

            echo "<tr class='' id='" . $chidon_id . "'><td>" . $school . "</td><td>" . $grade . "</td><td>" . $student . "</td><td>" . $serial . "</td><td>";
            echo $shoe . "</td><td>" . $sandwich . "</td><td>" . $allergies . "</td><td>" . ($in_zone ? 'yes' : 'no') . "</td> 
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
    </table>
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
        let fields = ['host', 'host_phone', 'street_num', 'suffix', 'street', 'apt', 'cross1', 'cross2']
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
    })
</script>
</html>