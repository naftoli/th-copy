# Required Database Indexes for Missions and Duch System

Based on analysis of actual queries in the codebase, here are the indexes needed to optimize performance.

## Query Analysis

### Query 1: Loading Users by School/Class
**File:** `mission_report/classes/missions.php` (line 35-47)

```sql
SELECT u.* FROM users u
JOIN classes c ON u.class_id = c.class_id
WHERE u.user_registered > 0
  AND u.school_id = ?
  AND u.class_id = ?
ORDER BY c.class_grade, c.class_sub, u.last, u.first
```

**Required Indexes:**
- `users`: Index on `(school_id, class_id, user_registered)` for WHERE clause
- `classes`: Index on `(class_id, class_grade, class_sub)` for JOIN and ORDER BY
- `users`: Index on `(class_id, last, first)` for ORDER BY on user columns

### Query 2: Get User Rank
**File:** `classes/user.php` (line 435-446)

```sql
SELECT MAX(rm.rank_ord) as rank_ord, r.rank_name, r.rank_image_id, rm.date_promoted
FROM rank_marks rm
JOIN ranks r USING (rank_ord)
WHERE rm.user_id = ?
```

**Required Indexes:**
- `rank_marks`: Index on `(user_id, rank_ord DESC)` for MAX aggregation
- `ranks`: Index on `(rank_ord)` for JOIN (likely already exists as PRIMARY KEY)

### Query 3: Get User Tracks
**File:** `classes/user.php` (line 471-480)

```sql
SELECT ut.* FROM user_tracks AS ut
JOIN subjects AS s USING (subject_id)
WHERE ut.user_id = ? AND ut.enrolled = 1
ORDER BY s.subject_ord
```

**Required Indexes:**
- `user_tracks`: Index on `(user_id, enrolled, subject_id)` for WHERE clause
- `subjects`: Index on `(subject_id, subject_ord)` for JOIN and ORDER BY

### Query 4: Get Date Tasks Missions (Main Query)
**File:** `classes/user_track.php` (line 126-131)

```sql
SELECT * FROM date_tasks_missions
WHERE lang_id = ?
  AND school_type_id = ?
  AND subject_id = ?
  AND level = ?
  AND track_id = ?
  AND start_date >= ?
  AND end_date <= ?
  AND created_by_parent IS NULL
ORDER BY created_by_parent IS NULL DESC, mission_number, start_date, mission_name
```

**Required Indexes:**
- `date_tasks_missions`: Composite index on `(school_type_id, subject_id, level, track_id, lang_id, start_date, end_date, created_by_parent)`
- Alternative: Separate index on `(start_date, end_date, school_type_id, subject_id, level, track_id)` for date range queries

### Query 5: Update User Track (LEFT JOIN)
**File:** `classes/user_track.php` (line 69-80)

```sql
SELECT dtm.* FROM date_tasks_missions AS dtm
LEFT JOIN date_tasks_mission_marks as dtmm 
  ON (dtm.date_tasks_mission_id = dtmm.date_tasks_mission_id AND dtmm.user_id = ?)
WHERE dtm.school_type_id = ?
  AND dtm.subject_id = ?
  AND dtm.track_id = ?
  AND dtm.level = ?
  AND dtm.start_date < ?
  AND dtmm.date_tasks_mission_id IS NULL
ORDER BY dtm.mission_number
```

**Required Indexes:**
- `date_tasks_mission_marks`: Index on `(date_tasks_mission_id, user_id)` for LEFT JOIN
- `date_tasks_missions`: Index on `(school_type_id, subject_id, track_id, level, start_date)` for WHERE clause

### Query 6: Get School Class
**File:** `classes/user.php` (line 300)

```sql
SELECT * FROM classes WHERE class_id = ?
```

**Required Indexes:**
- `classes`: Index on `(class_id)` - likely already exists as PRIMARY KEY

### Query 7: Get Subject Info
**File:** `classes/user_track.php` (line 112)

```sql
SELECT subject_name, subject_image_id FROM subjects WHERE subject_id = ?
```

**Required Indexes:**
- `subjects`: Index on `(subject_id)` - likely already exists as PRIMARY KEY

### Query 8: Get Streaks (Duch Printing)
**File:** `mobile/streaks/classes/class.streaks.php` (line 29-48)

```sql
SELECT st.*, dt.name, dt.cat, dt.streak_duch_cat, dt.streak_duch_name
FROM streak_tasks st
JOIN date_tasks dt USING (streak_id)
JOIN users u USING (user_id)
JOIN user_tracks ut USING (user_id)
JOIN date_tasks_missions dtm USING (date_tasks_mission_id, school_type_id, track_id, level)
WHERE st.user_id = ?
  AND dt.streak_show = 1
GROUP BY st.streak_id
```

**Required Indexes:**
- `streak_tasks`: Index on `(user_id)` for WHERE clause
- `date_tasks`: Index on `(streak_id, streak_show)` for JOIN and filtering
- `date_tasks_missions`: Index on `(date_tasks_mission_id, school_type_id, track_id, level)` for JOIN

### Query 9: Get Streak Marks (Duch Printing)
**File:** `mobile/streaks/classes/class.streaks.php` (line 120-127)

```sql
SELECT DISTINCT mark_date
FROM date_tasks_marks dtm
JOIN date_tasks dt USING (date_task_id)
WHERE dt.streak_id = ?
  AND dtm.user_id = ?
  AND dtm.mark_date >= ?
  AND dtm.mark_date <= ?
ORDER BY mark_date DESC
```

**Required Indexes:**
- `date_tasks_marks`: Index on `(user_id, mark_date)` for WHERE and ORDER BY
- `date_tasks`: Index on `(date_task_id, streak_id)` for JOIN

### Query 10: Get Parsha (Duch/Mission Printing)
**File:** `mission_report/classes/missionDisplay.php` (line 52-56)

```sql
SELECT name FROM parshos WHERE start = ? AND end = ?
```

**Required Indexes:**
- `parshos`: Index on `(start, end)` for WHERE clause

### Query 11: Get Sponsorships (Duch Printing)
**File:** `mission_report/classes/missionDisplay.php` (line 377)

```sql
SELECT * FROM sponsorships WHERE start_date >= ? AND end_date <= ? LIMIT 1
```

**Required Indexes:**
- `sponsorships`: Index on `(start_date, end_date)` for date range query

### Query 12: Get Date Tasks Marks (Duch Printing - Accomplished Tasks)
**File:** `db.php` (line 558) - Used in duch printing for accomplished tasks timeline

```sql
SELECT ... FROM date_tasks_marks dtm
LEFT JOIN date_tasks USING (date_task_id)
LEFT JOIN date_tasks_missions USING (date_tasks_mission_id)
LEFT JOIN subjects USING (subject_id)
WHERE mark_inactive = 0
  AND user_id = ?
  AND mark_date >= ? AND mark_date <= ?
```

**Required Indexes:**
- `date_tasks_marks`: Index on `(user_id, mark_date, mark_inactive)` for WHERE clause
- `date_tasks`: Index on `(date_task_id)` for JOIN (likely already exists as PRIMARY KEY)
- `date_tasks_missions`: Index on `(date_tasks_mission_id)` for JOIN (likely already exists as PRIMARY KEY)

### Query 13: Get Medal Marks (Duch Printing)
**File:** `db.php` (line 543) - Used in duch printing for medal awards timeline

```sql
SELECT ... FROM medal_marks
LEFT JOIN medals USING (medal_ord)
LEFT JOIN subjects USING (subject_id)
WHERE user_id = ?
  AND date_awarded >= ? AND date_awarded <= ?
```

**Required Indexes:**
- `medal_marks`: Index on `(user_id, date_awarded)` for WHERE clause
- `medals`: Index on `(medal_ord)` for JOIN (likely already exists as PRIMARY KEY)

### Query 14: Get Rank Marks by Date Range (Duch Printing)
**File:** `db.php` (line 547) - Used in duch printing for rank promotions timeline

```sql
SELECT ... FROM rank_marks
LEFT JOIN ranks USING (rank_ord)
WHERE user_id = ?
  AND date_promoted >= ? AND date_promoted <= ?
```

**Required Indexes:**
- `rank_marks`: Index on `(user_id, date_promoted)` for WHERE clause (in addition to existing index)

### Query 15: Get Army-Wide Tehillim Quotas (Shabbos Mevorchim)
**File:** `class.shabbosMevorchim.php` (line 279-297)

```sql
SELECT sum(dt.quantity) AS total
FROM date_tasks dt
JOIN date_tasks_missions dtm USING (date_tasks_mission_id)
JOIN user_tracks ut USING (track_id, level, subject_id)
JOIN users u USING (user_id)
JOIN schools s USING (school_id)
JOIN classes c USING (class_id)
WHERE ut.subject_id = 1
  AND ut.enrolled = 1
  AND dtm.start_date = ?
  AND dtm.end_date = ?
  AND dtm.school_type_id = u.school_type_id
  AND dt.grid_id = ?
  AND s.school_era IS NULL
  AND c.class_era = 0
  AND u.user_registered > 0
  AND u.school_id != 612
  AND u.user_id = ut.user_id
  AND u.lang_id = dtm.lang_id
```

**Required Indexes:**
- `date_tasks`: Index on `(grid_id, date_tasks_mission_id)` for WHERE and JOIN
- `date_tasks_missions`: Index on `(date_tasks_mission_id, start_date, end_date, school_type_id, lang_id)` for JOIN and WHERE
- `user_tracks`: Index on `(user_id, track_id, level, subject_id, enrolled)` for JOIN and WHERE
- `users`: Index on `(user_id, school_id, user_registered, school_type_id, lang_id)` for JOIN and WHERE
- `classes`: Index on `(class_id, class_era)` for JOIN and WHERE
- `schools`: Index on `(school_id, school_era)` for JOIN and WHERE

### Query 16: Get Army-Wide Tehillim Done (Shabbos Mevorchim)
**File:** `class.shabbosMevorchim.php` (line 301-307)

```sql
SELECT sum(dtm.done_qty) AS total
FROM date_tasks_marks dtm
JOIN date_tasks dt USING (date_task_id)
JOIN date_tasks_missions dtmm USING (date_tasks_mission_id)
WHERE dtmm.start_date = ?
  AND dtmm.end_date = ?
  AND dt.grid_id = ?
```

**Required Indexes:**
- `date_tasks_marks`: Index on `(date_task_id)` for JOIN (likely already exists)
- `date_tasks`: Index on `(date_task_id, grid_id)` for JOIN and WHERE
- `date_tasks_missions`: Index on `(date_tasks_mission_id, start_date, end_date)` for JOIN and WHERE

### Query 17: Get Tehillim Backups (Shabbos Mevorchim)
**File:** `class.shabbosMevorchim.php` (line 317-320, 878-883, 983-987)

```sql
SELECT IFNULL(sum(tb.done_qty), 0) AS total
FROM tehillim_backups tb
WHERE tb.sm_date = ?
  AND tb.grid_id = ?
  [AND tb.user_id = ?]  -- Optional for per-user queries
  [AND u.class_id = ?]  -- Optional for per-class queries (with JOIN users)
```

**Required Indexes:**
- `tehillim_backups`: Index on `(sm_date, grid_id, user_id)` for WHERE clause
- `tehillim_backups`: Index on `(sm_date, grid_id)` for army/school-wide queries

### Query 18: Get Class Tehillim Quotas (Shabbos Mevorchim)
**File:** `class.shabbosMevorchim.php` (line 837-852)

```sql
SELECT sum(dt.quantity) AS total
FROM date_tasks dt
JOIN date_tasks_missions dtm USING (date_tasks_mission_id)
JOIN user_tracks ut USING (track_id, level, subject_id)
JOIN users u USING (user_id)
JOIN classes c ON (c.class_id = u.class_id)
WHERE u.class_id = ?
  AND ut.subject_id = 1
  AND dtm.start_date = ?
  AND dtm.end_date = ?
  AND dt.grid_id = ?
  AND dtm.school_type_id = u.school_type_id
  AND u.user_registered > 0
  AND ut.enrolled = 1
  AND u.user_id = ut.user_id
  AND u.lang_id = dtm.lang_id
```

**Required Indexes:**
- Same as Query 15, but with emphasis on `class_id` filtering

### Query 19: Get Student Tehillim Quota (Shabbos Mevorchim)
**File:** `class.shabbosMevorchim.php` (line 949-963)

```sql
SELECT dt.quantity AS total, dt.date_task_id
FROM date_tasks dt
JOIN date_tasks_missions dtm USING (date_tasks_mission_id)
JOIN user_tracks ut USING (track_id, level, subject_id)
WHERE dtm.subject_id = 1
  AND dtm.start_date = ?
  AND dtm.end_date = ?
  AND dt.grid_id = ?
  AND dtm.school_type_id = ?
  AND ut.user_id = ?
  AND dtm.lang_id = ?
  AND ut.enrolled = 1
```

**Required Indexes:**
- `date_tasks`: Index on `(grid_id, date_tasks_mission_id)` for WHERE and JOIN
- `date_tasks_missions`: Index on `(date_tasks_mission_id, subject_id, start_date, end_date, school_type_id, lang_id)` for JOIN and WHERE
- `user_tracks`: Index on `(user_id, track_id, level, subject_id, enrolled)` for JOIN and WHERE

### Query 20: Get Student Tehillim Done (Shabbos Mevorchim)
**File:** `class.shabbosMevorchim.php` (line 966-973)

```sql
SELECT MAX(dtm.done_qty) AS total
FROM date_tasks_marks dtm
JOIN date_tasks dt USING (date_task_id)
JOIN date_tasks_missions dtmm USING (date_tasks_mission_id)
WHERE dtm.user_id = ?
  AND dtmm.start_date = ?
  AND dtmm.end_date = ?
  AND dt.grid_id = ?
```

**Required Indexes:**
- `date_tasks_marks`: Index on `(user_id, date_task_id)` for WHERE and JOIN
- `date_tasks`: Index on `(date_task_id, grid_id)` for JOIN and WHERE
- `date_tasks_missions`: Index on `(date_tasks_mission_id, start_date, end_date)` for JOIN and WHERE

### Query 21: Get Users with Tehillim Tracks (Shabbos Mevorchim)
**File:** `class.shabbosMevorchim.php` (line 1176-1181)

```sql
SELECT u.*, ut.track_id, ut.level
FROM users u
JOIN user_tracks ut USING (user_id)
WHERE class_id = ?
  AND user_registered > 0
  AND ut.subject_id = 1
ORDER BY last, first
```

**Required Indexes:**
- `users`: Index on `(class_id, user_registered, last, first)` for WHERE and ORDER BY
- `user_tracks`: Index on `(user_id, subject_id)` for JOIN and WHERE

### Query 22: Get Chidon Students (Chidon Testing)
**File:** `chidonTests/class.chidonTests.php` (line 99-124)

```sql
SELECT tc.*, tci.highest_track, u.first, u.last, u.gender, u.user_serial,
       c.class_id, c.class_grade, c.class_sub, s.school_id, s.school_name
FROM th_chidon tc
JOIN users u USING (user_id)
JOIN schools s ON s.school_id = u.school_id
JOIN classes c ON c.class_id = u.class_id
LEFT JOIN th_chidon_info tci ON tc.year = tci.year AND tc.user_id = tci.user_id
WHERE tc.year = ?
  [AND u.school_id = ?]
  [AND u.class_id = ?]
  [AND u.user_id = ?]
  [AND u.gender = ?]
ORDER BY s.school_name, class_grade, class_sub, last, first
```

**Required Indexes:**
- `th_chidon`: Index on `(year, user_id)` for WHERE clause
- `th_chidon_info`: Index on `(year, user_id)` for LEFT JOIN
- `users`: Index on `(user_id, school_id, class_id, gender)` for JOIN and WHERE
- `schools`: Index on `(school_id)` for JOIN (likely already exists as PRIMARY KEY)
- `classes`: Index on `(class_id, class_grade, class_sub)` for JOIN and ORDER BY

### Query 23: Get Chidon Scores (Chidon Testing)
**File:** `chidonTests/class.chidonTests.php` (line 224-232)

```sql
SELECT * FROM th_chidon_marks
WHERE th_chidon_id = ?
```

**Required Indexes:**
- `th_chidon_marks`: Index on `(th_chidon_id)` for WHERE clause

### Query 24: Get Highest Track (Chidon Testing)
**File:** `chidonTests/class.chidonTests.php` (line 644-651)

```sql
SELECT highest_track FROM th_chidon_info
WHERE year = ? AND user_id = ?
```

**Required Indexes:**
- `th_chidon_info`: Index on `(year, user_id)` for WHERE clause (already needed for Query 22)

### Query 25: Get Passing Averages (Chidon Testing)
**File:** `chidonTests/class.chidonTests.php` (line 740-752)

```sql
SELECT * FROM chidon_passing_avgs
WHERE year = ?
  AND (
    user_id = ?
    OR school_id = (SELECT school_id FROM users WHERE user_id = ?)
    OR class_id = (SELECT class_id FROM users WHERE user_id = ?)
  )
```

**Required Indexes:**
- `chidon_passing_avgs`: Index on `(year, user_id, school_id, class_id)` for WHERE clause
- `users`: Index on `(user_id, school_id, class_id)` for subqueries

### Query 26: Get Test Levels (Chidon Testing)
**File:** `chidonTests/class.chidonTests.php` (line 778-791)

```sql
SELECT * FROM chidon_test_levels
WHERE year = ?
  AND test_type = ?
  AND (
    user_id = ?
    OR school_id = (SELECT school_id FROM users WHERE user_id = ?)
    OR class_id = (SELECT class_id FROM users WHERE user_id = ?)
  )
```

**Required Indexes:**
- `chidon_test_levels`: Index on `(year, test_type, user_id, school_id, class_id)` for WHERE clause
- `users`: Index on `(user_id, school_id, class_id)` for subqueries

### Query 27: Get Chidon Brochures (Chidon Shipping)
**File:** `chidon_shipping/class.chidonShipping.php` (line 68-75)

```sql
SELECT * FROM th_chidon tc
JOIN users u USING (user_id)
WHERE tc.year = ?
  [AND u.gender = ?]
  [AND u.school_id = ?]
```

**Required Indexes:**
- `th_chidon`: Index on `(year, user_id)` for WHERE clause
- `users`: Index on `(user_id, gender, school_id)` for JOIN and WHERE

### Query 28: Get Chidon Guides (Chidon Shipping)
**File:** `chidon_shipping/class.chidonShipping.php` (line 105-112, 129-136)

```sql
SELECT * FROM th_chidon tc
JOIN users u USING (user_id)
WHERE tc.year = ?
  [AND tc.khk_reg = 1]
  [AND u.gender = ?]
  [AND u.school_id = ?]
```

**Required Indexes:**
- `th_chidon`: Index on `(year, user_id, khk_reg)` for WHERE clause
- `users`: Index on `(user_id, gender, school_id)` for JOIN and WHERE

### Query 29: Get Chidon Gifts (Chidon Shipping)
**File:** `chidon_shipping/class.chidonShipping.php` (line 724-730)

```sql
SELECT * FROM th_chidon tc
JOIN users u USING (user_id)
WHERE tc.date_paid > 0
  AND tc.year = ?
  [AND u.gender = ?]
  [AND u.school_id = ?]
```

**Required Indexes:**
- `th_chidon`: Index on `(year, date_paid, user_id)` for WHERE clause
- `users`: Index on `(user_id, gender, school_id)` for JOIN and WHERE

### Query 30: Get Chidon Awards (Chidon Shipping)
**File:** `chidon_shipping/class.chidonShipping.php` (line 844-855)

```sql
SELECT *, tcf.khk as khk_final
FROM th_chidon_finals tcf
JOIN th_chidon tc USING (user_id, year)
JOIN users u USING (user_id)
WHERE tcf.year = ?
  AND (track_1 > 0 OR track_2 > 0 OR track_3 > 0 OR track_4 > 0 OR tcf.khk > 0)
  [AND u.gender = ?]
  [AND u.school_id = ?]
GROUP BY user_id
```

**Required Indexes:**
- `th_chidon_finals`: Index on `(year, user_id)` for WHERE and JOIN
- `th_chidon`: Index on `(user_id, year)` for JOIN

### Query 31: Get Chidon Shipping Report (Chidon Shipping)
**File:** `chidon_shipping/report.php` (line 138-166)

```sql
SELECT u.user_id, u.school_id, [fields]
FROM users u
[JOIN th_chidon tc USING (user_id)]
[JOIN schools s ON s.school_id = u.school_id]
[JOIN classes c ON c.class_id = u.class_id]
[JOIN chidon_user_prizes cup USING (user_id)]
[JOIN chidon_prizes cp USING (chidon_prize_id)]
WHERE u.school_id IN (?)
  [AND tc.year = ?]
  [AND u.gender = ?]
ORDER BY u.school_id, c.class_grade, c.class_sub, u.last, u.first
```

**Required Indexes:**
- `th_chidon`: Index on `(user_id, year)` for JOIN and WHERE
- `chidon_user_prizes`: Index on `(user_id)` for JOIN
- `chidon_prizes`: Index on `(chidon_prize_id)` for JOIN (likely already exists as PRIMARY KEY)
- `users`: Index on `(school_id, gender, last, first)` for WHERE and ORDER BY
- `classes`: Index on `(class_id, class_grade, class_sub)` for JOIN and ORDER BY

### Query 32: Get Registration Charges for Chidon (Chidon Shipping)
**File:** `reports/chidon/combined.php` (line 14-40)

```sql
SELECT amount, date, s.*, logo, first, last, c.class_grade, c.class_sub, tc.book,
       rc.user_id, rc.study_guide_shipped, rc.book_shipped, rc.type, u.user_serial
FROM registration_charges rc
JOIN users u USING (user_id)
JOIN schools s ON s.school_id = u.school_id
JOIN classes c ON c.class_id = u.class_id
JOIN th_chidon tc ON (tc.user_id = rc.user_id AND tc.year = rc.year)
WHERE (type LIKE '%LDE%' OR type LIKE '%YB%')
  AND rc.year = ?
  [AND rc.date >= ? AND rc.date <= ?]
  [AND (type LIKE '%LDE%' AND study_guide_shipped = 0) OR (type LIKE '%YB%' AND book_shipped = 0)]
  AND rc.school_id IN (?)
ORDER BY school_name, c.class_grade, c.class_sub, last, first
```

**Required Indexes:**
- `registration_charges`: Index on `(year, user_id, type, date, school_id)` for WHERE clause
- `registration_charges`: Index on `(year, type, study_guide_shipped, book_shipped)` for shipping status filtering
- `th_chidon`: Index on `(user_id, year)` for JOIN

### Query 33: Get Recruitment Credits (Chidon Shipping)
**File:** `chidon_shipping/class.chidonShipping.php` (line 284-291)

```sql
SELECT u.user_id, u.gender, count(*) as credits
FROM users u
JOIN th_chidon tc ON u.user_serial = tc.recruited_by
WHERE year = ?
  [AND u.gender = ?]
  [AND u.school_id = ?]
GROUP BY u.user_id
```

**Required Indexes:**
- `th_chidon`: Index on `(year, recruited_by)` for JOIN and WHERE
- `users`: Index on `(user_serial, user_id, gender, school_id)` for JOIN and WHERE

### Query 34: Get Chidon Eligibility History (Chidon Testing)
**File:** `chidonTests/class.chidonTests.php` (line 989-991, 1010-1016)

```sql
SELECT * FROM th_chidon
WHERE user_id = ?
  AND year >= ?
```

**Required Indexes:**
- `th_chidon`: Index on `(user_id, year)` for WHERE clause

```sql
SELECT * FROM th_chidon_info
WHERE year >= ?
```

**Required Indexes:**
- `th_chidon_info`: Index on `(year, user_id)` for WHERE clause

### Query 35: Get Medals by Subject and Date Range (Medals System)
**File:** `classes/user.php` (line 831-854, 856-880)

```sql
SELECT * FROM medal_marks AS mm
JOIN subjects AS s USING (subject_id)
JOIN medals AS m USING (medal_ord)
WHERE user_id = ?
  AND date_awarded >= ? AND date_awarded <= ?
  [AND subject_id = ?]
  [AND mm.date_received IS NULL]
  [AND mm.date_received IS NOT NULL]
```

**Required Indexes:**
- `medal_marks`: Index on `(user_id, subject_id, date_awarded, date_received)` for WHERE clause with optional filters

### Query 36: Get Ranks by Date Range with Filters (Ranks System)
**File:** `classes/user.php` (line 882-907, 909-935)

```sql
SELECT * FROM rank_marks AS rm
JOIN ranks AS r USING (rank_ord)
WHERE user_id = ?
  AND rm.date_promoted >= ? AND rm.date_promoted <= ?
  [AND rm.date_card_received IS NOT NULL]
  [AND rm.date_card_received IS NULL]
  [AND rm.date_book_received IS NOT NULL]
  [AND rm.date_book_received IS NULL]
  [AND rm.date_promoted NOT IN (2455817,2455772)]
```

**Required Indexes:**
- `rank_marks`: Index on `(user_id, date_promoted, date_card_received, date_book_received)` for WHERE clause with optional filters

### Query 37: Check if Medal Was Awarded (Medals System)
**File:** `classes/user_track.php` (line 392-400)

```sql
SELECT count(*) AS awarded FROM medal_marks
WHERE user_id = ?
  AND subject_id = ?
  AND medal_ord = ?
```

**Required Indexes:**
- `medal_marks`: Index on `(user_id, subject_id, medal_ord)` for WHERE clause (covers exact match lookup)

### Query 38: Get Rank Marks by Rank Ord (Ranks System)
**File:** `classes/user.php` (line 817-829)

```sql
SELECT date_promoted FROM rank_marks
WHERE rank_ord = ?
  AND user_id = ?
```

**Required Indexes:**
- `rank_marks`: Index on `(rank_ord, user_id)` for WHERE clause (alternative to user_id-first index)

### Query 39: Get User Medals Grouped by Subject (Missions Page)
**File:** `missions.php` (line 50)

```sql
SELECT subject_id, medal_ord, subject_name, medal_name, profile_photo_id
FROM (
  SELECT MAX(medal_ord) medal_ord, subject_id
  FROM medal_marks
  WHERE user_id = ?
  GROUP BY subject_id
) medal_cur
JOIN subjects USING (subject_id)
JOIN medals USING (medal_ord)
JOIN medals_subjects USING (subject_id, medal_ord)
```

**Required Indexes:**
- `medal_marks`: Index on `(user_id, subject_id, medal_ord DESC)` for MAX aggregation and GROUP BY
- `medals_subjects`: Index on `(subject_id, medal_ord)` for JOIN

### Query 40: Get Rank Info - Count Total Medals (Ranks System)
**File:** `classes/user.php` (line 945-970)

```sql
-- Get highest rank
SELECT rank_ord FROM rank_marks
WHERE user_id = ?
ORDER BY rank_ord DESC LIMIT 1

-- Count total medals
SELECT count(*) as total FROM medal_marks
WHERE user_id = ?
```

**Required Indexes:**
- `rank_marks`: Index on `(user_id, rank_ord DESC)` for ORDER BY LIMIT (already covered by Query 2)
- `medal_marks`: Index on `(user_id)` for COUNT aggregation

### Query 47: Get Auction Winners for Shipping (Auction System)
**File:** `reports/shipping/functions/get_auctions.php` (line 3-11)

```sql
SELECT user_id, auction_id, prize_id, quantity, prize_name, auction_name, users.school_id, shipped, shipment_id, shipments.name as shipment_name
FROM auction_winners
JOIN auctions USING (auction_id)
JOIN prizes_auction USING (prize_id)
LEFT JOIN shipments USING (shipment_id)
JOIN users USING (user_id)
WHERE auction_ran = 1
  AND auction_run_date >= ? AND auction_run_date <= ?
  [AND users.school_id = ?]
```

**Required Indexes:**
- `auction_winners`: Index on `(auction_id, user_id, prize_id)` for JOIN and WHERE
- `auctions`: Index on `(auction_id, auction_ran, auction_run_date)` for JOIN and WHERE
- `users`: Index on `(user_id, school_id)` for JOIN and WHERE

### Query 48: Get Auction Winners by Auction ID (Auction System)
**File:** `admin_auction_run.php` (line 100-112)

```sql
SELECT aw.*, users.*, school_name, school_number, classes.class_grade, classes.class_sub, prizes_auction.prize_name, prize_number
FROM auction_winners AS aw
JOIN users USING (user_id)
JOIN prizes_auction USING (prize_id)
JOIN auctions USING (auction_id)
LEFT JOIN schools ON (users.school_id=schools.school_id)
LEFT JOIN classes USING (class_id)
WHERE auction_id = ?
  AND auctions.auction_ran = 1
  [AND school_id = ?]
ORDER BY users.last
```

**Required Indexes:**
- `auction_winners`: Index on `(auction_id, user_id, prize_id)` for WHERE and JOIN (already covered by Query 47)
- `auctions`: Index on `(auction_id, auction_ran)` for WHERE clause

### Query 49: Get Available Auctions (Auction System)
**File:** `admin_auction_run.php` (line 125-131), `db.php` (line 580)

```sql
SELECT * FROM auctions
WHERE auction_ran = 0
  [AND school_id = ?]
  [AND school_id IS NULL]
  [AND (auction_run_date IS NULL OR auction_run_date > ?)]
ORDER BY auction_date DESC
```

**Required Indexes:**
- `auctions`: Index on `(auction_ran, school_id, auction_date DESC)` for WHERE and ORDER BY
- `auctions`: Index on `(auction_ran, auction_run_date)` for date filtering

### Query 50: Get Auction Prizes (Auction System)
**File:** `auction/class.auction.php` (line 50-62, 68-81)

```sql
-- Grand prizes
SELECT pa.prize_id
FROM prizes_auction pa
JOIN auction_prizes ap USING (prize_id)
WHERE auction_id = ?
  AND pa.prize_points >= 72
  AND ap.available = 1

-- Regular prizes
SELECT pa.prize_id, pa.prize_name, ap.available
FROM prizes_auction pa
JOIN auction_prizes ap USING (prize_id)
WHERE auction_id = ?
  AND pa.prize_points < 72
  AND ap.available > 1
```

**Required Indexes:**
- `auction_prizes`: Index on `(auction_id, prize_id, available)` for WHERE clause
- `prizes_auction`: Index on `(prize_id, prize_points)` for JOIN and WHERE

### Query 51: Get Raffle Winners (Raffle System)
**File:** `raffles/shared/classes/Raffle.php` (line 403-436)

```sql
SELECT [prize fields], users.user_id, users.first, users.last, users.gender, schools.school_id, schools.school_name, [fields], classes.class_sub, classes.class_grade, [admin fields]
FROM raffle_winners
[JOIN prizes_auction USING (prize_id)]
[JOIN prizes USING (prize_id)]
JOIN users USING (user_id)
JOIN schools ON users.school_id = schools.school_id
JOIN classes USING (class_id)
LEFT JOIN admin_auths ON user_id = id
LEFT JOIN admins USING (admin_id)
WHERE raffle_winners.raffle_id = ?
  [AND users.school_id = ?]
GROUP BY user_id
ORDER BY [various sorting options]
```

**Required Indexes:**
- `raffle_winners`: Index on `(raffle_id, user_id, prize_id)` for WHERE and GROUP BY
- `users`: Index on `(user_id, school_id, gender)` for JOIN and WHERE
- `admin_auths`: Index on `(id)` for LEFT JOIN

### Query 52: Get Raffles by Date (Raffle System)
**File:** `raffles/tasks/run_raffle/run_raffle.php` (line 60)

```sql
SELECT * FROM raffles
WHERE DATE(run_date) = CURDATE()
  AND date_ran IS NULL
```

**Required Indexes:**
- `raffles`: Index on `(run_date, date_ran)` for WHERE clause

### Query 53: Get Raffles by Type and Year (Raffle System)
**File:** `mobile/api/raffles/functions.php` (line 258)

```sql
SELECT * FROM raffles
WHERE type = ?
  AND year = ?
```

**Required Indexes:**
- `raffles`: Index on `(type, year)` for WHERE clause

### Query 54: Get Raffle Winners for Shipping (Raffle System)
**File:** `raffles/shared/shipping/functions/getWinners.php` (line 56-66)

```sql
SELECT user_id, raffle_id, raffle_winners.prize_id, users.school_id, first, last, shipped, class_grade, class_sub, prizes.name as prize, raffles.name as raffle, school_name
FROM raffle_winners
JOIN users USING (user_id)
JOIN raffles USING (raffle_id)
JOIN (SELECT prize_id, 'weekly' AS type, name FROM prizes UNION SELECT prize_id, 'monthly' AS type, prize_name AS name FROM prizes_auction) prizes
  ON prizes.prize_id = raffle_winners.prize_id AND prizes.type = raffles.type
JOIN schools USING (school_id)
JOIN classes USING (class_id)
WHERE [filter conditions]
ORDER BY [sorting]
```

**Required Indexes:**
- `raffle_winners`: Index on `(raffle_id, user_id, prize_id)` for JOIN (already covered by Query 51)
- `raffles`: Index on `(raffle_id, type)` for JOIN
- `users`: Index on `(user_id, school_id)` for JOIN

### Query 55: Check Raffle Winner (Raffle System)
**File:** `mobile/api/raffles/functions.php` (line 136)

```sql
SELECT * FROM raffle_winners
WHERE raffle_id = ?
  AND user_id = ?
```

**Required Indexes:**
- `raffle_winners`: Index on `(raffle_id, user_id)` for WHERE clause (already covered by Query 51)

### Query 56: Get Achievement Cards (Achievement Card System)
**File:** `v2/application/models/AchievementCards.php` (line 20-68)

```sql
SELECT * FROM achievement_cards
WHERE [various column filters]
ORDER BY modified DESC, created DESC
```

**Required Indexes:**
- `achievement_cards`: Index on `(modified DESC, created DESC)` for ORDER BY
- `achievement_cards`: Index on `(card_serial)` for card lookup
- `achievement_cards`: Index on `(institution_id, campaign_id, card_type)` for filtering

### Query 57: Validate Achievement Card (Achievement Card System)
**File:** `v2/application/models/AchievementCards.php` (line 107-127)

```sql
SELECT * FROM achievement_cards
WHERE card_serial = ?
```

**Required Indexes:**
- `achievement_cards`: Index on `(card_serial)` for WHERE clause (already covered by Query 56)

### Query 58: Update Achievement Card Status (Achievement Card System)
**File:** `v2/application/models/AchievementCards.php` (line 144-151)

```sql
UPDATE achievement_cards
SET status = 'scanned'
WHERE card_serial = ?
```

**Required Indexes:**
- `achievement_cards`: Index on `(card_serial)` for WHERE clause (already covered by Query 56)

### Query 59: Get Achievement Card Templates (Achievement Card System)
**File:** `v2/application/models/Campaigns.php` (line 2718-2739)

```sql
SELECT * FROM achievement_cards
INNER JOIN campaigns ON achievement_cards.campaign_id = campaigns.campaign_id
WHERE achievement_cards.institution_id = ?
  AND achievement_cards.created_by = ?
  AND achievement_cards.card_type = ?
```

**Required Indexes:**
- `achievement_cards`: Index on `(institution_id, created_by, card_type, campaign_id)` for WHERE and JOIN
- `campaigns`: Index on `(campaign_id)` for JOIN (likely already exists as PRIMARY KEY)

### Query 60: Get Raffle Tickets (Raffle Ticket System)
**File:** `auction/class.raffleTicket.php` (line 25-44)

```sql
SELECT SUM(mission_count) as total
FROM date_tasks_mission_marks
WHERE mark_date >= ?
  AND mark_date <= ?
  AND user_id = ?
```

**Required Indexes:**
- `date_tasks_mission_marks`: Index on `(user_id, mark_date)` for WHERE clause (already covered by Query 12)

### Query 61: Get Shipments (Shipping Reports)
**File:** `reports/shipping/classes/Shipment.php` (line 62-73), `reports/shipping/shipments/ajax/report.php` (line 57)

```sql
SELECT * FROM shipments
WHERE [school_id = ?]
  [AND (date_shipped > ? OR date_shipped IS NULL)]
  [AND (date_shipped < ? OR date_shipped IS NULL)]
  [AND status IN (?)]
```

**Required Indexes:**
- `shipments`: Index on `(school_id, date_shipped, status)` for WHERE clause filtering

### Query 62: Get Shipment Details Count (Shipping Reports)
**File:** `reports/shipping/classes/Shipment.php` (line 277-278)

```sql
SELECT COUNT(*) AS total FROM shipment_details
WHERE shipment_id = ?
```

**Required Indexes:**
- `shipment_details`: Index on `(shipment_id)` for WHERE clause

### Query 63: Get Shipment Details with Item Names (Shipping Reports)
**File:** `reports/shipping/classes/Shipment.php` (line 240-259)

```sql
SELECT shipment_details.*, name FROM shipment_details
[JOIN various tables based on type]
WHERE shipment_id = ?
ORDER BY name, item
```

**Required Indexes:**
- `shipment_details`: Index on `(shipment_id, type, item_id, item_ord, item_extra_id)` for WHERE and JOIN

### Query 64: Get Tracking Numbers (Shipping Reports)
**File:** `reports/shipping/classes/Shipment.php` (line 290-291), `reports/shipping/functions/get_tracking_numbers.php` (line 18)

```sql
SELECT * FROM tracking_numbers
WHERE shipment_id = ?
[AND school_id = ?]
```

**Required Indexes:**
- `tracking_numbers`: Index on `(shipment_id)` for WHERE clause
- `tracking_numbers`: Index on `(school_id)` for school filtering

### Query 65: Get Lines Learned by Campaign and User (Tanya System)
**File:** `class.tanya.php` (line 53), `class.balPehCampaign.php` (line 112, 130, 151, 172, 177, 185)

```sql
SELECT lines_learned, mission_sheet_amount FROM lines_learned
WHERE campaign_id = ?
  AND user_id = ?
```

**Required Indexes:**
- `lines_learned`: Index on `(campaign_id, user_id)` for WHERE clause

### Query 66: Get Total Lines Learned by School/Class (Tanya System)
**File:** `class.balPehCampaign.php` (line 98-103)

```sql
SELECT sum(lines_learned) as total FROM lines_learned
WHERE user_id IN (SELECT user_id FROM users WHERE school_id = ? [OR class_id = ?] AND (user_registered > 0 OR yan = 1))
  AND campaign_id = ?
```

**Required Indexes:**
- `lines_learned`: Index on `(campaign_id, user_id, lines_learned)` for WHERE and SUM aggregation
- `users`: Index on `(school_id, user_registered, yan)` for subquery filtering
- `users`: Index on `(class_id, user_registered, yan)` for class filtering

### Query 67: Get Tanya User Info (Tanya System)
**File:** `db.php` (line 685), `admin_stats.php` (line 464)

```sql
SELECT track, year, lines_goal, lines_done, lines_offset, tanya_start_date, length_days, length_days_offset, medal_ord
FROM tanya_users
JOIN tanya_goals USING (track)
WHERE user_id = ?
```

**Required Indexes:**
- `tanya_users`: Index on `(user_id)` for WHERE clause
- `tanya_goals`: Index on `(track)` for JOIN (likely already exists as PRIMARY KEY)

### Query 68: Get Line Campaigns (Tanya System)
**File:** `class.tanya.php` (line 10-14)

```sql
SELECT id FROM line_campaigns
WHERE type = ?
ORDER BY id DESC LIMIT 1
```

**Required Indexes:**
- `line_campaigns`: Index on `(type, id DESC)` for WHERE and ORDER BY LIMIT

### Query 69: Get Mivtzoim Campaigns (Mivtzoim System)
**File:** `mivtzoim/classes/mivtzoim.php` (line 350-354)

```sql
SELECT * FROM mivtzoim
```

**Required Indexes:**
- `mivtzoim`: No specific index needed (full table scan for configuration)

### Query 70: Get Mivtzoim Tasks by Short Name (Mivtzoim System)
**File:** `mivtzoim/classes/mivtzoim.php` (line 134-156)

```sql
SELECT DISTINCT start_date, end_date, grid_id, name, quantity
FROM date_tasks dt
JOIN date_tasks_missions dtm USING (date_tasks_mission_id)
WHERE short_name = ?
  AND dtm.start_date >= ? AND dtm.end_date <= ?
  AND dtm.subject_id = ?
  AND dtm.lang_id = ?
  AND dtm.school_type_id NOT IN (14,15)
ORDER BY start_date
```

**Required Indexes:**
- `date_tasks`: Index on `(short_name, date_tasks_mission_id)` for WHERE clause
- `date_tasks_missions`: Index on `(date_tasks_mission_id, start_date, end_date, subject_id, lang_id, school_type_id)` for JOIN and WHERE

### Query 71: Get Mivtzoim Marks by Grid IDs (Mivtzoim System)
**File:** `mivtzoim/classes/mivtzoim.php` (line 172-194)

```sql
SELECT DISTINCT dtmm.start_date, dtmm.end_date, dt.grid_id, dtm.user_id, dtm.done_qty
FROM date_tasks dt
JOIN date_tasks_missions dtmm USING (date_tasks_mission_id)
LEFT JOIN date_tasks_marks dtm USING (date_task_id)
WHERE dt.grid_id IN (?)
  AND dtmm.start_date >= ? AND dtmm.end_date <= ?
```

**Required Indexes:**
- `date_tasks`: Index on `(grid_id, date_tasks_mission_id)` for WHERE clause (already covered by Query 15)
- `date_tasks_missions`: Index on `(date_tasks_mission_id, start_date, end_date)` for JOIN and WHERE (already covered)

### Query 72: Calculate Mivtzoim User Marks (Mivtzoim System)
**File:** `mivtzoim/classes/mivtzoim.php` (line 657-694)

```sql
SELECT u.user_id, SUM(dtm.done_qty) AS total
FROM date_tasks dt
JOIN date_tasks_marks dtm USING (date_task_id)
JOIN users u USING (user_id)
WHERE dt.grid_id IN (?)
  AND dtm.mark_date >= ? AND dtm.mark_date <= ?
  [AND u.school_id = ?]
GROUP BY u.user_id
```

**Required Indexes:**
- `date_tasks`: Index on `(grid_id, date_task_id)` for WHERE and JOIN
- `date_tasks_marks`: Index on `(date_task_id, user_id, mark_date, done_qty)` for JOIN, WHERE, and SUM aggregation
- `users`: Index on `(user_id, school_id)` for JOIN and WHERE

### Query 73: Check Pesukim Task Completion (12 Pesukim System)
**File:** `pesukim/class.pesukim.php` (line 17-31)

```sql
SELECT * FROM date_tasks_marks
JOIN date_tasks dt USING (date_task_id)
WHERE dt.grid_id = ?
  AND user_id = ?
```

**Required Indexes:**
- `date_tasks`: Index on `(grid_id, date_task_id)` for WHERE and JOIN (already covered by Query 15)
- `date_tasks_marks`: Index on `(date_task_id, user_id)` for JOIN and WHERE (already covered by Query 72)

### Query 74: Get Pesukim Recruits (12 Pesukim System)
**File:** `pesukim/class.pesukim.php` (line 215-227)

```sql
SELECT * FROM pesukim_recruits pr
JOIN users u ON u.user_id = pr.recruited_id
WHERE pr.recruiter_id = ?
```

**Required Indexes:**
- `pesukim_recruits`: Index on `(recruiter_id, recruited_id)` for WHERE and JOIN
- `users`: Index on `(user_id)` for JOIN (likely already exists as PRIMARY KEY)

### Query 75: Get Pesukim Recruiter (12 Pesukim System)
**File:** `pesukim/class.pesukim.php` (line 168-177)

```sql
SELECT recruiter_id FROM pesukim_recruits
WHERE recruited_id = ?
```

**Required Indexes:**
- `pesukim_recruits`: Index on `(recruited_id)` for WHERE clause

### Query 76: Get Pesukim Duch Recruits (12 Pesukim System)
**File:** `pesukim/class.pesukim.php` (line 158-166)

```sql
SELECT * FROM pesukim_duch_recruits
WHERE user_id = ?
```

**Required Indexes:**
- `pesukim_duch_recruits`: Index on `(user_id)` for WHERE clause

### Query 77: Get Pesukim Mechunachim (12 Pesukim System)
**File:** `pesukim/class.pesukim.php` (line 341-350)

```sql
SELECT * FROM pesukim_mechunachim
WHERE mechanech_user_id = ?
```

**Required Indexes:**
- `pesukim_mechunachim`: Index on `(mechanech_user_id)` for WHERE clause

### Query 78: Verify Pesukim Mechunach (12 Pesukim System)
**File:** `pesukim/class.pesukim.php` (line 312-327)

```sql
UPDATE pesukim_mechunachim
SET verified = 1, date_verified = NOW()
WHERE mechanech_user_id = ?
  AND mechunach_id = ?
  AND verification_code = ?
```

**Required Indexes:**
- `pesukim_mechunachim`: Index on `(mechanech_user_id, mechunach_id, verification_code)` for WHERE clause

### Query 79: Get Pesukim Minutes (12 Pesukim System)
**File:** `pesukim/class.pesukim.php` (line 379-394)

```sql
SELECT IFNULL(SUM(done_qty), 0) as total
FROM date_tasks_marks
JOIN date_tasks dt USING (date_task_id)
WHERE dt.grid_id = ?
  AND mark_date >= ? AND mark_date <= ?
  AND user_id = ?
```

**Required Indexes:**
- `date_tasks`: Index on `(grid_id, date_task_id)` for WHERE and JOIN (already covered)
- `date_tasks_marks`: Index on `(date_task_id, user_id, mark_date, done_qty)` for JOIN, WHERE, and SUM (already covered by Query 72)

### Query 80: Get Chidon Drive Children (Chidon Drive System)
**File:** `chidonOld/chidon_drive/ajax/getChildren.php` (line 29-45)

```sql
SELECT u.user_id, u.school_id, u.class_id, [fields], tc.*, 
       conf.chidon_confirmation_id as schoolConfirmed, 
       cor.open_reg as openRegForSchool,
       a.admin_id, [fields]
FROM users u 
JOIN schools s USING (school_id)
JOIN th_chidon tc USING (user_id)  
JOIN classes c ON c.class_id = u.class_id 
LEFT JOIN chidon_confirmations conf ON (u.school_id = conf.school_id AND conf.year = :year) 
LEFT JOIN chidon_open_reg cor ON (cor.school_id = u.school_id AND cor.year = :year) 
JOIN admin_auths aa ON aa.id = u.user_id 
JOIN admins a USING (admin_id) 
WHERE tc.year = :year 
  AND a.admin_id = :admin
```

**Required Indexes:**
- `th_chidon`: Index on `(year, user_id)` for WHERE clause (already covered)
- `chidon_confirmations`: Index on `(year, school_id)` for LEFT JOIN
- `chidon_open_reg`: Index on `(year, school_id)` for LEFT JOIN
- `admin_auths`: Index on `(id, admin_id, role_id)` for JOIN
- `users`: Index on `(user_id, school_id, class_id)` for JOIN (already covered)

### Query 81: Get Chidon Drive Subsidies (Chidon Drive System)
**File:** `chidonOld/chidon_drive/ajax/getChildren.php` (line 59-63)

```sql
SELECT IFNULL(SUM(subsidy_amount), 0) as raised 
FROM chidon_user_subsidies 
WHERE chidon_year = :year AND user_id = :user
```

**Required Indexes:**
- `chidon_user_subsidies`: Index on `(chidon_year, user_id, subsidy_amount)` for WHERE and SUM

### Query 82: Get Chidon Drive Personal Credit (Chidon Drive System)
**File:** `chidonOld/chidon_drive/ajax/getChildren.php` (line 65-70)

```sql
SELECT IFNULL(SUM(amount), 0) as personal_credit 
FROM registration_charges 
WHERE year = :year AND user_id = :user 
  AND type IN ('RRYSD', 'RRYDA', 'RRHVN')
```

**Required Indexes:**
- `registration_charges`: Index on `(year, user_id, type, amount)` for WHERE and SUM (partially covered by Query 32)

### Query 83: Get Keep Registration Open (Chidon Drive System)
**File:** `chidonOld/chidon_drive/ajax/getChildren.php` (line 102)

```sql
SELECT * FROM keep_reg_open WHERE year = ?
```

**Required Indexes:**
- `keep_reg_open`: Index on `(year)` for WHERE clause

### Query 84: Get Chidon Drive Donations (Chidon Drive System)
**File:** `chidonOld/chidon_drive/ajax/getDonations.php` (line 8-22)

```sql
SELECT cd.chidon_donation_id, donation_amount, donation_date,
       chidon_user_subsidy_id, user_id, subsidy_amount
FROM chidon_donations cd
JOIN chidon_user_subsidies cus ON cd.chidon_donation_id = cus.chidon_donation_id
WHERE cd.chidon_year = :year
```

**Required Indexes:**
- `chidon_donations`: Index on `(chidon_year, chidon_donation_id)` for WHERE and JOIN
- `chidon_user_subsidies`: Index on `(chidon_donation_id, chidon_year, user_id)` for JOIN

### Query 85: Get Chidon Drive Totals (Chidon Drive System)
**File:** `chidonOld/chidon_drive/reports/index.php` (line 9-16, 23-30, 39-46)

```sql
SELECT SUM(donation_amount) as donation 
FROM chidon_donations 
WHERE chidon_year = :year

SELECT SUM(rohr_subsidy) as rohr, SUM(paid) as reg
FROM th_chidon
WHERE year = :year

SELECT COUNT(*) AS total 
FROM th_chidon
WHERE year = :year AND date_paid > 0
```

**Required Indexes:**
- `chidon_donations`: Index on `(chidon_year, donation_amount)` for WHERE and SUM
- `th_chidon`: Index on `(year, rohr_subsidy, paid)` for WHERE and SUM
- `th_chidon`: Index on `(year, date_paid)` for WHERE and COUNT

### Query 86: Get Chidon Drive Family Donations (Chidon Drive System)
**File:** `chidonOld/chidon_drive/reports/index.php` (line 118-128, 221-231)

```sql
SELECT for_family_id, SUM(donation_amount) as total, a.*
FROM chidon_donations d
LEFT JOIN admins a ON a.admin_id = d.for_family_id
WHERE chidon_year = :year
GROUP BY for_family_id
```

**Required Indexes:**
- `chidon_donations`: Index on `(chidon_year, for_family_id, donation_amount)` for WHERE, GROUP BY, and SUM

### Query 87: Get Chidon Drive Children for Family (Chidon Drive System)
**File:** `chidonOld/chidon_drive/reports/index.php` (line 133-140, 236-243)

```sql
SELECT id
FROM admin_auths
WHERE admin_id = :id AND role_id = 1
```

**Required Indexes:**
- `admin_auths`: Index on `(admin_id, role_id, id)` for WHERE clause (partially covered by Query 80)

### Query 88: Get Chidon Drive Child Subsidies (Chidon Drive System)
**File:** `chidonOld/chidon_drive/reports/index.php` (line 142-154, 245-257)

```sql
SELECT u.first, IFNULL(SUM(subsidy_amount), 0) AS total, tc.rohr_subsidy, tc.paid 
FROM chidon_user_subsidies 
JOIN users u USING (user_id) 
JOIN th_chidon tc USING (user_id) 
WHERE user_id = :id AND chidon_year = :year
  AND tc.year = :year
```

**Required Indexes:**
- `chidon_user_subsidies`: Index on `(user_id, chidon_year, subsidy_amount)` for WHERE and SUM (already covered)
- `th_chidon`: Index on `(user_id, year, rohr_subsidy, paid)` for JOIN and WHERE

### Query 89: Get Chidon Drive Family Children (Chidon Drive System)
**File:** `chidonOld/chidon_drive/classes/ChidonDrive.php` (line 128-139)

```sql
SELECT aa.id
FROM admin_auths aa
JOIN th_chidon tc ON aa.id = tc.user_id
WHERE aa.admin_id = :admin_id AND aa.role_id = 1
  AND tc.year = :year
  AND tc.fundraising_goal > 0
```

**Required Indexes:**
- `admin_auths`: Index on `(admin_id, role_id, id)` for WHERE clause (already covered)
- `th_chidon`: Index on `(user_id, year, fundraising_goal)` for JOIN and WHERE

### Query 90: Get Chidon Drive School Children (Chidon Drive System)
**File:** `chidonOld/chidon_drive/classes/ChidonDrive.php` (line 188-196)

```sql
SELECT user_id  
FROM th_chidon 
WHERE year = :year AND fundraising_goal > 0
  AND school_id = :school
```

**Required Indexes:**
- `th_chidon`: Index on `(year, school_id, fundraising_goal, user_id)` for WHERE clause

### Query 91: Get Chidon Drive Community Children (Chidon Drive System)
**File:** `chidonOld/chidon_drive/classes/ChidonDrive.php` (line 230-238)

```sql
SELECT user_id 
FROM th_chidon 
WHERE year = :year AND fundraising_goal > 0 
  AND school_id IN (?)
```

**Required Indexes:**
- `th_chidon`: Index on `(year, school_id, fundraising_goal, user_id)` for WHERE clause (already covered)

### Query 92: Get Chidon Drive Amount Raised (Chidon Drive System)
**File:** `chidonOld/chidon_drive/classes/ChidonDrive.php` (line 91-99)

```sql
SELECT IFNULL(SUM(subsidy_amount), 0) AS total 
FROM chidon_user_subsidies
WHERE chidon_year = :year 
  AND user_id IN (?)
```

**Required Indexes:**
- `chidon_user_subsidies`: Index on `(chidon_year, user_id, subsidy_amount)` for WHERE and SUM (already covered)

### Query 93: Get Chidon Drive Global Donations (Chidon Drive System)
**File:** `chidonOld/chidon_drive/classes/ChidonDrive.php` (line 27-34)

```sql
SELECT IFNULL(SUM(donation_amount), 0) AS total 
FROM chidon_donations
WHERE year = :year
```

**Required Indexes:**
- `chidon_donations`: Index on `(chidon_year, donation_amount)` for WHERE and SUM (already covered)

### Query 94: Get Chidon Confirmations (Chidon Drive System)
**File:** `chidonOld/newReports/confirmed_report.php` (line 19)

```sql
SELECT * FROM chidon_confirmations WHERE year = :year
```

**Required Indexes:**
- `chidon_confirmations`: Index on `(year)` for WHERE clause (already covered)

### Query 95: Get Chidon Open Registration (Chidon Drive System)
**File:** `chidonOld/newReports/confirmed_report.php` (line 25)

```sql
SELECT * FROM chidon_open_reg WHERE year = :year
```

**Required Indexes:**
- `chidon_open_reg`: Index on `(year)` for WHERE clause (already covered)

### Query 96: Get Promotions by Date Range (Promotions System)
**File:** `api/core/homepage/promotions.php` (line 41-49)

```sql
SELECT user_id, class_id, first, last, mobile_pic, user_photo_id, school_name,
       class_grade, class_sub, date_promoted, rank_ord, rank_name 
FROM rank_marks 
JOIN ranks USING (rank_ord) 
JOIN users u USING (user_id) 
JOIN schools s USING (school_id) 
JOIN classes c USING (class_id) 
WHERE rank_ord > 1 AND date_promoted >= ? AND date_promoted <= ? 
  AND (filter) AND u.user_registered IS NOT NULL [AND u.gender = ?] [AND u.school_id = ?]
GROUP BY user_id 
ORDER BY date_promoted DESC, rank_ord DESC, last, first
```

**Required Indexes:**
- `rank_marks`: Index on `(date_promoted, rank_ord, user_id)` for WHERE, GROUP BY, and ORDER BY
- `ranks`: Index on `(rank_ord)` for JOIN (likely already exists as PRIMARY KEY)
- `users`: Index on `(user_id, user_registered, gender, school_id, last, first)` for JOIN and WHERE
- `schools`: Index on `(school_id)` for JOIN (likely already exists as PRIMARY KEY)
- `classes`: Index on `(class_id, class_grade, class_sub)` for JOIN (already covered)

### Query 97: Get Today's Promotions (Promotions System)
**File:** `mobile/news/ajax/getPromotions.php` (line 63-69)

```sql
SELECT u.first, u.last, u.gender, s.school_name, MAX(rm.rank_ord), r.rank_name 
FROM users u 
JOIN schools s USING (school_id) 
JOIN rank_marks rm USING (user_id) 
JOIN ranks r USING (rank_ord) 
WHERE rm.date_promoted = ? 
GROUP BY rm.user_id
```

**Required Indexes:**
- `rank_marks`: Index on `(date_promoted, user_id, rank_ord DESC)` for WHERE, GROUP BY, and MAX
- `users`: Index on `(user_id, gender)` for JOIN
- `schools`: Index on `(school_id)` for JOIN (likely already exists as PRIMARY KEY)
- `ranks`: Index on `(rank_ord)` for JOIN (likely already exists as PRIMARY KEY)

### Query 98: Get Store Prizes (Store System)
**File:** `api/rewards/prizes.php` (line 24-28)

```sql
SELECT * FROM prizes
WHERE institution_id IN (?)
  [AND institution_id = ?]
ORDER BY is_active DESC, prize_count ASC, prize_name ASC
```

**Required Indexes:**
- `prizes`: Index on `(institution_id, is_active, prize_count, prize_name)` for WHERE and ORDER BY

### Query 99: Get Prize Classes (Store System)
**File:** `api/rewards/prizes.php` (line 32)

```sql
SELECT prize_id, class_id FROM prize_classes
```

**Required Indexes:**
- `prize_classes`: Index on `(prize_id, class_id)` for efficient lookup

### Query 100: Get Store Prizes for User (Store System)
**File:** `mobile/store/ajax/getPrizes.php` (line 23-30)

```sql
SELECT prize_id, prize_name, [fields], class_id
FROM prizes p 
LEFT JOIN prize_classes USING (prize_id)
WHERE is_active = 1
  AND prize_count > 0 
  AND institution_id = ?
  AND (class_id IS NULL OR class_id = ?)
```

**Required Indexes:**
- `prizes`: Index on `(institution_id, is_active, prize_count, prize_id)` for WHERE clause
- `prize_classes`: Index on `(prize_id, class_id)` for LEFT JOIN (already covered)

### Query 101: Check One-Time Prize Limit (Store System)
**File:** `mobile/store/ajax/getPrizes.php` (line 51)

```sql
SELECT * FROM user_prizes 
WHERE prize_id = ? 
  AND user_id = ? 
  AND is_reversed = 0 
  AND created >= ?
```

**Required Indexes:**
- `user_prizes`: Index on `(prize_id, user_id, is_reversed, created)` for WHERE clause

### Query 102: Get User Orders (Store System)
**File:** `mobile/store/ajax/getOrders.php` (line 15-37)

```sql
SELECT up.prize_id, up.user_id, [fields], p.prize_name, p.points, p.image_id, dp.*
FROM user_prizes up
LEFT JOIN prizes p USING (prize_id)
LEFT JOIN deleted_prizes dp USING (prize_id)
WHERE up.user_id = ?
ORDER BY up.created DESC
```

**Required Indexes:**
- `user_prizes`: Index on `(user_id, created DESC)` for WHERE and ORDER BY
- `prizes`: Index on `(prize_id)` for LEFT JOIN (likely already exists as PRIMARY KEY)
- `deleted_prizes`: Index on `(prize_id)` for LEFT JOIN

### Query 103: Get Orders List (Store System)
**File:** `api/rewards/orders.php` (line 23-33)

```sql
SELECT user_prize_id, user_serial, first, last, prize_name, p.points, points.points as total,
       quantity, status, class_grade, class_sub, orders.created 
FROM user_prizes orders 
JOIN prizes p USING (prize_id) 
JOIN users u USING (user_id) 
JOIN classes c USING (class_id) 
JOIN user_points points USING (user_prize_id) 
WHERE is_reversed = 0 AND [filter] AND status = ? 
ORDER BY orders.created DESC, class_grade ASC, class_sub ASC, first ASC, last ASC, prize_name ASC
```

**Required Indexes:**
- `user_prizes`: Index on `(is_reversed, institution_id, status, created DESC)` for WHERE and ORDER BY
- `user_prizes`: Index on `(is_reversed, class_id, status, created DESC)` for teacher filter
- `prizes`: Index on `(prize_id)` for JOIN (likely already exists as PRIMARY KEY)
- `users`: Index on `(user_id, class_id, first, last)` for JOIN and ORDER BY
- `classes`: Index on `(class_id, class_grade, class_sub)` for JOIN and ORDER BY
- `user_points`: Index on `(user_prize_id)` for JOIN

### Query 104: Get Store for Single User (Store System)
**File:** `api/rewards/orders.php` (line 53-65)

```sql
SELECT prizes.*, COUNT(user_prize_id) AS ordered 
FROM prizes
LEFT JOIN prize_classes USING (prize_id) 
LEFT JOIN user_prizes ON prizes.prize_id = user_prizes.prize_id 
  AND is_reversed = 0 AND user_id = ?
WHERE is_active = 1 AND prize_count > 0 
  AND prizes.institution_id = ?
  AND (class_id IS NULL OR class_id = ?)
GROUP BY prizes.prize_id
HAVING (one_per_user = 0 OR ordered = 0)
ORDER BY points, prize_name
```

**Required Indexes:**
- `prizes`: Index on `(institution_id, is_active, prize_count, prize_id, points, prize_name)` for WHERE and ORDER BY
- `prize_classes`: Index on `(prize_id, class_id)` for LEFT JOIN (already covered)
- `user_prizes`: Index on `(prize_id, user_id, is_reversed, user_prize_id)` for LEFT JOIN and COUNT

### Query 105: Place Order - Get Prize Details (Store System)
**File:** `api/rewards/orders.php` (line 97-99)

```sql
SELECT * FROM prizes WHERE prize_id = ?
```

**Required Indexes:**
- `prizes`: Index on `(prize_id)` for WHERE clause (likely already exists as PRIMARY KEY)

### Query 106: Place Order - Generate Serial (Store System)
**File:** `api/rewards/orders.php` (line 122-128)

```sql
SELECT serial FROM user_prizes WHERE serial NOT IN (SELECT serial FROM user_prizes)
```

**Required Indexes:**
- `user_prizes`: Index on `(serial)` for uniqueness check

### Query 107: Place Order - Insert User Prize (Store System)
**File:** `api/rewards/orders.php` (line 138-146)

```sql
INSERT INTO user_prizes (prize_id, user_id, institution_id, quantity, serial) 
VALUES (?, ?, ?, ?, ?)
```

**Required Indexes:**
- `user_prizes`: Index on `(prize_id, user_id, institution_id)` for efficient inserts and lookups

### Query 108: Place Order - Insert User Points (Store System)
**File:** `api/rewards/orders.php` (line 154-162)

```sql
INSERT INTO user_points (prize_id, user_prize_id, user_id, institution_id, points, resource_name) 
VALUES (?, ?, ?, ?, ?, "store")
```

**Required Indexes:**
- `user_points`: Index on `(user_prize_id, user_id, institution_id)` for efficient inserts and lookups

### Query 109: Place Order - Update Stock (Store System)
**File:** `api/rewards/orders.php` (line 168-170)

```sql
UPDATE prizes SET prize_count = prize_count - ? WHERE prize_id = ?
```

**Required Indexes:**
- `prizes`: Index on `(prize_id)` for WHERE clause (likely already exists as PRIMARY KEY)

### Query 110: Redeem Orders (Store System)
**File:** `api/rewards/orders.php` (line 208-211)

```sql
UPDATE user_prizes SET status="Redeemed", redeemed_by = ? 
WHERE user_prize_id IN (?)
```

**Required Indexes:**
- `user_prizes`: Index on `(user_prize_id)` for WHERE clause (likely already exists as PRIMARY KEY)

### Query 111: Reverse Orders - Get User Points (Store System)
**File:** `api/rewards/orders.php` (line 242-246)

```sql
SELECT p.user_prize_id, p.user_point_id, p.points, o.prize_id, 
       o.user_id, o.quantity, o.institution_id 
FROM user_points p 
JOIN user_prizes o USING (user_prize_id) 
WHERE user_prize_id IN (?)
```

**Required Indexes:**
- `user_points`: Index on `(user_prize_id)` for JOIN and WHERE
- `user_prizes`: Index on `(user_prize_id, prize_id, user_id, quantity, institution_id)` for JOIN

### Query 112: Reverse Orders - Update Stock (Store System)
**File:** `api/rewards/orders.php` (line 266-268)

```sql
UPDATE prizes SET prize_count = prize_count + ? WHERE prize_id = ?
```

**Required Indexes:**
- `prizes`: Index on `(prize_id)` for WHERE clause (likely already exists as PRIMARY KEY)

### Query 113: Get Store Purchases (Legacy Store System)
**File:** `classes/user.php` (line 222)

```sql
SELECT prize_points, prize_quantity FROM store_purchases WHERE user_id = ?
```

**Required Indexes:**
- `store_purchases`: Index on `(user_id, prize_points, prize_quantity)` for WHERE clause

### Query 114: Get Cart Items (Legacy Store System)
**File:** `kiosk/store.php` (line 53, 79)

```sql
SELECT store_purchase_id FROM store_purchases 
WHERE user_id = ? AND prize_id = ? AND prize_shipped = 0

SELECT cp.prize_id, ps.prize_name, ps.prize_image_id, cp.prize_quantity 
FROM store_purchases AS cp 
JOIN prizes_camp AS ps USING (prize_id) 
WHERE user_id = ? AND prize_shipped = 0
```

**Required Indexes:**
- `store_purchases`: Index on `(user_id, prize_id, prize_shipped)` for WHERE clause
- `prizes_camp`: Index on `(prize_id)` for JOIN (likely already exists as PRIMARY KEY)

### Query 115: Checkout (Legacy Store System)
**File:** `kiosk/store.php` (line 22)

```sql
UPDATE store_purchases SET prize_shipped=1 WHERE user_id = ? AND prize_shipped=0
```

**Required Indexes:**
- `store_purchases`: Index on `(user_id, prize_shipped)` for WHERE clause (already covered)

### Query 116: Get Store Configuration (Store System)
**File:** `v2/application/models/Store.php` (line 1419-1421)

```sql
SELECT * FROM config_store WHERE institution_id = ?
```

**Required Indexes:**
- `config_store`: Index on `(institution_id)` for WHERE clause (likely already exists as PRIMARY KEY)

### Query 117: Get Prize Classes for Prize (Store System)
**File:** `api/models/StorePrize.php` (line 42-44)

```sql
SELECT class_id FROM prize_classes WHERE prize_id = ?
```

**Required Indexes:**
- `prize_classes`: Index on `(prize_id, class_id)` for WHERE clause (already covered)

### Query 118: Check Payment Processing (Store System)
**File:** `mobile/store/ajax/purchase.php` (line 45-47)

```sql
SELECT * FROM payment_processing WHERE user_id = ?
```

**Required Indexes:**
- `payment_processing`: Index on `(user_id)` for WHERE clause

### Query 119: Get User Store Points (Store System)
**File:** `mobile/store/ajax/purchase.php` (line 68)

```sql
-- Uses Points class which queries user_points table
SELECT SUM(points) FROM user_points WHERE user_id = ? AND resource_name = 'store'
```

**Required Indexes:**
- `user_points`: Index on `(user_id, resource_name, points)` for WHERE and SUM

### Query 120: Check Serial Uniqueness (Store System)
**File:** `mobile/store/ajax/purchase.php` (line 116-117)

```sql
SELECT serial FROM user_prizes WHERE serial = ?
```

**Required Indexes:**
- `user_prizes`: Index on `(serial)` for uniqueness check (already covered)

### Query 41: Get Missions for User (Missions Page)
**File:** `missions.php` (line 25-47)

```sql
SELECT subject_id, subject_name, [fields], date_tasks_missions.date_tasks_mission_id, [fields]
FROM subjects
JOIN school_subjects USING (subject_id)
JOIN school_type_subjects USING (subject_id)
JOIN users USING (school_id, school_type_id)
JOIN user_tracks USING (user_id, subject_id)
JOIN date_tasks_missions USING (school_type_id, subject_id, level, track_id)
LEFT JOIN date_tasks_mission_marks USING (user_id, subject_id, date_tasks_mission_id)
LEFT JOIN institutions USING (inst_id)
WHERE user_id = ?
  AND enrolled = 1
  AND start_date <= ? AND end_date >= ?
  AND user_registered IS NOT NULL
  AND (
    NOT EXISTS (SELECT * FROM date_tasks JOIN date_tasks_marks USING (date_task_id) WHERE date_tasks.date_tasks_mission_id = date_tasks_missions.date_tasks_mission_id AND user_id = ?)
    OR
    EXISTS (SELECT * FROM user_mission_entries WHERE user_id = ? AND entry_type = 'date_tasks_missions' AND entry_id = date_tasks_missions.date_tasks_mission_id)
  )
  AND NOT EXISTS (SELECT * FROM date_tasks JOIN date_tasks_marks USING (date_task_id) JOIN date_tasks_missions date_tasks_missions_alt USING (date_tasks_mission_id) WHERE date_tasks_missions_alt.subject_id = date_tasks_missions.subject_id AND (date_tasks_missions_alt.mission_number = date_tasks_missions.mission_number OR (date_tasks_missions.mission_number IS NULL AND date_tasks_missions_alt.start_date = date_tasks_missions.start_date AND date_tasks_missions_alt.end_date = date_tasks_missions.end_date)) AND date_tasks_missions_alt.date_tasks_mission_id != date_tasks_missions.date_tasks_mission_id AND user_id = ?)
ORDER BY inst_name, subject_name, subject_id, mission_number, start_date, mission_name
```

**Required Indexes:**
- `date_tasks_missions`: Index on `(school_type_id, subject_id, level, track_id, start_date, end_date)` for JOIN and WHERE (already covered)
- `date_tasks_mission_marks`: Index on `(user_id, subject_id, date_tasks_mission_id)` for LEFT JOIN (already covered)
- `user_mission_entries`: Index on `(user_id, entry_type, entry_id)` for EXISTS subquery
- `date_tasks`: Index on `(date_task_id, date_tasks_mission_id)` for EXISTS subquery
- `date_tasks_marks`: Index on `(date_task_id, user_id)` for EXISTS subquery

### Query 42: Get Medals for Shipping (Shipping System)
**File:** `reports/shipping/functions/get_medals.php` (line 19-23)

```sql
SELECT user_id, school_id, date_awarded, date_shipped, medal_name, medal_ord, medal_off_image_id, subject_name, subject_image_id, subject_id
FROM medal_marks
JOIN users USING (user_id)
JOIN medals USING (medal_ord)
JOIN subjects USING (subject_id)
WHERE date_awarded >= ? AND date_awarded <= ?
  [AND school_id = ?]
ORDER BY user_id, medal_ord
```

**Required Indexes:**
- `medal_marks`: Index on `(date_awarded, user_id, school_id, medal_ord)` for WHERE and ORDER BY
- `users`: Index on `(user_id, school_id)` for JOIN

### Query 43: Get Ranks for Shipping (Shipping System)
**File:** `reports/shipping/functions/get_ranks.php` (line 18-21)

```sql
SELECT rank_marks.*, school_id, rank_name, rank_image_id
FROM rank_marks
JOIN users USING (user_id)
JOIN ranks USING (rank_ord)
WHERE date_promoted >= ? AND date_promoted <= ?
  [AND school_id = ?]
```

**Required Indexes:**
- `rank_marks`: Index on `(date_promoted, user_id, school_id)` for WHERE clause
- `users`: Index on `(user_id, school_id)` for JOIN

### Query 44: Get Missions for User Track (Missions System)
**File:** `classes/user_track.php` (line 402-425)

```sql
SELECT * FROM date_tasks_missions AS dtm
WHERE dtm.school_type_id = ?
  AND dtm.subject_id = ?
  AND dtm.track_id = ?
  AND dtm.level = ?
  AND dtm.start_date >= ?
  AND dtm.end_date <= ?
```

**Required Indexes:**
- `date_tasks_missions`: Index on `(school_type_id, subject_id, track_id, level, start_date, end_date)` for WHERE clause (already covered by Query 4)

### Query 45: Fetch Date Tasks Missions (Missions System)
**File:** `classes/user_track.php` (line 427-448)

```sql
SELECT * FROM date_tasks_missions
WHERE school_type_id = ?
  AND subject_id = ?
  AND track_id = ?
  AND level = ?
  AND start_date >= ?
ORDER BY start_date ASC
```

**Required Indexes:**
- `date_tasks_missions`: Index on `(school_type_id, subject_id, track_id, level, start_date)` for WHERE and ORDER BY (already covered by Query 4)

### Query 46: Get User Rank with MAX (Missions Page)
**File:** `missions.php` (line 15)

```sql
SELECT user_id, MAX(rank_ord) rank_ord
FROM rank_marks
WHERE user_id = ?
GROUP BY user_id
```

**Required Indexes:**
- `rank_marks`: Index on `(user_id, rank_ord DESC)` for MAX aggregation (already covered by Query 2)

## Recommended Index Creation Script

```sql
-- ============================================================================
-- Indexes for users table
-- ============================================================================

-- For Query 1: Filtering by school_id, class_id, user_registered
CREATE INDEX idx_users_school_class_reg ON users (school_id, class_id, user_registered);

-- For Query 1: ORDER BY on user columns after JOIN
-- NOTE: Consolidated into idx_users_class_reg_order (see Priority 4, line 280)

-- ============================================================================
-- Indexes for classes table
-- ============================================================================

-- For Query 1: JOIN and ORDER BY on class_grade, class_sub
CREATE INDEX idx_classes_order ON classes (class_id, class_grade, class_sub);

-- ============================================================================
-- Indexes for rank_marks table
-- ============================================================================

-- For Query 2: MAX(rank_ord) WHERE user_id = ?
CREATE INDEX idx_rank_marks_user_max ON rank_marks (user_id, rank_ord DESC);

-- ============================================================================
-- Indexes for user_tracks table
-- ============================================================================

-- For Query 3: WHERE user_id = ? AND enrolled = 1
CREATE INDEX idx_user_tracks_enrolled ON user_tracks (user_id, enrolled, subject_id);

-- ============================================================================
-- Indexes for subjects table
-- ============================================================================

-- For Query 3: JOIN and ORDER BY subject_ord
CREATE INDEX idx_subjects_ord ON subjects (subject_id, subject_ord);

-- ============================================================================
-- Indexes for date_tasks_missions table
-- ============================================================================

-- For Query 4: Complex WHERE clause with multiple conditions
CREATE INDEX idx_dtm_complete ON date_tasks_missions (
  school_type_id, 
  subject_id, 
  level, 
  track_id, 
  lang_id,
  start_date, 
  end_date,
  created_by_parent
);

-- For Query 5: WHERE clause for update_user_track
CREATE INDEX idx_dtm_update ON date_tasks_missions (
  school_type_id, 
  subject_id, 
  track_id, 
  level, 
  start_date
);

-- ============================================================================
-- Indexes for date_tasks_mission_marks table
-- ============================================================================

-- For Query 5: LEFT JOIN optimization
CREATE INDEX idx_dtmm_mission_user ON date_tasks_mission_marks (date_tasks_mission_id, user_id);

-- ============================================================================
-- Indexes for duch and mission printing system
-- ============================================================================

-- For Query 8: Streaks lookup
CREATE INDEX idx_streak_tasks_user ON streak_tasks (user_id);
CREATE INDEX idx_date_tasks_streak ON date_tasks (streak_id, streak_show);
CREATE INDEX idx_dtm_streak_join ON date_tasks_missions (date_tasks_mission_id, school_type_id, track_id, level);

-- For Query 9: Streak marks lookup
CREATE INDEX idx_dtm_marks_user_date ON date_tasks_marks (user_id, mark_date);
CREATE INDEX idx_date_tasks_streak_id ON date_tasks (date_task_id, streak_id);

-- For Query 10: Parsha lookup
CREATE INDEX idx_parshos_dates ON parshos (start, end);

-- For Query 11: Sponsorships lookup
CREATE INDEX idx_sponsorships_dates ON sponsorships (start_date, end_date);

-- For Query 12: Date tasks marks for accomplished tasks
CREATE INDEX idx_dtm_marks_user_date_inactive ON date_tasks_marks (user_id, mark_date, mark_inactive);

-- For Query 13: Medal marks lookup
-- NOTE: Covered by idx_medal_marks_user_subject_date (see Priority 2, line 138)

-- For Query 14: Rank marks by date range
-- NOTE: Covered by idx_rank_marks_user_date_filters (see Priority 2, line 158)

-- ============================================================================
-- Indexes for Shabbos Mevorchim Tehillim system
-- ============================================================================

-- For Query 15 & 18: Tehillim quotas (army-wide and class-wide)
CREATE INDEX idx_date_tasks_grid_mission ON date_tasks (grid_id, date_tasks_mission_id);
CREATE INDEX idx_dtm_mission_dates_school ON date_tasks_missions (date_tasks_mission_id, start_date, end_date, school_type_id, lang_id);
CREATE INDEX idx_user_tracks_composite ON user_tracks (user_id, track_id, level, subject_id, enrolled);
CREATE INDEX idx_users_school_composite ON users (user_id, school_id, user_registered, school_type_id, lang_id);
CREATE INDEX idx_classes_era ON classes (class_id, class_era);
CREATE INDEX idx_schools_era ON schools (school_id, school_era);

-- For Query 16 & 20: Tehillim done marks
CREATE INDEX idx_date_tasks_task_grid ON date_tasks (date_task_id, grid_id);
CREATE INDEX idx_dtm_marks_mission_dates ON date_tasks_missions (date_tasks_mission_id, start_date, end_date);
CREATE INDEX idx_dtm_marks_user_task ON date_tasks_marks (user_id, date_task_id);

-- For Query 17: Tehillim backups
CREATE INDEX idx_tehillim_backups_dates_grid ON tehillim_backups (sm_date, grid_id);
CREATE INDEX idx_tehillim_backups_user ON tehillim_backups (sm_date, grid_id, user_id);

-- For Query 19: Student tehillim quota
CREATE INDEX idx_dtm_mission_complete ON date_tasks_missions (date_tasks_mission_id, subject_id, start_date, end_date, school_type_id, lang_id);

-- For Query 21: Users with tehillim tracks
CREATE INDEX idx_users_class_reg_order ON users (class_id, user_registered, last, first);
-- NOTE: idx_user_tracks_user_subject covered by idx_user_tracks_composite (see Priority 4, line 230)

-- ============================================================================
-- Indexes for Chidon Testing system
-- ============================================================================

-- For Query 22: Chidon students lookup
CREATE INDEX idx_th_chidon_year_user ON th_chidon (year, user_id);
CREATE INDEX idx_th_chidon_info_year_user ON th_chidon_info (year, user_id);
CREATE INDEX idx_users_chidon_composite ON users (user_id, school_id, class_id, gender);

-- For Query 23: Chidon scores lookup
CREATE INDEX idx_th_chidon_marks_id ON th_chidon_marks (th_chidon_id);

-- For Query 25: Passing averages lookup
CREATE INDEX idx_chidon_passing_avgs ON chidon_passing_avgs (year, user_id, school_id, class_id);

-- For Query 26: Test levels lookup
CREATE INDEX idx_chidon_test_levels ON chidon_test_levels (year, test_type, user_id, school_id, class_id);

-- For Query 34: Eligibility history
CREATE INDEX idx_th_chidon_user_year ON th_chidon (user_id, year);
CREATE INDEX idx_th_chidon_info_year_user ON th_chidon_info (year, user_id);

-- ============================================================================
-- Indexes for Chidon Shipping system
-- ============================================================================

-- For Query 27 & 28: Chidon brochures and guides
CREATE INDEX idx_th_chidon_year_khk ON th_chidon (year, user_id, khk_reg);

-- For Query 29: Chidon gifts (date_paid filtering)
CREATE INDEX idx_th_chidon_year_paid ON th_chidon (year, date_paid, user_id);

-- For Query 30: Chidon awards (finals)
CREATE INDEX idx_th_chidon_finals_year_user ON th_chidon_finals (year, user_id);
CREATE INDEX idx_th_chidon_user_year ON th_chidon (user_id, year);

-- For Query 31: Shipping report
CREATE INDEX idx_chidon_user_prizes_user ON chidon_user_prizes (user_id);
CREATE INDEX idx_users_school_gender_order ON users (school_id, gender, last, first);

-- For Query 32: Registration charges for chidon
CREATE INDEX idx_reg_charges_chidon ON registration_charges (year, user_id, type, date, school_id);
CREATE INDEX idx_reg_charges_shipping ON registration_charges (year, type, study_guide_shipped, book_shipped);

-- For Query 33: Recruitment credits
CREATE INDEX idx_th_chidon_recruited ON th_chidon (year, recruited_by);
CREATE INDEX idx_users_serial_composite ON users (user_serial, user_id, gender, school_id);
```

## Priority Order

### Priority 1 (Critical - Execute First)
1. `idx_user_tracks_enrolled` - Used in every user track lookup
2. `idx_rank_marks_user_max` - Used for every user rank lookup
3. `idx_dtm_complete` - Used for every mission lookup per track
4. `idx_dtmm_mission_user` - Used in LEFT JOIN for mission marks
5. `idx_dtm_marks_user_date` - Used for streak marks and accomplished tasks (Duch printing)
6. `idx_dtm_marks_user_date_inactive` - Used for accomplished tasks timeline (Duch printing)

**Note**: `idx_rank_marks_user_date` was consolidated into `idx_rank_marks_user_date_filters` (covers same queries plus additional filters)

### Priority 2 (High Impact)
7. `idx_users_school_class_reg` - Used when loading users by school/class
8. `idx_classes_order` - Used for ORDER BY in user loading
9. `idx_users_class_reg_order` - Used for ORDER BY on user columns (consolidates idx_users_class_order)
10. `idx_streak_tasks_user` - Used for streaks lookup (Duch printing)
11. `idx_date_tasks_streak` - Used for streaks JOIN (Duch printing)
12. `idx_medal_marks_user_subject_date` - Used for medals by subject and date range (Medals system) - also covers user_date queries
13. `idx_rank_marks_user_date_filters` - Used for ranks by date range with filters (Ranks system) - also covers user_date queries
14. `idx_medal_marks_user_subject_ord` - Used for checking if medal was awarded (Medals system)
15. `idx_medal_marks_user_subject_max` - Used for user medals grouped by subject (Missions page)
16. `idx_user_mission_entries_lookup` - Used for EXISTS subquery in missions page
17. `idx_medal_marks_shipping` - Used for medal shipping queries
18. `idx_rank_marks_shipping` - Used for rank shipping queries

**Consolidated**: 
- `idx_users_class_order` → consolidated into `idx_users_class_reg_order`
- `idx_medal_marks_user_date` → covered by `idx_medal_marks_user_subject_date`
- `idx_rank_marks_user_date` → covered by `idx_rank_marks_user_date_filters`

### Priority 3 (Moderate Impact)
19. `idx_subjects_ord` - Used for ORDER BY in track queries
20. `idx_dtm_update` - Used in update_user_track method
21. `idx_dtm_streak_join` - Used for streaks JOIN with missions
22. `idx_date_tasks_streak_id` - Used for streak marks JOIN
23. `idx_parshos_dates` - Used for parsha lookup (Duch/Mission printing)
24. `idx_sponsorships_dates` - Used for sponsorships lookup (Duch printing)
25. `idx_medal_marks_user_count` - Used for counting total medals (Ranks system)
26. `idx_rank_marks_ord_user` - Used for getting rank marks by rank_ord (Ranks system)
27. `idx_medals_subjects_composite` - Used for JOIN in missions page
28. `idx_date_tasks_mission_id` - Used for EXISTS subquery in missions page
29. `idx_date_tasks_marks_task_user` - Used for EXISTS subquery in missions page

### Priority 4 (Shabbos Mevorchim Tehillim System)
30. `idx_date_tasks_grid_mission` - Used for tehillim quota queries (Critical for SMT)
31. `idx_dtm_mission_dates_school` - Used for tehillim mission lookups
32. `idx_user_tracks_composite` - Used for tehillim user track JOINs (covers idx_user_tracks_user_subject)
33. `idx_date_tasks_task_grid` - Used for tehillim done marks queries
34. `idx_tehillim_backups_dates_grid` - Used for backup table lookups (Critical for SMT)
35. `idx_tehillim_backups_user` - Used for per-user backup lookups
36. `idx_dtm_marks_user_task` - Used for student done marks queries
37. `idx_users_class_reg_order` - Used for loading users with tehillim tracks

**Consolidated**: `idx_user_tracks_user_subject` → covered by `idx_user_tracks_composite`

### Priority 5 (Chidon Testing System)
38. `idx_th_chidon_year_user` - Used for chidon student lookups (Critical for chidon)
39. `idx_th_chidon_info_year_user` - Used for highest track lookups
40. `idx_th_chidon_marks_id` - Used for score lookups
41. `idx_chidon_passing_avgs` - Used for passing average calculations
42. `idx_chidon_test_levels` - Used for test level lookups
43. `idx_th_chidon_user_year` - Used for eligibility history queries

### Priority 6 (Chidon Shipping System)
44. `idx_th_chidon_year_khk` - Used for brochures and guides (Critical for shipping)
45. `idx_th_chidon_year_paid` - Used for gifts and ID cards (includes user_id)
46. `idx_th_chidon_finals_year_user` - Used for awards lookup
47. `idx_chidon_user_prizes_user` - Used for shipping report JOINs
48. `idx_reg_charges_chidon` - Used for registration charges queries (Critical for shipping)
49. `idx_reg_charges_shipping` - Used for shipping status filtering
50. `idx_th_chidon_recruited` - Used for recruitment credits
51. `idx_users_serial_composite` - Used for recruitment JOINs
52. `idx_th_chidon_user_year` - Used for JOINs (duplicate of Priority 5, line 43)

### Priority 7 (Auction, Raffle, and Achievement Card Systems)
53. `idx_auction_winners_composite` - Used for auction winners lookup (Critical for auctions)
54. `idx_auctions_ran_school_date` - Used for available auctions filtering
55. `idx_auctions_ran_run_date` - Used for auction date filtering
56. `idx_auction_prizes_auction_available` - Used for prize availability lookup
57. `idx_prizes_auction_points` - Used for prize points filtering
58. `idx_raffle_winners_composite` - Used for raffle winners lookup (Critical for raffles)
59. `idx_raffles_run_date_ran` - Used for raffle run date filtering
60. `idx_raffles_type_year` - Used for raffle filtering by type and year
61. `idx_raffles_id_type` - Used for raffle JOINs
62. `idx_admin_auths_id_admin_role` - Used for admin auth LEFT JOINs (covers idx_admin_auths_id)
63. `idx_achievement_cards_serial` - Used for card serial lookup (Critical for achievement cards)
64. `idx_achievement_cards_modified_created` - Used for ORDER BY in card queries
65. `idx_achievement_cards_institution_campaign_type` - Used for card template filtering

**Consolidated**: `idx_admin_auths_id` → covered by `idx_admin_auths_id_admin_role`

### Priority 8 (Shipping Reports, Tanya, Mivtzoim, and 12 Pesukim Systems)
66. `idx_shipments_school_date_status` - Used for shipment filtering (Critical for shipping reports)
67. `idx_shipment_details_shipment` - Used for shipment details count
68. `idx_shipment_details_composite` - Used for shipment details with item filtering
69. `idx_tracking_numbers_shipment` - Used for tracking numbers by shipment
70. `idx_tracking_numbers_school` - Used for tracking numbers by school
71. `idx_lines_learned_campaign_user` - Used for lines learned lookup (Critical for Tanya)
72. `idx_lines_learned_campaign_user_sum` - Used for total lines learned aggregation
73. `idx_tanya_users_user` - Used for Tanya user info lookup
74. `idx_line_campaigns_type_id` - Used for latest campaign lookup
75. `idx_date_tasks_short_name` - Used for mivtzoim tasks lookup (Critical for Mivtzoim)
76. `idx_date_tasks_marks_task_user_date_qty` - Used for mivtzoim marks aggregation
77. `idx_pesukim_recruits_recruiter` - Used for recruits by recruiter (Critical for 12 Pesukim)
78. `idx_pesukim_recruits_recruited` - Used for recruiter lookup
79. `idx_pesukim_duch_recruits_user` - Used for duch recruits lookup
80. `idx_pesukim_mechunachim_user` - Used for mechunachim lookup
81. `idx_pesukim_mechunachim_verify` - Used for mechunach verification

### Priority 9 (Chidon Drive and Promotions Systems)
82. `idx_chidon_confirmations_year_school` - Used for chidon confirmations lookup (Critical for chidon drive) - covers year-only queries
83. `idx_chidon_open_reg_year_school` - Used for open registration lookup - covers year-only queries
84. `idx_admin_auths_id_admin_role` - Used for admin auth JOINs in chidon drive
85. `idx_chidon_user_subsidies_year_user` - Used for subsidy lookups (Critical for chidon drive)
86. `idx_keep_reg_open_year` - Used for keep registration open lookup
87. `idx_chidon_donations_year_id` - Used for donation lookups
88. `idx_chidon_user_subsidies_donation_year` - Used for donation-subsidy JOINs
89. `idx_chidon_donations_year_amount` - Used for donation totals
90. `idx_th_chidon_year_rohr_paid` - Used for chidon totals
91. `idx_th_chidon_year_paid` - Used for paid chidon count (includes user_id)
92. `idx_chidon_donations_year_family` - Used for family donation totals
93. `idx_th_chidon_user_year_subsidy` - Used for child subsidy lookups
94. `idx_th_chidon_user_year_fundraising` - Used for family children lookup
95. `idx_th_chidon_year_school_fundraising` - Used for school/community children lookup
96. `idx_rank_marks_date_ord_user` - Used for promotions by date range (Critical for promotions)
97. `idx_users_reg_gender_school_order` - Used for promotions user JOINs
98. `idx_rank_marks_date_user_ord` - Used for today's promotions lookup

**Consolidated**: 
- `idx_chidon_confirmations_year` → covered by `idx_chidon_confirmations_year_school`
- `idx_chidon_open_reg_year` → covered by `idx_chidon_open_reg_year_school`

### Priority 10 (Store and Prizes Systems)
99. `idx_prize_classes_prize_class` - Used for prize classes lookup
100. `idx_prizes_institution_active_count_points_name` - Used for store prizes lookup (Critical for store) - covers all prize queries
101. `idx_user_prizes_prize_user_reversed_created` - Used for one-time prize limit check
102. `idx_user_prizes_user_created` - Used for user orders lookup (Critical for orders)
103. `idx_deleted_prizes_prize` - Used for deleted prizes JOIN
104. `idx_user_prizes_reversed_institution_status_created` - Used for orders list by institution
105. `idx_user_prizes_reversed_class_status_created` - Used for orders list by class
106. `idx_users_user_class_order` - Used for orders user JOINs
107. `idx_user_points_user_prize` - Used for user points JOIN in orders
108. `idx_user_prizes_prize_user_reversed_id` - Used for store user prize lookups
109. `idx_user_prizes_serial` - Used for serial uniqueness check (Critical for orders)
110. `idx_user_prizes_prize_user_institution` - Used for placing orders
111. `idx_user_points_user_prize_user_institution` - Used for order points tracking
112. `idx_user_prizes_prize_user_qty_institution` - Used for reverse orders
113. `idx_store_purchases_user_points_qty` - Used for legacy store purchases
114. `idx_store_purchases_user_prize_shipped` - Used for legacy cart items
115. `idx_payment_processing_user` - Used for payment processing guard
116. `idx_user_points_user_resource_points` - Used for user store points calculation

**Consolidated**: 
- `idx_prizes_institution_active_count` → covered by `idx_prizes_institution_active_count_points_name`
- `idx_prizes_institution_active_count_id` → covered by `idx_prizes_institution_active_count_points_name`

## Index Consolidation Summary

After consolidation, **119 unique indexes** remain (down from ~142 originally).

### Consolidated Indexes
- **rank_marks**: `idx_rank_marks_user_date` → covered by `idx_rank_marks_user_date_filters`
- **medal_marks**: `idx_medal_marks_user_date` → covered by `idx_medal_marks_user_subject_date`
- **medal_marks**: `idx_medal_marks_user_count` → covered by other user_id indexes
- **users**: `idx_users_class_order` → covered by `idx_users_class_reg_order`
- **user_tracks**: `idx_user_tracks_user_subject` → covered by `idx_user_tracks_composite`
- **admin_auths**: `idx_admin_auths_id` → covered by `idx_admin_auths_id_admin_role`
- **chidon_confirmations**: `idx_chidon_confirmations_year` → covered by `idx_chidon_confirmations_year_school`
- **chidon_open_reg**: `idx_chidon_open_reg_year` → covered by `idx_chidon_open_reg_year_school`
- **prizes**: `idx_prizes_institution_active_count` and `idx_prizes_institution_active_count_id` → covered by `idx_prizes_institution_active_count_points_name`
- **th_chidon**: Duplicate `idx_th_chidon_user_year` removed, duplicate `idx_th_chidon_year_paid` (year, date_paid only) removed

### Duplicate Sections Removed
- Entire duplicate PRIORITY 2 section (medals/ranks/missions indexes) removed

See `CONSOLIDATION_SUMMARY.md` for detailed analysis.

## Notes

- Index column order matters: Put most selective columns first
- DESC on rank_ord allows MySQL to use index for MAX() efficiently
- Composite indexes can be used for queries that match the leftmost prefix
- Monitor index usage with `SHOW INDEX FROM table_name` and `EXPLAIN` queries
- Index creation may lock tables temporarily - run during low-traffic periods
- Some indexes were kept despite overlap because they serve different query patterns with different column orders
