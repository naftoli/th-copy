# Business Rules: Points, Missions, Tasks, Marks & Streaks, Medals & Ranks

**Extracted from:** mashpiadb and pointsDB SQL dumps + PHP API/class files  
**Date extracted:** 2026-04-30  
**Analyst note:** This is a legacy codebase mixing procedural `mysql_query` and PDO. Fragments are from multiple files. Rules are inferred from schema constraints, enum values, NOT NULL columns, application logic, and PHP class behavior. SME verification is required for all rules.

---

## Points

```
Rule ID:      BR-PTS-001
Category:     Points
Description:  Each point award is stored with a decimal precision of 2 (up to 8 digits), meaning fractional
              points (e.g. 0.50, 1.25) are supported. Points are always non-negative (unsigned).
Source:       mashpiadb_points.sql (table: points), mashpiadb_date_tasks.sql (date_tasks.points)
DB Evidence:  award_points DECIMAL(8,2) UNSIGNED NOT NULL; date_tasks.points DECIMAL(6,2) UNSIGNED NOT NULL DEFAULT 0.00
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PTS-002
Category:     Points
Description:  A point award is keyed on (user_id, subject_id, award_date). This means a user can receive
              at most one point record per subject per day in the legacy points table.
Source:       mashpiadb_points.sql (table: points)
DB Evidence:  PRIMARY KEY (user_id, subject_id, award_date)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PTS-003
Category:     Points
Description:  Points awards carry two "circle" attributes (left_circle, right_circle), each a single
              character. These likely represent visual badge/circle indicators associated with the
              point award (e.g. subject category icons). Both are required.
Source:       mashpiadb_points.sql (table: points), mashpiadb_points_codes.sql (table: points_codes)
DB Evidence:  award_left_circle CHAR(1) NOT NULL; award_right_circle CHAR(1) NOT NULL
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-PTS-004
Category:     Points
Description:  Point awards may belong to a numbered "series" (award_series, tinyint unsigned, nullable).
              A series groups related point awards together (e.g. a set of bonus awards or a campaign series).
              The series is optional — an award may exist outside any series.
Source:       mashpiadb_points.sql (table: points)
DB Evidence:  award_series TINYINT(3) UNSIGNED DEFAULT NULL; KEY award_series (user_id, subject_id, award_series, award_date)
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-PTS-005
Category:     Points
Description:  Points codes (redeemable codes) have an expiration date. A code cannot be redeemed after
              its expiration_date. Codes are scoped to a school and subject.
Source:       mashpiadb_points_codes.sql (table: points_codes)
DB Evidence:  expiration_date DATE NOT NULL; school_id INT UNSIGNED NOT NULL; subject_id INT UNSIGNED NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PTS-006
Category:     Points
Description:  Points code templates are reusable templates for generating point codes. They may be
              school-specific (school_id is nullable, meaning a template can be global or school-scoped).
              Each template specifies a fixed point value, subject, description, and optional series.
Source:       mashpiadb_points_codes_templates.sql (table: points_codes_templates)
DB Evidence:  school_id INT UNSIGNED DEFAULT NULL; points DECIMAL(8,2) UNSIGNED NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PTS-007
Category:     Points
Description:  Member points (stored separately in member_points) record integer point totals per user
              per date. This table appears to be a simplified summary/ledger of daily points for members,
              distinct from the decimal-precision points table.
Source:       mashpiadb_member_points.sql (table: member_points)
DB Evidence:  points INT(10) UNSIGNED NOT NULL; points_date DATE NOT NULL
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-PTS-008
Category:     Points
Description:  BP (bonus points) are tracked per user per bp_type per reference. The combination of
              (bp_type_id, user_id, type, ref) must be unique — a user cannot receive the same
              bonus point type+reference combination more than once.
Source:       mashpiadb_bp_points.sql (table: bp_points)
DB Evidence:  UNIQUE KEY points (bp_type_id, user_id, type, ref)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PTS-009
Category:     Points
Description:  BP (bonus points) are classified by a bp_type. There are a maximum of 2 BP types
              in the system (AUTO_INCREMENT=3 with existing records implies IDs 1 and 2 are in use).
Source:       mashpiadb_bp_types.sql (table: bp_types)
DB Evidence:  AUTO_INCREMENT=3; PRIMARY KEY (bp_type_id)
Confidence:   Low
SME Verified: No
```

```
Rule ID:      BR-PTS-010
Category:     Points
Description:  BP campaign summaries are maintained at three levels: user, class, and school. Each summary
              tracks num_lines (number of BP line entries) per campaign at that level. The user summary
              also tracks child_count (defaulting to 1), suggesting aggregation across children.
Source:       mashpiadb_bp_user_summary.sql, mashpiadb_bp_class_summary.sql, mashpiadb_bp_school_summary.sql
DB Evidence:  child_count INT NOT NULL DEFAULT 1; PRIMARY KEY (campaign_id, user_id/class_id/school_id)
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-PTS-011
Category:     Points
Description:  In the pointsDB system, a task can have a fixed point value, a minimum, and a maximum,
              supporting variable-point tasks where the actual award falls within a range.
Source:       pointsDB_tasks.sql (table: tasks)
DB Evidence:  points INT(7) UNSIGNED DEFAULT 0; min_points INT(7) UNSIGNED DEFAULT NULL; max_points INT(7) DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PTS-012
Category:     Points
Description:  In the pointsDB system, each user point record (user_points) can be linked to a prize,
              a scratch card, or an achievement card. Points may be flagged as auction_only_points,
              meaning they can only be used in auctions and not for general spending.
Source:       pointsDB_user_points.sql (table: user_points); mashpia.com/public/api/points/details.php
DB Evidence:  auction_only_points TINYINT(3) UNSIGNED NOT NULL DEFAULT 0; prize_id, scratch_card_id, achievement_card_id columns
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PTS-013
Category:     Points
Description:  A point record may be reversed. The reversed_user_point_id column on user_points allows
              linking a reversal transaction back to the original point award.
Source:       pointsDB_user_points.sql (table: user_points)
DB Evidence:  reversed_user_point_id INT(11) UNSIGNED DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PTS-014
Category:     Points
Description:  Point transactions are traceable to a source (resource_name). Known resource types observed
              in application code: 'store', 'transaction_manager_store' (store purchase/return),
              'specific achievement card', 'scratch_card', 'admin_users_manual' (manual admin adjustment).
Source:       mashpia.com/public/api/points/details.php
DB Evidence:  resource_name VARCHAR(45) DEFAULT NULL in pointsDB.user_points; switch/case on resource_name
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PTS-015
Category:     Points
Description:  Task marks for subject_id 136 (Pesukim) use a quantity-multiplied point calculation:
              mark_points = mark_quantity * task_points. For all other subjects, mark_points equals
              the fixed task point value. Pesukim marks are also flagged as auction_only_points=1.
Source:       mashpia.com/public/api/missions/mark.php (updateTask method)
DB Evidence:  if ($user_task['subject_id'] == 136) { $mark_points = $mark * $user_task['points']; $auction_only_points = 1; }
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PTS-016
Category:     Points
Description:  For daily tasks with a subject other than 136 (Pesukim), the done_qty is binarized to
              either '1' (marked) or '0' (not marked), regardless of the raw mark value.
              Pesukim daily tasks retain the actual numeric quantity.
Source:       mashpia.com/public/api/missions/mark.php (updateTask method)
DB Evidence:  if ($user_task['daily_task'] && $user_task['subject_id'] != 136) $done_qty = $done_qty ? '1' : '0';
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PTS-017
Category:     Points
Description:  Achievement tasks carry an optional flag indicating whether the points are "auction only".
              By default auction_only_points is 0 (regular points). Achievement tasks are scoped
              to a subject and have integer point values.
Source:       mashpiadb_achievement_tasks.sql (table: achievement_tasks)
DB Evidence:  auction_only_points TINYINT(3) UNSIGNED NOT NULL DEFAULT 0; points INT(10) UNSIGNED NOT NULL
Confidence:   High
SME Verified: No
```

---

## Missions

```
Rule ID:      BR-MSN-001
Category:     Missions
Description:  Missions exist at multiple scopes: global (all schools), school-specific, class-specific,
              user-specific, and child-specific. A mission can be selectively enabled for any of these
              levels via junction tables.
Source:       mashpiadb_global_missions.sql, mashpiadb_school_missions.sql, mashpiadb_class_missions.sql,
              mashpiadb_user_missions.sql, mashpiadb_children_missions.sql
DB Evidence:  Separate junction tables: school_missions(school_id, mission_id), class_missions(class_id, mission_id),
              user_missions(user_id, mission_id), children_missions(mission_id, user_id)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MSN-002
Category:     Missions
Description:  Every mission belongs to a campaign and has a sequence number (ordering within campaign)
              and a point value. Missions in the main missions table are unsigned integers for points.
Source:       mashpiadb_missions.sql (table: missions), mashpiadb_global_missions.sql (table: global_missions)
DB Evidence:  sequence TINYINT(3) UNSIGNED NOT NULL; campaign_id INT UNSIGNED NOT NULL; points INT UNSIGNED NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MSN-003
Category:     Missions
Description:  Global missions support multilingual names — they have both an English (mission_name)
              and a French (mission_name_fr) name. This reflects the system's French-language user base.
Source:       mashpiadb_global_missions.sql (table: global_missions)
DB Evidence:  mission_name VARCHAR(255) NOT NULL; mission_name_fr VARCHAR(255) NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MSN-004
Category:     Missions
Description:  Camp missions are organized under a separate camp campaign (camp_campaign_id) and also
              carry a sequence and point value. Camp missions have an additional link to the global
              missions table (mission_id FK). Camp missions can grow up to at least 1220 entries
              (AUTO_INCREMENT=1221).
Source:       mashpiadb_camp_missions.sql (table: camp_missions)
DB Evidence:  sequence TINYINT(3) UNSIGNED NOT NULL; mission_id INT UNSIGNED NOT NULL; points INT UNSIGNED NOT NULL;
              camp_campaign_id INT UNSIGNED NOT NULL; AUTO_INCREMENT=1221
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MSN-005
Category:     Missions
Description:  Chain missions are structured hierarchically by school_type, subject, level, track, and
              floor. The primary key covers all five dimensions, meaning a chain mission is uniquely
              identified by the combination of these five attributes. Chain missions have a text description.
Source:       mashpiadb_chain_missions.sql (table: chain_missions)
DB Evidence:  PRIMARY KEY (school_type_id, subject_id, level, track_id, floor)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MSN-006
Category:     Missions
Description:  User mission entries track which mission was completed via which mechanism: either
              'date_tasks_missions' (regular dated task missions) or 'chain_missions'. A user can
              only have one entry per (user_id, entry_type, entry_id) combination, and each entry is
              linked to a unique points code (code_id UNIQUE).
Source:       mashpiadb_user_mission_entries.sql (table: user_mission_entries)
DB Evidence:  entry_type ENUM('date_tasks_missions','chain_missions') NOT NULL;
              PRIMARY KEY (user_id, entry_type, entry_id); UNIQUE KEY code_id (code_id)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MSN-007
Category:     Missions
Description:  Chanuka missions are tracked separately per user per task number per year. A user cannot
              receive the same Chanuka mission task more than once in the same year.
Source:       mashpiadb_chanuka_missions.sql (table: chanuka_missions)
DB Evidence:  UNIQUE KEY missions (user_id, task_num, year)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MSN-008
Category:     Missions
Description:  The primary missions table for date-based tasks (date_tasks_missions) classifies each
              mission into a mission group: '' (unclassified), 'shabbos', or 'weekday'. This allows
              the system to distinguish between Shabbos-specific and weekday missions.
Source:       mashpiadb_date_tasks_missions.sql (table: date_tasks_missions)
DB Evidence:  mission_group ENUM('','shabbos','weekday') NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MSN-009
Category:     Missions
Description:  Each date_tasks_mission has a mission_value (decimal, default 1.0) that represents how
              much it counts toward medal progress. A mission can count as more or less than 1 full
              mission. The mission_count column in date_tasks_mission_marks defaults to 1, allowing
              a mission mark to count multiple times.
Source:       mashpiadb_date_tasks_missions.sql; mashpiadb_date_tasks_mission_marks.sql
DB Evidence:  mission_value DECIMAL(5,1) UNSIGNED NOT NULL DEFAULT 1.0;
              mission_count INT(11) NOT NULL DEFAULT 1
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MSN-010
Category:     Missions
Description:  A mission can be marked as "personal" (personal=1), meaning it was created for a specific
              user or parent, and not shown globally. Personal missions are excluded from the class
              grid view.
Source:       mashpiadb_date_tasks_missions.sql; mashpia.com/public/api/missions/grid.php
DB Evidence:  personal TINYINT(1) NOT NULL DEFAULT 0; WHERE dtm.personal = 0 (in grid.php)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MSN-011
Category:     Missions
Description:  Missions can be created by a school (created_by_school) or by a parent (created_by_parent).
              In the grid view, only non-parent-created missions are shown by default (created_by_parent IS NULL).
              The API also filters missions by the requesting school.
Source:       mashpiadb_date_tasks_missions.sql; mashpia.com/public/api/missions/grid.php
DB Evidence:  created_by_school INT UNSIGNED DEFAULT NULL; created_by_parent INT DEFAULT NULL;
              AND (dtm.created_by_school IS NULL OR dtm.created_by_school = :school_id) in grid.php
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MSN-012
Category:     Missions
Description:  Missions have start and end dates. A mission is only active/visible when the current date
              falls within its date range. Mission lookup always filters by start_date and end_date.
Source:       mashpiadb_date_tasks_missions.sql; multiple PHP files
DB Evidence:  start_date MEDIUMINT(8) UNSIGNED NOT NULL; end_date MEDIUMINT(8) UNSIGNED NOT NULL (Julian Day Numbers)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MSN-013
Category:     Missions
Description:  A mission can be "default_on" or "default_off". If default_on=0, the mission only appears
              for a user if they have explicitly opted in via the Defaults personalization system.
              The default is default_on=1 (shown to all eligible users by default).
Source:       mashpiadb_date_tasks_missions.sql; mashpia.com/public/classes/user_track.php
DB Evidence:  default_on TINYINT(3) UNSIGNED NOT NULL DEFAULT 1;
              if ($row['default_on'] || $d->isOn($date_tasks_mission->date_tasks_mission_id, 'mission'))
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MSN-014
Category:     Missions
Description:  Birthday missions are a special type of mission. They are identified by the presence of
              "Birthday!" in the mission_name (English) or "יום הולדת" in the mission_description
              (Hebrew). A birthday mission is only shown to the specific user whose birthday it is,
              via the birthdays junction table.
Source:       mashpia.com/public/classes/user_track.php (get_date_tasks_missions method)
DB Evidence:  strpos($row['mission_name'], 'Birthday!') !== false || strpos($row['mission_description'], 'יום הולדת') !== false;
              SELECT * FROM birthdays WHERE user_id = X AND date_tasks_mission_id = Y
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MSN-015
Category:     Missions
Description:  The Chidon Limmud mission is a special mission within subject_id=21 that is only shown
              when the chidonLimmud flag is active. The system filters out missions with "Chidon Limmud"
              in the name when this flag is false.
Source:       mashpia.com/public/classes/user_track.php
DB Evidence:  if ($this->subject_id == 21 && !$chidonLimmud) $sql .= " AND mission_name NOT LIKE '%Chidon Limmud%'";
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MSN-016
Category:     Missions
Description:  A mission is considered completed when all mandatory tasks within it are fully marked.
              Completion is stored in date_tasks_mission_marks. If tasks are unmarked, the mission mark
              is deleted. If all mandatory tasks are done, the mission is inserted as marked. A mission
              may only have one mark per user (PRIMARY KEY on user_id + date_tasks_mission_id).
Source:       mashpia.com/public/classes/missions_updater.php; mashpia.com/public/api/missions/mark.php (updateMission)
DB Evidence:  PRIMARY KEY (user_id, date_tasks_mission_id) in date_tasks_mission_marks;
              mandatory_qty=1 filter in mission completion check
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MSN-017
Category:     Missions
Description:  When admin adds missions manually (via add_missions.php), the system checks how many
              missions the student has already completed for that subject and only inserts the difference
              to reach the requested total. It never decrements missions — it only adds.
Source:       mashpia.com/public/add_missions.php
DB Evidence:  $missing = $total - $finished; for ($j = 0; $j < $missing; $j++) { /* insert */ }
              "if ($finished < $total) ... else { continue; }"
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MSN-018
Category:     Missions
Description:  Tanya and Mishna marks (grid IDs 21001–21008, 21013, 21014) trigger a special update to
              a lines_learned table for the Yud Alef Nissan campaign. A mark value > 0 upserts the
              record; a mark value of 0 deletes it. These grid IDs are specifically linked to Tanya
              or Mishna campaigns by the campaign type field.
Source:       mashpia.com/public/api/missions/mark.php (create method)
DB Evidence:  $gridIds = [21001,21002,21003,21004,21005,21006,21007,21008,21013,21014];
              if (strtolower($row['type']) == 'tanya') $tanyaCampaign; else if ('mishna') $mishnaCampaign;
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MSN-019
Category:     Missions
Description:  In the pointsDB missions system, a mission can specify percentage_required (default 100),
              meaning a student must complete that percentage of tasks to count the mission as done.
              Missions also support a default_velocity (default 1.00) which controls the pace/speed
              of the mission.
Source:       pointsDB_missions.sql (table: missions)
DB Evidence:  percentage_required INT DEFAULT 100; default_velocity DECIMAL(10,2) DEFAULT 1.00
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MSN-020
Category:     Missions
Description:  Completing a mission may trigger an automatic points_up, medal_up, or rank_up event,
              as configured on the mission record in pointsDB. These fields are nullable, meaning
              not every mission triggers a promotion.
Source:       pointsDB_missions.sql (table: missions)
DB Evidence:  points_up INT DEFAULT NULL; medal_up VARCHAR(255) DEFAULT NULL; rank_up VARCHAR(255) DEFAULT NULL
Confidence:   Medium
SME Verified: No
```

---

## Tasks

```
Rule ID:      BR-TSK-001
Category:     Tasks
Description:  Tasks in the legacy system (mashpiadb.tasks) support five recurrence types:
              daily, weekly, monthly_date, monthly_week, and yearly. Each task has a start_date,
              end_date, and an "every" interval (e.g. every 2 weeks).
Source:       mashpiadb_tasks.sql (table: tasks)
DB Evidence:  rep_type ENUM('daily','weekly','monthly_date','monthly_week','yearly') NOT NULL;
              every SMALLINT(5) NOT NULL; rep_param1 TINYINT(3) NOT NULL; rep_param2 TINYINT(3) NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-TSK-002
Category:     Tasks
Description:  Tasks in the date_tasks system are categorized as either "daily" (daily_task=1) or
              "non-daily/weekly/Shabbos" (daily_task=0). This flag controls how marks are counted
              (binary per-day vs. single mark per period).
Source:       mashpiadb_date_tasks.sql (table: date_tasks); mashpia.com/public/api/missions/mark.php
DB Evidence:  daily_task TINYINT(1) UNSIGNED NOT NULL DEFAULT 0;
              if ($user_task['daily_task'] ...) $done_qty = $done_qty ? '1' : '0';
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-TSK-003
Category:     Tasks
Description:  Tasks have a mandatory_qty flag (tinyint, 1=mandatory, 0=optional/bonus). Only mandatory
              tasks (mandatory_qty=1) are checked when determining if a mission is complete.
              Non-mandatory tasks are bonus tasks and do not block mission completion.
Source:       mashpiadb_date_tasks.sql; mashpia.com/public/classes/missions_updater.php;
              mashpia.com/public/classes/user_track.php
DB Evidence:  mandatory_qty SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0;
              WHERE dt.mandatory_qty = 1 in mission completion queries
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-TSK-004
Category:     Tasks
Description:  Tasks may have a quantity requirement (date_tasks.quantity). When set, the student must
              achieve done_qty >= quantity for the task to be counted as complete. When null, any mark
              counts as completion for non-daily tasks.
Source:       mashpiadb_date_tasks.sql; mashpia.com/public/classes/missions_updater.php
DB Evidence:  quantity SMALLINT(5) UNSIGNED DEFAULT NULL;
              AND (dtm.done_qty < dt.quantity) in mission completion checks
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-TSK-005
Category:     Tasks
Description:  Tasks may be flagged as "bonus" (is_bonus=1). Bonus tasks do not affect mission completion
              but may still earn points.
Source:       mashpiadb_date_tasks.sql (table: date_tasks)
DB Evidence:  is_bonus TINYINT(3) UNSIGNED NOT NULL DEFAULT 0
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-TSK-006
Category:     Tasks
Description:  Tasks have a "needed" field (needed tinyint, NOT NULL) which for daily tasks indicates
              how many times (days) the task needs to be marked to satisfy the requirement within
              the mission period.
Source:       mashpiadb_date_tasks.sql; mashpia.com/public/api/missions/mark.php (daily_check query)
DB Evidence:  needed TINYINT(1) UNSIGNED NOT NULL;
              HAVING dt.needed > total (count of daily marks)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-TSK-007
Category:     Tasks
Description:  Tasks can be marked as "focus tasks" (focus_task=1). Focus tasks are visually emphasized
              in the UI but follow the same completion rules as regular tasks.
Source:       mashpiadb_date_tasks.sql
DB Evidence:  focus_task TINYINT(1) UNSIGNED NOT NULL DEFAULT 0
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-TSK-008
Category:     Tasks
Description:  Tasks have a "default_on" setting (default 1). When default_on=0, the task is hidden
              unless the user has explicitly opted into it via personalization settings.
Source:       mashpiadb_date_tasks.sql; mashpia.com/public/classes/daily_task.php
DB Evidence:  default_on TINYINT(3) UNSIGNED NOT NULL DEFAULT 1;
              $this->default_on = isset($row['default_on']) ? $row['default_on'] : 1;
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-TSK-009
Category:     Tasks
Description:  Tasks are grouped into categories (cat column, up to 100 chars) and sorted within categories
              by cat_ord (decimal). Tasks with an empty cat or empty short_name are excluded from the
              class grid view.
Source:       mashpiadb_date_tasks.sql; mashpia.com/public/api/missions/grid.php
DB Evidence:  cat VARCHAR(100) DEFAULT NULL; cat_ord DECIMAL(6,2) DEFAULT NULL;
              AND dt.cat != '' AND dt.short_name != '' in grid query
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-TSK-010
Category:     Tasks
Description:  Tasks are organized within a grid system using grid_id. Multiple date_task rows may share
              the same grid_id, meaning they are displayed and marked together as a single grid cell.
              When marking, existing marks for a grid_id on a given day are deleted before inserting
              the new mark.
Source:       mashpiadb_date_tasks.sql; mashpia.com/public/api/missions/mark.php
DB Evidence:  grid_id INT UNSIGNED DEFAULT NULL; KEY grid (grid_id);
              DELETE dtm FROM date_tasks_marks dtm JOIN date_tasks dt USING (date_task_id)
              WHERE dt.grid_id = :grid_id AND user_id = :user_id AND mark_date = :mark_date
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-TSK-011
Category:     Tasks
Description:  Tasks can have mission_marking and grid_marking flags. mission_marking controls whether
              the task appears in the mission-marking view; grid_marking controls appearance in the
              grid view. Both are nullable, suggesting they may or may not be set.
Source:       mashpiadb_date_tasks.sql; mashpia.com/public/api/missions/tasks.php (personalize endpoint)
DB Evidence:  mission_marking TINYINT(1) UNSIGNED DEFAULT NULL;
              grid_marking TINYINT(1) UNSIGNED DEFAULT NULL
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-TSK-012
Category:     Tasks
Description:  Tasks are scoped to users through level, track, and school_type. A task is only active
              (task_active) for a specific combination of (task_id, school_type_id, level, track_id),
              and tasks have per-scope point values and optional quantity overrides.
Source:       mashpiadb_task_active.sql (table: task_active)
DB Evidence:  PRIMARY KEY (task_id, school_type_id, track_id, level);
              points DECIMAL(6,2) UNSIGNED NOT NULL DEFAULT 0.00; quantity SMALLINT(5) UNSIGNED DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-TSK-013
Category:     Tasks
Description:  Task frequency is defined by a frequencies table with per-day-of-week flags (monday through
              sunday, plus shabbos as a separate day from saturday). Each frequency is associated with
              a frequency_period. A task can be marked on only the days indicated by its frequency flags.
Source:       mashpiadb_frequencies.sql (table: frequencies)
DB Evidence:  monday TINYINT(1) UNSIGNED NOT NULL DEFAULT 0; ... shabbos TINYINT(1) UNSIGNED NOT NULL DEFAULT 0;
              sunday TINYINT(1) UNSIGNED NOT NULL DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-TSK-014
Category:     Tasks
Description:  Tasks in the global_tasks system are keyed to a mission, campaign, camp type, level, and
              period. Each global task has a fixed point value and must have a unique combination of
              task_id and task_name.
Source:       mashpiadb_global_tasks.sql (table: global_tasks)
DB Evidence:  UNIQUE KEY mission (task_id, task_name); period_id INT UNSIGNED NOT NULL; level_id INT UNSIGNED NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-TSK-015
Category:     Tasks
Description:  Task scoping follows a hierarchy: tasks can be assigned at the school level (school_tasks),
              class level (class_tasks), user level (user_tasks), or group level (group_tasks). Group
              tasks additionally track whether the task is a "group task" (group_task flag, default 0)
              and belong to a division and period.
Source:       mashpiadb_school_tasks.sql, mashpiadb_class_tasks.sql, mashpiadb_user_tasks.sql,
              mashpiadb_group_tasks.sql
DB Evidence:  group_task TINYINT(1) UNSIGNED NOT NULL DEFAULT 0; division_id INT UNSIGNED NOT NULL DEFAULT 0;
              UNIQUE KEY group_task (group_id, camp_task_id)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-TSK-016
Category:     Tasks
Description:  The pointsDB tasks system supports two frequency types: 'One-Time' and 'Recurring'.
              One-Time tasks can only be completed once; Recurring tasks can be completed multiple times.
Source:       pointsDB_tasks.sql (table: tasks)
DB Evidence:  frequency ENUM('One-Time','Recurring') DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-TSK-017
Category:     Tasks
Description:  In the pointsDB system, tasks can be locked (is_locked=1), preventing new marks.
              Tasks can also be toggled active/inactive (is_active, default 1). A locked task
              is visible but not editable.
Source:       pointsDB_tasks.sql (table: tasks)
DB Evidence:  is_locked TINYINT(1) UNSIGNED DEFAULT 0; is_active TINYINT(1) DEFAULT 1
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-TSK-018
Category:     Tasks
Description:  In the pointsDB system, tasks have a velocity (decimal, 2 decimal places) which likely
              controls the rate at which tasks are expected to be completed or the pace of points accrual.
Source:       pointsDB_tasks.sql (table: tasks)
DB Evidence:  velocity DECIMAL(10,2) DEFAULT NULL
Confidence:   Low
SME Verified: No
```

```
Rule ID:      BR-TSK-019
Category:     Tasks
Description:  The pointsDB scheduling system supports fine-grained scheduling at multiple levels:
              Yearly, Monthly, Weekly, Daily. Tasks and missions can have schedules that specify
              which years, months, weeks, days-of-month, and days-of-week they are active.
Source:       pointsDB_scheduling_params.sql (table: scheduling_params), pointsDB_schedules.sql
DB Evidence:  frequency ENUM('Yearly','Monthly','Weekly','Daily') DEFAULT NULL;
              years, weeks_in_year, days_in_year, months, weeks_in_month, days_in_month, days_of_week columns
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-TSK-020
Category:     Tasks
Description:  Mivtzoim (outreach campaigns) are time-bounded campaigns with start and end dates.
              Each mivtzoim campaign links to tasks by short_name. A task's short_name is the key
              used to associate it with mivtzoim campaigns. A (mivtzoim_id, short_name) pair is unique.
Source:       mashpiadb_mivtzoim.sql; mashpiadb_mivtzoim_tasks.sql
DB Evidence:  UNIQUE KEY main (mivtzoim_id, short_name); start INT UNSIGNED DEFAULT NULL; end INT UNSIGNED DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-TSK-021
Category:     Tasks
Description:  Tasks used in the pointsDB system may be assessed via a graded scale (tasks_scale).
              Each scale entry has a grade (text), a ladder value (integer ranking), a percentage,
              and an is_required flag (default 1). The scale provides rubric-style scoring for a task.
Source:       pointsDB_tasks_scale.sql (table: tasks_scale)
DB Evidence:  grade VARCHAR(55) NOT NULL; ladder INT(11) NOT NULL; percentage INT DEFAULT NULL;
              is_required TINYINT(1) DEFAULT 1
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-TSK-022
Category:     Tasks
Description:  Subjects 12, 40, and 136 are excluded from the standard subject listing for regular
              mark/streak views. Additionally, subject 15 is excluded when listing subjects for streaks.
              This suggests these subjects have special handling and are not regular task subjects.
Source:       mashpia.com/public/api/missions/subjects.php
DB Evidence:  $exceptions = [12, 40, 136]; if (isset($_GET['for_streak'])) { $exceptions = [12, 15, 40, 136]; }
Confidence:   High
SME Verified: No
```

---

## Marks & Streaks

```
Rule ID:      BR-MRK-001
Category:     Marks & Streaks
Description:  Task marks (date_tasks_marks) record the mark date (Julian Day Number), quantity done
              (done_qty, default 0), points awarded (mark_points, default 0.00), and a general
              quantity field (mark_quantity, default 0). A mark can be set inactive (mark_inactive=1)
              without deleting the record, preserving history.
Source:       mashpiadb_date_tasks_marks.sql (table: date_tasks_marks)
DB Evidence:  done_qty SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0;
              mark_points DECIMAL(8,2) UNSIGNED NOT NULL DEFAULT 0.00;
              mark_inactive TINYINT(1) NOT NULL DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MRK-002
Category:     Marks & Streaks
Description:  The marks table (legacy system) allows one mark per (user_id, mark_date, task_id).
              A mark stores a level, optional track_id, points, and optional quantity. The primary
              key enforces one mark per task per user per day.
Source:       mashpiadb_marks.sql (table: marks)
DB Evidence:  PRIMARY KEY (user_id, mark_date, task_id)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MRK-003
Category:     Marks & Streaks
Description:  Mission marks (date_tasks_mission_marks) record when a user completes a mission.
              A user can only have one mission mark per (user_id, date_tasks_mission_id) — once
              marked, it cannot be marked again for the same mission instance.
Source:       mashpiadb_date_tasks_mission_marks.sql (table: date_tasks_mission_marks)
DB Evidence:  PRIMARY KEY (user_id, date_tasks_mission_id)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MRK-004
Category:     Marks & Streaks
Description:  Mission marks can be overridden by an admin (mark_override=1). When override is set,
              the mission is counted as complete regardless of whether underlying tasks are done.
              By default mark_override=0 (no override).
Source:       mashpiadb_date_tasks_mission_marks.sql; mashpia.com/public/classes/missions_updater.php
DB Evidence:  mark_override TINYINT(1) UNSIGNED NOT NULL DEFAULT 0;
              mark_override=0 set during normal insert
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MRK-005
Category:     Marks & Streaks
Description:  The date_tasks_marks table has a noDuplicates column (default 0). When set to a non-zero
              value, this likely prevents duplicate marks from being recorded for the same user+task+date.
              The exact enforcement logic is in application code rather than a database constraint.
Source:       mashpiadb_date_tasks_marks.sql (table: date_tasks_marks)
DB Evidence:  noDuplicates INT(10) UNSIGNED NOT NULL DEFAULT 0
Confidence:   Low
SME Verified: No
```

```
Rule ID:      BR-MRK-006
Category:     Marks & Streaks
Description:  When marking a task via the grid API, the system first deletes all existing marks for
              that grid_id + user_id + mark_date, then inserts the new mark. This means marking a
              task is idempotent — re-marking overwrites the previous mark.
Source:       mashpia.com/public/api/missions/mark.php (updateTask method)
DB Evidence:  DELETE dtm FROM date_tasks_marks JOIN date_tasks USING (date_task_id)
              WHERE dt.grid_id = :grid_id AND user_id = :user_id AND mark_date = :mark_date;
              followed by INSERT ... ON DUPLICATE KEY UPDATE done_qty ...
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MRK-007
Category:     Marks & Streaks
Description:  A mark of 0 (falsy) means "unmark". In the marking API, if the mark value is 0/false,
              no INSERT is performed — only the existing DELETE fires. This effectively removes the mark.
Source:       mashpia.com/public/api/missions/mark.php (updateTask method)
DB Evidence:  if ($mark) { /* INSERT mark */ } — the block is skipped when mark == 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MRK-008
Category:     Marks & Streaks
Description:  After each batch of marks is processed (per date, per user), the weekly total task cache
              is updated. Medal and rank status are rechecked and updated for all affected users after
              all marks in the batch are written.
Source:       mashpia.com/public/api/missions/mark.php (create method)
DB Evidence:  TotalWeeklyTasks::updateUser($user_id, $mark_date, false);
              $medal_updater->update_medal_two($user_id); $rank_updater->update_rank_two($user_id);
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MRK-009
Category:     Marks & Streaks
Description:  Streaks track consecutive completion of tasks. A streak is identified by streak_id and
              is associated with tasks via the streak_id column in date_tasks. A streak belongs to a
              specific user and year, and records how many days/periods are needed and how many have
              been achieved.
Source:       mashpiadb_streak_tasks.sql (table: streak_tasks); mashpia.com/public/mobile/streaks/classes/class.streaks.php
DB Evidence:  UNIQUE KEY hachloto (user_id, streak_id, year);
              num_days INT UNSIGNED DEFAULT NULL; days_needed INT UNSIGNED DEFAULT NULL;
              task_type ENUM('daily','weekly','monthly','other') DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MRK-010
Category:     Marks & Streaks
Description:  Streak task types define the required interval between marks for the streak to remain
              unbroken: daily requires a mark each day (interval=1), weekly requires a mark every
              7 days (interval=7), monthly requires a mark every 28 days (interval=28).
              Any gap exceeding the interval breaks the streak.
Source:       mashpia.com/public/mobile/streaks/classes/class.streaks.php (computeDaysDone method)
DB Evidence:  $needed_interval = ($task_type === 'weekly') ? 7 : (($task_type === 'monthly_tasks') ? 28 : 1);
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MRK-011
Category:     Marks & Streaks
Description:  Streaks are calculated by scanning backward from the end date. The algorithm counts
              consecutive marks within the allowed interval window, stopping at the first break
              where the interval is exceeded and no mark has yet been found in that window.
Source:       mashpia.com/public/mobile/streaks/classes/class.streaks.php (getDaysDone / computeDaysDone)
DB Evidence:  for (; $end >= $this->start; $end--) { ... if (ctr > needed_interval && !found) break; }
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MRK-012
Category:     Marks & Streaks
Description:  Streak type is determined by the underlying task: if the date_task is daily (daily_task=1),
              the streak is 'daily'. If the task belongs to subject_id=1, the streak is 'monthly'.
              All other non-daily tasks default to 'weekly' streaks.
Source:       mashpia.com/public/mobile/streaks/classes/class.streaks.php (getTaskType method)
DB Evidence:  if ($row['daily_task']) { $task_type = 'daily'; }
              else if ($row['subject_id'] == 1) { $task_type = 'monthly'; }
              else { $task_type = 'weekly'; }
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MRK-013
Category:     Marks & Streaks
Description:  Streak setup (via createStreak.php) looks back 90 days from the current date to initialize
              the streak window. The number of required days (days_needed) is computed as:
              - daily: total days in window
              - weekly: floor(days / 7)
              - monthly: floor(days / 28)
              Minimum days_needed is always 1.
Source:       mashpia.com/public/api/missions/createStreak.php; mashpia.com/public/mobile/streaks/classes/class.streaks.php (setupStreak)
DB Evidence:  $start = $end - 90; if ($task_type == 'weekly') { $days_needed = floor($num_days / 7); }
              if ($days_needed < 1) $days_needed = 1;
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MRK-014
Category:     Marks & Streaks
Description:  Only streaks with streak_show=1 are visible to users. Streaks with streak_show=0 are
              tracked internally but hidden from the UI.
Source:       mashpia.com/public/mobile/streaks/classes/class.streaks.php (setStreaks query)
DB Evidence:  WHERE st.user_id = :user_id AND dt.streak_show = 1
              streak_show TINYINT(1) UNSIGNED DEFAULT 0 in date_tasks
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MRK-015
Category:     Marks & Streaks
Description:  Task completion for non-daily tasks requires done_qty >= quantity. For tasks without a
              quantity (quantity IS NULL), any single mark (date_task_id not null in marks table)
              counts as completion for the mission.
Source:       mashpia.com/public/classes/missions_updater.php; mashpia.com/public/classes/user_track.php
DB Evidence:  AND dt.quantity IS NULL AND dtm.date_task_id IS NULL (checks for unstarted tasks)
              AND dt.quantity IS NOT NULL AND (dtm.done_qty < dt.quantity) (checks quantity tasks)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MRK-016
Category:     Marks & Streaks
Description:  The task_marks table has a "test" column (enum 'a','b', NOT NULL), suggesting an A/B
              testing split for marks. This field must always be set when inserting a mark record.
Source:       mashpiadb_task_marks.sql (table: task_marks)
DB Evidence:  test ENUM('a','b') NOT NULL
Confidence:   Low
SME Verified: No
```

```
Rule ID:      BR-MRK-017
Category:     Marks & Streaks
Description:  Date task marks support a mechunach_id reference (default 0), linking a mark to a
              specific educator/mechunach who recorded it. This provides traceability for marks.
Source:       mashpiadb_date_tasks_marks.sql (table: date_tasks_marks)
DB Evidence:  mechunach_id INT(10) UNSIGNED NOT NULL DEFAULT 0; KEY mechunach (mechunach_id)
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-MRK-018
Category:     Marks & Streaks
Description:  Mission marks track a missions_updated flag (default 0). When set to 1 after a mark is
              processed, it indicates that the downstream mission progress calculation has been run
              for this mark. This prevents double processing.
Source:       mashpiadb_date_tasks_mission_marks.sql (table: date_tasks_mission_marks)
DB Evidence:  missions_updated TINYINT(1) UNSIGNED NOT NULL DEFAULT 0
Confidence:   Medium
SME Verified: No
```

---

## Medals & Ranks

```
Rule ID:      BR-MDL-001
Category:     Medals & Ranks
Description:  Medals are awarded per subject. To earn a medal, a user must complete a cumulative number
              of missions in a given subject. The missions required for each medal is stored in
              medals_subjects.missions_required. Medal thresholds are additive: each successive medal
              requires completing medals_subjects.missions_required *additional* missions beyond the
              previous medal's requirement.
Source:       mashpia.com/public/classes/medal_updater.php; mashpia.com/public/classes/user_track.php
DB Evidence:  $missions_required += $row['missions_required']; /* cumulative sum */
              if ($finished_missions >= $missions_required) /* then award medal */
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MDL-002
Category:     Medals & Ranks
Description:  The medal award check runs every time a user's tasks are marked. If the cumulative
              missions for a subject have reached a medal threshold, the medal is awarded (inserted
              into medal_marks). If missions fall below a threshold (e.g. after an admin correction),
              the medal is removed (deleted from medal_marks).
Source:       mashpia.com/public/classes/medal_updater.php (update_medal_two)
DB Evidence:  if ($finished_missions >= $missions_required) { INSERT INTO medal_marks ... }
              else { DELETE FROM medal_marks WHERE medal_ord=... }
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MDL-003
Category:     Medals & Ranks
Description:  Medals are ordered by medal_ord (tinyint unsigned, primary key). Medal names must be
              unique across the system. Medals have English, French, and Hebrew name fields.
              Medal images come in "on" and "off" variants (for earned vs. not-yet-earned display).
Source:       mashpiadb_medals.sql (table: medals)
DB Evidence:  PRIMARY KEY (medal_ord); UNIQUE KEY medal_name (medal_name); UNIQUE KEY medal_name_fr (medal_name_fr);
              medal_name_he VARCHAR(45) DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MDL-004
Category:     Medals & Ranks
Description:  Medal marks record the date awarded (Julian Day Number), shipping dates, and receipt
              dates. A medals_updated flag (default 0) tracks whether the medal has been synced to
              downstream systems. The new_system_updated flag (default 0) tracks sync with a newer
              points system. prof_medals_updater tracks professional medal processing.
Source:       mashpiadb_medal_marks.sql (table: medal_marks)
DB Evidence:  medals_updated TINYINT(1) UNSIGNED NOT NULL DEFAULT 0;
              new_system_updated TINYINT(1) NOT NULL DEFAULT 0;
              prof_medals_updater TINYINT(4) NOT NULL DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MDL-005
Category:     Medals & Ranks
Description:  Medal inventory is tracked per (subject_id, medal_ord, medal_type). Two medal back types
              exist: 'number_on_back' and 'picture_on_back' (default). Each combination can have a
              separate in_stock count.
Source:       mashpiadb_medals_inventory.sql (table: medals_inventory)
DB Evidence:  medal_type ENUM('number_on_back','picture_on_back') NOT NULL DEFAULT 'picture_on_back';
              UNIQUE KEY Index 2 (subject_id, medal_ord, medal_type); in_stock INT NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MDL-006
Category:     Medals & Ranks
Description:  Ranks are earned by accumulating medals. Each rank has a medals_required threshold.
              A user attains a rank when their total medal count (across all subjects) meets or
              exceeds that rank's medals_required value. Ranks are ordered by rank_ord (primary key,
              auto-increment). The system supports up to 14 ranks (AUTO_INCREMENT=15).
Source:       mashpiadb_ranks.sql (table: ranks); mashpia.com/public/classes/rank_updater.php
DB Evidence:  medals_required TINYINT(3) UNSIGNED NOT NULL;
              if ($medals_aquired >= $medals_required) { INSERT INTO rank_marks ... }
              AUTO_INCREMENT=15 (meaning 14 ranks currently defined)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MDL-007
Category:     Medals & Ranks
Description:  The rank check runs every time a user's tasks are marked. If a rank threshold is met,
              the rank is inserted into rank_marks. If the medal count drops below the threshold
              (e.g. medals were removed), the rank is deleted from rank_marks.
Source:       mashpia.com/public/classes/rank_updater.php (update_rank_two)
DB Evidence:  if ($medals_aquired >= $medals_required) { INSERT INTO rank_marks ... }
              else { DELETE FROM rank_marks WHERE rank_ord=... AND user_id=... }
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MDL-008
Category:     Medals & Ranks
Description:  When a user earns a new rank for the first time, a WordPress "promotion" post is
              automatically created (post_type='promotion') with the user's name, school, gender,
              and rank name as metadata. This powers a public-facing promotion feed.
Source:       mashpia.com/public/classes/rank_updater.php (updateWP / import_promotion)
DB Evidence:  'post_type' => 'promotion'; add_post_meta($id, 'rank', $info['rankName']);
              add_post_meta($id, 'user_id', $info['user']); add_post_meta($id, 'school', ...)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MDL-009
Category:     Medals & Ranks
Description:  Rank marks track physical reward logistics: date_promoted (when rank was achieved),
              date_printed (when card was printed), date_book_shipped, date_book_received,
              date_card_shipped, date_card_received. A ranks_updated flag (default 0) tracks
              downstream sync status.
Source:       mashpiadb_rank_marks.sql (table: rank_marks)
DB Evidence:  date_printed TIMESTAMP NULL DEFAULT NULL; date_book_shipped TIMESTAMP NULL DEFAULT NULL;
              date_card_shipped TIMESTAMP NULL DEFAULT NULL; ranks_updated TINYINT(1) UNSIGNED NOT NULL DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MDL-010
Category:     Medals & Ranks
Description:  Ranks have English, French, and Hebrew name variants. They also carry visual attributes
              (rank_color, rank_color_fr for language-specific colors, rank_image_id, rank_background_image_id,
              prof_rank_image_id). Ranks also have a shipping_code for logistics.
Source:       mashpiadb_ranks.sql (table: ranks)
DB Evidence:  rank_name_fr VARCHAR(255) DEFAULT NULL; rank_name_he VARCHAR(45) DEFAULT NULL;
              rank_color VARCHAR(32) NOT NULL; shipping_code VARCHAR(45) DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MDL-011
Category:     Medals & Ranks
Description:  In the pointsDB medals system, medals have a medal_hierarchy (integer) and a medal_value
              (integer). The hierarchy determines ordering, and the value likely determines the point
              threshold or point reward for earning the medal. Medals are scoped to a campaign and
              institution.
Source:       pointsDB_medals.sql (table: medals)
DB Evidence:  medal_hierarchy INT(10) UNSIGNED NOT NULL; medal_value INT(10) UNSIGNED NOT NULL;
              institution_id INT UNSIGNED NOT NULL; campaign_id INT UNSIGNED NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MDL-012
Category:     Medals & Ranks
Description:  In the pointsDB ranks system, a rank is earned by accumulating a specific total of
              points (rank_points, unsigned integer). Ranks are institution-scoped and have an
              associated rank image.
Source:       pointsDB_ranks.sql (table: ranks)
DB Evidence:  rank_points INT(11) UNSIGNED NOT NULL; institution_id INT UNSIGNED NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MDL-013
Category:     Medals & Ranks
Description:  The pointsDB rules system supports Allow/Deny rules (rule_type ENUM) that can be applied
              to specific users, institutions, campaigns, and prizes. Rules have a rule_applies_to
              descriptor and a rule text. By default a rule is of type 'Deny'. Rules can be activated
              or deactivated (is_active, default 1).
Source:       pointsDB_rules.sql (table: rules)
DB Evidence:  rule_type ENUM('Allow','Deny') NOT NULL DEFAULT 'Deny';
              is_active TINYINT(1) DEFAULT 1; rule_applies_to VARCHAR(255) NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MDL-014
Category:     Medals & Ranks
Description:  The possible_missions count used for medal eligibility is the sum of completed_missions
              and incomplete_missions (missions in the user's date range, whether done or not).
              A medal is potentially achievable if possible_missions >= cumulative missions_required.
Source:       mashpia.com/public/classes/user_track.php (set_possible_missions, get_subject_medals)
DB Evidence:  $this->possible_missions = $this->completed_missions + $this->incomplete_missions;
              if ($this->possible_missions >= $missions_required) { /* award medal UI */ }
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-MDL-015
Category:     Medals & Ranks
Description:  Goals define a date range (goal_start and goal_end) per (school_type, subject, level, track).
              Goals represent the overall academic year target for a track. The primary key ensures
              each track combination has at most one goal definition.
Source:       mashpiadb_goals.sql (table: goals)
DB Evidence:  PRIMARY KEY (school_type_id, subject_id, level, track_id);
              goal_start VARCHAR(255) NOT NULL; goal_end VARCHAR(255) NOT NULL
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-MDL-016
Category:     Medals & Ranks
Description:  The levels table has exactly 3 levels (AUTO_INCREMENT=4), representing educational
              grade bands (e.g. elementary, middle, high school or equivalent in the Tzivos Hashem
              context).
Source:       mashpiadb_levels.sql (table: levels)
DB Evidence:  AUTO_INCREMENT=4 (3 rows in table)
Confidence:   Low
SME Verified: No
```

---

*End of extracted business rules. Total rules: 57.*

**Next Steps for SME Verification:**
- Confirm medal missions_required values per subject (medals_subjects table not included in dump list).
- Confirm the meaning of subject IDs 12, 15, 40, 136 (exceptions list in subjects.php).
- Confirm rank medals_required thresholds for all 14 ranks.
- Confirm the purpose of `award_left_circle` / `award_right_circle` fields.
- Confirm whether `mark_override=1` can override non-mandatory task completion.
- Confirm the meaning of `bp_type` IDs 1 and 2.
- Clarify whether `noDuplicates` in date_tasks_marks is enforced in application logic or is now defunct.
