# Module: Missions

This document catalogs what the system *decides*, *enforces*, and *allows* for the Missions domain. It is organized by sub-module.

---

## Sub-Module: Missions (General)

### Business Rules

1. A mission belongs to a campaign (subject), has a start date and end date expressed as Julian Day Numbers, and is tied to a specific school type and language.
   Source: `SQLdump/mashpiadb_date_tasks_marks.sql`, `public/api/missions/mark.php`:lines 42–50

2. Missions are scoped by user: a user is only eligible for missions that match their enrolled level (from `user_tracks`), their school type, and their language ID.
   Source: `public/api/missions/mark.php`:lines 42–50 (`JOIN user_tracks`, `JOIN users … school_type_id`, `lang_id`)

3. A mission is only active for a soldier when its `start_date ≤ mark_date ≤ end_date`; marks outside that window are silently ignored because the date-task lookup query returns no rows.
   Source: `public/api/missions/mark.php`:line 50

4. Only soldiers who are registered (`user_registered > 0`) appear in any grid or mission list.
   Source: `public/api/missions/grid.php`:line 24; `public/mission_report/classes/missions.php`:line 42

5. The grid endpoint restricts the soldier list to the current teacher's platoon (`class_id`); teachers cannot see soldiers from other platoons.
   Source: `public/api/missions/grid.php`:lines 22–25

6. The grid API endpoint (`/missions/grid`) requires the caller to have `login->code === 'TEACHER'`; all other roles are blocked.
   Source: `public/api/missions/grid.php`:lines 8–10 (`authenticate()`)

7. Missions marked with `personal = 1` are hidden from the grid view and from the tehillim endpoint.
   Source: `public/api/missions/grid.php`:line 146 (`dtm.personal = 0`); `public/api/missions/tehillim.php`:line 22

8. Tasks must have a non-empty `cat` and non-empty `short_name` to appear in the grid.
   Source: `public/api/missions/grid.php`:lines 147–148

9. School-created tasks (`created_by_school`) are only shown to teachers from that same school; global tasks (null `created_by_school`) are shown to all schools.
   Source: `public/api/missions/grid.php`:line 154

10. The mission list for the grid is filtered to subjects enrolled by at least one registered soldier in the class, and for daily/weekly type separately.
    Source: `public/api/missions/grid.php`:lines 118–120, 150, 157–165

11. After every mark operation, each affected soldier's medal and rank are recalculated immediately.
    Source: `public/api/missions/mark.php`:lines 119–125

12. After every mark operation, the weekly-task cache (`TotalWeeklyTasks`) is updated for each affected soldier.
    Source: `public/api/missions/mark.php`:line 72

13. A mission is considered "completed" when all its mandatory non-daily tasks have a mark meeting the required quantity AND all its mandatory daily tasks have marks on the required number of days; if any are missing, the mission-completion record is deleted.
    Source: `public/api/missions/mark.php`:lines 186–230 (`updateMission()`)

14. Mission completion is recorded via `INSERT IGNORE` so the first completion date is preserved even if the same mission is re-evaluated.
    Source: `public/api/missions/mark.php`:lines 208–213

15. Subjects with IDs 12, 40, and 136 are always excluded from the subjects list. When loading subjects for streaks, subject 15 is additionally excluded.
    Source: `public/api/missions/subjects.php`:lines 23–26

16. Only subjects where at least one user is actively enrolled (`ut.enrolled = 1`) are shown.
    Source: `public/api/missions/subjects.php`:lines 29–37

---

## Sub-Module: Tasks

### Business Rules

1. Tasks are classified as either daily (`daily_task = 1`) or non-daily (weekly/shabbos/other); the `daily_task` flag changes how the quantity is stored on marking.
   Source: `public/api/missions/mark.php`:lines 149–150; `SQLdump/mashpiadb_date_tasks.sql`:line 40

2. A task has a `mandatory_qty` flag. When it is `1` (and the task is non-daily), the task's completion contributes to whether the parent mission is marked as complete.
   Source: `public/api/missions/mark.php`:lines 187–194; `SQLdump/mashpiadb_date_tasks.sql`:line 32

3. A task has a `needed` count (for daily tasks). A daily mission is only complete when the actual number of daily marks equals or exceeds `needed`.
   Source: `public/api/missions/mark.php`:lines 196–205

4. Each task has a `grid_id` that serves as the public identifier used for marking; a null `grid_id` means the task is not markable via the grid.
   Source: `SQLdump/mashpiadb_date_tasks.sql`:line 46; `public/api/missions/grid.php` (commented-out filter at line 148)

5. A task has two separate visibility flags: `mission_marking` controls whether it appears on printed mission sheets; `grid_marking` controls whether it appears in the electronic marking grid.
   Source: `SQLdump/mashpiadb_date_tasks.sql`:lines 52–53; `public/api/missions/tasks/includes/columns.js`:lines 18–29

6. Tasks are organized by label, and each label carries a `frequency_id` that determines whether the task is Daily, Weekly, Shabbos, or Pesukim-frequency.
   Source: `public/api/missions/labels.php`; `SQLdump/mashpiadb_labels.sql`:line 32; `public/api/print/missions_print_cache.php`:lines 15–19

7. Only active labels (`active = 1`) are returned by the labels API.
   Source: `public/api/missions/labels.php`:line 8

8. School-specific tasks (`created_by_school`) are only visible to users belonging to that school or institution; global tasks are visible everywhere.
   Source: `public/api/missions/tasks.php` (PersonalizeRouter GET):lines 27–33

9. Task listing is scoped to the current program year (`start_date >= :start AND end_date <= :end`).
   Source: `public/api/missions/tasks.php`:line 35

10. Tasks created by a parent (`created_by_parent IS NOT NULL`) are filtered out of the tasks management page.
    Source: `public/api/missions/tasks.php`:line 28

11. A new task requires a valid `school_id`; if none is provided and the current user has no school association, the creation is rejected with "Invalid Base".
    Source: `public/api/missions/tasks.php`:lines 113–114

12. Task customization (enable/disable, sort order) is stored per-class and per-grid-type (daily/weekly) in `class_date_task_sorting` and `class_date_tasks_disabled`.
    Source: `public/api/missions/grid.php`:lines 81–98; `SQLdump` (implied by queries)

13. Tasks have a `streak_id` field; when non-zero, streak accomplishment lookups use `streak_id` rather than `grid_id`.
    Source: `public/api/missions/streaks.php`:lines 128–132; `SQLdump/mashpiadb_date_tasks.sql`:line 55

14. Each task carries a `label_ord` that controls display ordering within its label group and on the duch.
    Source: `public/api/print/missions_print_cache.php`:lines 15–19; `SQLdump/mashpiadb_date_tasks.sql`:line 35

15. The exception system operates at three levels: school (`school_task_exceptions`), class (`class_task_exceptions`), and user (`user_task_exceptions`); a row in any of these tables suppresses the task for that scope.
    Source: `SQLdump/mashpiadb_school_task_exceptions.sql`; `SQLdump/mashpiadb_class_task_exceptions.sql`; `SQLdump/mashpiadb_user_task_exceptions.sql`

16. Mandatory status can be overridden at the school (`school_task_info`), class (`class_task_info`), and user (`user_task_info`) levels independently of the global task setting.
    Source: `SQLdump/mashpiadb_school_task_info.sql`; `SQLdump/mashpiadb_class_task_info.sql`; `SQLdump/mashpiadb_user_task_info.sql`

---

## Sub-Module: Mark / Scoring

### Business Rules

1. Marking a task always deletes any prior mark for the same `(grid_id, user_id, mark_date)` combination before inserting the new value, making marks idempotent (last write wins).
   Source: `public/api/missions/mark.php`:lines 133–157 (`updateTask()`)

2. When the mark value is zero (falsy), only the delete step runs; no mark row is inserted, effectively clearing the task for that day.
   Source: `public/api/missions/mark.php`:line 160

3. For daily tasks, the stored `done_qty` is always coerced to `1` or `0` (boolean), except for subject 136 which retains the raw numeric value.
   Source: `public/api/missions/mark.php`:lines 149–150

4. For subject 136 (a special quantity-based subject), points are calculated as `mark × task.points` and the mark is flagged as `auction_only_points = 1`, meaning those points are used only for the auction and not for standard scoring.
   Source: `public/api/missions/mark.php`:lines 161–167

5. For all other subjects, `mark_points` equals the fixed `task.points` value regardless of the quantity marked, and `auction_only_points = 0`.
   Source: `public/api/missions/mark.php`:lines 164–167

6. When a Tanya or Mishna grid ID (specifically IDs 21001–21008, 21013, 21014) is marked, the system simultaneously writes or updates the `lines_learned` table (Yud Alef Nissan campaign tables). If `mark > 0` and a row already exists, it updates; otherwise it inserts. If `mark = 0`, it deletes the `lines_learned` row.
   Source: `public/api/missions/mark.php`:lines 75–113

7. Marks can be submitted for multiple users and multiple dates in a single call; the system loops and applies the same mark value to all combinations.
   Source: `public/api/missions/mark.php`:lines 36–116

8. If a `grid_id` has no matching `date_tasks` row for a given user/date combination (e.g., user is not enrolled in that campaign, or date is outside the mission window), no mark is written for that user — it is silently skipped.
   Source: `public/api/missions/mark.php`:lines 57–61

9. The daily tab restricts date selection to the past year; the maximum selectable date is today.
   Source: `front-end/src/pages/missions/mark/tabs/DailyTab.jsx`:lines 41–43 (`maxDate={moment()}`, `minDate={moment().subtract(1, 'years')}`)

10. Teachers mark missions via a grid UI; BC (Base Commander) users mark via a per-soldier inline view organized by label.
    Source: `front-end/src/pages/missions/mark/index.jsx`:lines 18–21; `front-end/src/pages/missions/mark/BCMarkPage.jsx`

11. The marking grid shows missions for the teacher's own class only; cross-class marking is not supported from the grid.
    Source: `public/api/missions/grid.php`:lines 22–25, 30

12. The Tehillim tab is only visible on the teacher mark page when the `login.modules.tehillim` flag is enabled for that user's account.
    Source: `front-end/src/pages/missions/mark/TeacherMarkPage.jsx`:lines 83, 97

13. The mark route does NOT enforce its own authentication check (the `MASHPIA_AUTH_REQUIRED` line is commented out); access depends on the shared header authentication only.
    Source: `public/api/missions/mark.php`:line 2 (commented `define("MASHPIA_AUTH_REQUIRED", true)`)

14. Weekly grid date selection uses parsha (Torah portion) as the time unit; the parsha selector shows up to one week into the future (`endDate = today + 7`).
    Source: `front-end/src/pages/missions/mark/tabs/WeeklyTab.jsx`:line 38

---

## Sub-Module: Personalize

### Business Rules

1. Personalization operates at three distinct levels: campaign (subject), task, and individual mission. Each level has separate enroll/unenroll or enable/disable semantics.
   Source: `public/api/missions/personalize.php`:lines 194–225 (`create()`)

2. At the campaign level, a single toggle either enrolls or unenrolls all resolved users (by user_id, class, or school) from the campaign.
   Source: `public/api/missions/personalize.php`:lines 196–207

3. At the task level, unenrolling creates a task exception entry; re-enrolling removes that exception.
   Source: `public/api/missions/personalize.php`:lines 210–215

4. At the mission level, unenrolling creates a mission exception entry; re-enrolling removes that exception.
   Source: `public/api/missions/personalize.php`:lines 217–224

5. Personalization requires at minimum a `school_id`; a request without a `school_id` is rejected with "School ID Required".
   Source: `public/api/missions/personalize.php`:line 43

6. The personalize page pre-populates with the logged-in user's `school_id` and `class_id` as defaults.
   Source: `front-end/src/pages/missions/personalize/PersonalizePage.jsx`:lines 41–47

7. When multiple platoons (class_ids) are selected and no specific soldier is chosen, the frontend makes a separate API call for each class and merges the enrollment responses, showing a union of enrollments across all selected classes.
   Source: `front-end/src/store/missions/personalize/operations.js`:lines 82–98 (`getCampaigns`), lines 111–127 (`getTasks`)

8. In the merged multi-class view, if any class has a task enrolled, the merged enrollment flag is `true` (logical OR across classes).
   Source: `front-end/src/store/missions/personalize/operations.js`:line 33 (`if (enrollmentA.enrolled || enrollmentB.enrolled)`)

9. A base-wide (school-level) setting overrides any individual soldier's or platoon's settings, as stated in the UI callout.
   Source: `front-end/src/pages/missions/personalize/PersonalizePage.jsx`:line 88

10. A task marked as `mandatory` (shown with a red asterisk `*`) is visually flagged in the personalize UI, though the toggle still appears.
    Source: `front-end/src/pages/missions/personalize/includes/Task.jsx`:lines 67–69; `front-end/src/pages/missions/personalize/includes/Mission.jsx`:lines 29–32

11. When a campaign is disabled (unenrolled), the task toggles inside it are automatically disabled in the UI (cannot be changed while the parent campaign is off).
    Source: `front-end/src/pages/missions/personalize/includes/Task.jsx`:line 61 (`disabled={disabled || isUpdating}`)

12. When a task is unenrolled, its individual mission checkboxes are disabled in the UI.
    Source: `front-end/src/pages/missions/personalize/includes/Mission.jsx`:line 29

13. Task personalization date scoping defaults to the current program year unless a `parsha_id` is provided, in which case it scopes to that parsha's start/end dates.
    Source: `public/api/missions/personalize.php`:lines 67–77 (`getTasks()`)

14. The name/label/marking-mode of a task (not its structural enrollment) can be updated via PATCH, but only within the scope of a `grid_id` and `lang_id`; the update applies to all missions sharing that grid/language pair.
    Source: `public/api/missions/tasks.php`:lines 59–101 (`update()`)

---

## Sub-Module: Streaks

### Business Rules

1. A streak tracks a soldier's consecutive-day (or weekly/monthly) completion of a specific task. The configuration is stored in `streak_tasks` with fields `user_id`, `streak_id`, `year`, `num_days`, `days_needed`, and `task_type`.
   Source: `SQLdump/mashpiadb_streak_tasks.sql`

2. Setting up a streak has a 90-day window: `createStreak.php` always computes `end = unixtojd()` (today) and `start = end - 90`.
   Source: `public/api/missions/createStreak.php`:lines 10–11

3. A soldier can only have one streak of a given type per year; the `streak_tasks` table enforces a unique key on `(user_id, streak_id, year)`.
   Source: `SQLdump/mashpiadb_streak_tasks.sql`:line 36

4. If a soldier already has an active (incomplete) streak in progress, the UI blocks the user from setting up another streak until the current one reaches the required number of days.
   Source: `front-end/src/pages/missions/streaks/Streaks.jsx`:lines 148–153, 166–171

5. Streak subjects available for setup exclude subjects 12, 15, 40, and 136 (more exclusions than the standard subject list which only excludes 12, 40, 136).
   Source: `public/api/missions/subjects.php`:lines 23–26

6. The streak accomplishment API (`/missions/streaks`) accepts any of three scope parameters: `user_id`, `class_id`, or `school_id`. Exactly one must be provided.
   Source: `public/api/missions/streaks.php`:lines 27–38

7. Streak accomplishment data is loaded by querying the same `date_tasks_marks` data used for regular task marks; there is no separate streak-marks table.
   Source: `public/api/missions/streaks.php`:lines 65–108 (uses `Accomplished` class with standard mark data)

8. When resolving marks for a streak, the system first checks for a `streak_id` on the task; if present, marks are indexed by `streak_id`, otherwise by `grid_id`.
   Source: `public/api/missions/streaks.php`:lines 128–132

9. The Tasks Accomplished chart displays accomplished days versus total days in the window; the default date window is the last 30 days.
   Source: `front-end/src/pages/missions/streaks/TasksAccomplished.jsx`:lines 323–328

10. The "Tasks Accomplished" chart sorts tasks by mission-sheet order by default (daily → weekly → shabbos, then by frequency, then label, then name). Other available sorts are alphabetical, completed ascending, and completed descending.
    Source: `front-end/src/pages/missions/streaks/TasksAccomplished.jsx`:lines 387–419 (`sortTasks()`)

11. For streak task loading, the available campaigns exclude frequency-type subjects (subjects 12, 15, 40, 136) specifically when the `for_streak=1` parameter is sent to `/missions/subjects`.
    Source: `public/api/missions/subjects.php`:lines 23–26; `front-end/src/pages/missions/streaks/Streaks.jsx`:line 115

---

## Sub-Module: Duch / Records

### Business Rules

1. A Duch (progress report) is scoped by a date range and can be generated for a specific school, one or more platoons, and/or specific soldiers.
   Source: `front-end/src/pages/missions/duch/DuchPage.jsx`:lines 13–21, 93–131

2. The Duch includes: task accomplishment data, newly earned medals, newly earned promotions (rank changes), and streak summaries — all filtered to the selected date range.
   Source: `public/api/print/missions_print_cache.php`:lines 88–158 (`build_duch_medals_ranks_cache()`)

3. The "New Medals" section shows only medals awarded within the selected date range; the "All Medals" section shows the highest medal per subject regardless of date.
   Source: `public/api/print/missions_print_cache.php`:lines 112–124 (all medals), lines 127–141 (date-range medals)

4. The "New Promotions" section shows only rank promotions that occurred within the selected date range.
   Source: `public/api/print/missions_print_cache.php`:lines 144–157

5. Birthday data is pre-loaded and cached per-user for the print render; if a user has a birthday associated with a mission in the `birthdays` table, that mission is flagged during rendering.
   Source: `public/api/print/missions_print_cache.php`:lines 171–190

6. Mark data for the Duch is batch-loaded in chunks of up to 500 users at a time to avoid huge IN clauses.
   Source: `public/api/print/missions_print_cache.php`:lines 43–76

7. Medal data is also batch-loaded in chunks of 500 users; medal names are looked up by `medal_ord`.
   Source: `public/api/print/missions_print_cache.php`:lines 96–124

8. When "Send to Ohel" is triggered from the Duch page, the PDF is generated via Puppeteer (the `/pdf-service`) and emailed to a hardcoded list of recipients: `naftolir@gmail.com` and `tziviaweinbaum@gmail.com`.
   Source: `public/api/print/duch.php`:lines 492–495

9. Images in the Duch PDF are downscaled to a maximum of 220×220 pixels at JPEG quality 85 to reduce PDF generation time.
   Source: `public/api/print/duch.php`:lines 426–441

10. For large schools, the Duch generation may split into multiple browser tabs, each handling a subset of platoons, to avoid memory limits.
    Source: `public/api/print/duch.php`:lines 366–388

11. The Duch date range can be specified via explicit start/end dates, by selecting "last N days" (7, 30, 60, or 90), or by selecting a Hebrew calendar month from a predefined list.
    Source: `front-end/src/pages/missions/duch/DuchPage.jsx`:lines 67–77, 133–165

12. Print rendering of tasks for the Duch only includes tasks where `mission_marking = 1`; tasks with `mission_marking = 0` are excluded from print output.
    Source: `public/api/print/missions_print_cache.php`:lines 15–19 (all queries filter `AND dt.mission_marking = 1`)

---

## Sub-Module: Print

### Business Rules

1. Printing mission sheets is restricted to authenticated users; the form posts to the legacy PHP print system.
   Source: `front-end/src/pages/missions/print/PrintPage.jsx`:line 102

2. The Base (school) selector on the Print page is disabled unless the user has Admin role; BC users are locked to their own school.
   Source: `front-end/src/pages/missions/print/PrintPage.jsx`:line 109 (`isDisabled={!isAdmin(login.code)}`)

3. The Platoon selector on the Print page is disabled unless the user has BC or higher role.
   Source: `front-end/src/pages/missions/print/PrintPage.jsx`:line 118 (`isDisabled={!isBC(login.code)}`)

4. When double-sided printing is selected, the minimum page count steps by 2 (to maintain even page counts for proper double-sided output).
   Source: `front-end/src/pages/missions/print/PrintPage.jsx`:line 179 (`step={double_sided ? 2 : 1}`)

5. The default parsha for print is the next parsha after today (i.e., the upcoming week's parsha, not the current week).
   Source: `front-end/src/pages/missions/print/PrintPage.jsx`:lines 54–62

6. The print page defaults to Hebrew dates; options are "No dates", "Hebrew dates", or "Hebrew and English dates".
   Source: `front-end/src/pages/missions/print/PrintPage.jsx`:line 26 (`dates: 'hebrew'`)

7. Mission sheets display a parent-signature line ("I reviewed my child's progress as a chayol in Hashem's army") at the bottom of each soldier's report.
   Source: `public/mission_report/newMark.php`:lines 748–751

8. Daily task rows on the printed mission sheet show seven individual checkboxes (one per day of the week).
   Source: `public/mission_report/newMark.php`:lines 320–336

9. For weekly and Shabbos tasks that have a `quantity` field, the printed sheet renders a text input instead of a simple checkbox.
   Source: `public/mission_report/newMark.php`:lines 427–436, 534–543

10. The print layout uses browser-agent-specific pagination breakpoints (Firefox vs Chrome) to determine when to start a new column or new page.
    Source: `public/mission_report/getTasks.php`:lines 97–114

11. For Shabbos Mevorchim tasks with a mandatory quota, the quota amount is printed as "My quota for this Shabbos Mevorchim is N kapitelach/minutes."
    Source: `public/mission_report/newMark.php`:lines 568–571

12. On the print page, a task flagged as `focus_task` is rendered with a special charge-card icon.
    Source: `public/mission_report/newMark.php`:lines 292–295, 447–450

13. The print page for school ID 9 (a special case) on production uses a separate URL `https://v.mashpia.com/api/print/missions` when accessed by a BC user; all other cases use the standard legacy URL.
    Source: `front-end/src/pages/missions/print/PrintPage.jsx`:lines 85–89

---

## Sub-Module: Subjects (Campaigns)

### Business Rules

1. A subject (campaign) has a `subject_type` that must be one of: `''`, `goal_hist`, `WWTC`, `Hakhel`, `school_points`, `home_points`, `Tanya`, `achievement`.
   Source: `SQLdump/mashpiadb_subjects.sql`:line 32

2. The Tehillim grid only shows campaigns with `subject_type = 'WWTC'`; all other grid views exclude WWTC campaigns.
   Source: `public/api/missions/grid.php`:lines 119–122

3. Subject ID 1 is hardcoded to display as "תהילים" (Tehillim), and subject ID 27 displays as "תניא" (Tanya), regardless of the stored `subject_name`.
   Source: `public/api/models/Subject.php`:lines 14–18

4. A subject's logo is resolved in priority order: subject_logo (filename) → subject_image_id (file upload) → default image `Footsteps.gif`.
   Source: `public/api/models/Subject.php`:lines 22–28

5. A subject can store a manual mission count adjustment for a user via a synthetic `date_tasks_missions` row with `level = 0` and `start_date = 0`. The adjustment is the delta between what was manually set and what was organically completed.
   Source: `public/api/models/Subject.php`:lines 30–65 (`setMissions()`, `getCustomizedId()`)

6. After a manual mission-count adjustment, medals and rank are immediately recalculated for the affected user.
   Source: `public/api/models/Subject.php`:lines 64–71

7. Achievement tasks for a subject are filtered by institution and optionally by school and platoon, depending on the role of the viewer (INST, BC, TEACHER).
   Source: `public/api/models/Subject.php`:lines 108–128 (`getAchievementTasks()`)

---

## Sub-Module: Parshos

### Business Rules

1. Parshos (Torah portions) are stored with Julian Day Number start and end dates; the `Parsha` model exposes Gregorian string equivalents via `start_date()` and `end_date()`.
   Source: `public/api/models/Parsha.php`:lines 10–16

2. The parshos list returned by `/missions/parshos` is scoped from the summer missions start date through the end of the current program year.
   Source: `public/api/missions/parshos.php`:lines 9–11

3. For weekly grid purposes, a "date" is the parsha ID, and the date range is resolved from the parsha's `start` and `end` Julian Day values.
   Source: `public/api/missions/grid.php`:lines 185–188 (`getDates()`)

4. For weekly mission tasks, the date filter logic is inverted compared to daily: tasks are included when their start/end falls *within* the parsha window (`start_date >= :start_date AND end_date <= :end_date`), rather than the parsha being within the task window.
   Source: `public/api/missions/grid.php`:lines 112–115

5. The parsha selector on the weekly print/duch/mark tabs displays parshos in descending order and includes the upcoming week (up to 7 days ahead of today).
   Source: `front-end/src/pages/missions/mark/tabs/WeeklyTab.jsx`:lines 37–39

---

## Open Questions

- The `needed` field on `date_tasks` is typed `tinyint(1)` but used as a count in the daily mission check (`dt.needed > total`). It is unclear whether this is a boolean (needs to be done at all) or a numeric threshold (e.g., 5 out of 7 days). (Source: `public/api/missions/mark.php`:line 204; `SQLdump/mashpiadb_date_tasks.sql`:line 42)

- Subject 136 receives auction-only points and retains raw quantity on marking, but the business meaning of "subject 136" is not named in the source; the special rule is identified only by the numeric ID. (Source: `public/api/missions/mark.php`:lines 149, 161–166)

- The `lines_learned` table is updated for grid IDs 21001–21008, 21013, 21014 as "Tanya" or "Mishna" marks, but the mapping between grid IDs and campaign type is determined at runtime via a `line_campaigns` table query rather than being hardcoded per grid. The exact Tanya/Mishna split within that ID range is not documented in the code beyond the `short_name` check for 'Mishna Testing' / 'מבחן משנה'. (Source: `public/api/missions/mark.php`:lines 75–113)

- The `noDuplicates` column on `date_tasks_marks` is present in the schema but its enforcement logic (if any) is not visible in the reviewed files. (Source: `SQLdump/mashpiadb_date_tasks_marks.sql`:line 37)

- `class_date_tasks_disabled` and `class_date_task_sorting` tables are referenced in queries but not included in the SQL dump list; their schemas are inferred from INSERT/DELETE queries only. (Source: `public/api/missions/grid.php`:lines 81–98)

- The `mission_type` filter parameter is threaded through the personalize API (`?action=getCampaigns`) but its allowed values and filtering logic are inside `TasksCustomizationNew::getCampaigns()`, which was not in the reviewed file list. (Source: `public/api/missions/personalize.php`:lines 38, 42, 247)

- The `personal` flag on `date_tasks_missions` suppresses missions in both the grid and tehillim endpoints, but it is unclear what workflow creates personal missions or who can create them. (Source: `public/api/missions/grid.php`:line 146; `public/api/missions/tehillim.php`:line 22)

- School ID 9 receives special print routing (`https://v.mashpia.com/api/print/missions`) in production for BC users. The reason for this special case is undocumented. (Source: `front-end/src/pages/missions/print/PrintPage.jsx`:lines 85–89)

- The `pointsDB.missions` table has a `percentage_required` field (default 100) and `default_velocity` (default 1.00), suggesting partial-completion scoring or velocity-based scoring is planned or partially implemented, but no current code was found that reads these fields. (Source: `SQLdump/pointsDB_missions.sql`:lines 44–45)
