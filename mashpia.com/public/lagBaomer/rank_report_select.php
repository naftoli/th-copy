<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();
$schoolIds = array_map('intval', array_keys($schools));

$selectedSchools = isset($_GET['schools']) && is_array($_GET['schools'])
    ? array_map('intval', $_GET['schools'])
    : [];
$selectedGrades = isset($_GET['grades']) && is_array($_GET['grades'])
    ? array_map('strval', $_GET['grades'])
    : [];
$selectedRanks = isset($_GET['ranks']) && is_array($_GET['ranks'])
    ? array_map('intval', $_GET['ranks'])
    : [];
$registeredOnly = isset($_GET['registered_only']) ? intval($_GET['registered_only']) : 1;
$reportView = isset($_GET['report_view']) ? $_GET['report_view'] : 'both'; // details|summary|both
$totalsByGender = isset($_GET['totals_by_gender']) ? intval($_GET['totals_by_gender']) : 1;
if (!in_array($reportView, ['details', 'summary', 'both'])) {
    $reportView = 'both';
}

// keep only schools this admin is allowed to see
$selectedSchools = array_values(array_intersect($selectedSchools, $schoolIds));
if (empty($selectedSchools)) {
    $selectedSchools = $schoolIds;
}

// available grades for selected schools
$availableGrades = [];
if (!empty($selectedSchools)) {
    $sqlGrades = "SELECT DISTINCT c.class_grade
                  FROM classes c
                  WHERE c.school_id IN (" . implode(', ', $selectedSchools) . ")
                  ORDER BY
                    CASE
                        WHEN c.class_grade = 'Pre1a' THEN 0
                        WHEN c.class_grade REGEXP '^[0-9]+$' THEN 1
                        ELSE 2
                    END,
                    c.class_grade + 0,
                    c.class_grade";
    $resultGrades = mysql_query($sqlGrades);
    while ($row = mysql_fetch_assoc($resultGrades)) {
        $availableGrades[] = $row['class_grade'];
    }
}
if (!in_array('Pre1a', $availableGrades)) {
    array_unshift($availableGrades, 'Pre1a');
} else {
    $availableGrades = array_values(array_unique(array_merge(['Pre1a'], $availableGrades)));
}
if (empty($selectedGrades)) {
    $selectedGrades = $availableGrades;
}

// available ranks and rank names
$rankRows = [];
$rankNames = [];
$sqlRanks = "SELECT rank_ord, rank_name FROM ranks ORDER BY rank_ord";
$resultRanks = mysql_query($sqlRanks);
while ($row = mysql_fetch_assoc($resultRanks)) {
    $ord = intval($row['rank_ord']);
    $rankRows[] = ['rank_ord' => $ord, 'rank_name' => $row['rank_name']];
    $rankNames[$ord] = $row['rank_name'];
}
if (empty($selectedRanks)) {
    $selectedRanks = array_map(function ($r) {
        return intval($r['rank_ord']);
    }, $rankRows);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rank Report (Select)</title>
    <link href="../admin_styles.css" rel="stylesheet" type="text/css">
    <style>
        body {
            -webkit-print-color-adjust: exact;
        }

        p {
            font-size: 12px;
        }

        table {
            font-size: 12px;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        .page-break {
            page-break-after: always;
        }

        .filters {
            background: #f7f7fb;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 14px;
            margin-bottom: 16px;
        }

        .filters h3 {
            margin: 0 0 10px 0;
            padding-bottom: 10px;
            border-bottom: 1px dashed #ddd;
        }

        .row {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .group {
            min-width: 260px;
            flex: 1 1 280px;
        }

        .options-box {
            max-height: 220px;
            overflow-y: auto;
            border: 1px solid #ddd;
            background: #fff;
            padding: 8px;
            border-radius: 6px;
        }

        .options-box label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 2px;
            width: 100%;
        }

        .options-box label:hover {
            background: #f8f9fc;
            border-radius: 4px;
        }

        .grades-box {
            display: block;
        }

        .grades-box label {
            width: 100%;
            padding: 3px 2px;
            border: none;
            border-radius: 0;
            background: transparent;
        }

        .scope-wrap {
            margin-top: 4px;
            padding-top: 8px;
            border-top: 1px dashed #ddd;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #ddd;
        }

        .scope-wrap label {
            display: inline-flex;
            align-items: center;
            margin-right: 16px;
            gap: 6px;
        }

        .muted {
            color: #666;
            font-size: 11px;
        }

        button, 
        input[type="button"] {
            padding: 10px;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
    <script>
        function print_report() {
            window.print();
        }

        function toggleAll(groupSelector, checked) {
            var boxes = document.querySelectorAll(groupSelector);
            for (var i = 0; i < boxes.length; i++) {
                boxes[i].checked = checked;
            }
        }

        function syncSelectAll(masterSelector, groupSelector) {
            var master = document.querySelector(masterSelector);
            if (!master) return;
            var boxes = document.querySelectorAll(groupSelector);
            var total = boxes.length;
            var checked = 0;
            for (var i = 0; i < boxes.length; i++) {
                if (boxes[i].checked) checked++;
            }
            master.checked = total > 0 && checked === total;
        }

        document.addEventListener('DOMContentLoaded', function () {
            var selectAllSchools = document.getElementById('selectAllSchools');
            var selectAllGrades = document.getElementById('selectAllGrades');

            if (selectAllSchools) {
                selectAllSchools.addEventListener('change', function () {
                    toggleAll('.schoolOption', this.checked);
                    syncSelectAll('#selectAllSchools', '.schoolOption');
                });
            }
            if (selectAllGrades) {
                selectAllGrades.addEventListener('change', function () {
                    toggleAll('.gradeOption', this.checked);
                    syncSelectAll('#selectAllGrades', '.gradeOption');
                });
            }
            var selectAllRanks = document.getElementById('selectAllRanks');
            if (selectAllRanks) {
                selectAllRanks.addEventListener('change', function () {
                    toggleAll('.rankOption', this.checked);
                    syncSelectAll('#selectAllRanks', '.rankOption');
                });
            }

            var schoolOptions = document.querySelectorAll('.schoolOption');
            for (var i = 0; i < schoolOptions.length; i++) {
                schoolOptions[i].addEventListener('change', function () {
                    syncSelectAll('#selectAllSchools', '.schoolOption');
                });
            }

            var gradeOptions = document.querySelectorAll('.gradeOption');
            for (var j = 0; j < gradeOptions.length; j++) {
                gradeOptions[j].addEventListener('change', function () {
                    syncSelectAll('#selectAllGrades', '.gradeOption');
                });
            }
            var rankOptions = document.querySelectorAll('.rankOption');
            for (var k = 0; k < rankOptions.length; k++) {
                rankOptions[k].addEventListener('change', function () {
                    syncSelectAll('#selectAllRanks', '.rankOption');
                });
            }

            syncSelectAll('#selectAllSchools', '.schoolOption');
            syncSelectAll('#selectAllGrades', '.gradeOption');
            syncSelectAll('#selectAllRanks', '.rankOption');
        });
    </script>
</head>
<body>
<? include('../admin_header.php'); ?>
<h1 class="no-print">Rank Report (Select)</h1>

<div class="no-print filters">
    <h3>Filters</h3>
    <form method="get">
        <div class="row">
            <div class="group">
                <strong>Schools</strong><br/>
                <label><input type="checkbox" id="selectAllSchools"> Select all schools</label>
                <div class="options-box">
                    <?php foreach ($schools as $schoolId => $schoolName) { ?>
                        <label>
                            <input type="checkbox" class="schoolOption" name="schools[]"
                                   value="<?= intval($schoolId) ?>" <?= in_array(intval($schoolId), $selectedSchools) ? 'checked' : '' ?>>
                            <?= htmlspecialchars($schoolName) ?>
                        </label><br/>
                    <?php } ?>
                </div>
            </div>

            <div class="group">
                <strong>Grades</strong><br/>
                <label><input type="checkbox" id="selectAllGrades"> Select all grades</label>
                <div class="options-box grades-box">
                    <?php foreach ($availableGrades as $grade) { ?>
                        <label>
                            <input type="checkbox" class="gradeOption" name="grades[]"
                                   value="<?= htmlspecialchars($grade) ?>" <?= in_array((string)$grade, $selectedGrades) ? 'checked' : '' ?>>
                            Grade <?= htmlspecialchars($grade) ?>
                        </label><br/>
                    <?php } ?>
                    <?php if (empty($availableGrades)) { ?>
                        <span class="muted">No grades found for selected schools.</span>
                    <?php } ?>
                </div>
            </div>

            <div class="group">
                <strong>Ranks</strong><br/>
                <label><input type="checkbox" id="selectAllRanks"> Select all ranks</label>
                <div class="options-box">
                    <?php foreach ($rankRows as $rank) { ?>
                        <label>
                            <input type="checkbox" class="rankOption" name="ranks[]"
                                   value="<?= intval($rank['rank_ord']) ?>" <?= in_array(intval($rank['rank_ord']), $selectedRanks) ? 'checked' : '' ?>>
                            <?= intval($rank['rank_ord']) ?> - <?= htmlspecialchars($rank['rank_name']) ?>
                        </label><br/>
                    <?php } ?>
                </div>
            </div>
        </div>
        <div class="scope-wrap">
            <strong>Children</strong><br/>
            <label>
                <input type="radio" name="registered_only" value="1" <?= $registeredOnly ? 'checked' : '' ?>>
                Registered only
            </label>
            <label>
                <input type="radio" name="registered_only" value="0" <?= !$registeredOnly ? 'checked' : '' ?>>
                All children
            </label>
            <br/><br/>
            <strong>Report type</strong><br/>
            <label>
                <input type="radio" name="report_view" value="both" <?= $reportView == 'both' ? 'checked' : '' ?>>
                Details + Summary
            </label>
            <label>
                <input type="radio" name="report_view" value="details" <?= $reportView == 'details' ? 'checked' : '' ?>>
                Details only
            </label>
            <label>
                <input type="radio" name="report_view" value="summary" <?= $reportView == 'summary' ? 'checked' : '' ?>>
                Summary only
            </label>
            <br/><br/>
            <strong>Totals</strong><br/>
            <label>
                <input type="radio" name="totals_by_gender" value="1" <?= $totalsByGender ? 'checked' : '' ?>>
                Split by gender
            </label>
            <label>
                <input type="radio" name="totals_by_gender" value="0" <?= !$totalsByGender ? 'checked' : '' ?>>
                Combine genders
            </label>
        </div>
        <button type="submit">Run Report</button>
        <input type='button' value='Print' onclick='print_report()'/>
    </form>
</div>

<?php
$users = [];

if (!empty($selectedSchools) && !empty($selectedGrades) && !empty($selectedRanks)) {
    $selectedGradesSql = array_map(function ($grade) {
        return "'" . mysql_real_escape_string($grade) . "'";
    }, $selectedGrades);
    $selectedRanksSql = array_map('intval', $selectedRanks);

    $sql = "SELECT s.school_name, u.user_id, u.last, u.first, u.gender, c.class_grade, c.class_sub, rm.rank_ord
            FROM rank_marks rm
            JOIN users u USING (user_id)
            JOIN classes c ON c.class_id = u.class_id
            JOIN schools s ON s.school_id = u.school_id
            WHERE u.school_id IN (" . implode(', ', $selectedSchools) . ")
              AND c.class_grade IN (" . implode(', ', $selectedGradesSql) . ")
              AND rm.rank_ord IN (" . implode(', ', $selectedRanksSql) . ")";

    if ($registeredOnly) {
        $sql .= " AND u.user_registered > 0";
    }

    $sql .= " ORDER BY s.school_name, c.class_grade, c.class_sub, u.gender, u.last, u.first, rm.rank_ord";
    // echo $sql; exit;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
        $userName = $row['first'] . ' ' . $row['last'];
        $users[$row['school_name']][$grade][$row['gender']][$userName] = $row['rank_ord'];
    }
}

$genderLookup = [
    'F' => 'Girl',
    'M' => 'Boy'
];
$grandTotals = ['F' => [], 'M' => []];
$generalsTotals = ['F' => 0, 'M' => 0];

if (empty($users)) {
    echo "<p>No results found for the selected filters.</p>";
}

foreach ($users as $school => $info) {
    $totals = ['F' => [], 'M' => []];
    $totalGenerals = ['F' => 0, 'M' => 0];
    $combinedTotals = [];
    $combinedGenerals = 0;

    foreach ($info as $grade => &$other) {
        foreach ($other as $gender => &$user) {
            foreach ($user as $name => $rank) {
                if (isset($totals[$gender][$rank])) $totals[$gender][$rank]++;
                else $totals[$gender][$rank] = 1;

                if (isset($combinedTotals[$rank])) $combinedTotals[$rank]++;
                else $combinedTotals[$rank] = 1;

                if ($rank >= 9) {
                    $totalGenerals[$gender]++;
                    $combinedGenerals++;
                    $generalsTotals[$gender]++;
                }

                if (isset($grandTotals[$gender][$rank])) $grandTotals[$gender][$rank]++;
                else $grandTotals[$gender][$rank] = 1;
            }
        }
    }

    if ($reportView !== 'summary') {
        foreach ($info as $grade => $other) {
            $classTotals = ['F' => [], 'M' => []];
            $classCombinedTotals = [];
            $classGenerals = ['F' => 0, 'M' => 0];
            $classGeneralsCombined = 0;

            echo "<h2>" . $school . ' - ' . $grade . "</h2>";
            echo "<table>";
            echo "<tr><th>Gender</th><th>Student</th><th>Rank</th></tr>";
            foreach ($other as $gender => $user) {
                foreach ($user as $name => $rank) {
                    echo "<tr><td>" . $gender . "</td><td>" . $name . "</td><td>" . (isset($rankNames[$rank]) ? $rankNames[$rank] : $rank) . "</td></tr>";
                    if (isset($classTotals[$gender][$rank])) $classTotals[$gender][$rank]++;
                    else $classTotals[$gender][$rank] = 1;

                    if (isset($classCombinedTotals[$rank])) $classCombinedTotals[$rank]++;
                    else $classCombinedTotals[$rank] = 1;

                    if ($rank >= 9) {
                        $classGenerals[$gender]++;
                        $classGeneralsCombined++;
                    }
                }
            }
            echo "</table>";

            foreach ($classTotals as $gender => &$classMore) {
                ksort($classMore);
            }
            ksort($classCombinedTotals);

            echo "<h3>" . $school . ' - ' . $grade . " Totals</h3>";
            echo "<table>";
            if ($totalsByGender) {
                echo "<tr><th>Gender</th><th>Rank</th><th>Total</th></tr>";
                foreach ($classTotals as $gender => $classOther) {
                    foreach ($classOther as $rank => $num) {
                        echo "<tr><td>" . $gender . "</td><td>" . (isset($rankNames[$rank]) ? $rankNames[$rank] : $rank) . "</td><td>" . $num . "</td></tr>";
                    }
                }
            } else {
                echo "<tr><th>Rank</th><th>Total</th></tr>";
                foreach ($classCombinedTotals as $rank => $num) {
                    echo "<tr><td>" . (isset($rankNames[$rank]) ? $rankNames[$rank] : $rank) . "</td><td>" . $num . "</td></tr>";
                }
            }
            echo "</table><br />";
            if ($totalsByGender) {
                foreach ($classGenerals as $gender => $total) {
                    if ($total > 0) echo "<p>Total " . $genderLookup[$gender] . " Generals (" . $grade . "): " . $total . "</p>";
                }
            } else {
                if ($classGeneralsCombined > 0) echo "<p>Total Generals (" . $grade . "): " . $classGeneralsCombined . "</p>";
            }

            echo "<div class='page-break'></div>";
        }
    }

    foreach ($totals as $gender => &$more) {
        ksort($more);
    }
    ksort($combinedTotals);

    if ($reportView !== 'details') {
        echo "<h2>" . $school . " Totals</h2>";
        echo "<table>";
        if ($totalsByGender) {
            echo "<tr><th>Gender</th><th>Rank</th><th>Total</th></tr>";
            foreach ($totals as $gender => $other) {
                foreach ($other as $rank => $num) {
                    echo "<tr><td>" . $gender . "</td><td>" . (isset($rankNames[$rank]) ? $rankNames[$rank] : $rank) . "</td><td>" . $num . "</td></tr>";
                }
            }
        } else {
            echo "<tr><th>Rank</th><th>Total</th></tr>";
            foreach ($combinedTotals as $rank => $num) {
                echo "<tr><td>" . (isset($rankNames[$rank]) ? $rankNames[$rank] : $rank) . "</td><td>" . $num . "</td></tr>";
            }
        }
        echo "</table><br />";
        if ($totalsByGender) {
            foreach ($totalGenerals as $gender => $total) {
                if ($total > 0) echo "<p>Total " . $genderLookup[$gender] . " Generals: " . $total . "</p>";
            }
        } else {
            if ($combinedGenerals > 0) echo "<p>Total Generals: " . $combinedGenerals . "</p>";
        }
        echo "<div class='page-break'></div>";
    }
}

if ($admin_user['auth'] == 'super' && !empty($users) && $reportView !== 'details') {
    foreach ($grandTotals as $gender => &$info) {
        ksort($info);
    }
    $grandTotalsCombined = [];
    foreach ($grandTotals as $gender => $ranks) {
        foreach ($ranks as $rank => $total) {
            if (!isset($grandTotalsCombined[$rank])) $grandTotalsCombined[$rank] = 0;
            $grandTotalsCombined[$rank] += $total;
        }
    }
    ksort($grandTotalsCombined);
    echo "<h2>Grand Totals</h2>";
    echo "<table>";
    if ($totalsByGender) {
        echo "<tr><th>Gender</th><th>Rank</th><th>Total</th><tr>";
        foreach ($grandTotals as $gender => $other) {
            foreach ($other as $rank => $total) {
                echo "<tr><td>" . $gender . "</td><td>" . (isset($rankNames[$rank]) ? $rankNames[$rank] : $rank) . "</td><td>" . $total . "</td></tr>";
            }
        }
    } else {
        echo "<tr><th>Rank</th><th>Total</th><tr>";
        foreach ($grandTotalsCombined as $rank => $total) {
            echo "<tr><td>" . (isset($rankNames[$rank]) ? $rankNames[$rank] : $rank) . "</td><td>" . $total . "</td></tr>";
        }
    }
    echo "</table>";
    echo "<div class='page-break'></div>";
    echo "<h2>Grand Totals for Generals</h2>";
    if ($totalsByGender) {
        foreach ($generalsTotals as $gender => $total) {
            if ($total > 0) echo "<p>Total " . $genderLookup[$gender] . " Generals: " . $total . "</p>";
        }
    } else {
        $allGenerals = array_sum($generalsTotals);
        if ($allGenerals > 0) echo "<p>Total Generals: " . $allGenerals . "</p>";
    }
}
?>
</body>
</html>
