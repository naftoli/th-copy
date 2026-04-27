<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once dirname(__FILE__) . '/../db.php';
require_once dirname(__FILE__) . '/../api/header/cache.php';

if (!Cache::enabled()) {
    fwrite(STDERR, "Redis cache is not enabled.\n");
    exit(1);
}

function jd_mod_day($jd)
{
    return $jd % 7;
}

function next_rollover_jd($today)
{
    // Existing codebase mapping:
    // 3 => Thursday, 4 => Friday
    // Rollover happens Thursday night after midnight => Friday (mod 4).
    $daysUntilFriday = (4 - jd_mod_day($today) + 7) % 7;
    if ($daysUntilFriday === 0) {
        $daysUntilFriday = 7;
    }
    return $today + $daysUntilFriday;
}

function ttl_until_jd($jdExclusiveEnd)
{
    $targetUnix = jdtounix($jdExclusiveEnd);
    $ttl = $targetUnix - time();
    return $ttl > 0 ? $ttl : 60;
}

function cache_set_rows($key, $rows, $ttl)
{
    Cache::set($key, $rows, $ttl);
}

function fetch_rows($sql)
{
    $rows = [];
    $query = mysql_query($sql);
    while ($row = mysql_fetch_assoc($query)) {
        $rows[] = $row;
    }
    return $rows;
}

$today = unixtojd();
// $startDate = next_rollover_jd($today);
$startDate = 2461155;
$endDate = $startDate + 6;

// Keep warm until the end of that week (next Friday at 00:00).
$ttl = ttl_until_jd($endDate + 1);

echo "Warming mission/task cache for JD {$startDate}-{$endDate} with TTL {$ttl}s\n";

$comboSql = "SELECT DISTINCT school_type_id, subject_id, level, track_id, lang_id
             FROM date_tasks_missions
             WHERE start_date >= {$startDate} AND end_date <= {$endDate}";
$comboQuery = mysql_query($comboSql);

$missionCacheCount = 0;
$taskCacheCount = 0;
$missionIds = [];

while ($combo = mysql_fetch_assoc($comboQuery)) {
    $schoolTypeId = (int) $combo['school_type_id'];
    $subjectId = (int) $combo['subject_id'];
    $level = (int) $combo['level'];
    $trackId = (int) $combo['track_id'];
    $langId = (int) $combo['lang_id'];

    foreach ([1, 0] as $printParentTasks) {
        $chidonOptions = ($subjectId === 21) ? [0, 1] : [0];
        foreach ($chidonOptions as $chidonLimmud) {
            $sql = "SELECT * FROM date_tasks_missions
                    WHERE lang_id = {$langId}
                    AND school_type_id = {$schoolTypeId}
                    AND subject_id = {$subjectId}
                    AND level = {$level}
                    AND track_id = {$trackId}
                    AND start_date >= {$startDate}
                    AND end_date <= {$endDate}";

            if (!$printParentTasks) {
                $sql .= " AND created_by_parent IS NULL";
            }
            if ($subjectId === 21 && !$chidonLimmud) {
                $sql .= " AND mission_name NOT LIKE '%Chidon Limmud%'";
            }

            $sql .= " ORDER BY created_by_parent IS NULL DESC, mission_number, start_date, mission_name";

            $rows = fetch_rows($sql);
            foreach ($rows as $row) {
                $missionIds[(int) $row['date_tasks_mission_id']] = true;
            }

            $cacheKey = implode(':', [
                'missions',
                'school', $schoolTypeId,
                'subject', $subjectId,
                'level', $level,
                'track', $trackId,
                'lang', $langId,
                'start', $startDate,
                'end', $endDate,
                'parent', $printParentTasks ? 1 : 0,
                'chidon', $chidonLimmud ? 1 : 0
            ]);

            cache_set_rows($cacheKey, $rows, $ttl);
            $missionCacheCount++;
        }
    }
}

foreach (array_keys($missionIds) as $missionId) {
    $queries = [
        "date_tasks:daily:mission:{$missionId}:ids:single" =>
            "SELECT l.label_name, l.frequency_id, f.frequency_name, fp.frequency_period_name, dt.*
             FROM date_tasks AS dt
             JOIN labels AS l USING (label_id)
             JOIN frequencies AS f USING (frequency_id)
             JOIN frequency_periods AS fp USING (frequency_period_id)
             WHERE dt.date_tasks_mission_id = {$missionId}
             AND f.frequency_name = 'Daily'
             AND dt.mission_marking = 1
             ORDER BY dt.label_ord, dt.grid_id",
        "date_tasks:weekly:mission:{$missionId}:ids:single" =>
            "SELECT l.label_name, l.frequency_id, f.frequency_name, fp.frequency_period_name, dt.*
             FROM date_tasks AS dt
             JOIN labels AS l USING (label_id)
             JOIN frequencies AS f USING (frequency_id)
             JOIN frequency_periods AS fp USING (frequency_period_id)
             WHERE dt.date_tasks_mission_id = {$missionId}
             AND f.frequency_name = 'Weekly'
             AND dt.mission_marking = 1
             ORDER BY dt.label_ord, dt.grid_id",
        "date_tasks:shabbos:mission:{$missionId}:ids:single" =>
            "SELECT l.label_name, l.frequency_id, f.frequency_name, fp.frequency_period_name, dt.*
             FROM date_tasks AS dt
             JOIN labels AS l USING (label_id)
             JOIN frequencies AS f USING (frequency_id)
             JOIN frequency_periods AS fp USING (frequency_period_id)
             WHERE dt.date_tasks_mission_id = {$missionId}
             AND f.frequency_name = 'Shabbos'
             AND dt.mission_marking = 1
             ORDER BY dt.label_ord, dt.grid_id",
        "date_tasks:no_label:mission:{$missionId}:ids:single" =>
            "SELECT *
             FROM date_tasks AS dt
             WHERE dt.date_tasks_mission_id = {$missionId}
             AND (dt.label_id IS NULL OR dt.label_id = 0)
             AND dt.mission_marking = 1
             ORDER BY dt.grid_id, dt.ord, dt.date_task_id",
        "date_tasks:pesukim:mission:{$missionId}" =>
            "SELECT l.label_name, l.frequency_id, f.frequency_name, fp.frequency_period_name, dt.*
             FROM date_tasks AS dt
             JOIN labels AS l USING (label_id)
             JOIN frequencies AS f USING (frequency_id)
             JOIN frequency_periods AS fp USING (frequency_period_id)
             WHERE dt.date_tasks_mission_id = {$missionId}
             AND f.frequency_name = 'Pesukim'
             AND dt.mission_marking = 1
             ORDER BY dt.label_ord, dt.grid_id",
    ];

    foreach ($queries as $cacheKey => $sql) {
        $rows = fetch_rows($sql);
        cache_set_rows($cacheKey, $rows, $ttl);
        $taskCacheCount++;
    }
}

echo "Mission cache keys warmed: {$missionCacheCount}\n";
echo "Task cache keys warmed: {$taskCacheCount}\n";
echo "Mission IDs warmed: " . count($missionIds) . "\n";
echo "Done\n";

