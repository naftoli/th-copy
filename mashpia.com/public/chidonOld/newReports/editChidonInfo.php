<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$defaultYear = GlobalSettings::getChidonYear();
$year = isset($_GET['year']) ? (int) $_GET['year'] : (int) $defaultYear;
$minYear = 5783;
$maxYear = max((int) $defaultYear + 1, $minYear);
$years = range($minYear, $maxYear);
rsort($years);

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
    $info[] = [
        'school_id' => $school_id,
        'school_name' => $school_name,
        'users' => $stmt->fetchAll(PDO::FETCH_ASSOC),
    ];
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
        border-bottom: 1px solid #f0f0f0;
        padding: 10px;
      }
    </style>
</head>
<body>
<h1>Confirmation Report</h1>
<form method="GET" style="margin-bottom: 20px;">
    <label for="year">Choose Year:</label>
    <select name="year" id="year">
        <?php foreach ($years as $yearOption) { ?>
            <option value="<?= $yearOption ?>" <?= $yearOption === $year ? 'selected' : '' ?>><?= $yearOption ?></option>
        <?php } ?>
    </select>
    <button type="submit">Go</button>
</form>
<?php
foreach ($info as $schoolInfo) {
    $school_id = (int) $schoolInfo['school_id'];
    $school_name = $schoolInfo['school_name'];
    $users = $schoolInfo['users'];
    echo "<h2>$school_name</h2>";
    ?>
    <form class="add-prize-form" style="margin: 10px 0 20px;">
        <label>
            Child Serial:
            <input type="text" name="user_serial" required />
        </label>
        <label style="margin-left: 10px;">
            Prize ID:
            <input type="number" name="prize_id" min="1" required />
        </label>
        <button type="submit" style="margin-left: 10px;">Add</button>
        <span class="add-prize-message" style="margin-left: 10px;"></span>
    </form>
    <?php
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
            <td id="<?= $user['user_id'] ?>" style="padding-left: 30px;">
                <?php foreach ($prizes as $prize) {
                    echo '<input type="checkbox" class="prize" id="' . $prize['prize_id'] . '" checked /> ' . $prize['prize_name'];
                    if (!empty($prize['he_name'])) {
                      echo ' <input type="text" class="he_name" value="' . $prize['he_name'] . '" />';
                    }
                    echo "<br />";
                } ?>
            </td>
        </tr>
    <?php } ?>
    </table>
<?php } ?>
</body>
<script>
  const year = <?= json_encode($year) ?>

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
          body: JSON.stringify({user_id, confirmed, year})
        });
        const data = await response.json()
        if (data.success) {
          alert('Updated.')
        } else {
          alert('Error updating.')
        }
      })
    })

    const prizeCheckboxes = document.querySelectorAll('.prize')
    prizeCheckboxes.forEach(checkbox => {
      checkbox.addEventListener('change', async (e) => {
        const prize_id = e.target.id
        const user_id = e.target.parentElement.id
        const unchecked = e.target.checked ? 0 : 1
        if (unchecked) {
          const confirmed = confirm('Are you sure you want to remove this prize?')
          if (confirmed) {
            const response = await fetch('api/removePrize.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({user_id, prize_id, year})
            });
            const data = await response.json()
            if (data.success) {
              alert('Removed.')
              // find prize in DOM and remove it
              e.target.parentElement.removeChild(e.target)
            } else {
              alert(data.error)
            }
          } else {
            e.target.checked = true
          }
        }
      })
    })

    const heNameInputs = document.querySelectorAll('.he_name')
    heNameInputs.forEach(input => {
      input.addEventListener('change', async (e) => {
        const prize_id = e.target.previousElementSibling.id
        const user_id = e.target.parentElement.id
        const he_name = e.target.value
        const response = await fetch('api/updateHeName.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({user_id, prize_id, he_name, year})
        });
        const data = await response.json()
        if (data.success) {
          alert('Updated.')
        } else {
          alert('Error updating.')
        }
      })
    })

    const addPrizeForms = document.querySelectorAll('.add-prize-form')
    addPrizeForms.forEach(form => {
      form.addEventListener('submit', async (e) => {
        e.preventDefault()
        const user_serial = form.querySelector('input[name="user_serial"]').value.trim()
        const prize_id = parseInt(form.querySelector('input[name="prize_id"]').value, 10)
        const school_id = parseInt(form.querySelector('input[name="school_id"]').value, 10)
        const messageEl = form.querySelector('.add-prize-message')

        if (!user_serial || Number.isNaN(prize_id) || Number.isNaN(school_id)) {
          messageEl.textContent = 'Please enter valid serial and prize id.'
          messageEl.style.color = 'red'
          return
        }

        const response = await fetch('api/addPrize.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({user_serial, prize_id, school_id, year})
        })
        const data = await response.json()
        if (data.success) {
          messageEl.textContent = 'Prize added.'
          messageEl.style.color = 'green'
          form.reset()
          setTimeout(() => location.reload(), 400)
        } else {
          messageEl.textContent = data.error || 'Error adding prize.'
          messageEl.style.color = 'red'
        }
      })
    })
  })
</script>
</html>
