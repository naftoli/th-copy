<?php require 'whatsapp.php'; ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            tr, th, td {
                font-size: 12px;
                padding: 3px;
                text-align: center;
            }
            tr:first-child {
                border-bottom: 1px dashed grey;
            }
            tr:nth-child(even) {
                background:rgba(255,255,60,0.5);
            }
            tr:nth-child(odd) {
                background:rgba(255,255,120,0.5);
            }
            .pageBreak {
                page-break-after: always;
            }
        </style>
    </head>
    <body>
        <?php if (isset($_POST['submit'])) : ?>
            <?php foreach ($completed as $grade => $info) : ?>
                <h3>WWTC Grade <?=$grade?></h3>
                <h3>Wall Of Honor</h3>
                <?php
                // get month
                foreach ($reportDates as $month => $d) {
                    if ($d == $_POST['date']) {
                        echo "<h3>Shabbos Mevorchim " . $month . "</h3>";
                    }
                }
                ?>
                <table>
                    <tr>
                        <th width="10">Place</th>
                        <th width="50">School</th>
                        <th width="50">Commander</th>
                        <th width="30"># of Chayolim</th>
                        <th width="70">% of Chayolim Completed Quota</th>
                    </tr>
                    <?php
                    $indx = 1;
                    foreach ($info as $id => $percent) {
                        echo "<tr><td>" . $indx++ . "</td><td>" . $schoolInfo[$id] . "</td><td>" . $classes[$id]['teacher'] . "</td><td>" .
                            $totalUsers[$id] . "</td><td>" . $percent . "%</td></tr>";
                    }
                    ?>
                </table>
                <div class="pageBreak"></div>
            <?php endforeach; ?>
        <?php else : ?>
            <form action="tehillim_whatsapp_summary.php" method="post">
                For: <select name="date">
                    <?php 
                    $i = 0;
                    $num = count($reportDates);
                    foreach ($reportDates as $month => $d) {
                        if (++$i == $num) 
                            echo "<option value=" . $d . " selected='selected'>Shabbos Mevorchim " . $month . "</option>";
                        else 
                            echo "<option value=" . $d . ">Shabbos Mevorchim " . $month . "</option>"; 
                    }
                    ?> 
                </select>
                <input type="submit" name="submit" value="Set Date" />
            </form>
        <?php endif; ?>
    </body>
</html>