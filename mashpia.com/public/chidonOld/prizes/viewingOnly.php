<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

<HEAD>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Chidon Prizes</title>
    <style type='text/css'>
      table {
        font-size: 14px;
        font-family: "Arial", sans-serif;
      }
      th, td {
        padding: 3px 10px;
      }
      .page-break {
        page-break-after: always;
      }
    </style>
</HEAD>

<BODY>
<h1>Chidon Prizes</h1>

<table>
    <tr>
        <th>Prize ID</th>
        <th>Picture</th>
        <th>name</th>
        <th>Color</th>
        <th>Size</th>
    </tr>

    <?php
    require_once '../../db.php';
    $sql = 'SELECT * FROM chidon_prizes WHERE year = 5783';
    $query = mysql_query($sql);
    while($row = mysql_fetch_assoc($query)) { ?>
        <tr>
            <td><?= $row['prize_id'] ?></td>
            <td>
                <? if ($row['prize_picture']) { ?>
                    <img src="../..<?= $row['prize_picture'] ?>" width="50" />
                <? } ?>
            </td>
            <td><?= $row['prize_name'] ?></td>
            <td><?= $row['color'] ?></td>
            <td><?= $row['size'] ?></td>
        </tr>
    <? }
    ?>
</table>
</body>
</html>