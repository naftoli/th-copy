<?php
ini_set('display_errors', 1);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

$year = $_REQUEST['year'] ?? GlobalSettings::getChidonRegYear();

$info = [];
$sql = "
    SELECT 
        reg_date,
        u.user_id,
        user_serial,
        gender,
        u.first AS first_name,
        u.last AS last_name,
        first_he,
        last_he,
        first_known_en,
        last_known_en,
        first_known_he,
        last_known_he,
        s.school_id,
        s.school_name,
        dob,
        u.lang_id,
        th_chidon_id,
        khk_reg,
        name_pref,
        size,
        book,
        yarmulka,
        recruited_by,
        poll,
        comments,
        test_type,
        non_th_school,
        a.*
    FROM
        users u
            JOIN
        th_chidon tc USING (user_id)
            JOIN
        schools s ON u.school_id = s.school_id
            JOIN
        admin_auths aa ON aa.id = tc.user_id
            JOIN
        admins a USING (admin_id)
    WHERE
        tc.year = $year AND u.school_id in (" . implode(',', array_keys($schools)) . ") 
    ORDER BY s.school_id , u.last , u.first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}

$prizes = [];
$sql = "select u.user_id, p.prize_name, p.size, p.color, p.price, u.he_name 
        from chidon_prizes p 
        join chidon_user_prizes u using (prize_id) 
        where u.year = $year  
        order by u.user_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $prizes[$row['user_id']][] = $row;
}

$langs = [
    1   =>  'English',
    2   =>  'Yiddish',
    3   =>  'French',
    4   =>  'Hebrew'
];

$customNames = [
    'en'    =>  'Full English Name',
    'he'    =>  'Full Hebrew Name',
    'nick_en'   =>  'English Name Known by',
    'nick_he'   =>  'Hebrew Name Known by'
];

$types = [
    'maven' => 'Yesod',
    'pro'   => 'Yediah',
    'expert'=> 'Havonah',
    'genius'=> 'Iyun'
];
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <title>Chidon Registration Report</title>
        <style>
            tr, th, td {
                font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
                font-size: 12px;
                padding: 5px;
                border-bottom: 1px solid grey;
            }
        </style>
    </head>
    <body>
        <h1>Chidon Registration Report</h1>
        <div>
            Choose Year:
            <select name="year" id="year">
                <?php
                for ($i = 4; $i <= 0; $i--) {
                    $yr = $year - $i;
                    echo "<option value='" . $yr . "'";
                    if ($yr == $year) echo " selected ";
                    echo ">" . $yr . "</option>";
                }
                ?>
            </select>
        </div>
        <table>
            <tr>
                <th>Registration Date</th>
                <th>User ID</th>
                <th>Serial Number</th>
                <th>School</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Full Hebrew Name</th>
                <th>English Name Known by</th>
                <th>Hebrew Name Known by</th>
                <th>DOB</th>
                <th>Gender</th>
                <th>Yarmulka</th>
                <th>Sweater Size</th>
                <th>Language</th>
                <th>Track</th>
                <th>Custom Item Name</th>
                <th>Book Number</th>
                <th>Registered for KHK</th>
                <th>Chidon Learning Method</th>
                <th>Invited by (Serial Number)</th>
                <th>Comments</th>
                <th>Prizes</th>
                <th>Personalized Prize Name</th>
                <th>Total Credits Used</th>
                <th>Non TH School</th>
                <th>Parent Name</th>
                <th>Parent Email</th>
            </tr>
            <?php
            foreach ($info as $row) {
                echo "<tr><td>" . $row['reg_date'] . "</td><td>" . $row['user_id'] . "</td><td>" . $row['user_serial'] .
                    "</td><td>" . $row['school_name'] . "</td><td>" . $row['first_name'] . "</td><td>" . $row['last_name'] . "</td><td>" .
                    $row['first_he'] . ' ' . $row['last_he'] . "</td><td>" . $row['first_known_en'] . ' ' .
                    $row['last_known_en'] . "</td><td>" . $row['first_known_he'] . ' ' . $row['last_known_he'] . "</td><td>" .
                    $row['dob'] . "</td><td>" . $row['gender'] . "</td><td>";
                if ($row['gender'] == 'M' && $row['yarmulka'] == '0') echo "<span style='color: red; font-width: bold;'>";
                else echo "<span>";
                echo $row['yarmulka'] . "</span></td><td>" . $row['size'] . "</td><td>" .
                    $langs[$row['lang_id']] . "</td><td>" . $types[strtolower($row['test_type'])] . "</td><td>" . $customNames[$row['name_pref']] .
                    "</td><td>" . $row['book'] . "</td><td>" .
                    ($row['khk_reg'] ? 'yes' : 'no') .
                    "</td><td>" . $row['poll'] . "</td><td>" . $row['recruited_by'] . "</td><td>" . $row['comments'] . "</td><td class='prize'>";
                if (isset($prizes[$row['user_id']])) {
                    foreach ($prizes[$row['user_id']] as $i => $prize) {
                        echo $prize['prize_name'];
                        if ($prize['size']) echo " Size: " . $prize['size'];
                        if ($prize['color']) echo " Color: " . $prize['color'];
                        if ($i < count($prizes[$row['user_id']]) - 1) echo "<hr />";
                    }
                }
                echo "</td><td>";
                $totalCredits = 0;
                if (isset($prizes[$row['user_id']])) {
                    foreach ($prizes[$row['user_id']] as $i => $prize) {
                        $totalCredits += floatval($prize['price']);
                        echo $prize['he_name'];
                        if ($i < count($prizes[$row['user_id']]) - 1) echo "<hr />";
                    }
                }
                echo "</td><td>" . $totalCredits;
                echo "</td><td>" . $row['non_th_school'] . "</td><td>" . $row['first'] . " " . $row['last'] . "</td><td>" . $row['admin_email'];
                echo "</td></tr>";
            }
            ?>
        </table>
    </body>
    <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
    <script>
        $("#year").change( function () {
            let yr = $(this).val()
            location.href = "comprehensive_reg_report.php?year=" + yr
        })
    </script>
</html>
