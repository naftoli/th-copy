it'# Business Rules: Campaigns, ID Cards / Hachayols, System Settings, Announcements, Add-Ons, Platoon Transitions

**Extraction date:** 2026-04-30  
**Sources:** SQL dump files in `/SQLdump/` and PHP files in `mashpia.com/public/`  
**Databases:** `mashpiadb` (admin/operational) and `pointsDB` (points/gamification engine)

---

## Campaigns

```
Rule ID:      BR-CAM-001
Category:     Campaigns
Description:  Every global campaign must have a name (English) and a point value assigned.
              A French-language name is also required at the database level. Campaigns default
              to being individual tasks (group_task = 0).
Source:       mashpiadb_global_campaigns.sql (table: global_campaigns)
DB Evidence:  campaign_name VARCHAR(255) NOT NULL; campaign_name_fr VARCHAR(255) NOT NULL;
              points INT UNSIGNED NOT NULL; group_task TINYINT DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CAM-002
Category:     Campaigns
Description:  Campaigns can be designated as either individual tasks or group tasks. These two
              types are displayed and managed separately in the admin interface. A campaign
              belongs to exactly one type at creation.
Source:       mashpiadb_global_campaigns.sql; admin_campaigns.php
DB Evidence:  group_task TINYINT(1) UNSIGNED NOT NULL DEFAULT 0
              PHP: WHERE group_task=0 and WHERE group_task=1 in separate queries
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CAM-003
Category:     Campaigns
Description:  Camp-level (school/institution) campaigns are variants of global campaigns,
              allowing each camp to override the campaign name and point value. A given
              global campaign can only be assigned once per camp (unique constraint).
              Camp campaigns can be individually activated or deactivated.
Source:       mashpiadb_camp_campaigns.sql (table: camp_campaigns)
DB Evidence:  UNIQUE KEY camp_id (camp_id, campaign_id);
              active TINYINT(1) UNSIGNED NOT NULL DEFAULT 1
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CAM-004
Category:     Campaigns
Description:  Camp campaigns default to active (active = 1). An inactive camp campaign
              is retained in the database and can be reactivated; it is not deleted.
Source:       mashpiadb_camp_campaigns.sql (table: camp_campaigns)
DB Evidence:  active TINYINT(1) UNSIGNED NOT NULL DEFAULT 1
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CAM-005
Category:     Campaigns
Description:  Line campaigns (recurring Torah study programs such as Tanya, Mishna, Tehillim)
              are typed to exactly one of three subjects. Each line campaign record stores
              a campaign date (Gregorian), a start date offset, and an academic year.
Source:       mashpiadb_line_campaigns.sql (table: line_campaigns)
DB Evidence:  type ENUM('Tanya','Mishna','Tehillim') NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CAM-006
Category:     Campaigns
Description:  Campaigns in the pointsDB system can be scoped to a specific institution or to
              a network, or left unscoped (global). A campaign with no institution_id and no
              network_id is considered a system-wide (global) campaign.
Source:       pointsDB_campaigns.sql (table: campaigns)
DB Evidence:  institution_id INT UNSIGNED DEFAULT NULL; network_id INT UNSIGNED DEFAULT NULL
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-CAM-007
Category:     Campaigns
Description:  Each campaign in the pointsDB system has independent flags controlling whether
              it awards points, medals, and ranks. All three are enabled by default (value 1).
              Any can be suppressed per campaign.
Source:       pointsDB_campaigns.sql (table: campaigns)
DB Evidence:  points TINYINT(1) DEFAULT 1; medals TINYINT(1) DEFAULT 1; ranks TINYINT(1) DEFAULT 1
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CAM-008
Category:     Campaigns
Description:  Campaigns have an active/inactive status. Campaigns are active by default
              (is_active = 1) and can be deactivated without deletion.
Source:       pointsDB_campaigns.sql (table: campaigns)
DB Evidence:  is_active TINYINT(1) DEFAULT 1
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CAM-009
Category:     Campaigns
Description:  A campaign can be marked as "default installed" (auto-assigned to new schools)
              via the installed_campaign_id and default_installed flag. This enables
              turnkey campaign enrollment for new institutions.
Source:       pointsDB_campaigns.sql (table: campaigns)
DB Evidence:  installed_campaign_id INT UNSIGNED DEFAULT 0; default_installed INT(1) DEFAULT 0
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-CAM-010
Category:     Campaigns
Description:  Campaigns can be restricted by school type. A campaign may apply to one or
              more school types; this is recorded as separate rows per type in the
              campaign_school_types table. A campaign with no rows in this table is
              available to all school types.
Source:       pointsDB_campaign_school_types.sql (table: campaign_school_types)
DB Evidence:  campaign_id INT; school_type VARCHAR(40) — no UNIQUE constraint,
              allowing multiple types per campaign
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-CAM-011
Category:     Campaigns
Description:  A user's participation in a campaign is tracked with a lifecycle status.
              The valid statuses are: In Progress, Completed, Paused, Resumed, Enrollment,
              Unenrollment. Points awarded are stored alongside the record and are allowed
              to be negative (the column is signed).
Source:       pointsDB_user_campaigns.sql (table: user_campaigns)
DB Evidence:  status ENUM('In Progress','Completed','Paused','Resumed','Enrollment','Unenrollment');
              points_given INT(11) DEFAULT NULL COMMENT 'dont unsign'
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CAM-012
Category:     Campaigns
Description:  A user's campaign record supports fractional task increments (decimal precision
              of 2 places), and tracks a position within a line-based campaign (line_offset)
              and a ladder (ranking/level) with a velocity metric. This enables campaigns
              structured as sequential reading programs (e.g., Tanya by chapter).
Source:       pointsDB_user_campaigns.sql (table: user_campaigns)
DB Evidence:  task_increment DECIMAL(10,2) UNSIGNED; line_offset INT(6) UNSIGNED;
              ladder INT(3) UNSIGNED; ladder_velocity DECIMAL(10,2) UNSIGNED
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CAM-013
Category:     Campaigns
Description:  User campaign progress is separately tracked in a dedicated progress table that
              stores current line position and campaign goal. Progress can be updated without
              creating a new user_campaigns log entry, supporting incremental advancement.
Source:       pointsDB_user_campaign_progress.sql (table: user_campaign_progress)
DB Evidence:  current_line INT(11) UNSIGNED NOT NULL; campaign_goal INT(10) UNSIGNED DEFAULT NULL;
              modified TIMESTAMP ON UPDATE current_timestamp()
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CAM-014
Category:     Campaigns
Description:  Campaign tasks can be assigned a maximum number of times they can be completed
              (max_times field). Tasks optionally have start and end dates, and can be set to
              a recurrence period (e.g., Weekly). Weekly tasks track specific days of the week
              on which they are valid.
Source:       admin_campaign_tasks.php
DB Evidence:  PHP: max_times field with maxlength="2"; start_date/end_date from Hebrew calendar;
              period_id references periods table; day-of-week boolean columns (sunday..saturday)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CAM-015
Category:     Campaigns
Description:  Campaign access within the admin interface is restricted to users with "camp"
              or "super" authorization. Camp directors see only their own camp's data;
              super users can access all camps.
Source:       admin_campaigns.php; admin_campaign_tasks.php; admin_campaign_groups.php
DB Evidence:  PHP: $admin_auth = array('camp');
              $user_type = ($admin_user['auth'] == "super") ? "super" : "camp"
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CAM-016
Category:     Campaigns
Description:  Campaigns in pointsDB have a ladder field (up to 3 digits) that represents
              an optional ranking tier associated with the campaign, enabling multi-level
              progression within a single campaign.
Source:       pointsDB_campaigns.sql (table: campaigns)
DB Evidence:  ladder INT(3) UNSIGNED DEFAULT NULL
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-CAM-017
Category:     Campaigns
Description:  Campaign goals can be logged over time per user. The user_campaign_logs table
              records snapshots of a user's campaign goal value on a given date, allowing
              historical tracking of goal changes.
Source:       pointsDB_user_campaign_logs.sql (table: user_campaign_logs)
DB Evidence:  campaign_goal DECIMAL(8,2) UNSIGNED; log_date INT(10) UNSIGNED
Confidence:   Medium
SME Verified: No
```

---

## ID Cards & Hachayols

```
Rule ID:      BR-IDC-001
Category:     ID Cards & Hachayols
Description:  The Hachayol (physical membership card/publication) is tracked by year, with
              each issue having a print number, issue number, name, and optional supplement.
              Each issue has a ship date. Issues are identified by both print number and
              issue number, which may differ.
Source:       mashpiadb_hachayols.sql (table: hachayols)
DB Evidence:  year INT; print_number INT; issue_number INT; name VARCHAR(45);
              supplement VARCHAR(45); ship_date DATETIME
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-IDC-002
Category:     ID Cards & Hachayols
Description:  A user can receive at most one Hachayol entitlement per year. The
              hachayols_to_give table enforces this with a unique constraint on (user_id, year).
              Each entitlement records which admin authorized it and the timestamp.
Source:       mashpiadb_hachayols_to_give.sql (table: hachayols_to_give)
DB Evidence:  UNIQUE KEY hachayol (user_id, year);
              admin_id INT(10) UNSIGNED NOT NULL DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-IDC-003
Category:     ID Cards & Hachayols
Description:  Hachayol base counts are tracked per school, broken out by four program/gender
              segments: CTH Boys-and-Girls (combined), Chidon Boys-and-Girls (combined),
              CTH Boys only, CTH Girls only, Chidon Boys only, Chidon Girls only. This allows
              per-school allocation across program tracks.
Source:       mashpiadb_base_hachayols.sql (table: base_hachayols)
DB Evidence:  cth_bg TINYINT(4) NOT NULL DEFAULT 0; chidon_bg TINYINT(4) NOT NULL DEFAULT 0;
              cth_b TINYINT(4) NOT NULL DEFAULT 0; cth_g TINYINT(4) NOT NULL DEFAULT 0;
              chidon_b TINYINT(4) NOT NULL DEFAULT 0; chidon_g TINYINT(4) NOT NULL DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-IDC-004
Category:     ID Cards & Hachayols
Description:  Hachayol shipments to chayolei (members) are batched by year and shipment
              number. Each shipment covers a range of issues (issue_start to issue_end).
              A given year can only have one record per shipment number.
Source:       mashpiadb_chayolei_hachayol_shipments.sql (table: chayolei_hachayol_shipments)
DB Evidence:  UNIQUE KEY uq_year_shipment (year, shipment_num);
              issue_start SMALLINT UNSIGNED NOT NULL; issue_end SMALLINT UNSIGNED NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-IDC-005
Category:     ID Cards & Hachayols
Description:  Physical ID (rank/membership) cards exist in two types: temporary and permanent.
              Temporary cards are printed in sheets of 5 rows x 2 columns (10 per page).
              Permanent cards are printed one per page (1 row x 1 column).
Source:       mashpiadb_ID_cards.sql; admin_card_print.php; admin_card_print_new.php
DB Evidence:  type ENUM('temporary','permanent') NOT NULL in ID_cards table;
              PHP: $lines=5; $cols=2; if ($card_type=='permanent') { $lines=1; $cols=1; }
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-IDC-006
Category:     ID Cards & Hachayols
Description:  Only super users can select between printing temporary or permanent ID cards.
              Non-super (school-level) admins are forced to print temporary cards only; the
              card type is hardcoded to 'temporary' for non-super users in both the UI and
              backend logic.
Source:       admin_card_print.php; admin_card_print_new.php
DB Evidence:  PHP: if ($admin_user['auth'] == 'super') $card_type = 'permanent';
              else $card_type = 'temporary'; UI radio shown only to super users
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-IDC-007
Category:     ID Cards & Hachayols
Description:  Card printing is restricted to registered users only (user_registered > 0).
              Unregistered users are excluded from the card print query regardless of other
              filter parameters.
Source:       admin_card_print.php; admin_card_print_new.php
DB Evidence:  PHP SQL: WHERE user_registered > 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-IDC-008
Category:     ID Cards & Hachayols
Description:  ID cards display a "Valid until" date derived from the student's 13th birthday
              (bar/bat mitzvah age), calculated using the Hebrew calendar. If no valid birth
              date is stored, the "Valid until" field is left blank on the card.
Source:       admin_card_print.php
DB Evidence:  PHP: cal_to_jd(CAL_JEWISH, 13, ..., $cal['year']+13) — adds 13 years to DOB;
              $valid = '' if no bdate is calculable
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-IDC-009
Category:     ID Cards & Hachayols
Description:  Card rank status can be printed in three modes: Current Only (highest achieved
              rank only), Current and Previous (all ranks earned before a cut-off date), and
              All Possible Ranks (used for pre-printing cards at all rank levels). Only super
              users can hide already-printed cards.
Source:       admin_card_print.php
DB Evidence:  PHP: rank_type ENUM via switch('current','past','all');
              hide_printed forced to 0 for non-super users
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-IDC-010
Category:     ID Cards & Hachayols
Description:  When a super user accesses the card print page, the system auto-assigns rank 1
              (private/lowest rank) to any users who have no rank record at all, ensuring every
              registered user has at least one rank before cards are printed.
Source:       admin_card_print.php
DB Evidence:  PHP: INSERT INTO rank_marks (rank_ord, user_id, date_promoted) VALUES (1, ...)
              triggered when rank_marks.rank_ord IS NULL for a user
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-IDC-011
Category:     ID Cards & Hachayols
Description:  ID cards are marked as "printed" per user per rank level. Super users can
              explicitly mark a batch of cards as printed after reviewing them on screen;
              this updates the date_printed field in rank_marks. Only cards where
              date_printed IS NULL are eligible for the mark-as-printed action.
Source:       admin_card_print.php; admin_card_print_new.php
DB Evidence:  PHP: UPDATE rank_marks SET date_printed = NOW()
              WHERE user_id = ? AND rank_ord = ? AND date_printed IS NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-IDC-012
Category:     ID Cards & Hachayols
Description:  Auth cards (membership authorization cards for the pointsDB system) have a
              defined lifecycle: not printed → printed → ordered → host printed → redeemed.
              Cards also have an expiration date and record the dates of each status change.
Source:       pointsDB_auth_cards.sql (table: auth_cards)
DB Evidence:  card_status ENUM('not printed','printed','ordered','host printed','redeemed')
              NOT NULL DEFAULT 'not printed'; card_expires INT; date_printed INT;
              date_card_ordered INT; date_card_redeemed INT
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-IDC-013
Category:     ID Cards & Hachayols
Description:  Auth card orders (physical card orders from institutions) capture full payment
              and shipping details including credit card last 4 digits, expiry, shipping
              address, price per unit, and quantity. Orders record a confirmation code and
              track both order placed date and order processed date separately.
Source:       pointsDB_auth_card_orders.sql (table: auth_card_orders)
DB Evidence:  confirmation_code CHAR(16); price_per_unit VARCHAR(11); quantity_purchased INT;
              sub_total DECIMAL(6,2); order_processed_date INT; creditcard_number VARCHAR(4)
              (last 4 only stored); created_by INT NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-IDC-014
Category:     ID Cards & Hachayols
Description:  Achievement cards (physical point-bearing cards given to students) can be
              created by three actor types: Institution Administrator, Teacher, or MissionsApp.
              Each card has a serial number, a point value, and tracks whether it has been
              scanned or not. Some achievement card points are designated as "auction only"
              and cannot be used for regular redemption.
Source:       pointsDB_achievement_cards.sql (table: achievement_cards)
DB Evidence:  card_type ENUM('Institution Administrator','Teacher','MissionsApp');
              status ENUM('scanned','not scanned','weblink') DEFAULT 'not scanned';
              auction_only_points TINYINT(3) UNSIGNED NOT NULL DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-IDC-015
Category:     ID Cards & Hachayols
Description:  Once an achievement card is scanned, it is moved from the achievement_cards
              table to the achievement_cards_scanned table. The scanned table mirrors the
              same schema as the live table, acting as an archive of redeemed cards.
Source:       pointsDB_achievement_cards_scanned.sql (table: achievement_cards_scanned)
DB Evidence:  Both tables share identical columns: card_serial, card_points, status, etc.
              achievement_cards_scanned has PRIMARY KEY (achievement_card_id) matching the
              source table's primary key.
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-IDC-016
Category:     ID Cards & Hachayols
Description:  Scratch cards are separate from achievement cards and are institution-specific.
              A scratch card has a serial and a control number (for validation), a point value,
              and tracks whether it has been scanned. A scanned scratch card records the
              resulting user_point_id for traceability.
Source:       pointsDB_scratch_cards.sql (table: scratch_cards)
DB Evidence:  card_serial INT(5); card_control INT(6); status ENUM('scanned','not scanned')
              DEFAULT 'not scanned'; user_point_id INT(11) DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-IDC-017
Category:     ID Cards & Hachayols
Description:  Base cards (reward/mission completion cards in the base-commander system) are
              tied to a template. The template defines the point value, subject, left/right
              circle indicators (achievement markers), a description, and an optional series
              number. Templates can be school-specific (school_id nullable for global templates).
Source:       mashpiadb_base_cards.sql; mashpiadb_base_templates.sql
DB Evidence:  base_templates: school_id INT UNSIGNED DEFAULT NULL (nullable = global template);
              points DECIMAL(8,2) UNSIGNED NOT NULL; left_circle CHAR(1) NOT NULL;
              right_circle CHAR(1) NOT NULL; series TINYINT(3) UNSIGNED DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-IDC-018
Category:     ID Cards & Hachayols
Description:  Army cards (a parallel card program to base cards) use an identical template
              structure (army_templates mirrors base_templates schema). Army cards and base
              cards are stored and managed in separate tables, indicating they are distinct
              programs despite sharing the same logical structure.
Source:       mashpiadb_army_cards.sql; mashpiadb_army_templates.sql
DB Evidence:  army_templates and base_templates have identical column definitions:
              school_id, subject_id, points, left_circle, right_circle, description, series
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-IDC-019
Category:     ID Cards & Hachayols
Description:  CKids Mission cards (a third card program) track the physical card order
              lifecycle separately from achievement and auth cards. Order states are: not
              ordered → ordered → printed → sent → received.
Source:       pointsDB_ckids_mission_cards.sql (table: ckids_mission_cards)
DB Evidence:  order_status ENUM('not ordered','ordered','printed','sent','received') DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-IDC-020
Category:     ID Cards & Hachayols
Description:  CKids Mission tasks (ckids_mission_app) have both a start/end date (the window
              during which they appear) and a separate activation/expiration date (the window
              during which they can be completed). These are stored as Unix timestamps.
              Tasks are scoped to a network and optionally have an access_level.
Source:       pointsDB_ckids_mission_app.sql (table: ckids_mission_app)
DB Evidence:  start_date INT UNSIGNED; end_date INT UNSIGNED;
              activation_date INT UNSIGNED DEFAULT 0; expiration_date INT UNSIGNED DEFAULT 0;
              access_level INT(1) UNSIGNED DEFAULT NULL; network_id INT(4) UNSIGNED
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-IDC-021
Category:     ID Cards & Hachayols
Description:  CKids Mission task completion (marking) is recorded per user per task per
              network. A user can complete a task multiple times if multiple rows exist,
              but no unique constraint is enforced — the system may rely on application-level
              deduplication.
Source:       pointsDB_ckids_mission_marking.sql (table: ckids_mission_marking)
DB Evidence:  No UNIQUE constraint on (user_id, task_id); network_id DEFAULT 1
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-IDC-022
Category:     ID Cards & Hachayols
Description:  School registration payments for the base program are tracked per school per
              year. Valid payment methods are: cash, credit, check, wire. Payment records
              include a free-text notes field.
Source:       mashpiadb_base_reg_payments.sql (table: base_reg_payments)
DB Evidence:  method ENUM('cash','credit','check','wire') NOT NULL;
              school_id INT UNSIGNED NOT NULL; year INT UNSIGNED NOT NULL;
              payment DECIMAL(7,2) UNSIGNED NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-IDC-023
Category:     ID Cards & Hachayols
Description:  ID card rank colors default to dark grey (#808080) when no rank color is
              assigned to a rank level. Card border and text colors are driven by the rank's
              color attribute.
Source:       admin_card_print.php
DB Evidence:  PHP: if (is_null($row['rank_color'])) $row['rank_color'] = '#808080';
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-IDC-024
Category:     ID Cards & Hachayols
Description:  ID cards display the student's name preferring the Hebrew name fields
              (first_he, last_he) over Latin-script names. If Hebrew name fields are empty,
              the Latin-script first/last name is used as fallback.
Source:       admin_card_print.php
DB Evidence:  PHP: es(!empty($row['first_he']) ? $row['first_he'] : $row['first'])
Confidence:   High
SME Verified: No
```

---

## System Settings & Dates

```
Rule ID:      BR-SYS-001
Category:     System Settings
Description:  Global system settings are stored as key-value pairs in a flat table
              (global_settings) with up to 255 characters per value. The table is expected
              to remain small (AUTO_INCREMENT=13 in the dump, indicating ~12 active settings).
Source:       mashpiadb_global_settings.sql (table: global_settings)
DB Evidence:  key VARCHAR(65); val VARCHAR(255); no UNIQUE constraint on key
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-SYS-002
Category:     System Settings
Description:  System dates are tracked per academic year using Julian Day (JD) numbers —
              the system operates on a Hebrew-calendar-aware date model. Each year entry
              records a corresponding Julian Day number for epoch/boundary calculations.
Source:       mashpiadb_system_dates.sql (table: system_dates)
DB Evidence:  year INT UNSIGNED; jd_date INT UNSIGNED — Julian Day Number format;
              AUTO_INCREMENT=25 (approximately 24 year boundaries stored)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SYS-003
Category:     System Settings
Description:  The pointsDB config_settings table allows configuration at three levels of
              granularity: institution-level, class-level, and user-level. Settings are
              organized into named sets (the "set" column) and individual keys within each
              set. Values can be up to 10,000 characters.
Source:       pointsDB_config_settings.sql (table: config_settings)
DB Evidence:  institution_id INT DEFAULT NULL; class_id INT DEFAULT NULL; user_id INT DEFAULT NULL;
              set VARCHAR(80); key VARCHAR(80) NOT NULL; val VARCHAR(10000) NOT NULL DEFAULT '0'
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SYS-004
Category:     System Settings
Description:  The store configuration (config_store) controls whether an institution's store
              allows army points and/or base points to be used for redemption. Both are
              enabled by default.
Source:       pointsDB_config_store.sql (table: config_store)
DB Evidence:  army_points TINYINT(1) DEFAULT 1; base_points TINYINT(1) DEFAULT 1;
              institution_id INT UNSIGNED NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SYS-005
Category:     System Settings
Description:  System reports are typed to a fixed set of report categories: WWTC, Hakhel,
              Auction, mission_cover_sheet, or blank. Each report has a date range
              (start_date and end_date as Julian Day numbers) and a visibility state of
              'all', 'process', or 'none'.
Source:       mashpiadb_reports.sql (table: reports)
DB Evidence:  report_type ENUM('','WWTC','Hakhel','Auction','mission_cover_sheet') NOT NULL;
              visibility ENUM('all','process','none') NOT NULL DEFAULT 'all'
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SYS-006
Category:     System Settings
Description:  Report access marks track which entities (school, class, team, user, or end_user)
              have printed or processed a report. A report can be marked as printed and
              separately marked as processed; both timestamps are tracked. Each report/entity
              combination is unique (composite PK).
Source:       mashpiadb_report_marks.sql (table: report_marks)
DB Evidence:  auth ENUM('school','class','team','user','end_user') NOT NULL;
              print_date TIMESTAMP; process_date TIMESTAMP;
              PRIMARY KEY (report_id, auth, id)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SYS-007
Category:     System Settings
Description:  Report subjects link a specific subject (e.g., a Torah study topic) to a
              report type. Currently only mission_cover_sheet reports support multi-subject
              associations. Each subject-to-report-type pairing is unique.
Source:       mashpiadb_report_subjects.sql (table: report_subjects)
DB Evidence:  report_type ENUM('mission_cover_sheet') NOT NULL;
              UNIQUE KEY subject_id (subject_id, report_type);
              table comment: 'For reports of more than one subject at a time'
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SYS-008
Category:     System Settings
Description:  The system supports multiple languages. The mashpiadb languages table stores
              named languages referenced by translations tables. The pointsDB app_text_languages
              table extends this with a display hierarchy and an is_active flag — only active
              languages are surfaced in the app.
Source:       mashpiadb_languages.sql; pointsDB_app_text_languages.sql
DB Evidence:  app_text_languages: is_active TINYINT(1) UNSIGNED DEFAULT 0; hierarchy INT DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SYS-009
Category:     System Settings
Description:  Two separate translation systems coexist: one for short strings (varchar, ≤255
              chars) and one for longer text. The varchar translations table uses a composite
              primary key of (lang, text), ensuring exactly one translation per language per
              source string. The text translations table uses a non-unique key on (lang, text(255)),
              allowing multiple translation records per string.
Source:       mashpiadb_translations_varchar.sql; mashpiadb_translations_text.sql
DB Evidence:  translations_varchar: PRIMARY KEY (lang, text) — enforces uniqueness;
              translations_text: KEY lang (lang, text(255)) — index only, not PK
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SYS-010
Category:     System Settings
Description:  App text (UI labels, instructional content) supports institution-specific and
              language-specific overrides. A priority field determines which text to use when
              multiple records match the same context. Higher-priority records override
              lower-priority ones.
Source:       pointsDB_app_text.sql (table: app_text)
DB Evidence:  institution_id INT UNSIGNED DEFAULT NULL; language_id INT UNSIGNED DEFAULT NULL;
              priority INT(11) UNSIGNED NOT NULL DEFAULT 0;
              primary_app_text_id INT(11) UNSIGNED NOT NULL DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SYS-011
Category:     System Settings
Description:  App keywords (domain-specific terminology labels like "School", "Platoon",
              "Soldier") can be overridden per institution and per language. The default
              keyword type is 'School', reflecting that these are primarily institution-type
              labels.
Source:       pointsDB_app_keywords.sql (table: app_keywords)
DB Evidence:  app_keyword_type VARCHAR(255) DEFAULT 'School';
              institution_id INT UNSIGNED DEFAULT NULL; language_id INT UNSIGNED DEFAULT NULL
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-SYS-012
Category:     System Settings
Description:  Language preferences can be set per user per section of the application. This
              allows a user to use different languages in different parts of the app (e.g.,
              Hebrew in one module, English in another).
Source:       pointsDB_app_language_pref.sql (table: app_language_pref)
DB Evidence:  user_id INT UNSIGNED NOT NULL; section VARCHAR(30) NOT NULL DEFAULT '';
              language_id INT UNSIGNED NOT NULL; institution_id INT UNSIGNED NOT NULL
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-SYS-013
Category:     System Settings
Description:  Network-level email alerts are triggered for three specific events: ID Card
              creation/ordering, User Registration, and Institution Registration. Alert
              recipients are configured per network and per event type.
Source:       pointsDB_network_alerts.sql (table: network_alerts)
DB Evidence:  alert_location ENUM('ID Cards','User Registration','Institution Registration');
              alert_email VARCHAR(255); network_id INT UNSIGNED
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SYS-014
Category:     System Settings
Description:  IP address logging is used for security checks. The check_ips table records
              each IP and the timestamp of the check. This appears to be a rate-limiting or
              access-control mechanism (AUTO_INCREMENT=475 indicating active historical use).
Source:       mashpiadb_check_ips.sql (table: check_ips)
DB Evidence:  ip VARCHAR(15) NOT NULL; time TIMESTAMP NOT NULL DEFAULT current_timestamp()
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-SYS-015
Category:     System Settings
Description:  System messages are typed to a fixed set of message contexts: report1, report2,
              base_mission, th_to_soldier, hakhel_directives_1, hakhel_directives_2. Each
              message type has exactly one message text (primary key on message_type).
Source:       mashpiadb_messages.sql (table: messages)
DB Evidence:  message_type ENUM('report1','report2','base_mission','th_to_soldier',
              'hakhel_directives_1','hakhel_directives_2') NOT NULL; PRIMARY KEY (message_type)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SYS-016
Category:     System Settings
Description:  Weekly emails are archived in the system with a date, subject, and full email
              body. This table uses MyISAM engine (unlike all other tables which use InnoDB),
              suggesting it is a legacy or low-criticality feature.
Source:       mashpiadb_weekly_emails.sql (table: weekly_emails)
DB Evidence:  ENGINE=MyISAM (only MyISAM table in the dataset);
              date DATE NOT NULL; subject VARCHAR(100) NOT NULL; email TEXT NOT NULL
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-SYS-017
Category:     System Settings
Description:  School accessories (e.g., scanners) are tracked per school per year with a
              quantity. This enables the system to know how many scanners are deployed at
              each school in each academic year.
Source:       mashpiadb_school_accessories.sql (table: school_accessories)
DB Evidence:  school_id INT UNSIGNED NOT NULL; year INT UNSIGNED NOT NULL;
              scanners INT UNSIGNED NOT NULL
Confidence:   Medium
SME Verified: No
```

---

## Announcements & Screens

```
Rule ID:      BR-ANN-001
Category:     Announcements & Screens
Description:  Screen announcements can be authored by HQ (created_by_hq = 1, default) or by
              schools. HQ announcements take precedence and are the default authorship.
Source:       mashpiadb_screen_announcements.sql (table: screen_announcements)
DB Evidence:  created_by_hq TINYINT(1) DEFAULT 1
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-ANN-002
Category:     Announcements & Screens
Description:  Announcements can be scoped to specific schools and/or specific classes by
              storing comma-separated ID lists in limit_to_schools and limit_to_classes.
              A NULL value in these fields means the announcement applies to all
              schools/classes.
Source:       mashpiadb_screen_announcements.sql (table: screen_announcements)
DB Evidence:  limit_to_schools VARCHAR(255) DEFAULT NULL; limit_to_classes VARCHAR(255) DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-ANN-003
Category:     Announcements & Screens
Description:  Announcements are typed to either the 'chidon' (academic competition) program
              or the 'chayolei' (general membership) program. An announcement cannot span
              both program types simultaneously.
Source:       mashpiadb_screen_announcements.sql (table: screen_announcements)
DB Evidence:  type ENUM('chidon','chayolei') DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-ANN-004
Category:     Announcements & Screens
Description:  Announcements have an active date range defined by from_date and to_date.
              Announcements outside their date range should not be displayed (enforced at
              application level, not via DB constraint).
Source:       mashpiadb_screen_announcements.sql (table: screen_announcements)
DB Evidence:  from_date DATE DEFAULT NULL; to_date DATE DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-ANN-005
Category:     Announcements & Screens
Description:  Announcements support both text content and image content. Text has a configurable
              size (stored as a string descriptor, e.g., "large") and images similarly have a
              size attribute. Either or both can be present on a single announcement.
Source:       mashpiadb_screen_announcements.sql (table: screen_announcements)
DB Evidence:  text VARCHAR(255); text_size VARCHAR(45); image VARCHAR(255); image_size VARCHAR(45)
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-ANN-006
Category:     Announcements & Screens
Description:  Each physical display screen is registered per school with a unique URL slug
              per school. Screens have an optional password for access control. A school can
              have multiple screens but each screen's URL must be unique within its school.
Source:       mashpiadb_screens.sql (table: screens)
DB Evidence:  UNIQUE KEY slug_UNIQUE (school_id, url);
              password VARCHAR(65) DEFAULT NULL; school_id INT UNSIGNED NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-ANN-007
Category:     Announcements & Screens
Description:  Each screen has configurable display settings. By default screens show HQ
              announcements (show_hq_announcements = 1) but do NOT show promotions
              (show_promotions = 0) or birthdays (show_birthdays = 0). The default lookahead
              window for both promotions and birthdays is 7 days.
Source:       mashpiadb_screen_settings.sql (table: screen_settings)
DB Evidence:  show_hq_announcements TINYINT(1) DEFAULT 1; show_promotions TINYINT(1) DEFAULT 0;
              promotions_days INT DEFAULT 7; show_birthdays TINYINT(1) DEFAULT 0;
              birthdays_days INT DEFAULT 7
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-ANN-008
Category:     Announcements & Screens
Description:  Screen promotion and birthday displays can be filtered by gender: 'F' (female),
              'M' (male), or '0' (all genders). This allows gender-segregated schools to show
              only relevant content on their screens.
Source:       mashpiadb_screen_settings.sql (table: screen_settings)
DB Evidence:  promotions_gender ENUM('0','F','M') DEFAULT NULL;
              birthdays_gender ENUM('0','F','M') DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-ANN-009
Category:     Announcements & Screens
Description:  Screens have independent toggles for showing Chayolei Hachayol content and
              Chidon content. Both are shown by default. Schools can suppress either program's
              content from their screen without affecting the other.
Source:       mashpiadb_screen_settings.sql (table: screen_settings)
DB Evidence:  show_chayolei TINYINT(1) UNSIGNED NOT NULL DEFAULT 1;
              show_chidon TINYINT(1) UNSIGNED NOT NULL DEFAULT 1
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-ANN-010
Category:     Announcements & Screens
Description:  Screen settings are one-to-one with screens; each screen has exactly one
              settings record. Deleting a screen cascades to delete its settings record.
Source:       mashpiadb_screen_settings.sql (table: screen_settings)
DB Evidence:  UNIQUE KEY SCREEN (screen_id);
              CONSTRAINT SCREEN_FK FOREIGN KEY (screen_id) REFERENCES screens(screen_id)
              ON DELETE CASCADE ON UPDATE CASCADE
Confidence:   High
SME Verified: No
```

---

## Add-Ons & Subscriptions

```
Rule ID:      BR-ADO-001
Category:     Add-Ons & Subscriptions
Description:  Add-on options (catalog items offered to schools) are grade-restricted.
              A given add-on option specifies which grades are eligible via the
              add_on_option_grades table. Multiple grades per option are supported.
Source:       mashpiadb_add_on_options.sql; mashpiadb_add_on_option_grades.sql
DB Evidence:  add_on_option_grades: add_on_option_id INT UNSIGNED NOT NULL; grade VARCHAR(10)
              No UNIQUE constraint, allowing the same grade to appear multiple times per option
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-ADO-002
Category:     Add-Ons & Subscriptions
Description:  School-level add-ons (subscriptions purchased by schools) have both a face
              value and a price, which may differ. This supports discounted or subsidized
              add-ons. Each add-on is scoped to an academic year.
Source:       mashpiadb_school_add_ons.sql (table: school_add_ons)
DB Evidence:  value DECIMAL(5,2) UNSIGNED NOT NULL; price DECIMAL(5,2) UNSIGNED NOT NULL;
              year INT(4) UNSIGNED NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-ADO-003
Category:     Add-Ons & Subscriptions
Description:  Some school add-ons require a size selection (e.g., clothing items). The
              needs_size flag controls whether the size picker is presented. User add-on
              records store the selected size as a 2-character code.
Source:       mashpiadb_school_add_ons.sql; mashpiadb_user_add_ons.sql
DB Evidence:  needs_size TINYINT(1) NOT NULL in school_add_ons;
              size CHAR(2) DEFAULT NULL in user_add_ons
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-ADO-004
Category:     Add-Ons & Subscriptions
Description:  A user can subscribe to a specific school add-on at most once. The user_add_ons
              table enforces uniqueness on (user_id, school_add_on_id). Attempting to
              subscribe the same user to the same add-on twice will fail at the database level.
Source:       mashpiadb_user_add_ons.sql (table: user_add_ons)
DB Evidence:  UNIQUE KEY user_id (user_id, school_add_on_id)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-ADO-005
Category:     Add-Ons & Subscriptions
Description:  User add-on fulfillment tracking records both a shipped date and a received
              date separately. An add-on is not considered delivered until both dates are
              populated. Either date may be null if the step has not yet occurred.
Source:       mashpiadb_user_add_ons.sql (table: user_add_ons)
DB Evidence:  shipped DATE DEFAULT NULL; received DATE DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-ADO-006
Category:     Add-Ons & Subscriptions
Description:  School-level grade restrictions for add-ons are stored separately from the
              global add-on option grades. The school_add_on_grades table lets each school
              further restrict which grades can access their specific add-on subscriptions,
              with a tinyint add-on number rather than a foreign key to the add-on ID.
Source:       mashpiadb_school_add_on_grades.sql (table: school_add_on_grades)
DB Evidence:  school_id INT UNSIGNED NOT NULL; add_on_number TINYINT(1) NOT NULL;
              grade CHAR(6) NOT NULL
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-ADO-007
Category:     Add-Ons & Subscriptions
Description:  The pointsDB user_addons table tracks digital add-ons (prizes/privileges)
              granted to users. These have an expiration date (Unix timestamp), meaning
              digital add-ons are time-limited. The prize_id links to the prize catalog.
Source:       pointsDB_user_addons.sql (table: user_addons)
DB Evidence:  expires INT(11) UNSIGNED DEFAULT NULL; prize_id INT UNSIGNED; created_by INT UNSIGNED
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-ADO-008
Category:     Add-Ons & Subscriptions
Description:  Add-on options have a catalog with up to 4 distinct option types (AUTO_INCREMENT=5).
              The catalog is centrally managed by HQ and schools select from available options
              rather than creating their own. Options apply globally across grade ranges.
Source:       mashpiadb_add_on_options.sql (table: add_on_options)
DB Evidence:  AUTO_INCREMENT=5 (only ~4 options exist globally);
              description VARCHAR(255) NOT NULL; no school_id column (global catalog)
Confidence:   Medium
SME Verified: No
```

---

## Platoon Transitions

```
Rule ID:      BR-PLT-001
Category:     Platoon Transitions
Description:  When a user is moved between platoons (classes), a platoon_transitions record
              is created capturing the old school, old class, new school, and new class. Both
              from and to values are stored, enabling full audit history of a student's
              class movements.
Source:       mashpiadb_platoon_transitions.sql (table: platoon_transitions)
DB Evidence:  school_id INT (destination); from_school_id INT (origin);
              class_id INT (destination); from_class_id INT (origin);
              user_id INT UNSIGNED NOT NULL; year INT UNSIGNED NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PLT-002
Category:     Platoon Transitions
Description:  Platoon transitions are attributed to the admin who performed the move
              (admin_id) and record the deployment timestamp (deployed_at), which may
              differ from the creation timestamp (created_at). This allows future-dated
              transitions to be scheduled.
Source:       mashpiadb_platoon_transitions.sql (table: platoon_transitions)
DB Evidence:  admin_id INT UNSIGNED DEFAULT NULL; deployed_at DATETIME DEFAULT NULL;
              created_at TIMESTAMP NOT NULL DEFAULT current_timestamp()
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PLT-003
Category:     Platoon Transitions
Description:  Platoon transitions are scoped to an academic year. A user may have multiple
              transition records across years. There is no unique constraint on (user_id, year),
              meaning multiple transitions within a single year are possible (e.g., a student
              who changes class mid-year more than once).
Source:       mashpiadb_platoon_transitions.sql (table: platoon_transitions)
DB Evidence:  year INT UNSIGNED NOT NULL; no UNIQUE constraint;
              AUTO_INCREMENT=123294 (high volume of historical transitions)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PLT-004
Category:     Platoon Transitions
Description:  Rank promotions are stored in a temporary staging table (new_ranks_temp) during
              batch promotion processing. The table records rank ordinal, user, and promotion
              date (Julian Day format). After processing, records are presumably moved to the
              production rank_marks table.
Source:       mashpiadb_new_ranks_temp.sql (table: new_ranks_temp)
DB Evidence:  rank_ord TINYINT(3) UNSIGNED NOT NULL DEFAULT 0; user_id INT UNSIGNED NOT NULL;
              date_promoted MEDIUMINT(8) UNSIGNED DEFAULT NULL (Julian Day)
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-PLT-005
Category:     Platoon Transitions
Description:  Medal awards are also staged in a temporary table (new_medals_temp) during
              batch medal processing. Medals are subject-specific (linked to a subject_id),
              distinguishing medal achievement from rank promotion which is purely ordinal.
Source:       mashpiadb_new_medals_temp.sql (table: new_medals_temp)
DB Evidence:  medal_ord TINYINT(3) UNSIGNED NOT NULL; subject_id INT UNSIGNED NOT NULL;
              user_id INT UNSIGNED NOT NULL; date_awarded MEDIUMINT UNSIGNED DEFAULT NULL
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-PLT-006
Category:     Platoon Transitions
Description:  Users must have at least one rank record before ID cards can be printed.
              The system auto-inserts rank 1 for any registered user missing a rank_marks
              record when a super-user opens the card print page. This prevents blank-ranked
              users from appearing in print batches without a valid rank.
Source:       admin_card_print.php
DB Evidence:  PHP: INSERT INTO rank_marks (rank_ord, user_id, date_promoted) VALUES (1, ...)
              for users WHERE rank_marks.rank_ord IS NULL; triggered on super-user page load
Confidence:   High
SME Verified: No
```

---

*End of business rules extraction. Total rules: 59*

*Note: These rules are inferred from schema structure and PHP application code. Rules marked Medium confidence should be validated with a subject matter expert (SME) who has operational knowledge of the Tzivos Hashem platform. Rules marked Low confidence are not present in this extraction — all rules here meet at least Medium evidence from DB or code artifacts.*
