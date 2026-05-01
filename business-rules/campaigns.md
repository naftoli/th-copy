# Campaign Business Rules

Campaigns covered: Tanya (Baal Peh), Tehillim / Shabbos Mevorchim, Mivtzoim, 12 Pesukim, TH Drive (Charidy), Mishna (Chevras Mishnayos / Mishna Baal Peh).

---

## Tanya

### Schema key tables
- `tanya_users` — per-user state: `track`, `year`, `lines_done`, `lines_offset`, `medal_ord`, `tanya_start_date`, `length_days`, `length_days_offset`, `pledges`, `collected`, `tanya_lines`, `mishna_lines`
- `tanya_goals` — per-track line-goal (track → lines_goal)
- `tanya_ladders` — ladder levels: `ladder`, `age`, `quota`, `qty`, `start_date`, `end_date`
- `tanya_totals` — school-level aggregates: `total_tanya`, `total_mishna` per `school_id`
- `lines_pledged` — pledge records: `campaign_id`, `school_id`, `lines_pledged`, `user_id`, `class_id`
- `lines_learned` — completion records: `campaign_id`, `school_id`, `lines_learned`, `user_id`, `class_id`, `mission_sheet_amount`, `noDuplicates`
- `demo_tanya_users` — demo/testing table: `lines_before_enrollment`, `lines_after_enrollment`, `desired_chapter_goal`, `ladder`, `enrolled`, `enrolled_date`

### Rules

Module: Tanya
Rule:   Each user is assigned to a numbered track (1–N), and that track has a `lines_goal` value stored in `tanya_goals`; the goal defines how many Tanya lines that user must learn.
Source: mashpiadb_tanya_goals.sql:CREATE TABLE / mashpiadb_tanya_users.sql:track column

Module: Tanya
Rule:   A user's effective lines-done count is `lines_done + lines_offset`, allowing administrators to manually credit or adjust a user's progress without overwriting the raw mark count.
Source: mashpiadb_tanya_users.sql:lines_done, lines_offset columns

Module: Tanya
Rule:   The system stores both `pledges` (money pledged per line) and `collected` (money actually received) on the `tanya_users` row, tracking the fundraising dimension of the campaign separately from learning progress.
Source: mashpiadb_tanya_users.sql:pledges, collected columns

Module: Tanya
Rule:   Campaign instances are identified by `campaign_id` in `lines_pledged` and `lines_learned`; the most-recent campaign of type 'Tanya' is selected by `ORDER BY id DESC LIMIT 1` when no explicit campaign is passed.
Source: mashpia.com/public/class.tanya.php:constructor (~line 10)

Module: Tanya
Rule:   A school may only be removed from the Tanya program (flag `tanya = 0`) if it is NOT a Chayolei school (`chayolei = 0`) and is currently enrolled (`tanya = 1`); Chayolei schools cannot be deleted from Tanya.
Source: mashpia.com/public/ajax/tanya/deleteTanyaSchool.php:UPDATE query (~line 13)

Module: Tanya
Rule:   Only a `super`-level admin may delete a school from the Tanya program.
Source: mashpia.com/public/ajax/tanya/deleteTanyaSchool.php:auth check (~line 5)

Module: Tanya
Rule:   The system tracks pledged lines and learned lines separately at three levels — school (`class_id = 0, user_id = 0`), class (`user_id = 0`), and individual (`user_id > 0`) — in the `lines_pledged` and `lines_learned` tables.
Source: mashpiadb_lines_pledged.sql, mashpiadb_lines_learned.sql / class.balPehCampaign.php:getTotalPledged

Module: Tanya
Rule:   When computing the school-level pledge total, the system takes the higher of two values: (a) the single school-level pledge row, or (b) the sum of all individual user pledge rows in that school.
Source: mashpia.com/public/class.balPehCampaign.php:getTotalPledged 'school' case (~lines 29–59)

Module: Tanya
Rule:   The `mission_sheet_amount` field on `lines_learned` records the quantity entered by the parent (via mission sheet), allowing the system to compare what the parent reported against what was marked by the school admin.
Source: mashpiadb_lines_learned.sql:mission_sheet_amount / class.tanya.php:getTotalLearnedForDuch

Module: Tanya / Mishna
Rule:   Campaign IDs distinguish Tanya from Mishna Baal Peh campaigns: even-numbered campaign IDs are Mishna, odd-numbered are Tanya.
Source: mashpia.com/public/class.balPehCampaign.php:getInstance (~line 203)

Module: Tanya
Rule:   Each user's Tanya ladder entry carries `quota`, `qty`, `age`, `start_date`, and `end_date` — the ladder is age-gated and time-bounded; a user is only on a given ladder level during the configured date range.
Source: mashpiadb_tanya_ladders.sql:CREATE TABLE

Module: Tanya
Rule:   `tanya_totals` aggregates `total_tanya` and `total_mishna` lines per school; this is a summary/cache table keyed only on `school_id` (no campaign_id), suggesting it holds the current-year school-wide total.
Source: mashpiadb_tanya_totals.sql:CREATE TABLE

---

## Tehillim / Shabbos Mevorchim (SM)

### Schema key tables
- `tehillim` — reference table: kapitel → number of pesukim
- `tehillim_ladders` — per-ladder, per-age, per-month quota: `ladder`, `age`, `month`, `kapitelach`, `minutes`, `speed`, `qty`
- `sm` — Shabbos Mevorchim results summary: `month`, `date`, `task` (enum Kapitelach/Minutes), `type` (enum army/school/class), `type_id`, `quota`, `accomplished`

### Rules

Module: Tehillim / Shabbos Mevorchim
Rule:   Tehillim quotas are tracked in two dimensions: Kapitelach (chapters) and Minutes; these are the only two valid task types in the `sm` results table.
Source: mashpiadb_sm.sql:task enum('Kapitelach','Minutes')

Module: Tehillim / Shabbos Mevorchim
Rule:   SM quota and accomplishment data are recorded at three organizational levels: army-wide (`type = 'army'`), school (`type = 'school'`), and class (`type = 'class'`), each identified by `type_id`.
Source: mashpiadb_sm.sql:type enum('army','school','class')

Module: Tehillim / Shabbos Mevorchim
Rule:   Each ladder level defines a monthly Tehillim quota differentiated by age, and specifies both a chapter count (`kapitelach`) and a time in minutes (`minutes`) together with a reading speed (`speed`) and a quantity (`qty`).
Source: mashpiadb_tehillim_ladders.sql:CREATE TABLE (ladder, age, month, kapitelach, minutes, speed, qty columns)

Module: Tehillim / Shabbos Mevorchim
Rule:   SM report dates are computed using Hebrew calendar Shabbos Mevorchim dates for each month of the year; the system uses a `calculateSM($year)` helper to generate the full list of Julian dates for a given year.
Source: mashpia.com/public/class.shabbosMevorchim.php:__construct (~line 80)

Module: Tehillim / Shabbos Mevorchim
Rule:   In a leap year the system separates Adar I and Adar II; in a non-leap year only Adar is shown (index 5 of the SM dates array is used; index 6 is skipped).
Source: mashpia.com/public/class.shabbosMevorchim.php:__construct (~lines 98–107)

Module: Tehillim / Shabbos Mevorchim
Rule:   A Shabbos Mevorchim date is shown as "done" (accomplishment data displayed) only if that date has already passed; dates within the upcoming 28-day window that have not yet arrived are shown as goal-only.
Source: mashpia.com/public/class.shabbosMevorchim.php:setReportDates (~lines 249–262)

Module: Tehillim / Shabbos Mevorchim
Rule:   Once a backup snapshot has been taken for a given SM date (recorded in `tehillim_backups`), the system reads accomplishment figures from the backup rather than live `date_tasks_marks`; backup data takes precedence over live marks.
Source: mashpia.com/public/class.shabbosMevorchim.php:setArmyResults, setSchoolResults, setStudentResults (~lines 373–379, 532–539, 1340)

Module: Tehillim / Shabbos Mevorchim
Rule:   The quota (goal) for a class is computed by summing the `quantity` field from `date_tasks` joined through `date_tasks_missions` filtered by `subject_id = 1`, `school_type_id`, `track_id`, `level`, and `lang_id` — meaning the goal adapts to the student's language and learning level.
Source: mashpia.com/public/class.shabbosMevorchim.php:setStudentResults sql1 query (~lines 1231–1244)

Module: Tehillim / Shabbos Mevorchim
Rule:   A class is considered to have "accomplished" its SM goal when `classDoneResults >= classResults` for the Kapitelach task; accomplished classes are recorded and reported separately.
Source: mashpia.com/public/class.shabbosMevorchim.php:setClassResults (~lines 1023–1032)

Module: Tehillim / Shabbos Mevorchim
Rule:   School number 82 and school 612 are explicitly excluded from army-wide SM calculations.
Source: mashpia.com/public/class.shabbosMevorchim.php:setArmyResults sql1 (~line 334) and setASR query (~line 558)

Module: Tehillim / Shabbos Mevorchim
Rule:   The WhatsApp class report supports grades Pre1a, 1, 2, 3, 4, 5, 6, 7, 8 only, and is filtered by gender; only schools with `tehillim = 1` are included.
Source: mashpia.com/public/class.shabbosMevorchim.php:setWhatsappClasses (~lines 877–893)

Module: Tehillim / Shabbos Mevorchim
Rule:   Only users with `user_registered > 0` and `ut.enrolled = 1` (enrolled in subject_id = 1) are counted in SM registration and quota totals.
Source: mashpia.com/public/class.shabbosMevorchim.php:setArmySchoolsResults sql2 (~lines 639–646)

Module: Tehillim / Shabbos Mevorchim
Rule:   The HQ leaderboard report ranks schools by "percentage of chayolim who completed their quota" (doneQuotas / registered), not by raw accomplishment volume.
Source: mashpia.com/public/class.shabbosMevorchim.php:generateHQReport (~lines 1742–1749)

Module: Tehillim / Shabbos Mevorchim
Rule:   SM result caching uses a 15-minute TTL (900 seconds) keyed on year, school_id, rDates, accomplishedOnly flag, and section name.
Source: mashpia.com/public/class.shabbosMevorchim.php:$cacheTtl = 900 (~line 61) and cacheKey() (~lines 132–139)

---

## Mivtzoim

### Schema key tables
- `mivtzoim` — campaign registry: `mivtzoim_id`, `name`, `start`, `end`
- `mivtzoim_tasks` — campaign-to-short-name mapping: `mivtzoim_id`, `short_name` (unique per campaign)
- `lulav_purchases` — Lulav set purchases: `year`, `admin_id`, `users`, `authorization`, `paid`
- `lulav_settings` — per-school lulav settings: `year`, `school_id`, `allow_lulav`, `lulav_shipping` (unique per year+school)
- `chanuka_missions` — per-user Chanuka task completion: `user_id`, `task`, `task_num`, `year` (unique per user+task_num+year)

### Rules

Module: Mivtzoim
Rule:   Each Mivtzoim campaign is a named, date-bounded entity (start/end Julian dates); tasks are linked to the campaign through a set of "short names" which map to `date_tasks` grid IDs for the campaign's date range.
Source: mivtzoim/classes/mivtzoim.php:__construct and setShortNames (~lines 21–55)

Module: Mivtzoim
Rule:   If a Mivtzoim campaign has no short names configured, the system refuses to display a marking grid and shows an error telling the admin to contact HQ.
Source: mivtzoim/classes/mivtzoim.php:getTasks (~lines 126–128)

Module: Mivtzoim
Rule:   When selecting available short names to set up a campaign, personal tasks and tasks from school_type_id 14 and 15 are excluded; only non-personal, non-type-14/15 tasks in subject_id = 12 during the campaign date range are shown.
Source: mivtzoim/classes/mivtzoim.php:availableShortNames (~lines 404–412)

Module: Mivtzoim
Rule:   Only `super`-level admins may configure (set up) a Mivtzoim campaign's task short names.
Source: mivtzoim/admin/tasks.php:auth check (~line 7)

Module: Mivtzoim
Rule:   Marking uses `grid_id` (not `date_task_id`) to look up existing marks, so a user's prior mark survives across language/level changes; the correct task_id is resolved at insert time from the user's current school_type_id, lang_id, and level.
Source: mivtzoim/classes/mivtzoim.php:markTasks (~lines 225–243 and 278–303)

Module: Mivtzoim
Rule:   A mark of 0 deletes the existing mark record; a positive integer updates it; a negative or absent value is ignored.
Source: mivtzoim/classes/mivtzoim.php:markTasks (~lines 248–264)

Module: Mivtzoim
Rule:   When a new mark is inserted (not an update), the `mark_date` is clamped to the task's start–end window: if today is before the task start, the mark date is set to the start; if after the end, it is set to the end.
Source: mivtzoim/classes/mivtzoim.php:markTasks (~lines 306–308)

Module: Mivtzoim
Rule:   Every new Mivtzoim mark insertion records `mark_points = 0.5`.
Source: mivtzoim/classes/mivtzoim.php:markTasks (~line 315)

Module: Mivtzoim
Rule:   Only registered chayolim (`user_registered > 0`) appear on the marking grid.
Source: mivtzoim/mark.php:WHERE user_registered > 0 (~line 71)

Module: Mivtzoim
Rule:   If a task's `quantity >= 1`, the mark input is a numeric text box; if `quantity < 1`, it is a checkbox (binary done/not-done); tasks with quantity >= 1 also display a running total column when there is more than one such task.
Source: mivtzoim/mark.php:~lines 165–180

Module: Mivtzoim
Rule:   Parent-entered marks and school-admin-entered marks share the same `date_tasks_marks` table; whichever is saved last wins (last-write-wins).
Source: mivtzoim/mark.php:infobox comment (~line 105)

Module: Mivtzoim — Lulav Purchases
Rule:   A school must opt in to Lulav set purchases; the admin selects a shipping tier or "pick up" option, which determines the per-child shipping charge stored in `lulav_settings.lulav_shipping` (default 15).
Source: mivtzoim/lulav_settings.php:form options and INSERT/UPDATE (~lines 19–44)

Module: Mivtzoim — Lulav Purchases
Rule:   Lulav shipping tiers are volume-based: 1–4 children = $15; 5–9 = $10; 10–14 = $6; 15–29 = $5; 30–99 = $4; 100+ = $3; pick-up = $0. A school sets one shipping fee per year.
Source: mivtzoim/lulav_settings.php:infobox and radio buttons (~lines 74–113)

Module: Mivtzoim — Lulav Purchases
Rule:   A school can opt out entirely by choosing the "remove from list" option (fee = -1), which sets `allow_lulav = 0`.
Source: mivtzoim/lulav_settings.php:radio value="-1" (~line 112); lulav_settings.php: allow calculation ~line 32

Module: Mivtzoim — Chanuka Purchases
Rule:   Each Chanuka item type (Menorah box, Brochure) has its own independent shipping configuration per school per year, stored separately by `item_id` in `mashpia_purchases.school_settings`.
Source: mivtzoim_purchases/chanuka_settings.php:fees array, item IDs 2 (Menorah) and 3 (Brochure) (~lines 17–34)

Module: Mivtzoim — Chanuka Purchases
Rule:   Menorah box base price is $2.50, shipping is $0.50 per box (total $3.00). Brochure base price is $0.25; shipping is volume-tiered: 50–99 = $0.16, 100–199 = $0.11, 200–299 = $0.07, 300–399 = $0.06, 400+ = $0.05. Minimum shipping order is 50 units.
Source: mivtzoim_purchases/chanuka_settings.php:infobox (~lines 89–113)

Module: Mivtzoim — General Purchases
Rule:   An admin must explicitly enable purchases for a school per item per year (`allow_purchases = 1`); purchases are disabled by default.
Source: mivtzoim_purchases/classes/MivtzoimSetting.php:enablePurchases / disablePurchases (~lines 45–126)

Module: Mivtzoim — General Purchases
Rule:   A purchase transaction is atomic: the `purchases` header row and all `purchase_details` child rows are written in a single database transaction; if any detail fails, the entire purchase is rolled back.
Source: mivtzoim_purchases/classes/MivtzoimPurchases.php:createPurchase (~lines 196–231)

Module: Mivtzoim — General Purchases
Rule:   Deleting a purchase is also atomic: `purchase_details` rows are deleted first, then the `purchases` header; if either delete fails, both are rolled back.
Source: mivtzoim_purchases/classes/MivtzoimPurchases.php:deletePurchase (~lines 248–262)

Module: Mivtzoim — General Purchases
Rule:   An order confirmation email is sent from `chidon@tzivoshashem.org` for Yahadus Book Sale orders and from `cth@tzivoshashem.org` for all other purchase types; ordering a book does NOT constitute enrollment for next year's Chidon.
Source: mivtzoim_purchases/classes/MivtzoimPurchases.php:sendEmail and getMessage (~lines 280–396)

---

## 12 Pesukim

### Schema key tables
- `pesukim_recruits` — recruiter → recruited relationships per year
- `pesukim_mechunachim` — mechunim (people being taught) linked to a mechanech user: `mechanech_user_id`, `name`, `phone`, `email`, `verification_code`, `verified`, `date_verified`, `year`
- `pesukim_settings` — global multipliers: `type`, `label`, `multiplier`

### Rules

Module: 12 Pesukim
Rule:   A chayol can recruit another chayol by entering the recruit's serial number or user_id; if the recruit is found, a `pesukim_recruits` row is created linking recruiter to recruited for the current year.
Source: mashpia.com/public/pesukim/class.pesukim.php:addRecruiter (~lines 33–68)

Module: 12 Pesukim
Rule:   When a chayol is recruited (via `addRecruit`), the recruiter immediately earns 200 auction-only points; each person up the recruiting chain earns an additional 20 points (recursive walk up the chain).
Source: mashpia.com/public/pesukim/class.pesukim.php:addRecruit (~lines 194–205)

Module: 12 Pesukim
Rule:   When a chayol recruits someone via the admin-entry path (`addRecruiter`), the recruiter earns 300 points (not 200) and the system also logs a mission completion with `auction_only_points = 1` and `mission_name = 'New Recruit'`.
Source: mashpia.com/public/pesukim/class.pesukim.php:addRecruiter (~lines 57–62) and addMissionCompletion (~lines 70–97)

Module: 12 Pesukim
Rule:   Points awarded for Pesukim recruiting are tagged `auction_only_points = 1`, meaning they cannot be used for regular rewards but only for auction-style redemptions.
Source: mashpia.com/public/pesukim/class.pesukim.php:addPoints (~line 108)

Module: 12 Pesukim
Rule:   When a mechanech (teacher) is added, the system automatically generates a 6-character alphanumeric verification code and sends it to the parent admin's email; the mechanech remains unverified until the code is submitted.
Source: mashpia.com/public/pesukim/class.pesukim.php:addMechunach (~lines 229–272) and createVerificationCode (~lines 275–282)

Module: 12 Pesukim
Rule:   A mechunach is verified by matching `user_id`, `mechunach_id`, and the `verification_code` all together; a mismatch on any of the three does not verify.
Source: mashpia.com/public/pesukim/class.pesukim.php:verifyMechunach (~lines 312–327)

Module: 12 Pesukim
Rule:   Task completion for mechunachim is only checked for mechunachim where `verified = 1`; unverified mechunachim are skipped entirely in task-check flows.
Source: mashpia.com/public/pesukim/ajax/checkMechunachimTasks.php:if (intval($mechunach['verified']) == 1) (~line 18)

Module: 12 Pesukim
Rule:   A chayol's Pesukim minutes are tallied using a fixed grid_id of 15036 scoped to the current year's start/end dates.
Source: mashpia.com/public/pesukim/class.pesukim.php:getMinutes (~lines 381–395)

Module: 12 Pesukim
Rule:   Campaign-level minutes totals (used on leaderboards) also use grid_id 15036 and aggregate across all users within the current year's date range.
Source: mashpia.com/public/pesukim/class.pesukimTotals.php:setMinutes and grid_id = 15036 (~lines 18, 24–36)

Module: 12 Pesukim
Rule:   Teaching totals ("how many people were taught all 12 Pesukim") are computed by counting distinct user+mechunach combinations where the sum of `done_qty` across the 12 teaching grid_ids (15037–15048) is >= 12.
Source: mashpia.com/public/pesukim/class.teachTotals.php:setTaughtBySchool HAVING summed_qty >= 12 (~lines 82–86)

Module: 12 Pesukim
Rule:   Enrollment into Pesukim (`subject_id = 136`) is limited to users with `lang_id = 1` (English) in Chayolei schools; the enrollment script uses `INSERT IGNORE` so existing tracks are not overwritten.
Source: mashpia.com/public/pesukim/enrollIntoPesukim.php:WHERE lang_id = 1 and INSERT IGNORE (~lines 22–35)

Module: 12 Pesukim
Rule:   The Pesukim settings table stores type/label/multiplier pairs; these multipliers can be updated by a super admin via `admin/api/saveSettings.php`.
Source: mashpia.com/public/pesukim/admin/api/saveSettings.php and mashpiadb_pesukim_settings.sql

Module: 12 Pesukim — Marking
Rule:   The marking page week boundaries run from Friday (start) through Thursday (end); if today falls Sunday–Friday, the start is calculated by subtracting the day-of-week offset to land on the most-recent Friday.
Source: mashpia.com/public/pesukim/marking/getTasks.php:getCurrentWeekDates (~lines 6–36)

---

## TH Drive / Charidy

### Schema key tables
- `charidy` — legacy donation import table: `year`, `fname`, `lname`, `email`, `donation`, `with_matching`, `solicited_by`, `parent_admin_id`
- `charidy_callers` — staff/caller list: `first`, `last`
- `charidy_donors` — unique donors (email is unique key): `parent_admin_id`, contact info, `needs_call`
- `charidy_donations` — per-donation records: `donor_id`, `year`, `amount`, `donation_date`, `user_id`, `child_only_donation`, `dedication_name`, `dedication_text`

### Rules

Module: TH Drive / Charidy
Rule:   Each donor has a unique email address (`charidy_donors.email` is a unique key); duplicate donors cannot be created by email.
Source: mashpiadb_charidy_donors.sql:UNIQUE KEY `email`

Module: TH Drive / Charidy
Rule:   A donation can be tagged as `child_only_donation = 1`, meaning it is attributed directly to a specific child (`user_id`) rather than to the school/family.
Source: mashpiadb_charidy_donations.sql:child_only_donation column

Module: TH Drive / Charidy
Rule:   A donation supports an optional dedication: `dedication_name` (up to 85 chars) and `dedication_text` (up to 255 chars).
Source: mashpiadb_charidy_donations.sql:dedication_name, dedication_text columns

Module: TH Drive / Charidy
Rule:   The legacy `charidy` import table records both the base `donation` amount and the `with_matching` amount separately, preserving the distinction between the donor's contribution and the matched total.
Source: mashpiadb_charidy.sql:donation, with_matching columns

Module: TH Drive / Charidy
Rule:   Callers (phone solicitors) are assigned donors via a `charidy_donors_callers` join table keyed by year; a donor with a blank or placeholder phone number (empty string, `..`, `...`, `....`, `.....`) is assigned to a designated catch-all caller.
Source: mashpia.com/public/charidy/data/assign_callers.php:phone IN ('','..','...','....','.....') (~line 46)

Module: TH Drive / Charidy
Rule:   The caller leaderboard tracks four metrics per caller: number of donors assigned, number who gave this year, total maximum past-year donation across their pool, and total current-year donation amount.
Source: mashpia.com/public/charidy/callers/leaderboard.php:four SQL queries per caller (~lines 47–71)

Module: TH Drive / Charidy
Rule:   Only a `super`-level admin can view the caller leaderboard.
Source: mashpia.com/public/charidy/callers/leaderboard.php:auth check (~line 5)

Module: TH Drive / Charidy
Rule:   Points (180 per user) can be manually granted to all registered chayolim linked to a given parent admin account; these points are recorded with `resource_name = 'admin_users_manual'`.
Source: mashpia.com/public/charidy/give_charidy_points.php:points = 180 and resource_name (~lines 13–19)

---

## Mishna (Chevras Mishnayos / Mishna Baal Peh)

### Schema key tables
- `mishnos` — master mishna index: `seder_id`, `mesechto_id`, `perek`, `mishna`, `num_lines`
- `mishna_assigned` — per-user mesechta assignment: `seder_id`, `mesechto_id`, `user_id`, `school_id`, `class_id`
- `mishna_learned` — per-mishna completion record: `mesechto_id`, `perek`, `mishna`, `lines_learned`, `user_id`, `date_learned`, `date_entered` (unique per mesechto+perek+mishna+user)
- `mishna_at_once` — tracks in-progress (currently being learned) perek per user: `user_id`, `mesechto_id`, `perek` (PK: all three)
- `mishna_ppl` — completion/points snapshot per school/class/user: `points`, `m_points`, `p_points`, `s_points`, `shas_points`

### Rules

Module: Mishna
Rule:   Each mishna in the system has a known line count (`num_lines`) stored in the `mishnos` table, enabling the system to credit precise line totals when a mishna is marked complete.
Source: mashpiadb_mishnos.sql:num_lines column

Module: Mishna
Rule:   A user can only mark each specific mishna (identified by mesechto + perek + mishna) as learned once; the `mishna_learned` table has a unique key on `(mesechto_id, perek, mishna, user_id)`.
Source: mashpiadb_mishna_learned.sql:UNIQUE KEY `mishna` (mesechto_id, perek, mishna, user_id)

Module: Mishna
Rule:   Each `mishna_learned` record stores both when it was learned (`date_learned`, user-supplied) and when it was entered into the system (`date_entered`, auto-timestamp), distinguishing reported learning date from data-entry date.
Source: mashpiadb_mishna_learned.sql:date_learned (nullable datetime) vs date_entered (timestamp DEFAULT current_timestamp)

Module: Mishna
Rule:   A user is assigned specific mesechtos to learn; each assignment row in `mishna_assigned` links a user to a seder and mesechta, scoped to their school and class.
Source: mashpiadb_mishna_assigned.sql:CREATE TABLE (seder_id, mesechto_id, user_id, school_id, class_id)

Module: Mishna
Rule:   The `mishna_at_once` table tracks which perek within a mesechta a user is currently learning; the primary key `(user_id, mesechto_id, perek)` ensures one active-perek record per user per mesechta.
Source: mashpiadb_mishna_at_once.sql:PRIMARY KEY (user_id, mesechto_id, perek)

Module: Mishna
Rule:   The `mishna_ppl` snapshot table records multiple point categories per row: `points` (total), `m_points` (mishna), `p_points` (perakim?), `s_points` (seder?), and `shas_points`; this allows multi-dimensional scoring breakdowns.
Source: mashpiadb_mishna_ppl.sql:points, m_points, p_points, s_points, shas_points columns

Module: Mishna
Rule:   The Chevras Mishnayos admin tool generates a per-school Excel roster (chevras_mishnayos_{school_id}.xls) pre-populated with user_id, serial, school, grade, English name, Hebrew name, date of birth, and gender for external data entry.
Source: mashpia.com/public/mishna/join.php:PHPExcel output (~lines 54–88)

Module: Mishna
Rule:   The Mishna roster supports importing lines-learned data back from an external service (chabadkid.com) via a button that calls `world/imports/import.php?school={school_number}`.
Source: mashpia.com/public/mishna/join.php:jQuery import button (~lines 131–139)

Module: Mishna
Rule:   The Mishna Baal Peh campaign uses even-numbered campaign IDs in the `line_campaigns` table (same table as Tanya), with learning tracked through `lines_learned` scoped to that campaign_id.
Source: mashpia.com/public/class.balPehCampaign.php:getInstance (~line 203) and class.mishna.php:getUsersTotalLearned

Module: Mishna
Rule:   The getMishnaDropdown ajax endpoint returns the full mishna hierarchy (seder → mesechta → perek → mishna with line counts) ordered by seder, mesechta, perek, mishna; it is available to school-level admins.
Source: mashpia.com/public/ajax/tanya/getMishnaDropdown.php:SELECT with ORDER BY (~lines 17–23)
