<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonRegYear();

if ($admin_user['auth'] != 'super') {
    die('Access denied');
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$adminSchools = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $adminSchools->getSchools();

$stmt = $MASHPIA_DB->prepare("
    SELECT 
        u.user_id,
        u.user_serial,
        u.first,
        u.last,
        c.class_grade,
        c.class_sub,
        s.school_name,
        tc.confirmed_info
    FROM
        th_chidon tc
            JOIN
        users u USING (user_id)
            JOIN
        schools s ON u.school_id = s.school_id
            JOIN
        classes c ON c.class_id = u.class_id
    WHERE
        tc.year = :year AND u.school_id = :school 
    ORDER BY c.class_grade, c.class_sub, u.last, u.first
");

$info = [];
foreach ($schools as $school_id => $school_name) {
    $stmt->execute([':year' => $year, ':school' => $school_id]);
    $info[$school_name] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$stmtPrizes = $MASHPIA_DB->prepare("
    SELECT 
        cp.*, prize_name
    FROM
        chidon_user_prizes cp 
    JOIN 
        chidon_prizes p ON cp.prize_id = p.prize_id 
    WHERE
        cp.year = :year AND user_id = :user_id
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Confirmation Report</title>
    <style>
      table {
        font-family: Arial, sans-serif;
        font-size: 14px;
      }

      tr, th, td {
        border-bottom: #f0f0f0 1px solid;
        padding: 10px;
      }
    </style>
</head>
<body>
<h1>Confirmation Report</h1>
<?php
foreach ($info as $school_name => $users) {
    echo "<h2>$school_name</h2>";
    echo "<table>";
    echo "<tr><th>Grade/Class</th><th>Serial</th><th>Name</th><th>Confirmed Info</th><th></th><th>Prizes</th></tr>";
    foreach ($users as $user) {
        $grade = $user['class_grade'] . ($user['class_sub'] ? " - " . $user['class_sub'] : "");
        // get prizes
        $stmtPrizes->execute([':year' => $year, ':user_id' => $user['user_id']]);
        $prizes = $stmtPrizes->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <tr>
            <td><?= $grade ?></td>
            <td><?= $user['user_serial'] ?></td>
            <td><?= $user['first'] . " " . $user['last'] ?></td>
            <td><?= $user['confirmed_info'] ? 'Yes' : 'No' ?></td>
            <td id="<?= $user['user_id'] ?>">
                <input type="checkbox" class="confirmed" <?= $user['confirmed_info'] ? 'checked' : '' ?> />
            </td>
            <td>
                <?php foreach ($prizes as $prize) {
                    echo $prize['prize_name'] . '<br>';
                } ?>
            </td>
        </tr>
    <?php } ?>
    </table>
<?php } ?>
</body>
<script>
  // Only super admins can update confirmation status
  <?php if ($superAdmin) : ?>
  // Add event listeners to checkboxes on document load
  document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll('.confirmed')
    checkboxes.forEach(checkbox => {
      checkbox.addEventListener('change', async (e) => {
        const user_id = e.target.parentElement.id
        const confirmed = e.target.checked ? 1 : 0
        const response = await fetch('updateConfirmation.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({user_id, confirmed})
        });
        const data = await response.json()
        if (data.success) {
          alert('Updated.')
        } else {
          alert('Error updating.')
        }
      })
    })
  })
  <?php endif; ?>
</script>
</html>
