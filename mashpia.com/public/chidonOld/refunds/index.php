<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No permission to be here.";
    exit;
}

require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
$year = 5780;

$admins = [];
$stmt = $MASHPIA_DB->prepare("
    SELECT 
        a.*
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
        $admins[$row['admin_id']] = $row;
    }
}

$raised = [];
$stmt = $MASHPIA_DB->prepare("
    SELECT SUM(donation_amount) as total FROM chidon_donations WHERE for_family_id = :admin AND chidon_year = :year
");
foreach ($admins as $id => $info) {
    $res = $stmt->execute([
        ':year'     => $year,
        ':admin'    => $id
    ]);
    if ($res) {
        $row = $stmt->fetch();
        $total = floatval($row['total']);
        if ($total) $raised[$id] = $total;
    }
}

$users = [];
$stmt = $MASHPIA_DB->prepare("
    SELECT * FROM th_chidon tc 
    JOIN users u USING (user_id) 
    WHERE tc.parent_id = :parent 
    AND tc.year = :year
    AND tc.date_paid is not null
");
foreach ($admins as $id => $info) {
    $res = $stmt->execute([
        ':parent'   => $id,
        ':year'     => $year
    ]);
    if ($res) {
        foreach ($stmt->fetchAll() as $row) {
            $users[$id][] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            body, tr, th, td {
                font-family: Arial, Helvetica, sans-serif;
                font-size: 14px;
            }
            tr, th, td {
                border: 1px black solid;
                padding: 5px;
            }
        </style>
    </head>
    <body>
        <h1>Chidon Refunds 5780</h1>
        <table>
            <tr>
                <th>Customer ID</th>
                <th>First</th>
                <th>Last</th>
                <th>Email</th>
                <th>Address</th>
                <th>Children Info</th>
                <th>Total Raised</th>
                <th>Activate refund page</th>
                <th>Create Voucher</th>
            </tr>
            <?php
            foreach ($admins as $admin_id => $row) {
                $address = $row['admin_address1'];
                if (!empty($row['admin_address2'])) $address .= " " . $row['admin_address2'];
                $address .= "<br />" . $row['admin_city'] . ', ' . $row['admin_state'] . ' ' . $row['admin_postal'] . "<br />" . $row['admin_country'];
                echo "<tr><td>" . $admin_id . "</td><td>" . $row['first'] . "</td><td>" . $row['last'] . "</td><td>" . $row['admin_email'] .
                    "</td><td>" . $address . "</td><td>";
                // children info
                $num_children = count($users[$admin_id]);
                foreach ($users[$admin_id] as $idx => $child) {
                    $name = $child['first'];
                    $paid = $child['paid'];
                    $date_paid = $child['date_paid'];
                    echo $name . "<br />Paid: " . $paid . "<br />Date Paid: " . $date_paid;
                    if ($num_children > ($idx + 1)) {
                        echo "<br /><hr width='50%' />";
                    }
                }
                // total raised
                echo "</td><td>";
                if (isset($raised[$admin_id])) echo "$" . number_format($raised[$admin_id], 2);
                // activate refund checkbox
                echo "</td><td><input type='checkbox' class='refund' id='" . $admin_id . "' ";
                if (intval($row['show_chidon_refund'])) echo "checked ";
                echo "/></td><td>";
                // vouchers
                echo "$<input type='text' class='voucher' placeholder='0.00' size='4' /><button class='create_voucher'>create voucher</button>";
                echo "</td></tr>";
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
            // vouchers
            $(".create_voucher").click( function() {
                const admin = $(this).parent().parent().find('.refund').attr('id')
                const amount = parseFloat($(this).parent().find('.voucher').val())
                if (amount) {
                    const that = this;
                    $.post('createVoucher.php', { admin: admin, amount: amount }, function(result) {
                        const res = JSON.parse(result)
                        if (res.success) {
                            alert('Voucher created.')
                            $(that).after(res.info)
                        } else {
                            alert(res.error)
                        }
                    })
                }
            })
        })
    </script>
</html>