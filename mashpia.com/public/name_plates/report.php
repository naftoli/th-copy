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
$sql = "SELECT * FROM name_plates p JOIN users u ON p.user_id = u.user_id";
if (!$super) {
    $sql .= " WHERE p.school_id IN (" . implode(',', array_keys($schools)) . ")";
}
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
    </style>
</head>
<body>
    <?php include '../admin_header.php'; ?>
    <h1>Name Plates Report</h1>
    <div class="infobox">
        You can edit the hebrew name of the child by clicking on the input field and typing the new name. 
        The changes will be saved automatically ONCE YOU CLICK OUTSIDE OF THE INPUT FIELD.
    </div>
    <table>
        <thead>
            <tr>
                <th>School</th>
                <th>Serial</th>
                <th>Child</th>
                <th>Qty</th>
                <th>Shipped</th>
                <th>Missing He Name</th>
                <th>Enter Hebrew Name</th>
                <th>Reason</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($info as $school_id => $rows) { ?>
                <?php foreach ($rows as $row) { ?>
                    <tr>
                        <td><?php echo $schools[$school_id]; ?></td>
                        <td><?php echo $row['user_serial']; ?></td>
                        <td><?php echo $row['first'] . ' ' . $row['last']; ?></td>
                        <td><?php echo $row['qty']; ?></td>
                        <td><?php echo empty($row['reason']) ? 'Yes' : 'No'; ?></td>
                        <td><?php echo intval($row['missing_he_name']) ? 'Yes' : 'No'; ?></td>
                        <td>
                            <input 
                                type='text' 
                                id='<?= $row['user_id'] ?>'
                                name='he_name[<?= $row['user_id'] ?>]' 
                                class='he_name' 
                                data-old='<?= $row['first_he'] . ' ' . $row['last_he'] ?>' 
                                value='<?= $row['first_he'] . ' ' . $row['last_he'] ?>' 
                                <?php if (empty($row['reason'])) echo 'disabled'; ?>
                            />
                        </td>
                        <td><?php echo $row['reason']; ?></td>
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
    
    $(document).ready(function() {
        let processingField = false;
        $('.he_name').on('blur', function(e) {
            // Prevent duplicate processing
            if (processingField) return;
            
            const id = $(this).attr('id');
            const value = $(this).val().trim();
            const old = $(this).data('old').trim();
            
            // only update if there's a value and if it's different from the old value 
            // or if it's empty and the old value is not empty
            let valid = validate(this, value);
            if (!valid) {
                processingField = true;
                // Use setTimeout to allow the alert to be dismissed before re-enabling processing
                setTimeout(function() {
                    processingField = false;
                }, 100);
                return;
            }
            
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
        });
    });
</script>
</html>