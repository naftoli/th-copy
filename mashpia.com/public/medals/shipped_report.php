<?php
$admin_auth = ['school'];
require_once '../header.php';
require_once '../api/header/db.php';

// make sure it's hq
if ($admin_user['auth'] != 'super') {
    echo 'You are not authorized to view this page.';
    exit;
}

if (isset($_POST['date']) && $_POST['date'] > 0) { // if we want to filter by date
    $date = $_POST['date'];
    $stmt = $MASHPIA_DB->prepare("
        SELECT 
            *
        FROM
            medal_marks
                JOIN
            users USING (user_id)
        WHERE
            date_shipped >= :date_start and date_shipped <= :date_end 
        ORDER BY last , first
    ");
    $stmt->execute([
        'date_start' => $date . ' 00:00:00',
        'date_end' => $date . ' 23:59:59'
    ]);
} else {
    // get all medals shipped since earned on May 17, 2024
    $jd = 2460447; // May 17, 2024
    $sql = "
        SELECT 
            *
        FROM
            medal_marks
                JOIN
            users USING (user_id)
        WHERE
            date_awarded >= :date 
        ORDER BY last , first";
    $stmt = $MASHPIA_DB->prepare($sql);
    $stmt->execute(['date' => $jd]);
}
$medals = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<DOCTYPE html>
<html>
    <head>
        <title>Shipped Report</title>
        <style>
          body {
            font-family: "Arial", sans-serif;
            font-size: 16px;
          }
          form {
            line-height: 1.6em;
          }
          form input[type="submit"] {
            padding: 10px;
            font-size: large;
          }
          tr, th, td {
            font-family: "Arial", sans-serif;
            padding: 10px;
            font-size: 14px;
            border-bottom: 1px #f0f0f0 solid;
          }
        </style>
    </head>
    <body>
        <!-- Display the data in a structured format -->
        <h1>Shipped Report</h1>

        <!-- add ability to filter by date -->
        <form method="post">
            Filter by Date Shipped (YYYY-MM-DD):<br />
            <label for="date">Date:</label>
            <input type="date" name="date" id="date"
                <?php if (isset($_POST['date'])) echo 'value="' . $_POST['date'] . '"'; ?>
            ><br />
            <input type="submit" value="Filter">
        </form>

        <table>
            <tr>
                <th>User ID</th>
                <th>Serial Number</th>
                <th>Last Name</th>
                <th>First Name</th>
                <th>Subject ID</th>
                <th>Medal Ord</th>
                <th>Date Earned</th>
                <th>Date Shipped</th>
            </tr>
            <?php foreach ($medals as $medal) : ?>
                <tr>
                    <td><?= $medal['user_id'] ?></td>
                    <td><?= $medal['user_serial'] ?></td>
                    <td><?= $medal['last'] ?></td>
                    <td><?= $medal['first'] ?></td>
                    <td><?= $medal['subject_id'] ?></td>
                    <td><?= $medal['medal_ord'] ?></td>
                    <td><?= jdtogregorian($medal['date_awarded']) ?></td>
                    <td><?= $medal['date_shipped'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </body>
</html>