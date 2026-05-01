# Business Rules: Rebbe's Gift (BP), Mission Marathon / Raffles, Yearly Gift, Auction

---

## Rebbe's Gift (Bal Peh — BP)

Module: Rebbe's Gift (BP)
Rule:   Two separate pledge campaigns exist per year — one for Tanya Bal Peh (lines of Tanya memorized) and one for Mishna Bal Peh (lines of Mishna memorized); each is identified by a row in the `line_campaigns` table with a `type` of `'tanya'` or `'mishna'`.
Source: bp/bp_history.php:changeLines.php (query on `line_campaigns where type`)

Module: Rebbe's Gift (BP)
Rule:   Each student's pledge count is stored in `bp_user_summary` as `num_lines` per campaign; updating a student's lines replaces the existing value (it is not additive).
Source: bp/saveLines.php:20-23

Module: Rebbe's Gift (BP)
Rule:   Lines are recorded per student, per campaign (year + type), so a student has a separate line count for each year and each subject type (Tanya and Mishna).
Source: mashpiadb_bp_user_summary.sql:26-32 (columns: campaign_id, user_id, num_lines, child_count)

Module: Rebbe's Gift (BP)
Rule:   Summary tables aggregate pledge totals at three levels: per-user (`bp_user_summary`), per-class (`bp_class_summary`), and per-school (`bp_school_summary`), all keyed by `campaign_id`.
Source: mashpiadb_bp_user_summary.sql, mashpiadb_bp_class_summary.sql, mashpiadb_bp_school_summary.sql

Module: Rebbe's Gift (BP)
Rule:   The raw line count per pledge event is stored in `lines_pledged` and includes the school, class, user, and campaign; this table serves as the audit trail for individual pledge entries.
Source: mashpiadb_lines_pledged.sql:26-34

Module: Rebbe's Gift (BP)
Rule:   Points are typed via `bp_types` (a lookup table); a unique constraint on `bp_points (bp_type_id, user_id, type, ref)` prevents duplicate point entries for the same reference.
Source: mashpiadb_bp_points.sql:32, mashpiadb_bp_types.sql

Module: Rebbe's Gift (BP)
Rule:   Pledge cards display each student's highest historical line count across all years for both Tanya and Mishna (used as a benchmark for the current year's pledge).
Source: bp/pledge_cards.php:144-152

Module: Rebbe's Gift (BP)
Rule:   Only students with grades `Pre1a, 1, 2, 3, 4, 5, 6, 7, 8` are printed on pledge cards; unrecognized grades are skipped.
Source: bp/pledge_cards.php:119-162 (`$all_grades` array + `in_array` check)

Module: Rebbe's Gift (BP)
Rule:   A student's prior-year line history is only displayed starting from the grade-appropriate year (e.g., a current 8th-grader's Tanya history starts in year 5773); the `$startYr` mapping determines which Hebrew year each grade began tracking.
Source: bp/pledge_cards.php:120-132 and bp/classPledge.php:122-134

Module: Rebbe's Gift (BP)
Rule:   Pledge records do not include the current year on pledge cards; only prior completed years are shown (query filters `l.year != $cur_year`).
Source: bp/pledge_cards.php:29 and bp/classPledge.php:108-110

Module: Rebbe's Gift (BP)
Rule:   Only registered users (`user_registered > 0`) whose class is not alumni/archived (`class_era = 0`) appear on the BP history and pledge reports.
Source: bp/bp_history.php:53-58

---

## Mission Marathon / Raffles

### Raffle Structure

Module: Raffles
Rule:   Raffles have three types: `weekly`, `monthly`, and `yearly`; each type has a different eligibility threshold (5 days for weekly, 60 days for monthly, 180 days for yearly — defaults stored in `Constants`).
Source: raffles/shared/classes/Constants.php:81-98, raffles/shared/classes/Raffle.php:325-381

Module: Raffles
Rule:   Each raffle has a defined `start_date`, `end_date`, and `run_date`; the runner only processes raffles where `DATE(run_date) = CURDATE()` and `date_ran IS NULL`.
Source: raffles/tasks/run_raffle/run_raffle.php:60-61, mashpiadb_raffles.sql:26-43

Module: Raffles
Rule:   A raffle can be scoped to three audiences independently: HQ staff (`show_for_hq`), Base Commanders (`show_for_bc`), and students/kids (`show_for_kids`).
Source: mashpiadb_raffles.sql:35-40

Module: Raffles
Rule:   A raffle may optionally override the default days-of-tasks requirement via the `days_of_tasks` column; if not set the system falls back to the constant for that raffle type.
Source: raffles/shared/classes/Raffle.php:213-215, raffles/yearly/classes/YearlyRaffle.php:218-220

### Weekly Raffle Eligibility

Module: Raffles — Weekly
Rule:   To be eligible for a weekly raffle a student must have completed tasks on at least 5 distinct days (counted by `count(distinct mark_date)`) within the raffle's start and end dates, measured against mission-report grid tasks (`grid_id = 13012`).
Source: raffles/shared/classes/Raffle.php:636-666 (`checkWeekly`)

Module: Raffles — Weekly
Rule:   Eligibility can also be set manually by inserting a row into `raffle_eligibility` with `eligible = 1`; such users bypass the task-count check.
Source: raffles/shared/classes/Raffle.php:254-278, raffles/shared/ajax/user_eligible.php:24-25

Module: Raffles — Weekly
Rule:   Each school has a hard cap on the number of weekly raffle winners it can produce; the cap values are encoded in `Constants::get_raffle_school_max_winners()` (e.g., school 255 = 19, school 54 = 12, school 9 = 8, most small schools = 1).
Source: raffles/shared/classes/Constants.php:11-79

Module: Raffles — Weekly
Rule:   A school's effective winner quota is the lesser of its configured maximum and the number of eligible students it has; no school can win more prizes than it has eligible students.
Source: raffles/tasks/run_raffle/weekly_raffle.php:44-45

Module: Raffles — Weekly
Rule:   A student who has already won a weekly raffle in the current year is skipped during drawing, provided the school still has more eligible students than its quota; if the school is exactly at quota the already-won student can still win.
Source: raffles/tasks/run_raffle/weekly_raffle.php:72-78

Module: Raffles — Weekly
Rule:   Prize selection is random; prizes are removed from the pool once their quantity reaches zero, preventing over-awarding.
Source: raffles/tasks/run_raffle/weekly_raffle.php:83-86

Module: Raffles — Weekly
Rule:   The total number of weekly prizes per run is configured as 185 (from `Constants::get_num_weekly_prizes()`).
Source: raffles/shared/classes/Constants.php:7-9

### Monthly Raffle Eligibility

Module: Raffles — Monthly
Rule:   To be eligible for a monthly raffle a student must have completed tasks on at least 60 distinct days (default; overridable per raffle via `days_of_tasks`) within the raffle window, using the same mission-report grid (`grid_id = 13012`).
Source: raffles/shared/classes/Raffle.php:349-353 and Constants.php:85-87 (`get_monthly_task_requirment = 60`)

Module: Raffles — Monthly
Rule:   For monthly raffles, prizes are pre-assigned to specific schools via `raffles_monthly` (each row = one prize for one school); the runner picks one eligible winner per school per prize entry.
Source: raffles/tasks/run_raffle/monthly_raffle.php:18-55, mashpiadb_raffles_monthly.sql

Module: Raffles — Monthly
Rule:   A student who has already won a monthly raffle this year is skipped when drawing for monthly prizes, provided the school has at least 2 eligible students remaining.
Source: raffles/tasks/run_raffle/monthly_raffle.php:43-47

Module: Raffles — Monthly
Rule:   Monthly raffle winners can also be manually entered by serial number and school via the `set_winner.php` endpoint (super-admin only); the winner is looked up by `user_serial` and `school_id`.
Source: raffles/monthly/ajax/set_winner.php

### Yearly Raffle Eligibility

Module: Raffles — Yearly
Rule:   To be eligible for the yearly raffle a student must have completed tasks on at least 180 distinct days (default from `Constants::get_yearly_task_requirment()`, overridable per raffle) across the full year window (`start_date` to `end_date` of the yearly raffle record).
Source: raffles/yearly/classes/YearlyRaffle.php:218-220, raffles/shared/classes/Constants.php:89-91

Module: Raffles — Yearly
Rule:   Yearly eligibility is computed by counting distinct `mark_date` values in `date_tasks_marks` joined to `date_tasks` where `grid_id = 13012` (the mission-report grid), between the raffle's start and end dates.
Source: raffles/yearly/classes/YearlyRaffle.php:126-151 (`getEligibility`)

Module: Raffles — Yearly
Rule:   Yearly eligibility is cached per-user in `user_yearly_raffle` (columns: `user_id`, `days`, `year`); the cache is upserted on every recalculation.
Source: raffles/yearly/classes/YearlyRaffle.php:164-187 (`getAndCacheEligibility`, `cacheUser`)

Module: Raffles — Yearly
Rule:   The eligibility report reads from the `user_yearly_raffle` cache and shows the threshold as 180 days (e.g., "45/180" displayed in the report UI).
Source: raffles/yearly/ajax/eligibility_report_hq.php:50 (`days >= $this->days_of_tasks`)

Module: Raffles — Yearly
Rule:   When an admin manually marks a user as eligible for the yearly raffle, the system inserts them into `user_yearly_raffle` with a `days` value equal to the required threshold so they appear eligible in all cache-based queries.
Source: raffles/shared/ajax/user_eligible.php:42-43

Module: Raffles — Yearly
Rule:   Removing manual eligibility for a yearly raffle deletes the user from both `raffle_eligibility` and `user_yearly_raffle` for the current year.
Source: raffles/shared/ajax/user_eligible.php:45-46

### Raffle Prize Distribution

Module: Raffles
Rule:   A raffle winner is recorded in `raffle_winners` with `shipped = 0` (default); shipping status must be updated separately.
Source: mashpiadb_raffle_winners.sql:30

Module: Raffles
Rule:   Weekly prizes are sourced from the `prizes` table (via `raffle_prizes`); monthly and yearly prizes are sourced from `prizes_auction` (via `raffles_monthly` or directly).
Source: raffles/shared/classes/Raffle.php:497-521 (`get_prizes`)

Module: Raffles
Rule:   Winners are bulk-importable via CSV (serial number + prize ID) for any raffle by super-admins only.
Source: raffles/tasks/addToRaffle.php:10-36

---

## Yearly Gift

Module: Yearly Gift
Rule:   The Yearly Gift is separate from the Yearly Raffle; it is awarded to students who have completed at least one task in each of a sufficient number of parshas (weeks) during the year, tracked in `user_yearly_gift`.
Source: yearly_prize/classes/TotalWeeklyTasks.php (entire class), mashpiadb_user_yearly_gift.sql

Module: Yearly Gift
Rule:   Each row in `user_yearly_gift` represents one parsha-week (identified by `start_date` and `end_date`) for one user; `marked = 1` means the student completed at least one valid task that week.
Source: mashpiadb_user_yearly_gift.sql:26-32, yearly_prize/classes/TotalWeeklyTasks.php:154-158

Module: Yearly Gift
Rule:   A week is considered completed (eligible for gift credit) if the student has at least one qualifying task mark in `date_tasks_marks` for that week, OR if an admin has manually overridden the week via `is_override = 1`.
Source: yearly_prize/classes/TotalWeeklyTasks.php:90-110 (`realtime_sql` UNION with `user_yearly_gift WHERE is_override = 1`)

Module: Yearly Gift
Rule:   The `is_override` flag in `user_yearly_gift` protects a manually-set week from being cleared by automated cache updates; non-override rows are cleared if real-time recalculation finds no marks.
Source: yearly_prize/classes/TotalWeeklyTasks.php:70-80 (`week_has_task` — if override is set and realtime is false, trust the cached value)

Module: Yearly Gift
Rule:   Admins can manually mark or unmark individual weeks for individual students via the AJAX endpoint `mark_week.php`; a manual mark always sets `is_override = 1`.
Source: yearly_prize/ajax/reports/total_weekly_task_mark.php:31-33

Module: Yearly Gift
Rule:   The non-override AJAX endpoint (in `ajax/yearly_gift/mark_week.php`) sets marks without the override flag; the mark-with-override endpoint (in `yearly_prize/ajax/reports/total_weekly_task_mark.php`) always sets `is_override = 1`.
Source: ajax/yearly_gift/mark_week.php:30-32 vs yearly_prize/ajax/reports/total_weekly_task_mark.php:31-33

Module: Yearly Gift
Rule:   Yearly-gift eligibility is scoped to the current year's start date (from `GlobalSettings::getCurYearDates()['start']`); weeks before that date are not counted.
Source: yearly_prize/classes/TotalWeeklyTasks.php:31-32

Module: Yearly Gift
Rule:   The prize-eligibility report lists each student's count of completed weeks out of total weeks in the selected range; it also shows whether the prize has been distributed (`distributed`) — which requires the prize to have been shipped first.
Source: yearly_prize/ajax/reports/total_weekly_tasks_summary.php:53-84

Module: Yearly Gift
Rule:   Each school has a configurable shipping method (`yearly_prize_shipping_method` on the `schools` table) of either `pickup` or `deliver`; only Chayolei schools (`chayolei = 1`) appear in the yearly prize prize-totals report.
Source: yearly_prize/ajax/reports/total_prizes.php:44-47

Module: Yearly Gift
Rule:   Prize shipping is tracked in `yearly_prize_shipping` separately from distribution; a prize can be `shipped` without yet being `distributed` — the report enforces showing the distribution checkbox only after shipping is marked.
Source: mashpiadb_yearly_prize_shipping.sql:26-34, yearly_prize/ajax/reports/total_weekly_tasks_summary.php:63-64

Module: Yearly Gift
Rule:   Staff members (Base Commanders, teachers, principals) are included in the yearly-gift shipping list alongside students and are tracked by `type` (not `user_id`) in `yearly_prize_shipping`.
Source: yearly_prize/ajax/reports/total_prizes.php:52-66, mashpiadb_yearly_prize_shipping.sql:28

---

## Auction

### Prize Classification

Module: Auction
Rule:   Prizes with `prize_points >= 72` (or `> 71`) are classified as "big" (grand) prizes; prizes with `prize_points < 72` are "small" prizes — this threshold governs which distribution algorithm applies.
Source: auction/auction.php:38-43 (`prize_points > 71`), auction/class.auction.php:51-56 (`prize_points >= 72`)

Module: Auction
Rule:   Each prize in `prizes_auction` carries a `prize_ratio` and `prize_points` value; `prize_points` directly determines both classification (big/small) and the ticket cost for automated distribution.
Source: mashpiadb_prizes_auction.sql:32-33

Module: Auction
Rule:   Prizes can be restricted by grade range (`min_grade` / `max_grade`, with French equivalents) and by gender (`M`, `F`, or `B` for both).
Source: mashpiadb_prizes_auction.sql:34-39

Module: Auction
Rule:   Each auction can have a `max_prize_points` cap and a `kiosk_auction` flag; auctions must be `approved = 1` before they are treated as live.
Source: mashpiadb_auctions.sql:35-37

### Ticket Distribution (Automated)

Module: Auction — Ticket Distribution
Rule:   Tickets are distributed automatically by calculating each student's remaining auction-point balance (total points earned since `auction_points_start_date` minus points already spent on existing ticket entries); a balance of at most 9 points is considered zero (minimum threshold is > 9).
Source: auction/ticket_distribution.php:28, 138

Module: Auction — Ticket Distribution
Rule:   The automated ticket distributor assigns one ticket to the highest-cost prize the student's remaining balance can afford, then deducts that cost, repeating until the balance drops to 9 or below.
Source: auction/ticket_distribution.php:83-138

Module: Auction — Ticket Distribution
Rule:   Tickets assigned by the automated system are flagged with `system_awarded = 1` in `auction_user_prizes`; manually or externally assigned tickets default to `system_awarded = 0`.
Source: mashpiadb_auction_user_prizes.sql:30, auction/ticket_distribution.php:119

Module: Auction — Ticket Distribution
Rule:   A fallback `giveOutTickets.php` routine gives one ticket each for five specific prizes to any student with at least 50 points who has no tickets in the auction yet; each prize costs 50 points and tickets are awarded sequentially until the balance drops below 50.
Source: auction/giveOutTickets.php:48-67

Module: Auction — Ticket Distribution
Rule:   Students in schools 12, 13, and 14 are excluded from the ticket distribution and from all big and small prize draws.
Source: auction/ticket_distribution.php:14 (`school_id not in (12,13,14)`), auction/auction.php:73, 152

### Running the Auction (Small Prizes)

Module: Auction — Small Prizes
Rule:   When awarding small prizes, a student must have fewer than 6 small prizes won (checked via `users.small_prizes_won < 6`) to be eligible as a ticket-pool candidate.
Source: auction/auction.php:150-151

Module: Auction — Small Prizes
Rule:   A student may not win more than 3 prizes in a single auction run (`numWon < 3`).
Source: auction/auction.php:216

Module: Auction — Small Prizes
Rule:   A school's percentage of total prizes won may not exceed its proportional share of the total student population plus 2%; if this ratio is exceeded the school is skipped for that draw.
Source: auction/auction.php:289-344 (`ratio_reached` — `round(100 * school_prizes/total_prizes) > round(100 * school_users/total_users) + 2`)

Module: Auction — Small Prizes
Rule:   When a student wins a small prize, `users.small_prizes_won` is incremented immediately so subsequent draws in the same run see the updated count.
Source: auction/auction.php:225-229

### Running the Auction (Big Prizes)

Module: Auction — Big Prizes
Rule:   Big prizes are awarded by selecting the student with the fewest `big_prizes_won` at both the school level and the user level; ties are broken by ordering on `school_big_prizes_won, user_big_prizes_won`.
Source: auction/auction.php:67-74

Module: Auction — Big Prizes
Rule:   After each big prize is awarded, both the school's `big_prizes_won` counter and the winning user's `big_prizes_won` counter are incremented in the database.
Source: auction/auction.php:92-104

### Running the Auction (Raffle-Style — class.auction.php / class.newAuction.php)

Module: Auction — Raffle Style
Rule:   In the raffle-style auction (`class.auction.php`), users who have already won a prize in any prior auction (auction_id >= 33) are excluded from the eligible pool for school-specific prizes.
Source: auction/class.auction.php:623-629

Module: Auction — Raffle Style
Rule:   Users who have no tickets in auction 37 but have at least 1,200 points form a secondary eligible pool (`users1200Points`); users with any points at all form a tertiary pool (`usersAnyPoints`).
Source: auction/class.auction.php:678-736

Module: Auction — Raffle Style
Rule:   Winner selection priority is: (1) users with tickets in the current auction, (2) users with ≥ 1,200 points, (3) users with any points; the system descends to the next pool only when the higher pool is empty for a given school.
Source: auction/class.auction.php:751-765 (`setWinners`)

Module: Auction — Raffle Style
Rule:   Grand prizes (points ≥ 72, `available = 1`) are drawn once per prize: a school is selected at random from the eligible school list, then a winner from that school; no school can win two grand prizes in the same run.
Source: auction/class.auction.php:770-797 (`setGrandPrizeWinners`)

Module: Auction — Raffle Style
Rule:   A user may win at most one prize per raffle-style auction run; once selected as a winner the user is added to `usersWon` and excluded from all subsequent draws in the same run.
Source: auction/class.auction.php:800-821 (`calculate`), auction/class.newAuction.php:50-57

Module: Auction — Raffle Style
Rule:   In `class.newAuction.php`, ticket quantity is respected: a user's `quantity` value in `auction_user_prizes` determines how many entries they have in the per-prize ticket pool (more tickets = higher probability of being drawn).
Source: auction/class.newAuction.php:30-36

Module: Auction — Raffle Style
Rule:   In the `NewAuction` runner, if a drawn winner has already won in this run, all their tickets for that prize are removed and a re-draw is performed recursively until a fresh winner is found or the pool is exhausted.
Source: auction/class.newAuction.php:50-57

### Raffle-Based Auction (raffle.class.php)

Module: Auction — Raffle Class
Rule:   Schools are added to the draw proportional to size: one entry per ≤100 students, with additional entries for every additional 100 students (threshold: `total > 75` adds another entry), ensuring larger schools get more draw slots.
Source: auction/raffle.class.php:85-112 (`setSchools`)

Module: Auction — Raffle Class
Rule:   A user who has already won a raffle prize in the current year (`auction_winners where auction_id >= 58`) may not win a second time unless the loop counter exceeds 5 attempts, at which point the school is flagged as a "bad school" and skipped.
Source: auction/raffle.class.php:343-355 (`getRandomChild` and `userWonThisYear`)

Module: Auction — Raffle Class
Rule:   When drawing for per-school specific prizes, the `schools_specific_prize` array lists school IDs (with repetition) representing the quantity allocation per school; each entry in that array results in exactly one prize winner drawn from that school.
Source: auction/raffle.class.php:239-247, auction/raffle_run.php:22-26

Module: Auction — Raffle Class
Rule:   The number of prizes a school is allocated in the non-specific prize pool is capped at total prizes available; once all prizes have been awarded the loop terminates.
Source: auction/raffle.class.php:262-263

### Auction Winners Table

Module: Auction
Rule:   Auction winners are stored in `auction_winners` with `shipped = 0` by default; quantity defaults to 1.
Source: mashpiadb_auction_winners.sql:29-31, auction/class.newAuction.php:138-143

Module: Auction
Rule:   Running a new auction for the same `auction_id` deletes all existing winners before re-drawing.
Source: auction/class.auction.php:827-829 (`deleteWinners`), auction/raffle.class.php:311-313 (`checkForWinners`)

Module: Auction
Rule:   The `auction_ran` flag is set to `1` on the auction record after a successful raffle run, preventing it from appearing in the "run auction" queue.
Source: auction/raffle.class.php:370-375 (`updateAuction`), auction/auction_run.php:39 (`auction_ran=0` filter)

Module: Auction
Rule:   The `auction_user_prizes` table records each student's bids/tickets with `won = 0` by default; `won` is updated to `1` when the student actually wins.
Source: mashpiadb_auction_user_prizes.sql:32

### Rebbe Coin Special Raffle

Module: Auction — Special Raffle
Rule:   In the Rebbe Coin raffle, users who hold a special prize ticket in auction 77 (prize 392) and have not won in any auction after auction 70 are the primary pool; users with no tickets in auction 77 but with at least 500 total points this year are added as a secondary pool.
Source: auction/rebbe_coin_raffle.php:6-38
