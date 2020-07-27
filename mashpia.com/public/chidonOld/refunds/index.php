<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
if ($admin_user['auth'] != 'super') {
    echo "No permission to be here.";
    exit;
}

require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$admins = [];
$stmt = $MASHPIA_DB->prepare("
    SELECT 
        a.admin_id, a.first, a.last, a.admin_email, a.show_chidon_refund
    FROM
        admins a
            JOIN
        th_chidon tc ON tc.parent_id = a.admin_id
    WHERE
        tc.year = :year AND tc.date_paid > 0
    GROUP BY a.admin_id 
    ORDER BY a.last
");
$res = $stmt->execute([':year' => $year]);
if ($res) {
    foreach ($stmt->fetchAll() as $row) {
        $admins[$row['admin_id']] = [
            'refund'    =>  intval($row['show_chidon_refund']), 
            'first'     =>  $row['first'], 
            'last'      =>  $row['last'], 
            'email'     =>  $row['admin_email']
        ];
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            body {
                font-family: Arial, Helvetica, sans-serif;
                font-size: 14px;
            }
        </style>
    </head>
    <body>
        <h1>Chidon Refunds 5780</h1>
        <table>
            <tr>
                <th>Admin ID</th>
                <th>First</th>
                <th>Last</th>
                <th>Email</th>
                <th>Show refund page</th>
            </tr>
            <?php
            foreach ($admins as $admin_id => $more) {
                echo "<tr><td>" . $admin_id . "</td><td>" . $more['first'] . "</td><td>" . $more['last'] . "</td><td>" . $more['email'] . 
                    "</td><td><input type='checkbox' class='refund' id='" . $admin_id . "' ";
                    if ($more['refund']) echo "checked ";
                    echo "/></td></tr>";
            }
            ?>
        </table>
    </body>
    <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
    <script>
        $(function() {
            $(".refund").click( function() {
                const checked = $(this).is(":checked") ? 1 : 0
                const id = $(this).attr('id')
                $.post('updateRefund.php', { admin_id: id, checked: checked }, function(res) {
                    const result = JSON.parse(res)
                    if (result.success) alert('Updated.')
                    else alert('Error updating.')
                })
            })
        })
    </script>
</html>