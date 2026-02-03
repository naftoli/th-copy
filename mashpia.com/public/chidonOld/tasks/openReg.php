<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once __DIR__ . '/../../header.php';
require_once __DIR__ . '/../../api/header/db.php';
require_once __DIR__ . '/../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

// get all schools
require_once __DIR__ . '/../../class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Open Registration</title>
</head>
<body>
    <h1>Open Registration</h1>
    <table>
        <tr>
            <th>School</th>
            <th>Open Registration</th>
        </tr>
        <?php foreach ($schools as $school_id => $school_name) : ?>
        <tr>
            <td><?php echo $school_name; ?></td>
            <td><input type="checkbox" name="open_registration" 
                data-school_id="<?php echo $school_id; ?>" 
                <?php echo isset($info[$school_id]) && $info[$school_id]['open_reg_5786'] ? 'checked' : ''; ?> /></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('input[name="open_registration"]').change(function() {
            const school_id = $(this).data('school_id');
            const open_registration = $(this).is(':checked') ? 1 : 0;
            fetch('open_reg_5786.php', {
                method: 'POST',
                body: JSON.stringify({ school_id: school_id, open_registration: open_registration })
            })
            .then(response => response.json())
            .then(data => {
                console.log(data);
                if (!data.success) {
                    alert('Error updating.');
                }
            });
        });
    });
</script>
</html>