<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$admin_auth = ['school'];
require_once '../header.php';
require_once '../api/header/db.php';
require_once '../class.adminSchools.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$super = $admin_user['auth'] == 'super';

$info = [];
$passed = [];
$not_passed = [];
$sql = "SELECT * FROM khk_info_5785 k 
        JOIN users u ON k.user_serial = u.user_serial
        JOIN classes c ON u.class_id = c.class_id";
if (!$super) {
    $sql .= " WHERE u.school_id IN (" . implode(',', array_keys($schools)) . ")";
}
$sql .= " ORDER BY u.school_id, c.class_grade, c.class_sub, u.last, u.first";
$stmt = $MASHPIA_DB->query($sql);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $info[$row['school_id']][] = $row;
    if (intval($row['amount_passed']) == 4) {
        $passed[$row['school_id']][] = $row;
    } else {
        $not_passed[$row['school_id']][] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>KHK Report</title>
    <style>
        tr, th, td {
            font-family: Arial, sans-serif;
            padding: 10px;
            font-size: 14px;
            border-bottom: 1px solid #ccc;
        }
    </style>
</head>
<body>
    <h1>KHK Report</h1>
    <?php foreach ([$passed, $not_passed] as $info) { ?>
    <table>
        <thead>
            <tr>
                <th>Full Hebrew Name</th>
                <th>Gender</th>
                <th>School</th>
                <th>Current Grade</th>
                <th>Serial</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Amount Passed</th>
                <th>5782</th>
                <th>5783</th>
                <th>5784</th>
                <th>5785</th>
                <th>Notes</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($info as $school_id => $rows) { ?>
                <?php foreach ($rows as $row) { ?>
                    <?php
                    $he_name = $row['first_he'] . ' ' . $row['last_he'];
                    // check if there's dbl quote in string
                    if (strpos($he_name, '"') !== false) {
                        $he_name = str_replace('"', '&quot;', $he_name);
                    }
                    ?>
                    <tr>
                        <td><?php echo $he_name; ?></td>
                        <td><?php echo $row['gender']; ?></td>
                        <td><?php echo $schools[$school_id]; ?></td>
                        <td><?php echo $row['class_grade'] . ($row['class_sub'] ? '-' . $row['class_sub'] : ''); ?></td>
                        <td><?php echo $row['user_serial']; ?></td>
                        <td><?php echo $row['first']; ?></td>
                        <td><?php echo $row['last']; ?></td>
                        <td><?php echo $row['amount_passed']; ?></td>
                        <td><?php echo $row['5782']; ?></td>
                        <td><?php echo $row['5783']; ?></td>
                        <td><?php echo $row['5784']; ?></td>
                        <td><?php echo $row['5785']; ?></td>
                        <td>
                            <textarea
                                id='<?= $row['user_serial'] ?>'
                                name='notes[<?= $row['user_serial'] ?>]'
                                class='notes'
                                rows='2'
                                cols='10'
                            >
                                <?php echo $row['notes']; ?>
                            </textarea>
                        </td>
                        <td>
                            <button onclick="saveNotes(<?= $row['user_serial'] ?>)">Save</button>
                        </td>
                    </tr>
                <?php } ?>
            <?php } ?>
        </tbody>
    </table>
    <br /><br />
    <?php } ?>
</body>
<script 
    src="https://code.jquery.com/jquery-1.12.4.min.js" 
    integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" 
    crossorigin="anonymous">
</script>
<script>
    function saveNotes(id) {
        const value = $('#'+id).val().trim();
        $.ajax({
            url: 'api/updateNotes.php',
            type: 'POST',
            data: {
                user_serial: id,
                notes: value
            },
            success: function(response) {
                console.log(response);
                const res = JSON.parse(response);
                if (res.success) {
                    alert('Saved.');
                } else {
                    alert(res.error);
                }
            }
        });
    }
</script>
</html>