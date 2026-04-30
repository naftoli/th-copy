# Chidon Business Rules

**Domain:** Chidon (Torah knowledge competition event)  
**Extracted from:** SQL dump files in `/SQLdump/` and PHP files in `mashpia.com/public/`  
**Extraction date:** 2026-04-30  
**Status:** Draft — SME verification required for all rules

---

## Registration

```
Rule ID:      BR-CHI-001
Category:     Registration
Description:  Participants must be in grades 4 through 8 only. No other grades
              are eligible to register for the Chidon.
Source:       mashpiadb_chidon_reg.sql, mashpiadb_th_chidon.sql,
              chidon_reg.php (grade loop 4..8)
DB Evidence:  chidon_reg.grade ENUM('4','5','6','7','8') NOT NULL;
              th_chidon.grade ENUM('4','5','6','7','8')
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-002
Category:     Registration
Description:  Each participant is associated with exactly one Chidon book number
              (1–4 for chidon_reg; 1–5 for th_chidon). Grade 4 maps to book 1,
              grade 5 to book 2, grade 6 to book 3, grades 7 and 8 to book 4
              (legacy system). The newer system adds book 5.
Source:       mashpiadb_chidon_reg.sql, mashpiadb_th_chidon.sql,
              chidon_reg_post.php (switch statement), chidon_reg.php
DB Evidence:  chidon_reg.book ENUM('1','2','3','4') NOT NULL;
              th_chidon.book ENUM('1','2','3','4','5')
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-003
Category:     Registration
Description:  A participant type must be one of: winner, parent, runnerUp,
              runnerUpP, or contestant. This controls the role of each
              registered person in the legacy (chidon_reg) system.
Source:       mashpiadb_chidon_reg.sql
DB Evidence:  chidon_reg.type ENUM('winner','parent','runnerUp','runnerUpP','contestant') NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-004
Category:     Registration
Description:  Each school registration requires a chaperone name and phone
              number. Schools may register up to four chaperones (chaperone_name
              through chaperone_name4, chaperone_phone through chaperone_phone4).
Source:       mashpiadb_chidon_schools.sql
DB Evidence:  chidon_schools.chaperone_name, chaperone_phone,
              chaperone_name2..4, chaperone_phone2..4
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-005
Category:     Registration
Description:  A school's Chidon registration is keyed by school_id and year,
              ensuring a school can only have one registration record per year.
Source:       mashpiadb_chidon_open_reg.sql, mashpiadb_chidon_confirmations.sql
DB Evidence:  chidon_open_reg UNIQUE KEY `school` (school_id, year);
              chidon_confirmations UNIQUE KEY `unique` (school_id, year)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-006
Category:     Registration
Description:  A school must explicitly open registration (open_reg flag) before
              participants from that school can enroll for a given year. This is
              tracked per school per year.
Source:       mashpiadb_chidon_open_reg.sql
DB Evidence:  chidon_open_reg.open_reg TINYINT(1) NOT NULL DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-007
Category:     Registration
Description:  Schools must confirm their roster by submitting a confirmation
              record. Each school can only have one confirmation per year
              (unique constraint on school_id + year).
Source:       mashpiadb_chidon_confirmations.sql, chidon_school_reg.php
DB Evidence:  chidon_confirmations UNIQUE KEY `unique` (school_id, year)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-008
Category:     Registration
Description:  Student enrollment for a school's Shabbaton does not open until
              the school has completed the school registration process, including
              registering all required chaperone/walking staff.
Source:       chidon_school_reg.php (agreement2 checkbox text)
DB Evidence:  th_chidon_schools.registered TINYINT(1) DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-009
Category:     Registration
Description:  Each Chidon event is typed as either "boys" or "girls". Schools,
              chaperones, and attendance times are all segregated by this
              chidon_type field.
Source:       mashpiadb_chidon_new.sql, mashpiadb_th_chidon_chaps.sql,
              mashpiadb_th_chidon_attendance_times.sql
DB Evidence:  chidon_new.type ENUM('boys','girls','mixed');
              th_chidon_chaps.chidon_type ENUM('boys','girls');
              th_chidon_attendance_times.chidon_type ENUM('boys','girls')
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-010
Category:     Registration
Description:  A participant record in th_chidon can be soft-deleted (deleted = 1)
              by non-super admins, or hard-deleted by super admins only. A
              soft-deleted participant remains visible to super admins but not to
              school admins.
Source:       chidon_tests.php (JS and PHP conditional logic)
DB Evidence:  th_chidon.deleted TINYINT(1) DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-011
Category:     Registration
Description:  A participant can only be deleted if they have not yet paid. If
              the paid field is set, the delete link is disabled for non-super
              admins; super admins see an empty delete slot for paid participants.
Source:       chidon_tests.php (PHP render logic lines 339-351)
DB Evidence:  th_chidon.paid; chidon_reg.paid
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-012
Category:     Registration
Description:  Each Chidon participant registration in th_chidon is unique per
              user per year (composite index on user_id + year).
Source:       mashpiadb_th_chidon.sql
DB Evidence:  KEY `idx_th_chidon_user_id_year` (user_id, year)
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-CHI-013
Category:     Registration
Description:  The legacy (chidon_reg) registration fee was $115 per participant
              (couvert fee), payable by credit card at time of registration.
Source:       chidon_reg.php (hardcoded $115 display), chidon_reg_post.php
              ($fee = 115)
DB Evidence:  chidon_reg.fee TINYINT(3); chidon_reg_post.php $fee = 115
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-014
Category:     Registration
Description:  The Chidon Drive fundraising cost per child was $350 total
              ($275 registration fee + $75 grant per child).
Source:       chidonOld/chidon_drive/classes/ChidonDrive.php
DB Evidence:  $costPerChild = 350; $regPerChild = 275; $grantPerChild = 75
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-015
Category:     Registration
Description:  A test language preference per participant is tracked and defaults
              to English ('en'). The alternative is Yiddish ('yi').
Source:       mashpiadb_th_chidon.sql
DB Evidence:  th_chidon.test_lang ENUM('en','yi') NOT NULL DEFAULT 'en'
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-016
Category:     Registration
Description:  Participants may indicate early registration; this is tracked as a
              boolean flag and may affect prize or benefit eligibility.
Source:       mashpiadb_th_chidon.sql
DB Evidence:  th_chidon.early_registration TINYINT(3) NOT NULL DEFAULT 0
Confidence:   Medium
SME Verified: No
```

---

## Scoring and Tests

```
Rule ID:      BR-CHI-020
Category:     Scoring and Tests
Description:  Each participant sits up to three tests (test1a/test1b,
              test2a/test2b, test3a/test3b). Each test has two parts (a and b).
              Six scores in total are stored per participant.
Source:       mashpiadb_th_chidon.sql, chidon_tests.php
DB Evidence:  th_chidon.test1a, test1b, test2a, test2b, test3a, test3b
              (all DECIMAL(5,2) unsigned)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-021
Category:     Scoring and Tests
Description:  The Part 1 average is computed as (test1a + test2a + test3a) / 3.
              The Part 2 average is computed as (test1b + test2b + test3b) / 3.
              The overall average is (avg1 + avg2) / 2. Division is always by 3
              (once all three tests are complete), regardless of how many are
              actually entered.
Source:       chidon_tests.php (lines 264-269)
DB Evidence:  Computed in PHP; stored results in th_chidon_avgs.avg
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-022
Category:     Scoring and Tests
Description:  The passing threshold for Shabbaton/contestant eligibility is a
              minimum average of 70 on Part 1 AND a minimum average of 70 on
              Part 2. A student must pass both parts to qualify.
Source:       chidon_winners.php (line 23), chidon_winners_estimate.php (line 20)
DB Evidence:  PHP: if ($avg1 >= 70 && $avg2 >= 70) { ... }
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-023
Category:     Scoring and Tests
Description:  Per school, per grade, the top 2 distinct average scores determine
              the contestants selected to advance. If multiple students share the
              same average, all are included in that placement.
Source:       chidon_winners.php (lines 36-46)
DB Evidence:  PHP: if (++$i > 2) break; (top 2 averages per school/grade)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-024
Category:     Scoring and Tests
Description:  Test types in the modern system are: maven, pro, expert, trophy,
              genius. Each test type represents a different track of difficulty
              or competition level.
Source:       mashpiadb_th_chidon.sql, mashpiadb_th_chidon_marks.sql
DB Evidence:  th_chidon.test_type ENUM('maven','pro','expert','trophy','genius') DEFAULT 'expert';
              th_chidon_marks.test_type ENUM('maven','pro','expert','trophy','genius') NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-025
Category:     Scoring and Tests
Description:  Each test mark (th_chidon_marks) records how many questions were
              answered correctly out of the total questions for a given test type
              and test number. A participant can only have one mark record per
              test_type + test_number combination (unique constraint).
Source:       mashpiadb_th_chidon_marks.sql
DB Evidence:  UNIQUE KEY `mark` (th_chidon_id, test_type, test_number);
              columns: answered_correctly, total_questions
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-026
Category:     Scoring and Tests
Description:  Each test level record (chidon_test_levels) is unique per student
              per test_type per year, tracking which difficulty level the student
              was assigned for each test type.
Source:       mashpiadb_chidon_test_levels.sql
DB Evidence:  UNIQUE KEY `test` (school_id, class_id, user_id, test_type, year)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-027
Category:     Scoring and Tests
Description:  A passing average per track per student per year is stored in
              chidon_passing_avgs. A final passing average (post-finals) is
              stored separately in chidon_final_passing_avgs. Both are unique
              per student per track per year.
Source:       mashpiadb_chidon_passing_avgs.sql,
              mashpiadb_chidon_final_passing_avgs.sql
DB Evidence:  UNIQUE KEY `avg` (school_id, class_id, user_id, track, year)
              on both tables
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-028
Category:     Scoring and Tests
Description:  The finals round records scores across up to four tracks plus a
              KHK track per student per year.
Source:       mashpiadb_th_chidon_finals.sql
DB Evidence:  th_chidon_finals: track_1, track_2, track_3, track_4, khk
              (all INT unsigned); UNIQUE KEY `final` (year, user_id)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-029
Category:     Scoring and Tests
Description:  The KHK (Kol HaTorah Kulo) track has its own separate mark records
              in th_khk_marks, keyed per participant and test number. A
              participant may have multiple KHK test marks (unique per
              th_chidon_id + test_number).
Source:       mashpiadb_th_khk_marks.sql
DB Evidence:  UNIQUE KEY `mark` (th_chidon_id, test_number);
              th_chidon.khk TINYINT(3) DEFAULT 0 (enrollment flag)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-030
Category:     Scoring and Tests
Description:  The Mitzvah Maven (MM) test track uses a separate set of three
              test fields: mm_test1, mm_test2, mm_test3. These are entered and
              managed via a separate admin page (chidon_mm_tests.php).
Source:       chidon_mm_tests.php, mashpiadb_th_chidon.sql
DB Evidence:  th_chidon.mm_test1, mm_test2, mm_test3 (INT unsigned, DEFAULT 0)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-031
Category:     Scoring and Tests
Description:  Once a test's deadline has passed, only HQ (super admin) can enter
              or modify marks for that test period. Non-super admins are locked
              out after each deadline window.
Source:       chidon_shutdown_vars.php, chidon_tests.php
DB Evidence:  PHP: $shutdown1/$shutdown2/$shutdown3 flags; input[disabled] for
              non-super after deadline
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-032
Category:     Scoring and Tests
Description:  The award type determines what award a participant is eligible for
              and can be one of: maven, pro, expert, trophy, genius, or "highest
              final passed". The reward_type (what they actually earned) can
              additionally include "khk trip".
Source:       mashpiadb_th_chidon.sql
DB Evidence:  th_chidon.award_type ENUM('maven','pro','expert','trophy','genius','highest final passed');
              th_chidon.reward_type ENUM('maven','pro','expert','trophy','genius','highest track passed','khk trip')
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-033
Category:     Scoring and Tests
Description:  Each participant's highest passing track is recorded centrally in
              th_chidon_info.highest_track, one record per user per year.
Source:       mashpiadb_th_chidon_info.sql
DB Evidence:  UNIQUE KEY `user` (year, user_id);
              th_chidon_info.highest_track VARCHAR(45)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-034
Category:     Scoring and Tests
Description:  The KHK track has a multi-year tracking table (khk_info_5785) that
              records how many tracks the student passed in each of the last four
              years (5782, 5783, 5784, 5785), along with a total amount_passed
              count.
Source:       mashpiadb_khk_info_5785.sql
DB Evidence:  khk_info_5785.amount_passed TINYINT(3) unsigned;
              columns 5782..5785 VARCHAR(60)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-035
Category:     Scoring and Tests
Description:  The school-grade average is computed and stored per year in
              th_chidon_avgs. Each school/grade combination has a unique average
              record per year.
Source:       mashpiadb_th_chidon_avgs.sql
DB Evidence:  UNIQUE KEY `avg` (year, school_id, grade);
              th_chidon_avgs.avg DECIMAL(5,2) unsigned
Confidence:   High
SME Verified: No
```

---

## Winners and Representatives

```
Rule ID:      BR-CHI-040
Category:     Winners and Representatives
Description:  Winners are tracked in th_chidon_winners with fields for school,
              grade, gender, team, trophy type, KHK trophy, and a blue_trophy
              flag. A student may win both a regular trophy and a KHK trophy.
Source:       mashpiadb_th_chidon_winners.sql
DB Evidence:  th_chidon_winners: trophy, khk_trophy (VARCHAR), blue_trophy
              TINYINT(1) DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-041
Category:     Winners and Representatives
Description:  Trophy types for contestants are bronze, gold, or silver.
Source:       mashpiadb_th_chidon.sql
DB Evidence:  th_chidon.trophy_type ENUM('bronze','gold','silver')
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-042
Category:     Winners and Representatives
Description:  Each school may designate school representatives and regional/
              international representatives separately. A participant's rep type
              is either 'grade' (school rep by grade) or 'khk' (KHK track rep).
Source:       mashpiadb_th_chidon.sql
DB Evidence:  th_chidon.school_rep TINYINT(3) DEFAULT 0;
              th_chidon.regional_rep TINYINT(3) DEFAULT 0;
              th_chidon.intl_rep TINYINT(3) DEFAULT 0;
              th_chidon.rep_type ENUM('grade','khk')
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-043
Category:     Winners and Representatives
Description:  A participant can be a Shabbaton participant across multiple tracks
              (maven, pro, expert, trophy). Shabbaton eligibility is tracked with
              separate boolean flags per track.
Source:       mashpiadb_th_chidon.sql
DB Evidence:  th_chidon.shabbaton_maven, shabbaton_pro, shabbaton_expert,
              shabbaton_trophy (all TINYINT(3) DEFAULT 0)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-044
Category:     Winners and Representatives
Description:  A participant's seat type at the awards ceremony is one of: medal,
              plaque, round one, or round two. Each seat has an assigned seat
              number.
Source:       mashpiadb_th_chidon.sql
DB Evidence:  th_chidon.seat_type ENUM('medal','plaque','round one','round two');
              th_chidon.seat_number INT(10) unsigned
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-045
Category:     Winners and Representatives
Description:  Participants competing in the KHK experience or regional trip track
              are designated via a dedicated enum field, keeping them separate
              from standard Chidon competitors.
Source:       mashpiadb_th_chidon.sql
DB Evidence:  th_chidon.khk_experience ENUM('khk experience','regional trip')
Confidence:   High
SME Verified: No
```

---

## Prizes

```
Rule ID:      BR-CHI-050
Category:     Prizes
Description:  Prizes have a defined quantity available. A prize can only be
              awarded to a participant if quantity > purchased count (i.e., stock
              has not been exhausted).
Source:       chidonOld/chidon_drive/ajax/chidon_prizes.php (line 15)
DB Evidence:  chidon_prizes.quantity INT(10) DEFAULT 0;
              chidon_prizes.purchased INT(10) DEFAULT 0;
              PHP: if ($row['quantity'] > $row['purchased']) $prizes[...]
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-051
Category:     Prizes
Description:  A prize is associated with a specific Chidon year. Prizes from
              previous years are not shown for the current year.
Source:       mashpiadb_chidon_prizes.sql,
              chidonOld/chidon_drive/ajax/chidon_prizes.php
DB Evidence:  chidon_prizes.year INT(10) unsigned;
              SQL: WHERE year = :year
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-052
Category:     Prizes
Description:  A user can only receive each specific prize once per year. The
              chidon_user_prizes table enforces a unique constraint on
              (user_id, prize_id, year).
Source:       mashpiadb_chidon_user_prizes.sql
DB Evidence:  UNIQUE KEY `prize` (user_id, prize_id, year)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-053
Category:     Prizes
Description:  Each prize assignment tracks whether it has been sent to the store
              (sent_to_store) and whether it has been picked up (picked_up).
Source:       mashpiadb_chidon_user_prizes.sql
DB Evidence:  chidon_user_prizes.sent_to_store TINYINT(3) DEFAULT 0;
              chidon_user_prizes.picked_up TINYINT(3) DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-054
Category:     Prizes
Description:  Certain prizes are excluded for specific schools via the
              chidon_prize_school_exceptions table. If a (prize_id, school_id)
              pair exists in this table, that prize cannot be awarded to
              participants from that school.
Source:       mashpiadb_chidon_prize_school_exceptions.sql
DB Evidence:  chidon_prize_school_exceptions (prize_id, school_id)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-055
Category:     Prizes
Description:  Prizes have both a retail price and an "our price" (cost to the
              organization), enabling cost tracking separate from list price.
Source:       mashpiadb_chidon_prizes.sql
DB Evidence:  chidon_prizes.price DECIMAL(5,2); chidon_prizes.our_price DECIMAL(5,2)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-056
Category:     Prizes
Description:  A credit-based prize system exists (chidon_credit_prizes) where
              each prize has a credits cost and a quantity limit. Participants
              can redeem credits for prizes.
Source:       mashpiadb_chidon_credit_prizes.sql,
              mashpiadb_chidon_credit_user_prizes.sql
DB Evidence:  chidon_credit_prizes.credits TINYINT(3) unsigned;
              chidon_credit_prizes.quantity INT(10) unsigned;
              chidon_credit_user_prizes (user_id, chidon_credit_prize_id)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-057
Category:     Prizes
Description:  The "made possible by" field on prizes records the donor or sponsor
              who funded each prize, supporting public recognition.
Source:       mashpiadb_chidon_prizes.sql
DB Evidence:  chidon_prizes.made_possible_by VARCHAR(200)
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-CHI-058
Category:     Prizes
Description:  Each participant has a KHK plaque flag and a KHK registration flag,
              tracking whether they are receiving a KHK plaque and whether they
              have registered for the KHK track.
Source:       mashpiadb_th_chidon.sql
DB Evidence:  th_chidon.khk_plaque TINYINT(4) DEFAULT 0;
              th_chidon.khk_reg TINYINT(4) DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-059
Category:     Prizes
Description:  Administrators can be granted permission to edit a participant's
              prizes via the edit_prizes flag.
Source:       mashpiadb_th_chidon.sql
DB Evidence:  th_chidon.edit_prizes TINYINT(3) NOT NULL DEFAULT 0
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-CHI-060
Category:     Prizes
Description:  Sweaters are gender-specific items (M or F) with defined sizing and
              pricing. Sweaters also track both retail price and the
              organization's cost.
Source:       mashpiadb_chidon_sweaters.sql
DB Evidence:  chidon_sweaters.gender ENUM('M','F') NOT NULL;
              price DECIMAL(4,2); our_price DECIMAL(4,2)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-061
Category:     Prizes
Description:  Chaperones can optionally purchase a sweater (sweater flag). If
              they do, a size must be provided (s, m, l, xl). The chaperones'
              sweater details are tracked in chidon_schools and th_chidon_chaps.
Source:       mashpiadb_chidon_schools.sql, mashpiadb_th_chidon_chaps.sql
DB Evidence:  chidon_schools.sweater TINYINT(3) DEFAULT 0;
              chidon_schools.s_size ENUM('s','m','l','xl');
              th_chidon_chaps.sweater TINYINT(1) DEFAULT 0;
              th_chidon_chaps.sweater_size ENUM('s','m','l','xl','xxl')
Confidence:   High
SME Verified: No
```

---

## Attendance

```
Rule ID:      BR-CHI-070
Category:     Attendance
Description:  Attendance is tracked at specific timed events over four possible
              days: Thursday, Friday, Motzei Shabbos, and Sunday.
Source:       mashpiadb_th_chidon_attendance_times.sql
DB Evidence:  th_chidon_attendance_times.day_of_week
              ENUM('thursday','friday','motzei shabbos','sunday')
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-071
Category:     Attendance
Description:  Attendance events are typed as walk, chap (chaperone group), or
              bunk. Each event is gender-specific (B = both, M = male, F = female)
              and chidon-type specific (boys or girls chidon).
Source:       mashpiadb_th_chidon_attendance_times.sql
DB Evidence:  att_type ENUM('walk','chap','bunk') NOT NULL;
              gender ENUM('B','M','F') NOT NULL DEFAULT 'B';
              chidon_type ENUM('boys','girls') NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-072
Category:     Attendance
Description:  Each attendance mark records who marked it and when, providing an
              audit trail. A default value of 0 (not marked) is applied and
              changed to 1 when marked present.
Source:       mashpiadb_th_chidon_attendance_marks.sql
DB Evidence:  th_chidon_attendance_marks.marked TINYINT(1) DEFAULT 0;
              marked_by INT(10) unsigned NOT NULL;
              marked_time TIMESTAMP DEFAULT current_timestamp()
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-073
Category:     Attendance
Description:  Each door at the event venue is assigned to specific schools.
              The same door-school pairing cannot be duplicated (unique
              constraint prevents double-assignment).
Source:       mashpiadb_th_chidon_attendance_school_doors.sql
DB Evidence:  UNIQUE KEY `no_duplicates` (door_number, school_id)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-074
Category:     Attendance
Description:  Participants are organized into walking groups and bunks. Walking
              groups and bunks each have an assigned staff member responsible for
              that group.
Source:       mashpiadb_th_chidon_walking_groups.sql,
              mashpiadb_th_chidon_bunks.sql
DB Evidence:  th_chidon_walking_groups.staff_id INT NOT NULL;
              th_chidon_bunks: counselor, c_number fields
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-075
Category:     Attendance
Description:  Schools must provide one walking supervisor for every 40 students
              attending the Shabbaton. The chaperone may count toward this number.
Source:       chidon_school_reg.php (page text)
DB Evidence:  th_chidon_chaps_needed.needed INT(10) unsigned NOT NULL;
              UNIQUE KEY `school` (year, school_id)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-076
Category:     Attendance
Description:  If a walking supervisor is absent or fails to complete
              responsibilities, a $200 penalty per day per supervisor is applied,
              charged to the credit card on file.
Source:       chidon_school_reg.php (terms text)
DB Evidence:  Implied by th_chidon_chap_payments table (records payments per
              school); stated in registration terms text
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-077
Category:     Attendance
Description:  A participant's walking zone is stored at both the individual level
              (th_chidon.walking_zone) and the bunk level (th_chidon_bunks.
              walking_zone). Walking zones are geographically defined by street
              ranges (even and odd house numbers) in chidon_walking_zones.
Source:       mashpiadb_th_chidon.sql, mashpiadb_th_chidon_bunks.sql,
              mashpiadb_chidon_walking_zones.sql
DB Evidence:  th_chidon.walking_zone VARCHAR(5);
              chidon_walking_zones: even_start, even_end, odd_start, odd_end
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-078
Category:     Attendance
Description:  Participants can optionally walk to/from the event (walk_day and
              walk_night flags), and can additionally be flagged as walking on
              Thursday (thurs_walking) or Motzei Shabbos (ms_walking).
Source:       mashpiadb_th_chidon.sql
DB Evidence:  th_chidon.walk_day TINYINT(1) DEFAULT 0;
              th_chidon.walk_night TINYINT(1) DEFAULT 0;
              th_chidon.thurs_walking TINYINT(3);
              th_chidon.ms_walking TINYINT(3)
Confidence:   High
SME Verified: No
```

---

## Payments and Subsidies

```
Rule ID:      BR-CHI-090
Category:     Payments and Subsidies
Description:  Payment for the Chidon is processed via Authorize.net credit card
              transactions. The approval field stores the transaction reference
              (formatted as approval_code:transaction_id:etc.).
Source:       chidon_reg_post.php (lines 184-187)
DB Evidence:  chidon_reg.approval VARCHAR(255); chidon_schools.approval VARCHAR(255);
              th_chidon.approval VARCHAR(255)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-091
Category:     Payments and Subsidies
Description:  An installment payment plan exists where the total amount can be
              split into multiple installments. Each installment record tracks the
              installment_amount, number_of_installments, total_amount, and
              start_date.
Source:       mashpiadb_th_chidon_installments.sql
DB Evidence:  th_chidon_installments: installment_amount DECIMAL(6,2),
              number_of_installments TINYINT(3), total_amount DECIMAL(6,2),
              start_date DATETIME
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-092
Category:     Payments and Subsidies
Description:  The Chidon Drive fundraising system allows families and schools to
              raise money toward the cost of participation. A subsidy is allocated
              per child from donations raised in the drive.
Source:       mashpiadb_chidon_user_subsidies.sql,
              chidonOld/chidon_drive/classes/ChidonDrive.php
DB Evidence:  chidon_user_subsidies: subsidy_amount DECIMAL(6,2) unsigned NOT NULL;
              chidon_donations: donation_amount DECIMAL(9,2) unsigned NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-093
Category:     Payments and Subsidies
Description:  A subsidy is always linked to a specific donation record (chidon_
              donation_id) and a specific student (user_id). Subsidies belong to
              a specific Chidon year.
Source:       mashpiadb_chidon_user_subsidies.sql
DB Evidence:  chidon_user_subsidies.chidon_donation_id NOT NULL;
              chidon_user_subsidies.user_id NOT NULL;
              chidon_user_subsidies.chidon_year
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-094
Category:     Payments and Subsidies
Description:  Donations can be anonymous. When anonymous = 1, the donor's name
              is not publicly displayed; the display_name field controls what is
              shown.
Source:       mashpiadb_chidon_donations.sql
DB Evidence:  chidon_donations.anonymous TINYINT(1) DEFAULT 0;
              chidon_donations.display_name VARCHAR(75)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-095
Category:     Payments and Subsidies
Description:  Refunds are tracked per school admin per year. A refund record
              stores the donation amount returned, the refund amount, number of
              $50 donations refunded, and number of children involved.
Source:       mashpiadb_chidon_refunds.sql
DB Evidence:  chidon_refunds: donation DECIMAL(6,2), refund DECIMAL(6,2),
              num_donation_50 TINYINT(3), num_children TINYINT(3),
              donation_50 VARCHAR(255)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-096
Category:     Payments and Subsidies
Description:  The Zelda payment record (th_chidon_zelda) is the canonical payment
              ledger for a participant's Chidon fee. It records: the registration
              fee, Chidon Drive contributions, subsidy, coupon/discount, amount
              paid, and remaining balance.
Source:       mashpiadb_th_chidon_zelda.sql
DB Evidence:  th_chidon_zelda: reg_fee INT unsigned NOT NULL, chidon_drive DECIMAL(6,2),
              subsidy DECIMAL(5,2), coupon INT, paid DECIMAL(5,2), balance DECIMAL(6,2)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-097
Category:     Payments and Subsidies
Description:  A coupon code can be applied to a participant's fee, with a reason
              field documenting why the discount was granted.
Source:       mashpiadb_th_chidon_zelda.sql
DB Evidence:  th_chidon_zelda.coupon INT(10) unsigned;
              th_chidon_zelda.coupon_reason VARCHAR(255)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-098
Category:     Payments and Subsidies
Description:  An "extra" allowance is tracked per admin in th_chidon_zelda_extra
              to handle special additional costs beyond the standard fee.
Source:       mashpiadb_th_chidon_zelda_extra.sql
DB Evidence:  th_chidon_zelda_extra: admin_id PK, extra INT(10) unsigned DEFAULT 0
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-CHI-099
Category:     Payments and Subsidies
Description:  Chaperone/staff payments for a school are recorded in
              th_chidon_chap_payments, with approval code and description. These
              are separate from participant payments.
Source:       mashpiadb_th_chidon_chap_payments.sql
DB Evidence:  th_chidon_chap_payments: paid DECIMAL(6,2), approval VARCHAR(255),
              description VARCHAR(255), school_id INT
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-100
Category:     Payments and Subsidies
Description:  A $500 hold is placed on the school's credit card on file at the
              time of school enrollment. This hold is cancelled after Chidon if
              no incidental charges (such as walking supervisor penalties) apply.
Source:       chidon_school_reg.php (agreement3 checkbox text)
DB Evidence:  th_chidon_schools.payment_profile_id INT (Authorize.net profile);
              th_chidon_parent_purchases.authorize_trans_type ENUM('charge','hold')
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-101
Category:     Payments and Subsidies
Description:  Participants have a prepaid_credit balance that can be applied
              toward fees. This credit is tracked per participant enrollment record.
Source:       mashpiadb_th_chidon.sql
DB Evidence:  th_chidon.prepaid_credit DECIMAL(6,2) unsigned NOT NULL DEFAULT 0.00;
              th_chidon.prepaid_credit_old DECIMAL(6,2) unsigned DEFAULT 0.00
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-102
Category:     Payments and Subsidies
Description:  Sponsors can cover multiple trips, with each sponsorship record
              tracking number of trips and total amount contributed. A sponsor
              links to an admin account.
Source:       mashpiadb_th_chidon_sponsors.sql
DB Evidence:  th_chidon_sponsors: num_trips TINYINT(1) NOT NULL, amount DECIMAL(6,2) NOT NULL,
              sponsor INT (references admin)
Confidence:   High
SME Verified: No
```

---

## Shipping

```
Rule ID:      BR-CHI-110
Category:     Shipping
Description:  The legacy Chidon merchandise purchase system (chidon table) offers
              three delivery methods: ship (mail to address), JCM pickup, and
              event pickup.
Source:       mashpiadb_chidon.sql, chidon_purchases.php
DB Evidence:  chidon.method ENUM('ship','jcm pickup','event pickup') NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-111
Category:     Shipping
Description:  Ticket types for the legacy system are gender-differentiated and
              event-differentiated: Men's tickets (Boys Chidon) at $10/$18/$36/
              $50/$100; Women's tickets (Boys Chidon) at $10/$18; Women's tickets
              (Girls Chidon) at $10/$18/$36/$50/$100.
Source:       chidon_purchases.php (ticketTypes array)
DB Evidence:  chidon table: m10, m18, m36, m50, m100, g10, g18, gg10, gg18,
              gg36, gg50, gg100 (all INT unsigned NOT NULL)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-112
Category:     Shipping
Description:  Modern prize/item shipping is tracked per user per item per year
              with a shipment_number to handle multiple shipments. A unique
              constraint prevents duplicate shipping records for the same
              user/item/year/item_num/shipment combination.
Source:       mashpiadb_th_chidon_shipping.sql
DB Evidence:  UNIQUE KEY `main` (user_id, item_id, year, item_num, shipment_number);
              th_chidon_shipping.shipment_number TINYINT(4) DEFAULT 1
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-113
Category:     Shipping
Description:  Shipping status for each item is tracked with a numeric status code
              (0 = not shipped, higher values indicate stages of processing). A
              date_shipped timestamp is set when the item ships.
Source:       mashpiadb_th_chidon_shipping.sql
DB Evidence:  th_chidon_shipping.status TINYINT(3) DEFAULT 0;
              th_chidon_shipping.date_shipped TIMESTAMP NULL DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-114
Category:     Shipping
Description:  Parent shipping is tracked separately per parent per year. Each
              parent can have only one shipping record per year (unique constraint).
              Shipping includes cost, address, and whether the destination is
              within the USA.
Source:       mashpiadb_chidon_parent_shipping.sql
DB Evidence:  UNIQUE KEY `shipping` (parent_id, year);
              chidon_parent_shipping: cost DECIMAL(5,2), usa TINYINT DEFAULT 1,
              myshliach_ak TINYINT DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-115
Category:     Shipping
Description:  The MyShliach AK (Alaska) program has a special shipping flag,
              indicating different handling or cost for participants in that
              geographic region.
Source:       mashpiadb_chidon_parent_shipping.sql
DB Evidence:  chidon_parent_shipping.myshliach_ak TINYINT(3) DEFAULT 0
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-CHI-116
Category:     Shipping
Description:  Missing item complaints are tracked per user per year. Each user
              can have only one missing items record per year, listing all missing
              items in a text field.
Source:       mashpiadb_chidon_missing_items.sql
DB Evidence:  UNIQUE KEY `user` (year, user_id);
              chidon_missing_items.items TEXT NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-117
Category:     Shipping
Description:  Merchandise purchases by parents (celeb boxes, parent sweaters for
              mother/father/grandparents) can optionally be shipped to an
              additional address. Each family member's sweater size and shipping
              address is captured separately.
Source:       mashpiadb_th_chidon_parent_purchases.sql
DB Evidence:  th_chidon_parent_purchases: sweater_mother, sweater_father,
              sweater_bubby, sweater_zaidy (sizes); corresponding _ship and
              _ship_addr fields for each
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-118
Category:     Shipping
Description:  A preparation flag ("prepared") on legacy purchases indicates
              whether the merchandise has been physically prepared for pickup or
              shipment before being marked as shipped.
Source:       chidon_purchases.php (prepared checkbox), mashpiadb_chidon.sql
DB Evidence:  chidon.prepared TINYINT(1) unsigned DEFAULT 0;
              chidon.shipped DATETIME (set on shipment)
Confidence:   High
SME Verified: No
```

---

## Chaperones and Staff

```
Rule ID:      BR-CHI-130
Category:     Chaperones and Staff
Description:  Chaperones have a chap_type flag that classifies their role within
              the Chidon (e.g., full-program chaperone vs. other types). A
              full_program flag indicates full Shabbaton participation.
Source:       mashpiadb_th_chidon_chaps.sql
DB Evidence:  th_chidon_chaps.chap_type TINYINT(3) DEFAULT 0;
              th_chidon_chaps.full_program TINYINT(1) DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-131
Category:     Chaperones and Staff
Description:  The number of chaperones required per school per year is stored in
              th_chidon_chaps_needed. Each school has one required count per year.
Source:       mashpiadb_th_chidon_chaps_needed.sql
DB Evidence:  UNIQUE KEY `school` (year, school_id);
              th_chidon_chaps_needed.needed INT unsigned NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-132
Category:     Chaperones and Staff
Description:  Staff members have a unique username and are assigned to a specific
              year. Super admins (super_admin = 1) have elevated permissions to
              access and modify all data.
Source:       mashpiadb_th_chidon_staff.sql
DB Evidence:  UNIQUE KEY `username_UNIQUE` (username);
              th_chidon_staff.super_admin TINYINT(3) DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-133
Category:     Chaperones and Staff
Description:  Staff are assigned to specific groups (walking groups or other
              organizational units) via the staff_assignments table, linking a
              staff member to a staff_type and group_number.
Source:       mashpiadb_th_chidon_staff_assignments.sql
DB Evidence:  th_chidon_staff_assignments: staff_type_id, group_number, staff_id
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-134
Category:     Chaperones and Staff
Description:  Chaperones may optionally own a vehicle to assist with
              transportation, tracked via the vehicle flag on both chap and staff
              records.
Source:       mashpiadb_th_chidon_chaps.sql, mashpiadb_th_chidon_staff.sql
DB Evidence:  th_chidon_chaps.vehicle TINYINT(3) DEFAULT 0;
              th_chidon_staff.vehicle TINYINT(3) DEFAULT 0
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-CHI-135
Category:     Chaperones and Staff
Description:  Bus codes for staff/buses must be unique; each bus has a unique
              code and is assigned to a specific staff member responsible for it.
Source:       mashpiadb_th_chidon_buses.sql
DB Evidence:  UNIQUE KEY `bus_code` (bus_code);
              th_chidon_buses.staff_id INT NOT NULL
Confidence:   High
SME Verified: No
```

---

## Transportation and Logistics

```
Rule ID:      BR-CHI-140
Category:     Transportation and Logistics
Description:  On Sunday after the event, schools must choose one of three options
              for their students: (1) bus to airport (Newark, LGA, or JFK),
              (2) bus back to Crown Heights (President and Kingston drop-off),
              or (3) school provides its own buses.
Source:       chidon_school_reg.php (radio button options)
DB Evidence:  th_chidon_schools.bus TINYINT(1) DEFAULT 0;
              th_chidon_schools.airport VARCHAR(45);
              th_chidon_schools.option VARCHAR(45)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-141
Category:     Transportation and Logistics
Description:  Participants can optionally have a ski or snowboard activity,
              with skill level and outerwear tracked.
Source:       mashpiadb_th_chidon.sql
DB Evidence:  th_chidon.ski ENUM('ski','snowboard');
              th_chidon.skill VARCHAR(45);
              th_chidon.outerwear VARCHAR(45)
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-CHI-142
Category:     Transportation and Logistics
Description:  Participants may be accommodated with host families. Host
              information (name, address, phone, street, street number, apartment)
              is collected and stored on the enrollment record.
Source:       mashpiadb_th_chidon.sql, chidon_reg.php (accommodation fields)
DB Evidence:  th_chidon: host, host_address1, host_address2, host_number,
              host_street, host_street_num, host_street_num_suffix, host_street_apt
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-143
Category:     Transportation and Logistics
Description:  The Chidon event spans multiple bus types: coach bus, school bus,
              open-air bus, dropoff bus, and Sunday PM bus. Each participant and
              staff member can be assigned to different bus types for different
              legs of travel.
Source:       mashpiadb_th_chidon.sql, mashpiadb_th_chidon_staff.sql
DB Evidence:  th_chidon: coach_bus, school_bus, open_air_bus, dropoff_bus,
              dropoff_seat, sunday_pm_bus
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-144
Category:     Transportation and Logistics
Description:  Lanyards are assigned per participant with a color code and a
              QR/barcode string. Lanyards are year-specific.
Source:       mashpiadb_chidon_lanyards.sql
DB Evidence:  chidon_lanyards: user_serial, color, code, year
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-145
Category:     Transportation and Logistics
Description:  Schools may optionally order food and snacks for the trip home from
              the event. This preference is stored per school per year.
Source:       chidon_school_reg.php (food checkbox), mashpiadb_th_chidon_schools.sql
DB Evidence:  th_chidon_schools.food TINYINT(1) DEFAULT 0
Confidence:   High
SME Verified: No
```

---

## Teams

```
Rule ID:      BR-CHI-150
Category:     Teams
Description:  Participants and staff can be assigned to a team (th_chidon_teams).
              Teams are named groups that organize participants for activities
              during the event.
Source:       mashpiadb_th_chidon_teams.sql, mashpiadb_th_chidon.sql
DB Evidence:  th_chidon.team_id INT; th_chidon_teams.team VARCHAR(65)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-151
Category:     Teams
Description:  Participants may be assigned to a school team, a regional team,
              and/or an international team, reflecting different levels of
              competition grouping.
Source:       mashpiadb_th_chidon.sql
DB Evidence:  th_chidon.school_team VARCHAR(45);
              th_chidon.regional_team VARCHAR(45);
              th_chidon.intl_team VARCHAR(45)
Confidence:   High
SME Verified: No
```

---

## Fundraising

```
Rule ID:      BR-CHI-160
Category:     Fundraising
Description:  Each participant who participates in the Chidon Drive fundraiser has
              a fundraising goal. The goal is based on their family's or school's
              number of eligible children multiplied by the cost per child ($350).
Source:       chidonOld/chidon_drive/classes/ChidonDrive.php (setGoal)
DB Evidence:  th_chidon.fundraising_goal INT unsigned;
              ChidonDrive: goal = count(children) * costPerChild ($350)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-161
Category:     Fundraising
Description:  Fundraising activity is tracked by minutes spent and type of
              fundraising performed.
Source:       mashpiadb_th_chidon.sql
DB Evidence:  th_chidon.fundraising_minutes INT unsigned;
              th_chidon.fundraising_type VARCHAR(20)
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-CHI-162
Category:     Fundraising
Description:  Donations can be earmarked for a specific family (for_family_id),
              directing the subsidy benefit to that family's children specifically.
Source:       mashpiadb_chidon_donations.sql
DB Evidence:  chidon_donations.for_family_id INT unsigned
Confidence:   High
SME Verified: No
```

---

## Miscellaneous / Administrative

```
Rule ID:      BR-CHI-170
Category:     Administrative
Description:  Items and prizes may have Spanish-language translations for labels
              and descriptions, stored in chidon_items_translations.
Source:       mashpiadb_chidon_items_translations.sql
DB Evidence:  chidon_items_translations: item_id, spanish VARCHAR(95)
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-CHI-171
Category:     Administrative
Description:  Participant allergies and medication information are collected and
              stored on each enrollment record to ensure safety during the event.
Source:       mashpiadb_th_chidon.sql, mashpiadb_chidon_reg.sql
DB Evidence:  th_chidon.allergies VARCHAR(255);
              th_chidon.medications VARCHAR(255);
              chidon_reg.allergies VARCHAR(255)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-172
Category:     Administrative
Description:  Certificates are tracked for printing status. Two separate flags
              track whether the regular certificate and the confirmation
              certificate have been printed.
Source:       mashpiadb_chidon_reg.sql
DB Evidence:  chidon_reg.cert_printed TINYINT(1) DEFAULT 0;
              chidon_reg.cert_conf_printed TINYINT(1) DEFAULT 0;
              chidon_reg.plaque_created TINYINT(1) DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-173
Category:     Administrative
Description:  Each participant can be assigned a round number (round_number) in
              the competition, and workshop assignment (workshop_number and lane).
Source:       mashpiadb_th_chidon.sql
DB Evidence:  th_chidon.round_number TINYINT(1);
              th_chidon.workshop_number VARCHAR(10) DEFAULT '0';
              th_chidon.lane INT DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-174
Category:     Administrative
Description:  A "Rohr subsidy" flag on a participant record indicates that the
              participant is receiving a Rohr Foundation subsidy toward their fees.
Source:       mashpiadb_th_chidon.sql
DB Evidence:  th_chidon.rohr_subsidy TINYINT(1) unsigned DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-175
Category:     Administrative
Description:  Participants with a sandwich preference for the event record their
              choice. Valid options are: tuna, egg, cc (cream cheese), plain, or
              cheese.
Source:       mashpiadb_th_chidon.sql
DB Evidence:  th_chidon.sandwich ENUM('tuna','egg','cc','plain','cheese')
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-176
Category:     Administrative
Description:  Parents can be contacted to resolve registration issues. The system
              tracks whether a parent has been contacted (contacted_parent flag)
              and stores notes from that contact (parent_notes).
Source:       mashpiadb_th_chidon.sql
DB Evidence:  th_chidon.contacted_parent TINYINT(3) DEFAULT 0;
              th_chidon.parent_notes TEXT
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-177
Category:     Administrative
Description:  Participants can be flagged as "dropped out" with a reason recorded.
              This allows tracking of attrition without deleting the enrollment
              record.
Source:       mashpiadb_th_chidon.sql
DB Evidence:  th_chidon.dropped_out TINYINT(3) DEFAULT 0;
              th_chidon.reason VARCHAR(45)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHI-178
Category:     Administrative
Description:  A participant's profile photo display can be suppressed via the
              show_pic flag (default 1 = show). Setting it to 0 hides the photo.
Source:       mashpiadb_th_chidon.sql
DB Evidence:  th_chidon.show_pic TINYINT(3) DEFAULT 1
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-CHI-179
Category:     Administrative
Description:  A "KHK override" flag allows administrators to manually override
              KHK eligibility rules for a participant.
Source:       mashpiadb_th_chidon.sql
DB Evidence:  th_chidon.khk_override TINYINT(3) DEFAULT 0
Confidence:   Medium
SME Verified: No
```
