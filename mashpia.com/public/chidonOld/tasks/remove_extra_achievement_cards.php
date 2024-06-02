<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$sql = "SELECT 
            first,
            last,
            class_grade,
            class_sub,
            user_point_id,
            achievement_card_id,
            user_id,
            institution_id,
            points
        FROM
            pointsDB.user_points p
                JOIN
            mashpiadb.users u USING (user_id)
                JOIN
            mashpiadb.classes c ON c.class_id = u.class_id
        WHERE
            institution_id in (" . implode(',', array_keys($schools)) . ")
                AND resource_name = 'specific achievement card'
                AND created >= '2023-09-01'
        ORDER BY class_grade, class_sub, last, first";
$res = $mysqli->query($sql);
$rows = $res->fetch_all(MYSQLI_ASSOC);

$users = [];
$cards = [];
foreach ($rows as $row) {
    $cards[$row['user_id']][$row['achievement_card_id']][] = $row;
    $users[$row['user_id']] = [
        'name'  => $row['first'] . ' ' . $row['last'],
        'class' => $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub'])
    ];
}

$points = [];
$duplicates = [];
foreach ($cards as $user => $more) {
    foreach ($more as $card_id => $details) {
        foreach ($details as $idx => $row) {
            if ($idx > 0) {
                $duplicates[] = $row['user_point_id'];
                $points[$row['user_id']][$card_id][] = $row['points'];
            }
        }
    }
}

$totals = [];
foreach ($points as $user => $more) {
    $totals[$user] = 0;
    foreach ($more as $card_id => $details) {
        foreach ($details as $point) {
            $totals[$user] += $point;
        }
    }
}

//echo "<pre>"; print_r($points); echo "</pre>";
//echo "<pre>"; print_r($totals); echo "</pre>";
if (isset($_POST['delete'])) {
    $deleted = 0;
    $success = true;
    $mysqli->begin_transaction();
    foreach ($duplicates as $point_id) {
        if (!$mysqli->query("delete from pointsDB.user_points where user_point_id = " . $point_id)) {
            $success = false;
            break;
        } else $deleted++;
    }
    if ($success) {
        $mysqli->commit();
        echo "Deleted " . $deleted . " rows.";
    } else {
        $mysqli->rollback();
        echo "Error deleting rows.";
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <title>Extra Points</title>
        <style>
          tr, th, td {
            font-family: Arial, Helvetica, sans-serif;
            padding: 6px;
            font-size: 12px;
            border-bottom: 1px solid grey;
          }
          button {
            padding: 5px;
            font-size: 14px;
          }
        </style>
    </head>
    <body>
      <p>
        Below you can see a list of children and how many extra points they have from achievement cards being scanned multiple times.<br />
        You can choose to delete all the extra points if you choose to.<br />
        The "extra" points are being calculated from "Sept 1, 2023".
      </p>
        <form action="" method="post">
          <button id="delete" onclick="return false;">Delete all extra points</button>
          <input type="hidden" name="delete" />
        </form>
        <br />
        <table>
            <tr>
                <th>Class</th>
                <th>Student</th>
                <th>Extra Points</th>
            </tr>
            <?php
            foreach ($totals as $user => $total) {
                echo "<tr><td>" . $users[$user]['class'] . "</td><td>" . $users[$user]['name'] . "</td><td>" . $total . "</td></tr>";
            }
            ?>
        </table>
    </body>
    <script>
      const deleteBtn = document.querySelector('#delete')
      deleteBtn.addEventListener('click', function() {
        let res = confirm('Are you sure you want to delete all the extra points? This cannot be undone!')
        if (res) document.forms[0].submit()
        return false
      })
    </script>
</html>
