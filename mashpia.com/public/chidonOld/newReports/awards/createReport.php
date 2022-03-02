<?php
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$year = GlobalSettings::getChidonYear();

$type = $_POST['award_type'];
$final = $_POST['final'];

// qry to get all kids that should get the award
$sql = "
    SELECT
        s.school_name, u.user_id, u.class_id, u.school_id, u.user_serial, u.first_he, u.last_he, u.gender, 
        c.class_grade, c.class_sub, tc.parent_id, tci.highest_track";
if ($final == 'after') $sql .= ", tcf.* ";
$sql .= "
    FROM 
        users u 
        join schools s using (school_id) 
        join classes c on c.class_id = u.class_id 
        join th_chidon tc using (user_id) 
        join th_chidon_info tci on tc.year = tci.year and tc.user_id = tci.user_id";
if ($final == 'after') $sql .= " left join th_chidon_finals tcf on tc.year = tcf.year and tc.user_id = tcf.user_id";
$sql .= "
    WHERE
        tc.year = $year
        and tc.date_paid > 0";
switch ($type) {
    case 'cert':
        $sql .= " and tci.highest_track = 'yesod'";
        break;
    case 'plaque':
        $sql .= " and tci.highest_track != 'yesod'";
        break;
    case 'medal':
        $sql .= " and tci.highest_track in ('havonah', 'iyun')";
        break;
    case 'trophy':
        $sql .= " and tci.highest_track = 'iyun'";
        break;
}
$sql .= "
    ORDER BY
        s.school_id, c.class_grade, c.class_sub, u.last, u.first";
//echo $sql . "<br />"; exit;
$stmt = $MASHPIA_DB->query($sql);
$rows = $stmt->fetchAll();
$info = [];
foreach ($rows as $row) {
    $info[$row['school_id']][] = $row;
}

// figure out how to deal with 'after' the finals
if ($final == 'after') {
    foreach ($info as $school_id => &$rows) {
        foreach ($rows as &$row) {
            $tracks = [
                1   => 'yesod',
                2   => 'yediah',
                3   => 'havonah',
                4   => 'iyun'
            ];
            $finals = [
                'yesod'     => 20,
                'yediah'    => 40,
                'havonah'   => 60,
                'iyun'      => 80
            ];
            $needed = [
                'yesod'     => 60,
                'yediah'    => 70,
                'havonah'   => 80,
                'iyun'      => 90
            ];
            $highest_track = $row['highest_track'];

            // find out if award is same as before final or not
            $award = false;
            $key = array_search($highest_track, $tracks);
            if ($key !== false) {
                $score = 0;
                // go down from key to find where the child is holding
                for ($i = $key; $i > 0; $i--) {
                    $level = 'level_' . $i;
                    if ($row[$level]) {
                        $score += $row[$level];
                        $divide_by = $finals[$tracks[$i]];
                        $final_score = number_format(($score / $divide_by) * 100, 2);
                        if ($final_score >= $needed[$tracks[$i]]) {
                            $award = $tracks[$i];
                        }
                    }
                }
            }

            // figure out what changes if any are applicable
            $change = '';
            if ($award != $highest_track) {
                switch ($award) {
                    case 'havonah':
                        $change = 'plaque / medal';
                        break;
                    case 'yediah':
                        $change = 'plaque';
                        break;
                    case 'yesod':
                        $change = 'certificate';
                        break;
                    default:
                        // nothing should be awarded
                        $change = 'no award';
                        break;
                }
            }
            $row['award'] = $change;
        }
    }
}

// find out order of kids for admins
$admins = [];
$sql = "select aa.admin_id, aa.id from admin_auths aa 
        join users u on u.user_id = aa.id 
        join th_chidon tc using (user_id) 
        where tc.year = $year 
        and tc.date_paid > 0 
        and u.school_id in (61, 269)";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $admins[$row['admin_id']][] = $row['id'];
}