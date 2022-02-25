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
        c.class_grade, c.class_sub, a.admin_id, tci.highest_track";
if ($final == 'after') $sql .= ", tcf.* ";
$sql .= "
    FROM 
        users u 
        join schools s using (school_id) 
        join classes c on c.class_id = u.class_id 
        join th_chidon tc using (user_id) 
        join admins a on a.admin_id = tc.parent_id 
        join th_chidon_info tci on tc.year = tci.year and tc.user_id = tci.user_id";
if ($final == 'after') $sql .= "left join th_chidon_finals tcf on tc.year = tcf.year and tc.user_id = tcf.user_id";
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
        $sql .= " and tci.highest_track == 'iyun'";
        break;
}
$sql .= "
    ORDER BY
        s.school_id, c.class_grade, c.class_sub, u.last, u.first";
//echo $sql . "<br />";
$stmt = $MASHPIA_DB->query($sql);
$info = $stmt->fetchAll();

// find out order of kids for admins
$admins = [];
$sql = "select aa.admin_id, aa.id from admin_auths aa 
        join users u on u.user_id = aa.id 
        join th_chidon tc using (user_id) 
        where tc.year = 5782 
        and tc.date_paid > 0 
        and u.school_id in (61, 269)";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $admins[$row['admin_id']][] = $row['id'];
}