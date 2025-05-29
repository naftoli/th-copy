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
$sql = "SELECT * FROM name_plates p 
        JOIN users u ON p.user_id = u.user_id
        JOIN classes c ON u.class_id = c.class_id";
if (!$super) {
    $sql .= " WHERE p.school_id IN (" . implode(',', array_keys($schools)) . ")";
}
$sql .= " ORDER BY p.school_id, c.class_grade, c.class_sub, u.last, u.first";
$stmt = $MASHPIA_DB->query($sql);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $info[$row['school_id']][] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Name Plates Report</title>
    <link href="../admin_styles.css" rel="stylesheet" type="text/css">
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
    <h1>Name Plates Report</h1>
    <table>
        <thead>
            <tr>
                <th>School</th>
                <th>Class</th>
                <th>Serial</th>
                <th>Child</th>
                <th>Qty</th>
                <th>Shipped</th>
                <th>Enter Hebrew Name</th>
                <th>Reason</th>
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
                        <td><?php echo $schools[$school_id]; ?></td>
                        <td><?php echo $row['class_grade'] . ($row['class_sub'] ? '-' . $row['class_sub'] : ''); ?></td>
                        <td><?php echo $row['user_serial']; ?></td>
                        <td><?php echo $row['first'] . ' ' . $row['last']; ?></td>
                        <td><?php echo $row['qty']; ?></td>
                        <td><?php echo intval($row['shipped']) ? 'Yes' : 'No'; ?></td>
                        <td>
                            <input 
                                type='text' 
                                id='<?= $row['user_id'] ?>'
                                name='he_name[<?= $row['user_id'] ?>]' 
                                class='he_name' 
                                data-old="<?= $he_name ?>" 
                                value="<?= $he_name ?>" 
                                <?php if (!intval($row['missing_he_name'])) echo 'disabled'; ?>
                            />
                        </td>
                        <td class="reason"><?php echo $row['reason']; ?></td>
                        <td>
                            <?php if (intval($row['missing_he_name'])) { ?>
                                <button onclick="saveHeName(<?= $row['user_id'] ?>)">Save</button>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            <?php } ?>
        </tbody>
    </table>
</body>
<script 
    src="https://code.jquery.com/jquery-1.12.4.min.js" 
    integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" 
    crossorigin="anonymous">
</script>
<script>
    // function to check hebrew characters
    function isHebrew(str) {
        // allow spaces and hyphens and single quote and double quote
        const regex = /[^\u0590-\u05FF \'\"\-]/;
        return !regex.test(str);
    }

    function validate(elem, val) {
        if (!isHebrew(val)) {
            alert('Hebrew name must contain only hebrew characters. Please try again.' + 
                '(spaces, hyphen, single quote, double quote allowed)');
            elem.focus();
            elem.select();
            return false;
        }
        return true;
    }

    function saveHeName(id) {
        const value = $('#'+id).val().trim();
        const old = $('#'+id).data('old').trim();
        if (!validate($('#'+id), value)) return;
        if (value != old || (value == '' && old != '')) {
            $.ajax({
                url: 'api/update_he_name.php',
                type: 'POST',
                data: {
                    user_id: id,
                    he_name: value
                },
                success: function(response) {
                    console.log(response);
                    if (response.error) {
                        alert(response.error);
                    }
                }
            });
        }
    }
</script>
</html>