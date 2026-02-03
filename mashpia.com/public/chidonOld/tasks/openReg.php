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

$stmt = $MASHPIA_DB->prepare("SELECT school_id, open_reg_5786 FROM schools WHERE school_id IN (" . implode(',', array_keys($schools)) . ")");
$stmt->execute();
$open_reg = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($open_reg as $reg) {
    $info[$reg['school_id']] = $reg;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Open Registration</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 1.5rem 2rem;
            background: #f5f5f5;
            color: #333;
        }
        h1 {
            margin: 0 0 1rem;
            font-size: 1.5rem;
        }
        .card {
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            overflow: hidden;
            max-width: 600px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 0.6rem 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f8f8;
            font-weight: 600;
            font-size: 0.9rem;
        }
        tr:hover td {
            background: #fafafa;
        }
        input[type="checkbox"] {
            width: 1.1rem;
            height: 1.1rem;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Open Registration</h1>
    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>School</th>
                    <th>Open Registration</th>
                </tr>
            </thead>
            <tbody>
        <?php foreach ($schools as $school_id => $school_name) : ?>
            <?php if ($school_name == ''): continue; endif; ?>
                <tr>
                    <td><?php echo htmlspecialchars($school_name); ?></td>
                    <td><input type="checkbox" name="open_registration"
                        data-school_id="<?php echo (int) $school_id; ?>"
                        <?php echo isset($info[$school_id]) && $info[$school_id]['open_reg_5786'] ? 'checked' : ''; ?> /></td>
                </tr>
        <?php endforeach; ?>
            </tbody>
        </table>
    </div>
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