# Business Rules: Camps, Fundraising & Learning Programs
**Tzivos Hashem Platform — Extracted from SQL Schema & PHP Source**
Generated: 2026-04-30
Sources: SQLdump/*.sql, mashpia.com/public/admin_camp*.php, admin_bp_lines.php

---

## Camps

```
Rule ID:      BR-CMP-001
Category:     Camps
Description:  Every camp must be assigned a gender: Male (M), Female (F), or Both (B).
              A camp cannot be created without specifying this field.
Source:       mashpiadb_camps.sql
DB Evidence:  camp_gender enum('M','F','B') NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CMP-002
Category:     Camps
Description:  A camp name must be unique within an institution. Two camps belonging
              to the same institution cannot share the same name.
Source:       mashpiadb_camps.sql
DB Evidence:  UNIQUE KEY camp_name (inst_id, camp_name)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CMP-003
Category:     Camps
Description:  Each camp must be assigned a unique camp number, globally across all camps.
Source:       mashpiadb_camps.sql
DB Evidence:  UNIQUE KEY camp_number (camp_number)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CMP-004
Category:     Camps
Description:  A camp can be designated as a "home camp" via a settings flag. This is the
              only supported settings value; the field is an enumerated set.
Source:       mashpiadb_camps.sql
DB Evidence:  camp_settings set('home_camp') NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CMP-005
Category:     Camps
Description:  A camp must specify a shipping method: either pickup or deliver. There is
              no default — the camp admin must choose explicitly.
Source:       mashpiadb_camps.sql
DB Evidence:  shipping_method enum('pickup','deliver') NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CMP-006
Category:     Camps
Description:  A camp can have up to two distinct sessions, each with its own start and
              end dates (session_one_start/end, session_two_start/end). Sessions are
              optional (columns are nullable).
Source:       mashpiadb_camps.sql
DB Evidence:  session_one_start, session_one_end, session_two_start, session_two_end
              mediumint(8) unsigned DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CMP-007
Category:     Camps
Description:  Kiosk printing is enabled by default for all camps. It can be turned off
              explicitly.
Source:       mashpiadb_camps.sql
DB Evidence:  kiosk_print tinyint(1) unsigned NOT NULL DEFAULT 1
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CMP-008
Category:     Camps
Description:  A camp belongs to exactly one institution and to exactly one school. Both
              fields are required and non-null.
Source:       mashpiadb_camps.sql
DB Evidence:  inst_id NOT NULL DEFAULT 8; school_id NOT NULL DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CMP-009
Category:     Camps
Description:  A camp must be linked to a camp type. Camp types are defined in a separate
              lookup table (camp_types) and multiple types may exist (AUTO_INCREMENT=5,
              implying at least 4 types in use).
Source:       mashpiadb_camps.sql, mashpiadb_camp_types.sql
DB Evidence:  camp_type_id int(10) unsigned NOT NULL (in camps table);
              camp_types table AUTO_INCREMENT=5
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CMP-010
Category:     Camps
Description:  Camp campaigns are unique per camp — a given global campaign can be
              assigned to a specific camp only once.
Source:       mashpiadb_camp_campaigns.sql
DB Evidence:  UNIQUE KEY camp_id (camp_id, campaign_id)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CMP-011
Category:     Camps
Description:  Each camp campaign has an active/inactive flag, defaulting to active (1).
              Inactive campaigns presumably stop awarding points.
Source:       mashpiadb_camp_campaigns.sql
DB Evidence:  active tinyint(1) unsigned NOT NULL DEFAULT 1
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CMP-012
Category:     Camps
Description:  A camp campaign can be flagged as a group task (group_task=1), meaning
              completion is tracked at the group level rather than the individual level.
              Default is individual (0).
Source:       mashpiadb_camp_campaigns.sql
DB Evidence:  group_task tinyint(1) unsigned NOT NULL DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CMP-013
Category:     Camps
Description:  Camp missions are organized within campaigns and carry a sequence number,
              allowing campaigns to have multiple ordered missions.
Source:       mashpiadb_camp_missions.sql
DB Evidence:  sequence tinyint(3) unsigned NOT NULL; camp_campaign_id int NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CMP-014
Category:     Camps
Description:  Within a mission, each task name must be unique. A task cannot appear
              twice under the same mission.
Source:       mashpiadb_camp_tasks.sql
DB Evidence:  UNIQUE KEY camp_mission_id (camp_mission_id, task_name)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CMP-015
Category:     Camps
Description:  Camp tasks are typed by camp_type_id and can be associated with a level
              (level_id, defaulting to 0) and a time period (period_id). Both missions
              and tasks carry independent point values.
Source:       mashpiadb_camp_tasks.sql
DB Evidence:  camp_type_id NOT NULL; level_id DEFAULT 0; period_id NOT NULL; points NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CMP-016
Category:     Camps
Description:  Camp task points are stored as decimal values on physical card codes.
              Each card code is tied to a specific camp and task, carries a point value,
              has left/right circle identifiers (likely for physical card design), and
              has an expiration date. Cards expire after a set date and cannot be
              redeemed after expiry.
Source:       mashpiadb_camp_card_codes.sql
DB Evidence:  expiration_date date NOT NULL; points decimal(8,2) unsigned NOT NULL;
              left_circle char(1) NOT NULL; right_circle char(1) NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CMP-017
Category:     Camps
Description:  Group points for camp groups are tracked by date, allowing historical
              point records per group per day.
Source:       mashpiadb_camp_group_points.sql
DB Evidence:  points_date date NOT NULL; group_id; points NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CMP-018
Category:     Camps
Description:  Camp prizes have a point cost (prize_points). Each prize has an
              availability quantity (prize_available, defaulting to 0 meaning none
              available until explicitly set). A prize is "installed" by default (1).
Source:       mashpiadb_prizes_camp.sql
DB Evidence:  prize_points int(10) unsigned NOT NULL; prize_available DEFAULT 0;
              installed tinyint(1) NOT NULL DEFAULT 1
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CMP-019
Category:     Camps
Description:  A prize name must be unique within a camp — the same prize name cannot
              appear twice for the same camp. Prizes may also be linked to a global
              prize template (global_prize_id, default 0 meaning no global link).
Source:       mashpiadb_prizes_camp.sql
DB Evidence:  UNIQUE KEY prize_name (prize_name, camp_id); global_prize_id DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CMP-020
Category:     Camps
Description:  Camp members are stored in the users table, linked by camp_id. A member
              addition note in the admin UI warns that adding a member via camp admin is
              for records only and does NOT register the child in TH. Official registration
              requires a separate TH registration process.
Source:       admin_camp_members.php
DB Evidence:  users.camp_id; UI note: "Adding a member's name is for your own records
              only and does not register him/her in TH."
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CMP-021
Category:     Camps
Description:  User accounts generated for camp members require a globally unique user_code
              (random 63-bit integer). The system retries up to 100,000 times to generate
              a unique code before raising an error.
Source:       admin_camp_members.php
DB Evidence:  users.user_code; PHP logic: do { $user_code = RAND... } while (COUNT > 0);
              if ($count++ > 100000) trigger_error
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CMP-022
Category:     Camps
Description:  Usernames are generated deterministically: first initial + cleaned last name
              (lowercase, non-letters stripped). If a collision exists, an integer suffix
              is appended until unique.
Source:       admin_camp_members.php
DB Evidence:  $username = firstInitial + cleanedLastName; while(duplicate) $count++;
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CMP-023
Category:     Camps
Description:  Camp admin access is role-based. Two roles exist: "camp" (camp director,
              limited to their own camp) and "super" (can access all camps). A camp
              director cannot switch between camps.
Source:       admin_camp_members.php, admin_camp_campaigns.php, admin_camp_groups.php
DB Evidence:  $admin_user['auth'] == "super" vs "camp"; camp director: $camp_id =
              $admin_user['auths']['camp'][0]
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CMP-024
Category:     Camps
Description:  Campaign tasks track start and end dates using the Jewish calendar (Julian
              Day Number via jewishtojd()). An end date is optional (NULL allowed).
Source:       admin_campaign_tasks.php
DB Evidence:  $start_date = jewishtojd(...); $end_date = "NULL" if date fields are -1
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CMP-025
Category:     Camps
Description:  Campaign tasks have a max_times field, limiting how many times a task can
              be completed/claimed. Weekly tasks additionally specify which days of the
              week (Sunday through Shabbos) they apply.
Source:       admin_campaign_tasks.php
DB Evidence:  INSERT INTO campaign_tasks (..., max_times, ...); UPDATE includes
              day-of-week columns (sunday, monday, ..., saturday/shabbos)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CMP-026
Category:     Camps
Description:  Task point values in the general task system are bounded: minimum 0,
              maximum 9999.99. This constraint is enforced on the client side when
              editing task points.
Source:       admin_camp_tasks.php
DB Evidence:  onChange="this.value = Math.max(0, Math.min(parseFloat('0'+this.value), 9999.99));"
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-CMP-027
Category:     Camps
Description:  Task quantity values are bounded: minimum 0, maximum 65535. This is
              consistent with a smallint unsigned range.
Source:       admin_camp_tasks.php
DB Evidence:  onChange="this.value = Math.max(0, Math.min(parseInt('0'+this.value), 65535));"
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-CMP-028
Category:     Camps
Description:  The task activation and point grid covers school years (levels) 3 through
              14, inclusive — meaning students from grade/year 3 up to grade/year 14 can
              participate in tasks, with different point values per year level and track.
Source:       admin_camp_tasks.php
DB Evidence:  foreach (range(3, 14) as $level); level = max(3, min(intval($level), 14))
Confidence:   High
SME Verified: No
```

---

## Fundraising & Donations

```
Rule ID:      BR-FND-001
Category:     Fundraising
Description:  Each Charidy donation record stores both the raw donation amount and a
              "with_matching" amount, tracking the post-match total separately from the
              original pledge.
Source:       mashpiadb_charidy.sql
DB Evidence:  donation int(10) unsigned; with_matching int(10) unsigned
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-FND-002
Category:     Fundraising
Description:  Charidy donations are linked to a parent admin (solicited via a mashpia/
              admin), recorded by year. Each record is associated with a specific
              campaign year.
Source:       mashpiadb_charidy.sql
DB Evidence:  parent_admin_id int(10) unsigned; year int(10) unsigned
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-FND-003
Category:     Fundraising
Description:  Charidy donors are uniquely identified by email address. No two donors
              can share the same email.
Source:       mashpiadb_charidy_donors.sql
DB Evidence:  UNIQUE KEY email (email)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-FND-004
Category:     Fundraising
Description:  A donor can be flagged as needing a phone call (needs_call), defaulting
              to 0 (no call needed). This supports a caller workflow where staff follow
              up with certain donors.
Source:       mashpiadb_charidy_donors.sql
DB Evidence:  needs_call tinyint(3) unsigned DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-FND-005
Category:     Fundraising
Description:  Each donor is assigned to at most one caller per year. The same donor
              cannot be assigned to two callers in the same year.
Source:       mashpiadb_charidy_donors_callers.sql
DB Evidence:  UNIQUE KEY noDuplicates (donor_id, year)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-FND-006
Category:     Fundraising
Description:  Charidy donations can be marked as child-only (child_only_donation flag,
              default 0). This allows filtering donations that should only be attributed
              to the child rather than a family or general campaign.
Source:       mashpiadb_charidy_donations.sql
DB Evidence:  child_only_donation tinyint(3) unsigned DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-FND-007
Category:     Fundraising
Description:  Charidy donations can carry a dedication name and dedication text for
              honorific/memorial purposes.
Source:       mashpiadb_charidy_donations.sql
DB Evidence:  dedication_name varchar(85); dedication_text varchar(255)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-FND-008
Category:     Fundraising
Description:  School staff records for charidy campaigns classify staff into three types:
              principal, bc (base commander/program coordinator), or teacher.
Source:       mashpiadb_charidy_school_staff.sql
DB Evidence:  staff_type enum('principal','bc','teacher')
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-FND-009
Category:     Fundraising
Description:  The charidy_final_list table stores historical donation data across at
              least two years (5776 and 5777 in the Jewish calendar), including
              per-year donation amounts and donor ranks, supporting multi-year
              fundraising analytics.
Source:       mashpiadb_charidy_final_list.sql
DB Evidence:  donation_5776, rank_5776, projected_5777, rank_5777 — separate
              columns per Hebrew year
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-FND-010
Category:     Fundraising
Description:  Temporary donation records (charidy_temp_donations) capture the
              with_matching amount at point of entry alongside the raw donation_amount,
              confirming that matching is computed and stored at transaction time.
              IP address is also captured.
Source:       mashpiadb_charidy_temp_donations.sql
DB Evidence:  with_matching decimal(6,2); donation_amount decimal(6,2); ip varchar(45)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-FND-011
Category:     Fundraising
Description:  General donations (donations table) require a non-null first name, last
              name, email, amount, and response code. These are mandatory fields for
              any donation record.
Source:       mashpiadb_donations.sql
DB Evidence:  first_name NOT NULL; last_name NOT NULL; email NOT NULL; amount NOT NULL;
              response NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-FND-012
Category:     Fundraising
Description:  General donations can be associated with a reason (e.g. campaign type),
              a dedication, and a family name — all optional.
Source:       mashpiadb_donations.sql
DB Evidence:  reason varchar(30) DEFAULT NULL; dedication varchar(100) DEFAULT NULL;
              family varchar(30) DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-FND-013
Category:     Fundraising
Description:  All donations (all_donations table) record a response string from the
              payment processor, serving as a transaction confirmation field. Timestamps
              default to current time.
Source:       mashpiadb_all_donations.sql
DB Evidence:  response varchar(255) NOT NULL; date timestamp DEFAULT current_timestamp()
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-FND-014
Category:     Fundraising
Description:  Hakhel donations are tracked per year and require a year field, linking
              donations to the specific Hakhel program year.
Source:       mashpiadb_hakhel_donations.sql
DB Evidence:  year int(10) unsigned NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-FND-015
Category:     Fundraising
Description:  Chidon donations can be made anonymously (anonymous flag, default 0) and
              can be made on behalf of a specific family (for_family_id). They are tied
              to a specific chidon_year.
Source:       mashpiadb_chidon_donations.sql
DB Evidence:  anonymous tinyint(1) unsigned DEFAULT 0; for_family_id; chidon_year NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-FND-016
Category:     Fundraising
Description:  Chidon donations carry a display_name that may differ from the donor's
              actual name, supporting the anonymous/display-name distinction.
Source:       mashpiadb_chidon_donations.sql
DB Evidence:  name varchar(75); display_name varchar(75) — two separate fields
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-FND-017
Category:     Fundraising
Description:  Each family can have at most one fundraising total per campaign year
              (family_raised table). The system prevents duplicate yearly totals
              per family.
Source:       mashpiadb_family_raised.sql
DB Evidence:  UNIQUE KEY once_only (admin_id, year)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-FND-018
Category:     Fundraising
Description:  Families accumulate pre-paid balances that track both the amount prepaid
              and the amount used. Refund information (refund_amount, refund_type,
              paypal) is tracked. There is a "real_prepaid" field distinct from the
              raw "prepaid", suggesting a reconciliation or adjustment process.
Source:       mashpiadb_family_prepaid_balances.sql
DB Evidence:  prepaid decimal(7,2) unsigned NOT NULL DEFAULT 0.00;
              used decimal(7,2) unsigned NOT NULL DEFAULT 0.00;
              real_prepaid decimal(7,2) unsigned DEFAULT NULL;
              refund_amount, refund_type, paypal
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-FND-019
Category:     Fundraising
Description:  Sponsorships have a mandatory start_date and end_date, defining a
              sponsorship window. A sponsor must provide their name, email, and phone.
              The amount paid is optional at creation time.
Source:       mashpiadb_sponsorships.sql
DB Evidence:  start_date date NOT NULL; end_date date NOT NULL;
              sponsor NOT NULL; email NOT NULL; phone NOT NULL;
              amount_paid decimal(8,2) DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-FND-020
Category:     Fundraising / Maos Chitim
Description:  The Maos Chitim (Passover food fund) program tracks both pledged and
              raised amounts per user per year. Users (students/families) make pledges
              which may differ from what is ultimately collected.
Source:       mashpiadb_maos_chitim.sql
DB Evidence:  pledged decimal(8,2); raised decimal(8,2); user_id NOT NULL; year NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-FND-021
Category:     Fundraising / Maos Chitim
Description:  Physical Maos Chitim cards have a numeric code and a small decimal value
              (max 99.99). Cards are redeemable instruments with associated dollar values.
Source:       mashpiadb_maos_chitim_cards.sql
DB Evidence:  number int PRIMARY KEY; value decimal(4,2) NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-FND-022
Category:     Fundraising / Maos Chitim
Description:  Individual student pledges for Maos Chitim are capped at a small amount
              (decimal(4,2) = max 99.99) and are recorded per user per year with a
              timestamp.
Source:       mashpiadb_maos_chitim_student_pledges.sql
DB Evidence:  amount decimal(4,2) NOT NULL; user_id NOT NULL; year NOT NULL
Confidence:   High
SME Verified: No
```

---

## Tanya Learning

```
Rule ID:      BR-LRN-001
Category:     Tanya Learning
Description:  The Tanya learning program uses tracks. Each track has a defined lines_goal
              (the number of Tanya lines a student is expected to learn on that track).
              The track number is the primary key.
Source:       mashpiadb_tanya_goals.sql
DB Evidence:  track tinyint(3) unsigned PRIMARY KEY; lines_goal smallint(5) unsigned NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-002
Category:     Tanya Learning
Description:  Each line of Tanya is uniquely identified by a line number and belongs
              to a specific page and perek (chapter). The maximum text per line is 4096
              characters. Line numbers and pages form a composite unique key.
Source:       mashpiadb_tanya_lines.sql
DB Evidence:  PRIMARY KEY (line); UNIQUE KEY page (page, line); text varchar(4096) NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-003
Category:     Tanya Learning
Description:  The Tanya ladder system defines learning schedules by age, quota type,
              quantity (qty), and date window (start_date / end_date). Different age
              groups have different learning quotas within each ladder level.
Source:       mashpiadb_tanya_ladders.sql
DB Evidence:  ladder, age, quota varchar(45), qty int, start_date int, end_date int —
              composite structure; no primary key suggests it is used as a lookup table
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-004
Category:     Tanya Learning
Description:  Each Tanya user has a track (defaulting to 1), a learning year (defaulting
              to 1), and a count of completed lines (lines_done, default 0) plus an
              offset (lines_offset) for lines already completed before program enrollment.
Source:       mashpiadb_tanya_users.sql
DB Evidence:  track DEFAULT 1; year DEFAULT 1; lines_done DEFAULT 0;
              lines_offset smallint(5) unsigned NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-005
Category:     Tanya Learning
Description:  Tanya users accumulate pledges and collections independently.
              pledges tracks money pledged per line, collected tracks money received.
              Both default to 0.00 and are unsigned decimals.
Source:       mashpiadb_tanya_users.sql
DB Evidence:  pledges decimal(8,2) unsigned NOT NULL DEFAULT 0.00;
              collected decimal(8,2) unsigned NOT NULL DEFAULT 0.00
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-006
Category:     Tanya Learning
Description:  Each Tanya user profile also aggregates their total Tanya lines and
              Mishna lines as denormalized counters (tanya_lines, mishna_lines).
              These allow quick reporting without re-scanning transaction rows.
Source:       mashpiadb_tanya_users.sql
DB Evidence:  tanya_lines int(11) NOT NULL; mishna_lines int(11) NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-007
Category:     Tanya Learning
Description:  Tanya users can earn medals. The medal order (medal_ord) is stored as a
              decimal (4,2) and defaults to 0.00. The decimal format suggests fractional
              medal levels or partial credit.
Source:       mashpiadb_tanya_users.sql
DB Evidence:  medal_ord decimal(4,2) unsigned NOT NULL DEFAULT 0.00
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-LRN-008
Category:     Tanya Learning
Description:  Tanya medal cards are physical redemption cards tied to a school, a
              subject, and a medal stage (tinyint). They have an expiration date after
              which they cannot be redeemed.
Source:       mashpiadb_tanya_medal_cards.sql
DB Evidence:  school_id NOT NULL; subject_id NOT NULL; medal_stage tinyint(3) unsigned NOT NULL;
              expiration_date date NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-009
Category:     Tanya Learning
Description:  School-level Tanya totals aggregate both total_tanya (Tanya lines) and
              total_mishna (Mishna lines) per school, with school_id as the primary key —
              one summary row per school.
Source:       mashpiadb_tanya_totals.sql
DB Evidence:  PRIMARY KEY (school_id); total_tanya NOT NULL; total_mishna NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-010
Category:     Tanya Learning (Demo / Pilot)
Description:  The demo Tanya system tracks users in an enrolled/not-enrolled state
              (enrolled tinyint DEFAULT 0). Users have a desired chapter goal, which is
              renamed to line_goal in newer usage. The demo system is used for testing
              only and is separate from production data.
Source:       mashpiadb_demo_tanya_users.sql
DB Evidence:  enrolled DEFAULT 0; desired_chapter_goal (COMMENT 'to be renamed to line_goal');
              TABLE COMMENT 'this table is user only for testing the tanya demo'
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-011
Category:     Tanya Learning (Demo)
Description:  In the demo Tanya mission system, each weekly task ("mission") tracks:
              the expected weekly line count (real), the adjusted lines after carrying
              over a remainder from the previous mission (sum), and a cumulative total
              of all lines completed up to this point (virtual_sum).
Source:       mashpiadb_demo_tanya_missions.sql
DB Evidence:  real decimal(4,2) COMMENT 'The weekly line number value; defined by ladder';
              sum decimal(4,2) COMMENT 'lines for this week after remainder subtracted';
              virtual_sum int COMMENT 'number of lines completed up till this point'
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-012
Category:     Tanya Learning (Demo)
Description:  Each user can have only one pending ladder-change request at a time
              (UNIQUE KEY user_id on demo_tanya_requests). A ladder change request
              specifies the target line_goal and the desired ladder to switch to.
Source:       mashpiadb_demo_tanya_requests.sql
DB Evidence:  UNIQUE KEY user_id (user_id); to_ladder int NOT NULL; line_goal int NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-013
Category:     Tanya Learning (Demo)
Description:  Tanya medals have a unique name and an integer "values" field (likely
              the line threshold to earn that medal). Medal order (medal_ord) is the
              primary key, ensuring medals are ranked in a defined sequence.
Source:       mashpiadb_demo_tanya_medals.sql
DB Evidence:  PRIMARY KEY (medal_ord); UNIQUE KEY medal_name (medal_name); values int NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-014
Category:     Tanya Learning (Demo)
Description:  Within the demo Tanya, a user cannot record the same line more than once
              within the same mission. Each (user, mission, line_number) combination
              is unique.
Source:       mashpiadb_demo_tanya_tasks.sql
DB Evidence:  UNIQUE KEY user_id_2 (user_id, mission, line_number)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-015
Category:     Tanya Learning (Demo)
Description:  Demo Tanya goals are tied to a specific year and chapter, allowing the
              program to define year-by-year chapter completion targets.
Source:       mashpiadb_demo_tanya_goals.sql
DB Evidence:  year int(4) NOT NULL; chapter varchar(2) NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-016
Category:     Tanya Learning (Admin BP Report)
Description:  The BP (Base Points) report groups learning progress using campaign IDs 9
              and 10, where campaign 9 represents Tanya lines and campaign 10 represents
              Mishna lines. These are distinct campaign identifiers for different learning
              program types within the BP system.
Source:       admin_bp_lines.php
DB Evidence:  WHERE bp.campaign_id in (9,10); columns labeled "Tanya Lines" and "Mishna Lines"
Confidence:   High
SME Verified: No
```

---

## Tehillim & Mishnah

```
Rule ID:      BR-LRN-017
Category:     Tehillim
Description:  The Tehillim (Psalms) table is the master list of all chapters (kapitels)
              and their verse count (posukim). Each kapitel has a fixed, non-nullable
              verse count.
Source:       mashpiadb_tehillim.sql
DB Evidence:  PRIMARY KEY (kapitel); posukim int(10) unsigned NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-018
Category:     Tehillim
Description:  The Tehillim ladder system defines reading schedules by ladder level, age,
              and month. For each combination, it specifies: which kapitels to read
              (kapitelach), estimated time in minutes, reading speed, and quantity (qty).
              This implies age- and month-appropriate reading pace targets.
Source:       mashpiadb_tehillim_ladders.sql
DB Evidence:  PRIMARY KEY (ladder, age, month); kapitelach varchar(20); minutes int;
              speed float(2,1); qty decimal(4,1)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-019
Category:     Tehillim
Description:  Tehillim progress backups are recorded with both a grid_id (scheduling
              grid) and an sm_date (Shmini / secular date). The primary per-entry
              uniqueness is (date_task_id, user_id), preventing duplicate completions
              for the same task on the same day for the same user.
Source:       mashpiadb_tehillim_backups.sql
DB Evidence:  UNIQUE KEY MAIN (date_task_id, user_id); mark_date; done_qty; year; grid_id; sm_date
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-020
Category:     Tehillim
Description:  The Tehillim backup table is high-volume (AUTO_INCREMENT=870680), confirming
              that Tehillim progress tracking is a core, heavily used feature of the
              platform.
Source:       mashpiadb_tehillim_backups.sql
DB Evidence:  AUTO_INCREMENT=870680
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-021
Category:     Mishnah
Description:  Mishnayos (individual Mishnah units) are organized hierarchically:
              seder (order) -> mesechta (tractate) -> perek (chapter) -> mishna number.
              Each mishna has a line count (num_lines), which is used for progress
              measurement in lines rather than raw mishna count.
Source:       mashpiadb_mishnos.sql
DB Evidence:  seder_id; mesechto_id; perek; mishna; num_lines — all NOT NULL;
              AUTO_INCREMENT=4161 (~4160 mishnayos catalogued)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-022
Category:     Mishnah
Description:  Mishna assignments link a specific mesechta to a user, school, and class.
              Students are assigned mesechtos to learn, not individual mishnayos.
Source:       mashpiadb_mishna_assigned.sql
DB Evidence:  seder_id; mesechto_id; user_id; school_id; class_id — all NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-023
Category:     Mishnah
Description:  "Mishna at once" tracks which perakim of a mesechta a user has committed
              to learn simultaneously (i.e. the entire perek at once, rather than
              mishna by mishna). Primary key is (user_id, mesechto_id, perek).
Source:       mashpiadb_mishna_at_once.sql
DB Evidence:  PRIMARY KEY (user_id, mesechto_id, perek)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-024
Category:     Mishnah
Description:  Each individual mishna can be learned only once per user. The combination
              (mesechta, perek, mishna, user_id) is unique, preventing duplicate completion
              records.
Source:       mashpiadb_mishna_learned.sql
DB Evidence:  UNIQUE KEY mishna (mesechto_id, perek, mishna, user_id)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-025
Category:     Mishnah
Description:  Mishna learning records track both when a mishna was learned (date_learned,
              user-reported) and when it was entered in the system (date_entered, auto
              timestamp). This separation allows retroactive entry.
Source:       mashpiadb_mishna_learned.sql
DB Evidence:  date_learned datetime DEFAULT NULL (user-supplied);
              date_entered timestamp NOT NULL DEFAULT current_timestamp() (system)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-026
Category:     Mishnah
Description:  Each perek of a mesechta can be completed only once per user
              (PRIMARY KEY on mesechto_id, perek, user_id in perokim_learned).
              Perek completion is tracked independently from individual mishna completion.
Source:       mashpiadb_perokim_learned.sql
DB Evidence:  PRIMARY KEY (mesechto_id, perek, user_id)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-027
Category:     Mishnah
Description:  Each mesechta (tractate) can be fully completed only once per user
              (PRIMARY KEY on mesechto_id, user_id in mesechtos_learned).
              Full mesechta completion is a milestone event tracked separately.
Source:       mashpiadb_mesechtos_learned.sql
DB Evidence:  PRIMARY KEY (mesechto_id, user_id)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-028
Category:     Mishnah
Description:  Each mesechta has a pre-computed summary: total perokim, total mishnayos,
              and total lines. This summary is used for progress calculations without
              re-counting.
Source:       mashpiadb_mesechtos_summary.sql
DB Evidence:  total_perokim, total_mishnos, total_lines — all NOT NULL;
              PRIMARY KEY (mesechto_id)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-029
Category:     Mishnah
Description:  Each perek similarly has a pre-computed summary of total_mishnos and
              total_lines. Primary key is (mesechto_id, perek).
Source:       mashpiadb_perokim_summary.sql
DB Evidence:  PRIMARY KEY (mesechto_id, perek); total_mishnos NOT NULL; total_lines NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-030
Category:     Mishnah / BP System
Description:  The mishna_ppl (points per line) table stores per-school and per-class
              point-rate configurations for the Mishna learning program. It tracks
              separate point rates for: general (points), Mishna (m_points), Parsha
              (p_points), Shas (shas_points), and an unspecified fourth category (s_points).
Source:       mashpiadb_mishna_ppl.sql
DB Evidence:  points decimal(3,2); m_points decimal(3,2); p_points decimal(3,2);
              s_points decimal(3,2); shas_points decimal(3,2)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-031
Category:     Mishnah / Lines
Description:  Lines learned are associated with a campaign, school, user, and class.
              The lines_learned count and a mission_sheet_amount (optional) are stored.
              A noDuplicates field (default 0) is used for deduplication control.
Source:       mashpiadb_lines_learned.sql
DB Evidence:  campaign_id; school_id; user_id; class_id; lines_learned; mission_sheet_amount;
              noDuplicates DEFAULT 0; AUTO_INCREMENT=177322 (high volume)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-032
Category:     Mishnah / Lines
Description:  Lines pledged are tracked similarly but separately from lines learned,
              allowing comparison between commitment and completion.
Source:       mashpiadb_lines_pledged.sql
DB Evidence:  campaign_id; school_id; user_id; class_id; lines_pledged — separate table
              from lines_learned; AUTO_INCREMENT=48315
Confidence:   High
SME Verified: No
```

---

## Pesukim & Kapitel

```
Rule ID:      BR-LRN-033
Category:     Pesukim / Kapitel
Description:  The Kapitel (Tehillim chapter verse) table stores every posuk (verse)
              within every kapitel, along with its word count. Kapitel must exist in the
              parent tehillim table (foreign key with CASCADE on update).
Source:       mashpiadb_kapitels.sql
DB Evidence:  CONSTRAINT kapitels_ibfk_1 FOREIGN KEY (kapitel) REFERENCES tehillim(kapitel)
              ON UPDATE CASCADE; words int(10) unsigned NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-034
Category:     Pesukim / Kapitel
Description:  Each posuk within a kapitel is unique (UNIQUE KEY on kapitel, posuk, words),
              preventing duplicate verse entries.
Source:       mashpiadb_kapitels.sql
DB Evidence:  UNIQUE KEY kapitel (kapitel, posuk, words)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-035
Category:     Pesukim
Description:  The Pesukim program has a recruitment component. Students (mechunachim)
              are recruited by a mechanech (educator/mentor), who must be a valid user
              in the system. The mechanech-mechunach relationship is enforced by a
              foreign key to the users table with CASCADE delete/update.
Source:       mashpiadb_pesukim_mechunachim.sql
DB Evidence:  CONSTRAINT user_id FOREIGN KEY (mechanech_user_id) REFERENCES users(user_id)
              ON DELETE CASCADE ON UPDATE CASCADE
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-036
Category:     Pesukim
Description:  A mechunach (recruit in Pesukim program) must be verified before being
              fully enrolled. The system generates a verification_code and tracks
              verified (default 0) and date_verified fields.
Source:       mashpiadb_pesukim_mechunachim.sql
DB Evidence:  verification_code varchar(45) DEFAULT NULL;
              verified tinyint(3) unsigned NOT NULL DEFAULT 0;
              date_verified datetime DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-037
Category:     Pesukim
Description:  Pesukim recruits track who recruited whom (recruiter_id -> recruited_id)
              by year. This enables a referral/chain-recruitment tracking system.
Source:       mashpiadb_pesukim_recruits.sql
DB Evidence:  recruiter_id NOT NULL; recruited_id NOT NULL; year DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-038
Category:     Pesukim
Description:  Pesukim "duch" (report) recruits record the name and mother's name
              (mothers_name) of each recruit. Mother's name is required for Jewish
              naming conventions used in dedications/records.
Source:       mashpiadb_pesukim_duch_recruits.sql
DB Evidence:  name varchar(45) NOT NULL; mothers_name varchar(55) NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-039
Category:     Pesukim
Description:  The Pesukim program has configurable point multipliers per type/label.
              Different activity types earn different multiples of a base point value.
              Only two setting records exist (AUTO_INCREMENT=3), suggesting two
              configurable multiplier types.
Source:       mashpiadb_pesukim_settings.sql
DB Evidence:  type varchar(45); label varchar(85); multiplier decimal(4,2);
              AUTO_INCREMENT=3 (2 records)
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-LRN-040
Category:     Pesukim / Parshos
Description:  Parshos (Torah portions) are defined with a start and end (likely line/
              posuk numbers) and linked to a Hebrew year (char(4)). Multiple parshos
              may exist per year. Both mashpiadb and pointsDB maintain their own parshos
              tables with the same structure.
Source:       mashpiadb_parshos.sql, pointsDB_parshos.sql
DB Evidence:  start int, end int, name varchar(30), year char(4); AUTO_INCREMENT=1058
              (mashpiadb) and AUTO_INCREMENT=257 (pointsDB)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-041
Category:     Pesukim / Sefer Hamitzvos
Description:  Users complete daily Sefer Hamitzvos (Book of Commandments) missions.
              Each record is per user, per mission, per date — there is no unique
              constraint, suggesting multiple entries per day are allowed.
Source:       mashpiadb_user_sefer_hamitzvos.sql
DB Evidence:  user_id, mission, date — no UNIQUE constraint; AUTO_INCREMENT=1720
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-LRN-042
Category:     General Learning / Book System
Description:  The pointsDB book system (used for structured learning content) supports
              configurable display of line numbers, paragraphs, pages, chapters, and
              volumes. Each feature defaults to enabled (1) and can be disabled per book.
              Books belong to institutions.
Source:       pointsDB_books.sql
DB Evidence:  line_numbers_enabled DEFAULT 1; paragraphs_enabled DEFAULT 1;
              pages_enabled DEFAULT 1; chapters_enabled DEFAULT 1; volumes_enabled DEFAULT 1;
              institution_id NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-043
Category:     General Learning / Limud Audio
Description:  Audio content for learning programs is indexed by unit number. Each unit
              has exactly one audio link. This provides per-unit audio support for the
              limud (learning) system.
Source:       mashpiadb_limud_audio.sql
DB Evidence:  PRIMARY KEY (unit); audio_link varchar(65) NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-LRN-044
Category:     General Learning / Limud Schedule
Description:  The limud_book_units table maps a specific day and year to a book unit.
              A given (day, book, unit, year) combination is unique, meaning a unit
              can only appear once on a given day within a year's learning schedule.
Source:       mashpiadb_limud_book_units.sql
DB Evidence:  UNIQUE KEY book_unit (day, book, unit, year)
Confidence:   High
SME Verified: No
```

---

*Note: All rules are inferred from schema structure (column types, constraints, defaults, enum values) and PHP logic. SME (Subject Matter Expert) review is required before these rules are used for system redesign or compliance purposes.*
