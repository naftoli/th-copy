<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . "/header.php";

if ($admin_user['auth'] != 'super') {
    echo "No permission to be here.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
$cur_yr = GlobalSettings::getChidonRegYear() - 1;
$from_yr = $cur_yr - 4;

if (isset($_POST['year'])) $chosen_yr = $_POST['year'];
else $chosen_yr = $cur_yr;

// get all children from th_chidon from past years
$info = [];
$stmt = $MASHPIA_DB->prepare("
    SELECT 
        tc.th_chidon_id,
        tc.user_id, 
        tc.year, 
        tc.reg_date, 
        tc.date_paid, 
        tc.test_type, 
        tc.reward_type, 
        tc.award_type, 
        u.first,
        u.last,
        u.user_serial,
        c.class_grade,
        c.class_sub,
        s.school_name
    FROM
        th_chidon tc 
        JOIN users u ON u.user_id = tc.user_id 
        JOIN classes c ON c.class_id = u.class_id 
        JOIN schools s ON s.school_id = u.school_id
    WHERE
        tc.year = :yr
    ORDER BY
        s.school_name, c.class_grade, c.class_sub, u.last, u.first, tc.year 
");
$stmt->execute([
    ':yr' => $chosen_yr,
]);
$children = $stmt->fetchAll();
foreach ($children as $child) {
    $info[$child['user_id']] = $child;
}

$types = [
    'maven' => 'Yesod',
    'pro'   => 'Yediah',
    'expert'=> 'Havonah',
    'genius'=> 'Iyun'
];

$fields = [
    'year' => 'Year',
    'school_name' => 'Current School', 
    'class_grade' => 'Current Grade', 
    'class_sub' => 'Current Sub', 
    'user_serial' => 'Serial', 
    'first' => 'First', 
    'last' => 'Last', 
    'reg_date' => 'Enrollment Date', 
    'date_paid' => 'Registration Date', 
    'test_type' => 'Track Signed Up For', 
    'reward_type' => 'Reward Override', 
    'award_type' => 'Award Override', 
    'marks' => 'Marks'
];

$mark_fields = [
    'test_number' => 'Test Number',
    'answered_correctly' => 'Answered Correctly',
    'total_questions' => 'Total Questions',
    'level' => 'Level'
];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Eligibility History</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        tr, th, td {
            font-family: Arial, sans-serif;
            font-size: 14px;
            border-bottom: 1px solid #ddd;
            padding: 10px;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <?php
    if (isset($_POST['year'])) $chosen_yr = $_POST['year'];
    ?>
    <!-- first choose year -->
    <form action="" method="post">
        <label for="year">Year:</label>
        <select id="year" name="year">
            <?php for ($i = $cur_yr; $i >= $from_yr; $i--) { ?>
                <option value="<?= $i ?>"
                <?php if ($chosen_yr == $i) echo 'selected'; ?>
                ><?= $i ?></option>
            <?php } ?>
        </select><br /><br />
        <button type="submit">Submit</button>
    </form>
    <?php
    if ($chosen_yr) {
    ?>
    <table>
        <thead>
            <tr>
                <?php foreach ($fields as $field => $label) { ?>
                    <th><?= $label ?></th>
                <?php } ?>
            </tr>
        </thead>
        <tbody> 
            <?php 
            foreach ($info as $user_id => $user) { 
                ?>
                    <tr>
                        <?php foreach ($fields as $field => $label) { ?>
                            <td>
                                <?php 
                                if ($field == 'marks') {
                                    // create table for marks
                                    ?>
                                    <button id="<?= $user['th_chidon_id'] ?>" class="marks" data-yr="<?= $chosen_yr ?>">Get Marks</button>
                                    <table style="display: none;">
                                        <thead>
                                            <tr>
                                                <th>Track</th>
                                                <?php foreach ($mark_fields as $field => $label) { ?>
                                                    <th><?= $label ?></th>
                                                <?php } ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            
                                        </tbody>
                                    </table>
                                    <?php
                                } else if (in_array($field, ['test_type', 'reward_type', 'award_type'])) {
                                    echo isset($types[$user[$field]]) ? $types[$user[$field]] : 'N/A';
                                } else {
                                    echo isset($user[$field]) ? $user[$field] : 'N/A';
                                }
                                ?>
                            </td>
                        <?php } ?>
                    </tr>
                    <?php
                }
            ?>
        </tbody>
    </table>
    <?php } ?>
</body>
<script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
<!-- <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.min.js" integrity="sha256-AlTido85uXPlSyyaZNsjJXeCs07eSv3r43kyCVc8ChI=" crossorigin="anonymous"></script> -->
<script>
    const mark_fields = {
        'test_number': 'Test Number',
        'answered_correctly': 'Answered Correctly',
        'total_questions': 'Total Questions', 
        'level': 'Level'
    };
    
    $(document).ready(function() {
        $('.marks').click(function(e) {
            e.preventDefault();
            const th_chidon_id = $(this).attr('id');
            const yr = $(this).data('yr');
            // check if we have already loaded the marks
            if ($(this).find('table tbody').html() != '') {
                return;
            }
            $.ajax({
                url: 'api/getMarks.php',
                type: 'GET',
                data: {
                    chidon_id: th_chidon_id,
                    yr: yr
                },
                success: function(response) {
                    if (response.success) {
                        let html = '';
                        const marks = response.marks;
                        if (Object.keys(marks).length == 0) {
                            html += `<tr><td colspan="5">No marks found</td></tr>`;
                        } else {
                            for (const track in marks) {
                                html += `<tr><td>${track}</td>`;
                                for (const field in mark_fields) {
                                    html += `<td>${marks[track][field]}</td>`;
                                }
                                html += '</tr>';
                            }
                            $(this).find('table tbody').html(html);
                            $(this).find('table').show();
                            $(this).hide();
                        }
                    }
                }
            });
        });
    });
</script>
</html>