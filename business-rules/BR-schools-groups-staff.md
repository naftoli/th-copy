# Business Rules: Schools, Classes, Groups & Divisions, Staff & Roles, Permissions

**Domain:** Tzivos Hashem — Schools, Classes, Groups, Staff, Roles, Permissions  
**Extracted from:** SQL dump files (mashpiadb and pointsDB schemas) + PHP admin files  
**Date extracted:** 2026-04-30  
**Extractor:** Claude (automated static analysis)  
**Status:** All rules are unverified — SME review required before use in system design

---

## Schools

```
Rule ID:      BR-SCH-001
Category:     Schools & Organizations
Description:  Every school must belong to exactly one institution (org-level grouping). A school
              cannot exist without an institution reference. The institution acts as the top of
              the organizational hierarchy above schools.
Source:       mashpiadb_schools.sql, mashpiadb_institutions.sql
DB Evidence:  schools.inst_id INT NOT NULL; UNIQUE KEY school_name (inst_id, school_name) — name
              uniqueness is scoped to the institution, not globally.
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SCH-002
Category:     Schools & Organizations
Description:  School names must be unique within the same institution. Two schools in different
              institutions may share the same name, but within one institution a duplicate name is
              rejected by both the database constraint and the PHP application logic.
Source:       mashpiadb_schools.sql, admin_school2.php
DB Evidence:  UNIQUE KEY school_name (inst_id, school_name)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SCH-003
Category:     Schools & Organizations
Description:  Every school is assigned a globally unique school number (sequential integer). This
              number is system-generated at creation time as MAX(school_number)+1 and cannot be
              shared with another school.
Source:       mashpiadb_schools.sql, admin_school2.php
DB Evidence:  UNIQUE KEY school_number (school_number); INSERT logic: SELECT IFNULL(MAX
              (school_number),0)+1 FROM schools schools_max
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SCH-004
Category:     Schools & Organizations
Description:  A school has exactly one gender designation: Boys (M), Girls (F), or Both (B).
              The default is Both. Schools designated as Both may carry separate logos for boys
              and girls.
Source:       mashpiadb_schools.sql, admin_school2.php
DB Evidence:  school_gender ENUM('M','F','B') NOT NULL DEFAULT 'B'; UI shows separate
              logo_boys / logo_girls fields only when school_gender == 'B'
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SCH-005
Category:     Schools & Organizations
Description:  A school may optionally be linked to a single Chabad mosad (Jewish communal
              institution) via the mosad_id field. This link is unique — no two schools can share
              the same mosad_id. A separate many-to-many relationship table (school_mosad_rel)
              also exists, suggesting a school may be associated with additional mosdos beyond
              the primary link.
Source:       mashpiadb_schools.sql, mashpiadb_school_mosad_rel.sql
DB Evidence:  schools.mosad_id INT(11) DEFAULT NULL; UNIQUE KEY mosad_id_UNIQUE (mosad_id);
              school_mosad_rel (school_id, mosad_id) UNIQUE KEY main (school_id, mosad_id)
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-SCH-006
Category:     Schools & Organizations
Description:  Schools have two separate shipping configurations: a default prize-delivery method
              (pickup or deliver, defaulting to deliver) and a yearly-prize-specific shipping
              method (pickup or deliver, defaulting to deliver). These can differ.
Source:       mashpiadb_schools.sql
DB Evidence:  shipping_method ENUM('pickup','deliver') NOT NULL DEFAULT 'deliver';
              yearly_prize_shipping_method ENUM('pickup','deliver') DEFAULT 'deliver'
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SCH-007
Category:     Schools & Organizations
Description:  Each school has a registration type controlling how it registers students. The
              three allowed values are '1', '2', and '3', with '3' as the default. The exact
              semantic difference between these types is not documented in the schema.
Source:       mashpiadb_schools.sql
DB Evidence:  reg_type ENUM('1','2','3') NOT NULL DEFAULT '3'
Confidence:   Low
SME Verified: No
```

```
Rule ID:      BR-SCH-008
Category:     Schools & Organizations
Description:  Schools are associated with a school type (school_type), which controls their
              management setting. The four management modes are: 'managed' (fully managed by
              admins), 'managed_personal' (managed but users can create personal tasks),
              'self_managed' (users can control their own ladder, year and TH type), and
              'personal_only' (only personal tasks). School type names are globally unique.
Source:       mashpiadb_school_types.sql, admin_school_type.php
DB Evidence:  school_types.school_type_setting ENUM('managed','managed_personal',
              'self_managed','personal_only') NOT NULL; UNIQUE KEY school_type_name
              (school_type_name)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SCH-009
Category:     Schools & Organizations
Description:  Each school type can be associated with one or more subjects (campaigns). A
              subject can only appear once per school type. Subjects are grouped by institution.
Source:       mashpiadb_school_type_subjects.sql, admin_school_type.php
DB Evidence:  school_type_subjects PRIMARY KEY (school_type_id, subject_id);
              UNIQUE KEY subject_id (subject_id, school_type_id)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SCH-010
Category:     Schools & Organizations
Description:  A school may optionally be flagged as a "home school" via the school_settings SET
              field. This is the only defined setting value. Home school behaviour is not further
              specified in the schema.
Source:       mashpiadb_schools.sql
DB Evidence:  school_settings SET('home_school') DEFAULT NULL
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-SCH-011
Category:     Schools & Organizations
Description:  A school can be flagged as a test school (test_school flag). Test schools are
              distinguishable from live schools in the system and default to 0 (not a test school).
Source:       mashpiadb_schools.sql
DB Evidence:  test_school TINYINT(3) UNSIGNED NOT NULL DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SCH-012
Category:     Schools & Organizations
Description:  Schools track participation flags for several named programs: chayolei, chidon,
              hachayols, tanya, tehillim, rewards, raffle_prizes. Each is a separate boolean/
              counter field. A school may participate in any combination of these programs.
Source:       mashpiadb_schools.sql
DB Evidence:  chayolei TINYINT(4) DEFAULT 0; chidon TINYINT(1) DEFAULT 0; hachayols TINYINT(3)
              DEFAULT 0; tanya TINYINT(4) DEFAULT 0; tehillim TINYINT(1) DEFAULT 0;
              rewards TINYINT(1) DEFAULT 0; raffle_prizes TINYINT(3) DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SCH-013
Category:     Schools & Organizations
Description:  Each school has program-specific fee fields: chayolei_fee (default $800.00),
              tanya_fee (default $0.00), chidon_fee (default $500.00), rewards_fee (default $0.00).
              These are decimal amounts stored per school, not derived from a rate table.
Source:       mashpiadb_schools.sql
DB Evidence:  chayolei_fee DECIMAL(6,2) UNSIGNED NOT NULL DEFAULT 800.00;
              tanya_fee DECIMAL(6,2) UNSIGNED NOT NULL DEFAULT 0.00;
              chidon_fee DECIMAL(6,2) UNSIGNED NOT NULL DEFAULT 500.00;
              rewards_fee DECIMAL(6,2) UNSIGNED NOT NULL DEFAULT 0.00
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SCH-014
Category:     Schools & Organizations
Description:  Schools have an Authorize.net customer profile and payment profile stored directly
              on the school record. A school's payment profile is created when the school is added
              (if credit card info is provided) and updated through the school edit form. Only
              admin users with specific role_ids (16, 18) or designated super-admin IDs are allowed
              to view and edit credit card information.
Source:       admin_school2.php
DB Evidence:  authorize_customer_profile_id VARCHAR(20); authorize_payment_profile_id VARCHAR(20);
              PHP: role_id IN (16,18) check before showing Payment Settings section
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SCH-015
Category:     Schools & Organizations
Description:  A school's store balance resets from a configurable date (store_reset). When this
              date is set, soldiers can only spend miles earned on or after that date. When no
              reset date is set, soldiers can spend all accumulated miles.
Source:       mashpiadb_schools.sql, admin_school2.php
DB Evidence:  store_reset INT(11) DEFAULT NULL; UI toggle: "Allow soldiers to spend all their
              miles" disables store_reset
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SCH-016
Category:     Schools & Organizations
Description:  Schools can be associated with multiple child types (e.g., regular student,
              special needs) via school_child_types and school_child_types2. Each school-child_type
              combination is unique per table. One of the assigned child types per school may be
              designated as the default (is_default = 1). Two separate versions of the
              school_child_types table exist (school_child_types and school_child_types2),
              suggesting a migration or versioning of this feature.
Source:       mashpiadb_school_child_types.sql, mashpiadb_school_child_types2.sql,
              mashpiadb_child_types.sql
DB Evidence:  school_child_types.is_default TINYINT(1) DEFAULT 0;
              UNIQUE KEY school_child_type_id (school_id, child_type_id)
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-SCH-017
Category:     Schools & Organizations
Description:  A school may have additional principals (extra_principals) beyond the primary
              principal fields stored directly on the school record. Each extra principal has a
              name, email, phone, and optional grade range.
Source:       mashpiadb_extra_principals.sql
DB Evidence:  extra_principals(extra_principal_id, school_id, name, email, phone, grades);
              school.principal, school.principal_email, school.principal_grades (primary fields)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SCH-018
Category:     Schools & Organizations
Description:  Schools can be linked to parent (admin) accounts via school_parents. This is a
              non-unique index (multiple admins per school, multiple schools per admin). The
              admin_id in school_parents is stored as VARCHAR(45), suggesting it may not always
              be a strict integer foreign key.
Source:       mashpiadb_school_parents.sql
DB Evidence:  school_parents(school_id INT, admin_id VARCHAR(45)); KEY main (school_id, admin_id)
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-SCH-019
Category:     Schools & Organizations
Description:  Schools can order kiosks. Each kiosk order records the kiosk type, whether it has
              a dedication, and the quantity. A school may have multiple kiosk orders of different
              types. Kiosk types have two prices: a regular price and a non-deductible price.
Source:       mashpiadb_school_kiosks.sql, mashpiadb_kiosk_types.sql
DB Evidence:  school_kiosks(school_id, kiosk_type_id, with_dedication TINYINT(1), quantity);
              kiosk_types(kiosk_name, price DECIMAL(8,2), non_ded_price DECIMAL(8,2))
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SCH-020
Category:     Schools & Organizations
Description:  The system tracks schools that are not part of the Tzivos Hashem network in a
              separate table (non_th_schools). These contain only basic directory information
              (name, city, state, zip, country, phone) and no financial or program data. They
              cannot have classes, students, or staff in the main system.
Source:       mashpiadb_non_th_schools.sql
DB Evidence:  non_th_schools(non_th_school_id, school_name, city, state, zip, country, phone);
              AUTO_INCREMENT up to 42108 — very large population
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SCH-021
Category:     Schools & Organizations
Description:  A school can be linked to a shliach (emissary) via shliach_mosad_rel, which maps
              shliach_id to mosad_id. This is a many-to-many relationship — a shliach may be
              linked to multiple mosdos and a mosad may have multiple shluchim.
Source:       mashpiadb_shliach_mosad_rel.sql
DB Evidence:  shliach_mosad_rel(shliach_id, mosad_id); UNIQUE KEY main (shliach_id, mosad_id)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SCH-022
Category:     Schools & Organizations
Description:  Schools may be enrolled into program-specific campaigns (subjects) independently
              of their school type's default subject list. Enrollment and un-enrollment is
              explicit per school per subject.
Source:       admin_school_subjects.php
DB Evidence:  INSERT IGNORE INTO school_subjects (school_id, subject_id); DELETE FROM
              school_subjects WHERE school_id AND subject_id
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SCH-023
Category:     Schools & Organizations
Description:  The pointsDB schema maintains a parallel institution record for each school. A
              pointsDB institution belongs to a network and has a host. Institutions can be
              active or inactive (is_active flag, default 1). The institution_type field is
              deprecated. Schools in mashpiadb map to institutions in pointsDB.
Source:       pointsDB_institutions.sql
DB Evidence:  institutions.is_active TINYINT(1) DEFAULT 1; institution_type VARCHAR(7) COMMENT
              'depreciated'; network_id NOT NULL; host_id NOT NULL
Confidence:   Medium
SME Verified: No
```

---

## Classes

```
Rule ID:      BR-CLS-001
Category:     Classes
Description:  Every class (platoon) must belong to exactly one school. A class cannot exist
              outside of a school context.
Source:       mashpiadb_classes.sql, admin_class.php
DB Evidence:  classes.school_id INT NOT NULL; UNIQUE KEY school_id (school_id, class_id)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CLS-002
Category:     Classes
Description:  Within a school and academic year (era), the combination of grade + grade_fr
              (French grade) + sub-group is unique. Attempting to add a duplicate grade/sub
              within the same school and era is rejected at both the DB and application level.
Source:       mashpiadb_classes.sql, admin_class.php
DB Evidence:  UNIQUE KEY class_grade (school_id, class_era, class_grade, class_grade_fr,
              class_sub); PHP: SELECT 1 FROM classes WHERE class_era=0 AND school_id=X AND
              class_grade=Y AND class_sub=Z
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CLS-003
Category:     Classes
Description:  The system supports a fixed, enumerated set of US/standard grade levels:
              Pre-school 1, Pre-school 2, Pre-school 3, Pre1a, and grades 1 through 12.
              These cannot be customised per school.
Source:       mashpiadb_classes.sql
DB Evidence:  class_grade ENUM('Pre-school 1','Pre-school 2','Pre-school 3','Pre1a','1','2',
              '3','4','5','6','7','8','9','10','11','12') NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CLS-004
Category:     Classes
Description:  The system also supports a parallel set of French grade levels, allowing a class
              to be identified in both standard and French nomenclature simultaneously. The French
              grade is optional. French grades include: Gan_1, Gan_2, Gan_3, Grand_Gan, CP, CE1,
              CE2, CM1, CM2, 6eme, 5eme, 4eme, 3eme, 2nde, 1ere, Term.
Source:       mashpiadb_classes.sql
DB Evidence:  class_grade_fr ENUM('Gan_1','Gan_2','Gan_3','Grand_Gan','CP','CE1','CE2','CM1',
              'CM2','6eme','5eme','4eme','3eme','2nde','1ere','Term') DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CLS-005
Category:     Classes
Description:  Each class has a "class era" (Hebrew year) indicating when it was active. Era 0
              means the class is current (active). Historical classes from previous years are
              retained but cannot be edited. Soldiers must be moved to a current-era class before
              their historical class can be deleted.
Source:       mashpiadb_classes.sql, admin_class.php
DB Evidence:  class_era SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Hebrew Year when class
              was active (0 for current)'; PHP: WHERE class_era = 0 for edit/add; UI message:
              "This class is from [era]. Please move all the soldiers..."
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CLS-006
Category:     Classes
Description:  A class that has enrolled students (soldiers) cannot be deleted. Only empty classes
              (0 students) may be deleted. The delete query explicitly checks for the absence of
              students before executing.
Source:       admin_class.php
DB Evidence:  DELETE FROM classes WHERE school_id=$X AND class_id=$Y AND NOT EXISTS (SELECT
              class_id FROM users WHERE users.school_id = classes.school_id AND users.class_id =
              classes.class_id); UI: "Can't delete, has soldiers"
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CLS-007
Category:     Classes
Description:  Each class has a default level (ladder year) with a valid range of 6 to 14. The
              system enforces a minimum of 6 and maximum of 14 for this value.
Source:       admin_class.php
DB Evidence:  default_level = max(6, min(gri('default_level', 6), 14)); classes.default_level
              TINYINT(3) UNSIGNED NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CLS-008
Category:     Classes
Description:  Classes have a gender_view setting ('self' or 'all', default 'all') which controls
              what content soldiers in that class can see relative to gender-segmented content.
Source:       mashpiadb_classes.sql
DB Evidence:  gender_view ENUM('self','all') NOT NULL DEFAULT 'all'
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-CLS-009
Category:     Classes
Description:  Classes may optionally specify the gender of the class (m or f). This field is
              nullable, allowing gender-neutral or mixed classes.
Source:       mashpiadb_classes.sql
DB Evidence:  class_gender ENUM('m','f') DEFAULT NULL
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-CLS-010
Category:     Classes
Description:  Each class tracks a miles-per-soldier rate (default 100) and a running miles
              balance (default 0) for mission-based reward calculations at the class level.
Source:       mashpiadb_classes.sql
DB Evidence:  miles_per_soldier INT(11) NOT NULL DEFAULT 100;
              miles_balance INT(11) NOT NULL DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CLS-011
Category:     Classes
Description:  The pointsDB schema has a parallel classes table linked to pointsDB institutions.
              It records grade, sub-group, gender (boys/girls/mixed, default mixed), active
              status, and a class hierarchy value for ordering purposes.
Source:       pointsDB_classes.sql
DB Evidence:  classes.gender ENUM('boys','girls','mixed') DEFAULT 'mixed'; is_active TINYINT(1)
              DEFAULT 1; class_hierarchy INT(5); institution_id NOT NULL
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-CLS-012
Category:     Classes
Description:  Admin access to class management is scoped to either 'school' or 'class' level
              auth. A class-level admin can only perform edit actions on their own class and
              cannot navigate to other schools. A school-level admin can manage all classes within
              their authorised school(s).
Source:       admin_class.php
DB Evidence:  $admin_auth = array('school','class'); if($auth_mode=='class') force action to
              'edit' or 'edit2'; school list filtered by admin_user['auths']['school'] for
              non-super users
Confidence:   High
SME Verified: No
```

---

## Groups & Divisions

```
Rule ID:      BR-GRP-001
Category:     Groups & Divisions
Description:  Groups are organized in a three-tier hierarchy: Group Type > Division > Group.
              A group always belongs to exactly one division, and a division always belongs to
              exactly one group type. This hierarchy is enforced through foreign key relationships.
Source:       mashpiadb_groups.sql, mashpiadb_divisions.sql, mashpiadb_group_types.sql
DB Evidence:  groups.division_id INT NOT NULL; UNIQUE KEY group (division_id, group_name);
              divisions.group_type_id INT NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-GRP-002
Category:     Groups & Divisions
Description:  Within a division, group names must be unique. The same group name may appear in
              different divisions.
Source:       mashpiadb_groups.sql
DB Evidence:  UNIQUE KEY group (division_id, group_name)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-GRP-003
Category:     Groups & Divisions
Description:  Group types belong to a camp (organization context), not a school. This suggests
              the group/division/group-type hierarchy is used for camp-based programming rather
              than school-based programming.
Source:       mashpiadb_group_types.sql
DB Evidence:  group_types.camp_id INT NOT NULL; KEY camp_id (camp_id)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-GRP-004
Category:     Groups & Divisions
Description:  A group type may optionally support divisions (has_divisions flag, default 1 = yes).
              When has_divisions is 0, the division level is skipped in UI rendering. This controls
              whether the admin can assign staff at the division granularity.
Source:       mashpiadb_group_types.sql, admin_staff_groups.php
DB Evidence:  group_types.has_divisions TINYINT(1) UNSIGNED NOT NULL DEFAULT 1;
              PHP: if ($row['divisions'] > 0) show division checkbox; else hide it
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-GRP-005
Category:     Groups & Divisions
Description:  Member (user) group assignments are time-bounded. Each assignment records a
              start_date and end_date (stored as mediumint, likely Hebrew date format). Group
              membership history is preserved — records are not deleted when a member leaves a
              group.
Source:       mashpiadb_member_groups.sql
DB Evidence:  member_groups(camp_id, user_id, group_type_id, division_id, group_id, start_date
              MEDIUMINT NOT NULL, end_date MEDIUMINT NOT NULL)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-GRP-006
Category:     Groups & Divisions
Description:  Member group division assignments (member_group_divisions) enforce that a user
              can only appear once per camp_group_division (unique constraint). The end_date is
              nullable, meaning an open-ended (current) assignment has no end date.
Source:       mashpiadb_member_group_divisions.sql
DB Evidence:  UNIQUE KEY member_group_division (user_id, camp_group_division_id);
              end_date MEDIUMINT DEFAULT NULL (nullable = still active)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-GRP-007
Category:     Groups & Divisions
Description:  The system supports a default set of group types (default_group_types) and a
              default set of division names (default_divisions) that serve as templates when
              creating new group structures. These tables have no foreign key constraints,
              suggesting they are lookup/seed tables only.
Source:       mashpiadb_default_group_types.sql, mashpiadb_default_divisions.sql
DB Evidence:  default_group_types(gt_id, group_type_name, logo_id); default_divisions
              (division_name VARCHAR(255)) — no PK or FK constraints
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-GRP-008
Category:     Groups & Divisions
Description:  The pointsDB schema has a separate groups table scoped to institutions (not camps).
              These groups are active/inactive (is_active default 1) and are created with a
              creator reference. This appears to be a distinct group concept from the mashpiadb
              camp-based groups.
Source:       pointsDB_groups.sql
DB Evidence:  groups(institution_id NOT NULL, group_name, is_active TINYINT DEFAULT 1,
              created_by NOT NULL)
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-GRP-009
Category:     Groups & Divisions
Description:  The system tracks scheduling periods for activities/sedorim with named days of
              the week (monday through sunday including shabbos). Periods are named and can
              apply to any combination of days. The sedorim (learning sessions) table is a
              simple lookup with up to 7 records.
Source:       mashpiadb_periods.sql, mashpiadb_sedorim.sql
DB Evidence:  periods(period_name, monday/tuesday/wednesday/thursday/friday/shabbos/sunday each
              TINYINT DEFAULT 0); sedorim(seder_id, seder)
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-GRP-010
Category:     Groups & Divisions
Description:  Terms (program terms/sessions) are scoped to a camp and must have unique names
              within that camp. Each term has a duration in days.
Source:       mashpiadb_terms.sql
DB Evidence:  terms(camp_id, term_name, term_days SMALLINT NOT NULL);
              UNIQUE KEY term (camp_id, term_name)
Confidence:   High
SME Verified: No
```

---

## Staff & Roles

```
Rule ID:      BR-STF-001
Category:     Staff & Roles
Description:  The system distinguishes eight authority levels (role_auth) that can be assigned
              to admins: 'school', 'class', 'team', 'user', 'director', 'head counselor',
              'counselor', 'staff'. These are stored in the roles table and control what level
              of the hierarchy the admin operates at.
Source:       mashpiadb_roles.sql
DB Evidence:  roles.role_auth ENUM('school','class','team','user','director','head counselor',
              'counselor','staff') NOT NULL; UNIQUE KEY role_auth (role_auth, role_name)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-STF-002
Category:     Staff & Roles
Description:  Staff role names must be globally unique across the system (not scoped to any
              school or camp). Each staff role carries explicit boolean flags for read, write,
              and delete permissions, all defaulting to 0 (denied).
Source:       mashpiadb_staff_roles.sql
DB Evidence:  staff_roles(role_name VARCHAR(50), allow_read TINYINT DEFAULT 0,
              allow_write TINYINT DEFAULT 0, allow_delete TINYINT DEFAULT 0);
              UNIQUE KEY employee_role (role_name)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-STF-003
Category:     Staff & Roles
Description:  A staff member (admin) can be assigned to multiple groups, divisions, and group
              types simultaneously. These assignments are recorded in separate junction tables
              and each combination (admin + group, admin + division, admin + group_type) must be
              unique — no duplicate assignments.
Source:       mashpiadb_staff_groups.sql, mashpiadb_staff_divisions.sql,
              mashpiadb_staff_group_types.sql, admin_staff_groups.php
DB Evidence:  staff_groups UNIQUE KEY employee_groups (admin_id, group_id);
              staff_divisions UNIQUE KEY employee_divisions (admin_id, division_id);
              staff_group_types UNIQUE KEY employee_group_types (admin_id, group_type_id)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-STF-004
Category:     Staff & Roles
Description:  Assigning a staff member to a group type automatically implies access to all
              divisions and groups within that type. The UI reflects this by cascading checkboxes:
              checking a group type auto-checks all its divisions, and checking a division
              auto-checks all its groups.
Source:       admin_staff_groups.php
DB Evidence:  JavaScript functions check_divisions(chckbx) and check_groups(chckbx) cascade
              selection; staff can be removed at group_type, division, or group granularity
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-STF-005
Category:     Staff & Roles
Description:  Staff members are stored in the admins table and linked to a camp via admin_auths
              (auth='camp'). The system auto-generates a username from first+last name, appending
              a numeric suffix if the name is already taken.
Source:       admin_staff.php
DB Evidence:  INSERT INTO admins; INSERT INTO admin_auths SET auth='camp'; username dedup loop:
              first+last, first+last+1, first+last+2, etc.
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-STF-006
Category:     Staff & Roles
Description:  The staff_info table provides an alternate, lightweight staff record linked to a
              school and optionally a class. This appears to be a legacy or supplementary contact
              table (using latin1 charset) separate from the full admin account system.
Source:       mashpiadb_staff_info.sql
DB Evidence:  staff_info(school_id NOT NULL, class_id DEFAULT NULL, staff_name, staff_position,
              staff_email, staff_number, staff_work_number); CHARSET=latin1
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-STF-007
Category:     Staff & Roles
Description:  Group roles (group_roles) record which admins are associated with which groups in
              a role capacity. Unlike staff_groups (which grants access), group_roles tracks the
              admin's role within the group. The group_id is nullable, suggesting a role can be
              assigned at the global level without a specific group.
Source:       mashpiadb_group_roles.sql
DB Evidence:  group_roles(admin_id NOT NULL, group_id DEFAULT NULL); no unique constraint —
              an admin may have multiple role records per group
Confidence:   Low
SME Verified: No
```

```
Rule ID:      BR-STF-008
Category:     Staff & Roles
Description:  Member (user/student) roles (member_roles) link users to groups in a role context.
              Like group_roles, the group_id is nullable. No uniqueness constraint exists —
              a user may appear multiple times.
Source:       mashpiadb_member_roles.sql
DB Evidence:  member_roles(user_id NOT NULL, group_id DEFAULT NULL); no unique constraint
Confidence:   Low
SME Verified: No
```

```
Rule ID:      BR-STF-009
Category:     Staff & Roles
Description:  Member type roles (member_type_roles) link users to group types, not specific
              groups, for role assignment. Group_type_id is nullable. No uniqueness constraint —
              multiple type-role assignments per user are allowed.
Source:       mashpiadb_member_type_roles.sql
DB Evidence:  member_type_roles(user_id NOT NULL, group_type_id DEFAULT NULL)
Confidence:   Low
SME Verified: No
```

```
Rule ID:      BR-STF-010
Category:     Staff & Roles
Description:  Group type roles (group_type_roles) record which admins have a role scoped to
              an entire group type. Group_type_id is nullable. No uniqueness constraint — the same
              admin can appear multiple times.
Source:       mashpiadb_group_type_roles.sql
DB Evidence:  group_type_roles(admin_id NOT NULL, group_type_id DEFAULT NULL); no UNIQUE KEY
Confidence:   Low
SME Verified: No
```

```
Rule ID:      BR-STF-011
Category:     Staff & Roles
Description:  Staff types (staff_types) define categories of staff (up to 15 defined types).
              Each type has only a name — no permissions are attached at this level. The mapping
              between staff types and behaviour is handled elsewhere in application logic.
Source:       mashpiadb_staff_types.sql
DB Evidence:  staff_types(staff_types_id, type_name VARCHAR(50)); AUTO_INCREMENT=16
              (15 types exist)
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-STF-012
Category:     Staff & Roles
Description:  School-level admin access is enforced by auth_mode checks in PHP. A school-level
              admin can only edit their own school profile, not add or view all schools. Only
              super-admins can add, delete, or view all institutions.
Source:       admin_school2.php, admin_class.php
DB Evidence:  if($auth_mode=='school') force action to 'edit'/'edit2' only; $admin_user['auth']
              == 'super' checks guard delete/add actions; school list filtered by
              admin_user['auths']['school'] for non-super
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-STF-013
Category:     Staff & Roles
Description:  Users (students) have a parent relationship tracked in pointsDB. The only
              supported relationship type is 'Parent' — no other relationship types (guardian,
              sibling, etc.) are defined in the enum.
Source:       pointsDB_relationships.sql
DB Evidence:  relationships.relationship ENUM('Parent') NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-STF-014
Category:     Staff & Roles
Description:  Users can be assigned to curriculum tracks per subject. Each user-subject
              combination is unique (primary key). A user can have a track, a level, and an
              enrolled flag per subject. The enrolled flag defaults to 1 (enrolled).
Source:       mashpiadb_user_tracks.sql
DB Evidence:  user_tracks PRIMARY KEY (user_id, subject_id); track_id DEFAULT NULL; level
              TINYINT DEFAULT NULL; enrolled TINYINT(1) DEFAULT 1
Confidence:   High
SME Verified: No
```

---

## Permissions

```
Rule ID:      BR-PRM-001
Category:     Permissions
Description:  Staff permissions on groups are stored at the individual admin + group level.
              Each combination of admin and group can have independent read, write, and delete
              flags, all defaulting to 0 (denied). A single admin+group pair can only have one
              permission record.
Source:       mashpiadb_staff_permissions.sql
DB Evidence:  staff_permissions(admin_id NOT NULL, group_id NOT NULL, read TINYINT DEFAULT 0,
              write TINYINT DEFAULT 0, delete TINYINT DEFAULT 0);
              UNIQUE KEY staff_group (admin_id, group_id)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PRM-002
Category:     Permissions
Description:  The pointsDB permissions system records a user's permission string per institution.
              A user can have multiple permission records for the same institution (no unique
              constraint on user_id + institution_id + permission). Permissions have an expiration
              date (registration_expiration), a creation date, and a default_permission flag
              (default 1).
Source:       pointsDB_permissions.sql
DB Evidence:  permissions(user_id NOT NULL, institution_id NOT NULL, permission VARCHAR(255)
              NOT NULL, registration_expiration INT DEFAULT 0, default_permission TINYINT
              DEFAULT 1); no UNIQUE constraint on (user_id, institution_id)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PRM-003
Category:     Permissions
Description:  Permissions in pointsDB can expire. The registration_expiration field (int,
              default 0) stores a Unix timestamp. An indexed field allows efficient queries for
              expired permission cleanup or enforcement.
Source:       pointsDB_permissions.sql
DB Evidence:  registration_expiration INT(11) DEFAULT 0; KEY registration_expiration
              (registration_expiration)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PRM-004
Category:     Permissions
Description:  The pointsDB permission_types table defines up to 7 distinct permission type
              categories (AUTO_INCREMENT=8). Permission types are referenced by string value
              in the permissions table rather than by ID, allowing flexible extensibility.
Source:       pointsDB_permission_types.sql
DB Evidence:  permission_types(permission_id, permission_type VARCHAR(255));
              permissions.permission VARCHAR(255) — stores the type string directly
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-PRM-005
Category:     Permissions
Description:  Permissions in pointsDB can be associated with a registration location (e.g.,
              which campaign or event the user registered through) and a template style for UI
              customisation. These are optional fields.
Source:       pointsDB_permissions.sql
DB Evidence:  registration_location VARCHAR(64) DEFAULT NULL;
              template_style VARCHAR(30) DEFAULT ''
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-PRM-006
Category:     Permissions
Description:  An admin auth hash (auth_hash) is stored per permission record in pointsDB,
              allowing token-based authentication for that specific permission grant. The hash
              is nullable — not all permissions require auth-hash validation.
Source:       pointsDB_permissions.sql
DB Evidence:  auth_hash VARCHAR(32) DEFAULT NULL
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-PRM-007
Category:     Permissions
Description:  Email notifications are configurable per permission record in pointsDB. Each
              permission can independently opt in or out of email notifications, defaulting to
              off (0).
Source:       pointsDB_permissions.sql
DB Evidence:  email_notification TINYINT(1) NOT NULL DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PRM-008
Category:     Permissions
Description:  The pointsDB networks table acts as a top-level grouping above institutions. Each
              institution belongs to exactly one network. Networks have a keyword (for URL/routing),
              an email, a terminology label (allowing domain-specific vocabulary), and an admin
              user owner.
Source:       pointsDB_networks.sql, pointsDB_institutions.sql
DB Evidence:  institutions.network_id INT NOT NULL; networks(network_keyword, network_terminology,
              admin_user_id)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PRM-009
Category:     Permissions
Description:  Institutions in pointsDB have a registration expiry timestamp (reg_expires, default
              0). An expired registration may restrict access to the institution's features,
              though enforcement logic is in application code not captured in these files.
Source:       pointsDB_institutions.sql
DB Evidence:  reg_expires INT(10) UNSIGNED DEFAULT 0
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-PRM-010
Category:     Permissions
Description:  In pointsDB, grades are institution-scoped. Each grade record has a hierarchy
              value for ordering, an active flag, and a creator reference. Grades can be
              deactivated (is_active=0) without deletion. This allows historical grade structures
              to be preserved.
Source:       pointsDB_grades.sql
DB Evidence:  grades(institution_id DEFAULT NULL, grade_name, grade_hierarchy, is_active
              TINYINT DEFAULT 1, created_by NOT NULL); KEY institution_id (institution_id)
Confidence:   High
SME Verified: No
```

---

## Cross-Cutting Observations

```
Rule ID:      BR-XCT-001
Category:     Cross-Cutting
Description:  The system uses two separate databases: mashpiadb (the main operational database
              for schools, classes, staff, camps, and gamification) and pointsDB (a secondary
              database tracking institutions, networks, permissions, and relationships, likely
              a newer or parallel system). Records in mashpiadb schools map to records in
              pointsDB institutions, but this mapping is not enforced by a DB-level constraint.
Source:       All SQL dumps (prefix mashpiadb_* vs pointsDB_*)
DB Evidence:  Two separate database headers in dump files; no cross-DB foreign keys
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-XCT-002
Category:     Cross-Cutting
Description:  The terminology used in the UI is domain-specific: schools are called "Bases,"
              classes are called "Platoons," students are called "Soldiers," and teachers are
              referred to by their role. This military metaphor is consistent throughout the admin
              interface.
Source:       admin_class.php, admin_school2.php
DB Evidence:  T_('Platoons'), T_('Soldiers'), T_('Base Management'), T_('Manage Soldiers'),
              T_('Manage Platoons')
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-XCT-003
Category:     Cross-Cutting
Description:  The system supports multi-language operation. Language preference (lang_id,
              default 1) is stored per school and per admin. At least English, Hebrew, and
              French are supported (evidenced by Hebrew name fields and French grade system).
Source:       mashpiadb_schools.sql, admin_staff.php
DB Evidence:  schools.lang_id INT DEFAULT 1; admins.lang (used in staff edit form);
              class_grade_fr (French grades); school_name_he (Hebrew name)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-XCT-004
Category:     Cross-Cutting
Description:  The Chabad mosad system (chabad_mosdos) contains a separate registry of Chabad
              Jewish communal institutions with their own internal mosad_id and primary_mosad_id.
              A mosad can have a type and a category. Detailed information about a mosad is
              stored as a JSON blob in chabad_mosad_info. The mosad system is linked to schools
              via school_mosad_rel and to shluchim via shliach_mosad_rel.
Source:       mashpiadb_chabad_mosdos.sql, mashpiadb_chabad_mosad_info.sql
DB Evidence:  chabad_mosdos(mosad_id, primary_mosad_id, name, mosad_type, mosad_category);
              chabad_mosad_info(mosad_id PK, json_info TEXT NOT NULL)
Confidence:   High
SME Verified: No
```

---

*Total rules extracted: 52*  
*Source files analysed: 38 SQL dump files, 7 PHP admin files*  
*All rules are draft status — no SME verification has been performed.*
