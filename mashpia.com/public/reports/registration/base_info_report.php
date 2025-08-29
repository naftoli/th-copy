<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    die('Access Denied');
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

// get headers base on school schema
$headers = [];
$sql = "  SHOW COLUMNS FROM schools";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    if (strpos($row['Field'], 'notes') !== false || strpos($row['Field'], 'shipping_requests') !== false) continue;
    $headers[] = $row['Field'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Base Info Report</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <style>
    .table-container {
        overflow-x: auto;
        overflow-y: visible;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #ffffff;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
        font-size: 14px;
        color: #111827;
    }

    .table th, .table td {
        padding: 10px 12px;
        border-bottom: 1px solid #e5e7eb;
        text-align: left;
        white-space: nowrap;
    }

    .table thead th {
        position: sticky;
        top: 0;
        background: #f8fafc;
        border-bottom: 2px solid #e5e7eb;
        z-index: 1;
    }

    .table tbody tr:nth-child(even) { background: #f9fafb; }
    .table tbody tr:hover { background: #eef2ff; }

    .table .num { text-align: right; font-variant-numeric: tabular-nums; }

    .table--compact th, .table--compact td { padding: 6px 10px; }
    </style>
</head>
<body>
    <h1>Base Info Report</h1>
    <div>
        <table id="base-info-report" class="table table-striped table-hover">
        <thead>
            <tr>
                <?php foreach ($headers as $header) { ?>
                    <th><?= $header ?></th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($schools as $school_id => $school) { ?>
                <?php $sql = "SELECT * FROM schools WHERE school_id = " . $school_id;
                $result = mysql_query($sql);
                $row = mysql_fetch_assoc($result);
                ?>
                <tr>
                    <?php foreach ($headers as $header) { ?>
                        <td><?= $row[$header] ?></td>
                    <?php } ?>
                </tr>
            <?php } ?>
        </tbody>
        </table>
    </div>
</body>
<script>
    $(function() {
        var dt = $('#base-info-report').DataTable({
            order: [[0, 'asc']],
            autoWidth: false,
            deferRender: true,
            info: true, 
            pageLength: 100
        });
        dt.columns.adjust();
    });
</script>
</html>