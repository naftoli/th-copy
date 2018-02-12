<?php
ini_set('display_errors', 1);
ini_set('max_execution_time', 500);
require 'whatsapp.php';
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <link href="https://fonts.googleapis.com/css?family=Hind+Vadodara:600" rel="stylesheet">
        <style>
            .whatsapp {
                text-align: center;
                margin: auto;
            }
            .whatsapp table {
                margin: auto;
            }
            tr, th, td {
                font-size: 14px;
                padding: 5px;
            }
            td {
                text-align: center;
                font-family: 'Hind Vadodara', sans-serif;
            }
            td.left {
                text-align: left;
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
            <div class="whatsapp">
            <?php foreach ($completed as $grade => $info) : ?>
                <img src='/images/whatsapp/WWTC-Top.png' />
                <h3>WWTC Grade <?=$grade?><br />Wall Of Honor</h3>
                <?php
                // get month
                foreach ($reportDates as $month => $d) {
                    if ($d == $_POST['date']) {
                        echo "<h3>Shabbos Mevorchim " . $month . "</h3>";
                    }
                }
                ?>
                <table align="center">
                    <tr>
                        <th width="10">Place</th>
                        <th width="50">School</th>
                        <th width="50">Commander</th>
                        <th width="30"># of Chayolim</th>
                        <th width="70">% of Chayolim Completed Quota</th>
                        <th width="20">Kapitelach Goal</th>
                        <th width="20">Accomplished</th>
                        <th width="20">Time Goal</th>
                        <th width="20">Accomplished</th>
                    </tr>
                    <?php
                    $totals = array();
                    $indx = 0;
                    foreach ($info as $id => $percent) {
                        echo "<tr><td>" . ($indx+1) . "</td><td class='left'>" . $schoolInfo[$id] . "</td><td class='left'>" . $classes[$id]['teacher'] . "</td><td>" .
                            $totalUsers[$id] . "</td><td>" . $percent . "%</td><td>" . $goal['Kapitelach'][$date][$id] . "</td><td>" .
                            (empty($done['Kapitelach'][$date][$id]) ? 0 : $done['Kapitelach'][$date][$id]) . "</td><td>" .
                            $goal['Minutes'][$date][$id] . "</td><td>" .
                            (empty($done['Minutes'][$date][$id]) ? 0 : $done['Minutes'][$date][$id]) . "</td></tr>";
                            
                        if (!$indx) {
                            $totals['numUsers'] = $totalUsers[$id];
                            $totals['quota'] = $doneQuotas['Kapitelach'][$id];
                            $totals['goalK'] = $goal['Kapitelach'][$date][$id];
                            $totals['goalM'] = $goal['Minutes'][$date][$id];
                            $totals['doneK'] = $done['Kapitelach'][$date][$id];
                            $totals['doneM'] = $done['Minutes'][$date][$id];
                        } else {
                            $totals['numUsers'] += $totalUsers[$id];
                            $totals['quota'] += $doneQuotas['Kapitelach'][$id];
                            $totals['goalK'] += $goal['Kapitelach'][$date][$id];
                            $totals['goalM'] += $goal['Minutes'][$date][$id];
                            $totals['doneK'] += $done['Kapitelach'][$date][$id];
                            $totals['doneM'] += $done['Minutes'][$date][$id];
                        }
                        $indx++;
                    }
                    echo "<tr><th colspan='3'>Totals</th><th>" . number_format($totals['numUsers']) . "</th><th>" .
                        number_format((($totals['quota'] / $totals['numUsers']) * 100.00), 2) . "%</th><th>" . number_format($totals['goalK']) .
                        "</th><th>" . number_format($totals['doneK']) . "</th><th>" . number_format($totals['goalM']) . "</th><th>" .
                        number_format($totals['doneM']) . "</th></tr>";
                    ?>
                </table>
                <img src='/images/whatsapp/WWTC-Bottom.png' />
                <div class="pageBreak"><br /></div>
            <?php endforeach; ?>
            </div>
        <?php else : ?>
            <form action="tehillim_whatsapp.php" method="post">
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
                </select><br />
                Gender: <input type="radio" name="gender" value="m" /> Boys 
                <input type="radio" name="gender" value="f" />Girls<br />
                <input type="submit" name="submit" value="Generate Report" />
            </form>
        <?php endif; ?>
    </body>
</html>