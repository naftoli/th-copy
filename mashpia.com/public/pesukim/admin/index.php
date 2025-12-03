<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

if ($admin_user['auth'] != 'super') {
    die('Access denied');
}

$sql = "select * from pesukim_settings";
$result = $MASHPIA_DB->query($sql);
$settings = $result->fetchAll();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <!-- Bootstrap v5.3.8 -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">        
        <title>Pesukim Admin</title>
        <style>
            body {
                padding: 20px;
            }
        </style>
    </head>
    <body>
        <h1>Pesukim Settings</h1>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Label</th>
                    <th>Calculation</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($settings as $setting) : ?>
                    <tr data-setting-id="<?= $setting['pesukim_setting_id'] ?>">
                        <td class="type"><input type="text" class="form-control" name="type" value="<?php echo $setting['type']; ?>"></td>
                        <td class="label"><input type="text" class="form-control" name="label" value="<?php echo $setting['label']; ?>"></td>
                        <td class="multiplier"><input type="number" class="form-control" name="multiplier" value="<?php echo $setting['multiplier']; ?>" min="0" max="1" step="0.25"></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button class="btn btn-primary" id="save">Save</button>
        <script>
            document.getElementById('save').addEventListener('click', function() {
                let settings = [];
                document.querySelectorAll('tbody tr').forEach(function(tr) {
                    let id = tr.dataset.settingId;
                    let setting = {
                        id: id,
                        type: tr.querySelector('td.type > input').value,
                        label: tr.querySelector('td.label > input').value,
                        multiplier: tr.querySelector('td.multiplier > input').value
                    };
                    settings.push(setting);
                });
                fetch('api/saveSettings.php', {
                    method: 'POST',
                    body: JSON.stringify(settings)
                }).then(response => response.json()).then(data => {
                    if (data.success) {
                        alert('Settings saved successfully');
                    } else {
                        alert('Error saving settings');
                        console.log(data.error);
                    }
                });
            });
        </script>
    </body>
</html>