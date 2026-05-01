# Business Rules: Base Management & Authentication

---

# Module: Auth

## Business Rules

1. Login accepts either a username/password pair, a Chabad.org OAuth key, or a Google OAuth token. No other login methods are supported.
   Source: `mashpia.com/public/api/auth/login.php:21-42`

2. Username lookup is case-insensitive; the system lowercases the submitted username before matching.
   Source: `mashpia.com/public/api/auth/classes/Auth.php:26`

3. If a username is not found, the system falls back to matching the same value against the email (`admin_email`) column before rejecting login.
   Source: `mashpia.com/public/api/auth/classes/Auth.php:28-30`

4. Passwords are stored in two forms: a bcrypt hash (`hashed_pass`) used for authentication, and an encrypted plaintext copy (`password`) used for display/legacy key generation. Both columns are always kept in sync on create and on update.
   Source: `mashpia.com/public/api/models/Admin.php:115-148`

5. When a Chabad.org login key finds no matching TH account, the system returns the Chabad.org Shliach profile (name, mosdos) to the client so the user can pre-fill a new account form rather than receiving a plain error.
   Source: `mashpia.com/public/api/auth/classes/Auth.php:39-53`, `mashpia.com/public/api/auth/login.php:31-35`

6. A Google login that finds no matching TH account returns a plain error — no pre-fill shortcut is offered (unlike the Chabad.org flow).
   Source: `mashpia.com/public/api/auth/classes/Auth.php:56-72`

7. Each admin account must have a globally unique username AND a globally unique email address. A unique Chabad.org shliach ID and a unique Google ID are also enforced if those external connections are set.
   Source: `mashpia.com/public/api/models/Admin.php:25-29`, `SQLdump/mashpiadb_admins.sql:87-88`

8. The `auth` column on the admins table determines HQ-level access. Only the values `super` and `ckidssuper` grant super-admin login; the values `''` and `inactive` do not.
   Source: `SQLdump/mashpiadb_admins.sql:29`, `mashpia.com/public/api/models/Admin.php:266-268`

9. A password-reset token expires in exactly one hour. Each new reset request deletes any previously pending reset for the same admin before inserting a new one, so only one outstanding reset token can exist per account at a time.
   Source: `mashpia.com/public/api/models/Admin.php:162-178`, `SQLdump/mashpiadb_password_reset.sql:26-35`

10. The forgot-password flow validates that the submitted value is a syntactically valid email address before attempting a lookup; an invalid email address is rejected outright.
    Source: `mashpia.com/public/api/auth/forgot.php:18-22`

11. A user may update their own password or username only if they also supply the current correct password (`current_password`). Submitting an empty string for password or username silently ignores that field (no change is made).
    Source: `mashpia.com/public/api/auth/current_user.php:17-33`

12. When a new account is created via the signup flow, the admin's username is automatically set to their email address, and the `beta` flag is set to 1.
    Source: `mashpia.com/public/api/auth/new_account.php:19-20`

13. New accounts require passwords and a password confirmation that match; mismatched passwords prevent account creation.
    Source: `mashpia.com/public/api/auth/new_account.php:12-13`

14. Every new admin account automatically triggers the creation of a corresponding helpdesk (support portal) account as a before-create callback.
    Source: `mashpia.com/public/api/models/Admin.php:95-105`

15. The client-side new account form checks email uniqueness in real time (on blur) via a separate legacy AJAX endpoint, in addition to the server-side uniqueness validation.
    Source: `front-end/src/pages/login/NewAccount.jsx:112-132`

16. Connecting a Chabad.org or Google account to an existing TH account requires validating the external token first; if validation fails the connection is rejected.
    Source: `mashpia.com/public/api/auth/current_user.php:56-75`

17. Each admin can have at most one linked Chabad.org account and at most one linked Google account; attempts to use an external ID already connected to a different admin are rejected.
    Source: `mashpia.com/public/api/models/Admin.php:27-29`

---

## Open Questions

- The `auth` ENUM includes `ckidssuper` — unclear whether this grants the same system-wide access as `super` or only C-Kids institution access. (Source: `SQLdump/mashpiadb_admins.sql:29`)
- The old password comparison (`old_password !== $current_user->password`) at `current_user.php:26-27` compares against the encrypted plaintext, not the bcrypt hash. The comment says "TODO remove" but it is still present — unclear if it is dead code or still in use in the legacy client.

---

# Module: Login / Access Context

## Business Rules

1. Every admin session has a "current login" that is one of six codes: `HQ` (headquarters), `INST` (institution manager), `BC` (base commander), `TEACHER` (platoon teacher), `PARENT` (parent portal), or `BLANK` (no access context assigned yet).
   Source: `mashpia.com/public/api/auth/classes/Login.php:91-155`

2. An admin is considered HQ only when their `auth` column equals `super`.
   Source: `mashpia.com/public/api/models/Admin.php:266-268`

3. A base (school) login (`BC`) is considered "active" only when the school's `school_era` column is NULL. A non-null `school_era` means the base is in an archived/historical state.
   Source: `mashpia.com/public/api/auth/classes/Login.php:118`

4. A platoon (class) login (`TEACHER`) is active only when its parent school's `school_era` is NULL.
   Source: `mashpia.com/public/api/auth/classes/Login.php:129`

5. Access to legacy (non-API) systems is granted only for institutions whose `inst_id` is 2 or 4. All other institutions and bases are API-only.
   Source: `mashpia.com/public/api/auth/classes/Login.php:28, 107, 118`

6. HQ and Institution logins are given access to all five program modules (chayolei, chidon, tehillim, tanya, rewards). A Base Commander login gets only the modules their school has paid for. A Teacher login inherits the modules of their base.
   Source: `mashpia.com/public/api/auth/classes/Login.php:163-192`

7. If an admin has no assigned logins (no institution, school, class, parent or user auth entries), they receive a `BLANK` login and cannot access any protected resources.
   Source: `mashpia.com/public/api/models/Admin.php:216-219`

8. An admin with at least one `user`-type auth entry (or with `is_parent = 1`) receives a `PARENT` login in addition to any staff logins.
   Source: `mashpia.com/public/api/models/Admin.php:210-214`

9. When no explicit login type/id is stored in the session cookie, the system defaults to the first login in the priority order: HQ > institution > school > class > parent > blank.
   Source: `mashpia.com/public/api/models/Admin.php:191-219`

---

# Module: Permissions (admin_auths)

## Business Rules

1. The `admin_auths` table links an admin to an entity (institution, school, class, team, user, camp, or staff) with one row per combination. The combination of `(admin_id, auth, id)` is the unique key — the same admin cannot hold two separate auth entries for the same entity.
   Source: `SQLdump/mashpiadb_admin_auths.sql:27-32`

2. When a new auth connection is created, the system assigns a default role and position based on auth type: `user` auth → role 1 / "Parent"; `class` auth → role 13 / "Teacher"; `school` auth → role 18 / "Base Commander"; `staff` auth → role 40 / no position label (empty string).
   Source: `mashpia.com/public/api/models/AdminAuth.php:21-45`

3. Only HQ (`HQ`), Institution (`INST`), and Base Commander (`BC`) logins may create new auth connections for arbitrary entities. A BC login can only create connections scoped to their own base.
   Source: `mashpia.com/public/api/core/admin_auths.php:32-36`

4. Removing a teacher from a class (via `removeTeacher`) does not delete their account — instead it demotes them to a `staff` auth on the parent school with role 40 (staff role), leaving their account intact.
   Source: `mashpia.com/public/api/core/admin_auths.php:73-98`

5. Roles are scoped to an auth type: the same role name can exist for different auth contexts (e.g., a "Base Commander" role under `school` auth is distinct from any role under `class` auth). The uniqueness constraint is `(role_auth, role_name)`.
   Source: `SQLdump/mashpiadb_roles.sql:27-31`

6. Staff permissions (read/write/delete) are stored per-admin per-group in `staff_permissions`, not per-role. This is a separate system from the `admin_auths` role_id.
   Source: `SQLdump/mashpiadb_staff_permissions.sql:26-35`

---

## Open Questions

- The `AdminAuth.setDefaultPosition` callback has a duplicate `else if ($this->auth === 'school')` branch; the second branch sets position to `'Staff Member'` but is unreachable because it matches the same condition as the earlier `'Base Commander'` branch. Unclear what position `staff` auth was intended to receive. (Source: `mashpia.com/public/api/models/AdminAuth.php:38-45`)
- The `auth` ENUM includes `team` and `camp` values but no API routes or login types handle them — their current use is unclear.

---

# Module: Institutions

## Business Rules

1. Institution names must be globally unique.
   Source: `SQLdump/mashpiadb_institutions.sql:32`, `mashpia.com/public/api/models/Institution.php:10`

2. Institution 12 is silently excluded from the institution list returned to the API — it is filtered out before the response is sent.
   Source: `mashpia.com/public/api/core/institutions.php:10-12`

3. Admins with an `institution` auth entry get the `INST` login code and can see all schools belonging to that institution.
   Source: `mashpia.com/public/api/auth/classes/Login.php:100-110`

---

# Module: Bases (Schools)

## Business Rules

1. Each base must belong to an institution (`inst_id` is NOT NULL). The combination of `inst_id` + `school_name` must be unique — two bases may share a name only if they are in different institutions.
   Source: `SQLdump/mashpiadb_schools.sql:154-156`

2. Each base has a unique `school_number` that is auto-assigned as MAX(school_number) + 1, seeded from 613769.
   Source: `SQLdump/mashpiadb_schools.sql:156`, `mashpia.com/public/api/models/School.php:67-74`

3. When a new base is created, the system auto-generates initials from the first letter of each word in the school name (if no initials are explicitly supplied).
   Source: `mashpia.com/public/api/models/School.php:77-82`

4. A base's `school_era` being NULL means the base is currently active. A non-null year value archives the base.
   Source: `mashpia.com/public/api/auth/classes/Login.php:118`, `SQLdump/mashpiadb_schools.sql:35`

5. Each base independently enables or disables five program modules: `chayolei`, `chidon`, `tanya`, `tehillim`, and `rewards`. These flags are set during registration.
   Source: `SQLdump/mashpiadb_schools.sql:82-88`, `mashpia.com/public/api/core/bases.php:73-79`

6. If the `tanya` module is selected during base registration, the `tehillim` module is also automatically enabled.
   Source: `mashpia.com/public/api/core/bases.php:80`

7. School number 612 is a system-reserved "unassigned students" school and is excluded from all base listing queries.
   Source: `mashpia.com/public/api/core/bases.php:19`

8. The HQ filter for base listing returns only schools where `chayolei = 1` OR `chidon = 1` — test schools and fully inactive bases are excluded from the standard HQ view.
   Source: `mashpia.com/public/api/auth/classes/Login.php:55-56`

9. A BC login can only view and manage the specific base(s) they are authorized to. They cannot view bases they are not connected to.
   Source: `mashpia.com/public/api/core/bases.php:174-186`

10. Only HQ and Institution (`INST`) logins can specify a `school_id` when accessing payment or base operations. A BC login is automatically scoped to their own base.
    Source: `mashpia.com/public/api/core/bases.php:153-165`

11. When a new school is created via the Chabad.org new-account flow, the school's institution is set to 10 (C-Kids) by default.
    Source: `mashpia.com/public/api/auth/new_account.php:43`

12. Base registration requires agreement to terms and conditions (enforced client-side) and payment via credit card if the total is greater than zero.
    Source: `front-end/src/pages/base-managment/base/RegisterBasePage.jsx:92-109`

13. During base registration, the cart total submitted by the client must match the server-calculated total (sum of cart item prices plus any outstanding balance, minus any discount) to within a rounding tolerance. A mismatch throws an exception and aborts registration.
    Source: `mashpia.com/public/api/models/School.php:194-198`

14. A base has three registration types: type 1 = tuition (school pays for all soldiers), type 2 = guaranteed (school commits all students will register by early-bird date), type 3 = standard (parents pay individually). The type value must be 1, 2, or 3.
    Source: `mashpia.com/public/api/models/SchoolRegistration.php:14-17`, `SQLdump/mashpiadb_schools.sql:103`

15. Each base can have a school-specific early-bird deadline; if that date is later than the global early-bird deadline, the school-specific date takes precedence.
    Source: `mashpia.com/public/api/models/School.php:328-334`

16. The school's default registration type is 3 (standard / parents pay).
    Source: `SQLdump/mashpiadb_schools.sql:103`

17. A school may have only one registration record per year (`(school_id, year)` is unique in `school_registrations`).
    Source: `mashpia.com/public/api/models/SchoolRegistration.php:8-10`

18. When a base is saved, any change to `allow_parent_tasks`, `print_parent_tasks`, or `pic_mission_type` is automatically propagated down to all platoons and soldiers in that base.
    Source: `mashpia.com/public/api/models/School.php:84-109`

19. Logos uploaded to a base must be JPEG or PNG; all other file types are rejected.
    Source: `mashpia.com/public/api/models/School.php:368-373`

20. Each base is automatically enrolled in a set of program campaigns on creation, based on whether it is a C-Kids school (`inst_id = 10`) or a standard school.
    Source: `mashpia.com/public/api/models/School.php:512-561`

---

## Open Questions

- The `school_gender` ENUM allows `M`, `F`, or `B` (boys/girls/both), but there is no enforcement in the base listing or access-control paths that restricts soldier gender to match school gender.
- The `mosad_id` column has a unique constraint; it is populated from Chabad.org data during sign-up. It is unclear what happens if two admins sign up with overlapping Chabad.org mosdos.

---

# Module: Platoons (Classes)

## Business Rules

1. A platoon's grade must be one of a fixed set of values: Pre-school 1, Pre-school 2, Pre-school 3, Pre1a, 1–12. No other grade values are allowed.
   Source: `SQLdump/mashpiadb_classes.sql:28`

2. Within a single school, the combination of `(school_id, class_era, class_grade, class_grade_fr, class_sub)` must be unique. This prevents duplicate platoons for the same grade and sub-group in the same year.
   Source: `SQLdump/mashpiadb_classes.sql:51-52`

3. A platoon can only be deleted if it has no soldiers assigned to it.
   Source: `mashpia.com/public/api/models/Platoon.php:45-47`

4. Only HQ, Institution, and Base Commander logins may create platoons. A BC login automatically has the new platoon assigned to their own base (school_id is not taken from the request payload).
   Source: `mashpia.com/public/api/core/platoons.php:83-88`

5. A Teacher login cannot view platoons other than their own via the show endpoint.
   Source: `mashpia.com/public/api/models/Platoon.php:36-42`

6. Changing `allow_parent_tasks`, `print_parent_tasks`, or `pic_mission_type` on a platoon automatically propagates the change to all soldiers in that platoon.
   Source: `mashpia.com/public/api/models/Platoon.php:49-70`

7. A class's `class_era` of 0 means the class is active in the current year; a non-zero value is the Hebrew year in which the class was active.
   Source: `SQLdump/mashpiadb_classes.sql:36`

---

# Module: Platoon Transitions

## Business Rules

1. A platoon transition represents a pending (not yet deployed) move of a soldier to a new school and/or class. A soldier can have only one pending (undeployed) transition at a time; creating a new one for the same soldier overwrites the previous pending record.
   Source: `mashpia.com/public/api/core/platoon_transition.php:126-158`

2. A transition is "deployed" by updating the `deployed_at` timestamp, recording the soldier's origin school and class in `from_school_id` / `from_class_id`, and physically updating the soldier's `school_id` and `class_id` records.
   Source: `mashpia.com/public/api/core/platoon_transition.php:110-123`

3. HQ and non-BC logins can only see and deploy transitions that they themselves created (`pt.admin_id = current admin_id`). A BC login can see all transitions involving their own base (either as origin or destination).
   Source: `mashpia.com/public/api/core/platoon_transition.php:21-24, 100-107`

4. "Removing" a soldier from a base (discharging) clears `user_registered`, and moves the soldier to school 612 (the system-reserved "unassigned students" school) while preserving their grade-equivalent class in that school.
   Source: `mashpia.com/public/api/core/platoon_transition.php:160-181`

5. A transition record stores the registration year at the time of creation; this year is supplied from the global registration settings.
   Source: `mashpia.com/public/api/core/platoon_transition.php:78, 134-156`

---

# Module: Soldiers (Users/Hachayols)

## Business Rules

1. Each soldier receives a globally unique auto-incrementing serial number (`user_serial`) and a randomly generated unique barcode (`user_code`). The barcode is prefixed with `3` when displayed.
   Source: `mashpia.com/public/api/models/Soldier.php:1219-1250`

2. A soldier cannot be created if another soldier in the same school already has the same first name, last name, and date of birth.
   Source: `mashpia.com/public/api/core/users.php:166-173`

3. A soldier's `school_type_id` is auto-set based on gender if not provided: boys → type 2, girls → type 3. If a type is provided but its gender-parity is wrong (e.g., a girls-type ID is set for a boy), the system corrects it automatically.
   Source: `mashpia.com/public/api/models/Soldier.php:1273-1288`

4. All new soldiers are marked `chayolei_eligible = 1`. Soldiers in grades 4–7 (but not grade 8) are also marked `chidon_eligible = 1` at creation.
   Source: `mashpia.com/public/api/core/users.php:187-188`

5. When a soldier is created for a Day School base (`inst_id = 4`), the `hachayols` and `medals_ranks` modules are automatically disabled for that soldier.
   Source: `mashpia.com/public/api/core/users.php:161-164`

6. Immediately after creation, a soldier's `chayolei`, `chidon`, and `yan` (Tanya) module flags are set to match the flags of their base.
   Source: `mashpia.com/public/api/models/Soldier.php:1318-1324`

7. A soldier is automatically enrolled in program campaigns and has birthday missions generated for all three calendars (English, Yiddish, Hebrew) immediately after creation.
   Source: `mashpia.com/public/api/models/Soldier.php:25-30`

8. Every new soldier receives rank 1 ("Private") at creation unless they already have a rank record.
   Source: `mashpia.com/public/api/models/Soldier.php:1290-1301`

9. A soldier can only be permanently deleted (record removed) if their total miles (points) is zero. If they have earned any miles, "deletion" instead moves them to school 612 (unassigned) with no school or class assigned.
   Source: `mashpia.com/public/api/models/Soldier.php:1327-1329`, `mashpia.com/public/api/core/users.php:326-336`

10. Only HQ, Institution, and Base Commander logins may delete or remove soldiers. Teachers and Parents cannot.
    Source: `mashpia.com/public/api/core/users.php:323-325`

11. A Teacher login can only access soldiers in their own platoon. A BC login can only access soldiers in their own base. A Parent login can only access soldiers linked to their account via `admin_auths`.
    Source: `mashpia.com/public/api/models/Soldier.php:48-61`

12. Profile pictures must be JPEG or PNG; other file types are rejected.
    Source: `mashpia.com/public/api/models/Soldier.php:463-466`

13. Chidon registration is only available for soldiers in grades 3–8 at a school that has the `chidon` flag enabled. It is completely blocked after 2027-02-12 (Eastern time).
    Source: `mashpia.com/public/api/models/Soldier.php:665-676, 754-762`

14. ID cards are generated only for soldiers where `user_registered > 0` and `medals_ranks = 1`. Schools 180, 585, 588, 612, and 709 are excluded from ID card generation.
    Source: `mashpia.com/public/api/core/id_cards.php:44-47`

15. When Tanya (`yan`) is turned off for a soldier, the system removes all existing Tanya/Mishna marks from the campaign summary tables.
    Source: `mashpia.com/public/api/core/users.php:269-272`, `mashpia.com/public/api/models/Soldier.php:1303-1316`

16. If a soldier's date of birth is changed, birthday missions are automatically recalculated.
    Source: `mashpia.com/public/api/models/Soldier.php:1212-1216`, `mashpia.com/public/api/core/users.php:262-265`

17. Hachayol (Chabad Siddur gift) eligibility is determined per-family: if any sibling in the same parent account already has a hachayol for the year, no additional hachayol is created. If no sibling has one, the hachayol is assigned to the youngest child in grade 5 or below (Pre1a or grades 1–5), or to the oldest child if no early-grade child exists. Australian school students are excluded from hachayol eligibility entirely.
    Source: `mashpia.com/public/api/models/Soldier.php:551-612`

---

## Open Questions

- The `gender` column in `users` is an ENUM of `M` or `F` but allows NULL. It is unclear when NULL is a valid state vs. missing data.
- The modules endpoint (`modules.php`) is whitelisted to only `hachayols` and `medals_ranks` — why these two specifically and not the others (chayolei, chidon, yan)?

---

# Module: Parents

## Business Rules

1. A parent account is a regular admin record with `is_parent = 1` and an `admin_auths` entry linking them to one or more soldiers via `auth = 'user'`.
   Source: `mashpia.com/public/api/models/Admin.php:210-214`

2. When a parent account is created, the default password is `p1234` (hard-coded). The system sends an email with the credentials to the parent.
   Source: `mashpia.com/public/api/core/parents.php:81`, `front-end/src/pages/base-managment/parents/NewParentModal.jsx:164`

3. A parent account requires either a father name or a mother name (or both) — an account with neither is rejected.
   Source: `front-end/src/pages/base-managment/parents/NewParentModal.jsx:105-106`

4. A soldier can only be linked to one parent account. If a soldier already has a parent linked, they cannot be added to a second parent account via the normal flow.
   Source: `mashpia.com/public/api/core/parents.php:117-121`

5. For HQ users, children are linked to a parent by entering their 7-digit serial numbers (format: `7XXXXXX`) as a comma-separated list. For non-HQ users, children are selected from a dropdown of available soldiers in the user's scope.
   Source: `mashpia.com/public/api/core/parents.php:113-115`, `front-end/src/pages/base-managment/parents/NewParentModal.jsx:91-99`

6. For non-HQ logins, the parent listing also includes a list of soldiers who have no parent account at all ("unassigned children"), so staff can identify and act on them.
   Source: `mashpia.com/public/api/core/parents.php:59-68`

7. HQ users do not see the unassigned-children list (the query is skipped for HQ).
   Source: `mashpia.com/public/api/core/parents.php:59`

8. When a parent is created, their admin account is automatically connected to every soldier in the system that has the given user serial number, provided those soldiers do not already have a parent.
   Source: `mashpia.com/public/api/core/parents.php:116-123`

9. Parent usernames must be unique; if the username already exists, the update is rejected.
   Source: `mashpia.com/public/api/core/parents.php:182-186`

---

# Module: Staff

## Business Rules

1. Staff email addresses must be unique across all admin accounts; attempting to create a staff account with an already-used email is rejected with a specific error message.
   Source: `mashpia.com/public/api/core/staff.php:115-118`

2. A staff member may hold multiple positions across different bases and/or platoons simultaneously; each position is a separate `admin_auths` row.
   Source: `mashpia.com/public/api/core/staff.php:62-101`

3. The default position label for a `school` auth is "Base Commander", for a `class` auth is "Teacher", and for a `staff` auth is "Unknown".
   Source: `mashpia.com/public/api/core/staff.php:9-13`

4. Super-admin accounts (`auth = 'super'`) are excluded from the staff listing.
   Source: `mashpia.com/public/api/core/staff.php:35`

5. Staff passwords are stored encrypted and are decrypted before being returned in the staff listing API response.
   Source: `mashpia.com/public/api/core/staff.php:48`

---

# Module: Modules (hachayols / medals_ranks flags)

## Business Rules

1. Only two module flags can be toggled via the modules API: `hachayols` and `medals_ranks`. All other module fields are not accessible through this endpoint.
   Source: `mashpia.com/public/api/core/modules.php:6`

2. Module updates can target individual soldiers, all soldiers in one or more platoons, or all soldiers in a school. In all cases, the scope is filtered against the current user's access permissions before any update is applied.
   Source: `mashpia.com/public/api/core/modules.php:87-98`

3. When a module is updated at the class or school level, the system simultaneously updates the matching flag on both the `users` and `classes` (or `schools`) tables.
   Source: `mashpia.com/public/api/core/modules.php:105-121`

---

# Module: School Contacts

## Business Rules

1. Each base tracks three categories of contacts: the Base Commander (stored in the `admins` table), the principal (stored in the `schools` table), and the Chidon coordinator (stored in the `schools` table).
   Source: `mashpia.com/public/api/core/school_contacts.php:21-52`

2. A base can have zero or more "extra principals" stored in a separate `extra_principals` table; each records name, email, phone, and the grade range they are responsible for.
   Source: `mashpia.com/public/api/core/school_contacts.php:45-61`

3. Contact updates for the BC, principal, Chidon coordinator, and extra principals are committed as a single database transaction; any failure rolls back all changes.
   Source: `mashpia.com/public/api/core/school_contacts.php:102-189`

4. The Chidon coordinator can be flagged as also being the Base Commander (`chidon_also_bc`).
   Source: `SQLdump/mashpiadb_schools.sql:100`, `mashpia.com/public/api/core/school_contacts.php:154`

---

# Module: New Account Registration (Bases via Chabad.org)

## Business Rules

1. When a Chabad.org user creates a new TH account, each Chabad.org mosad they select is either matched to an existing school by `mosad_id` or created as a new school. The admin is then linked to each resulting school via an `admin_auths` school entry.
   Source: `mashpia.com/public/api/auth/new_account.php:26-58`

2. If a mosad has no existing school and a new one is created, the school's shipping address is copied from the primary address, initials are auto-generated, notes are set from the mosad URL, and institution is set to 10 (C-Kids).
   Source: `mashpia.com/public/api/auth/new_account.php:38-46`

3. Mosdos that are returned by Chabad.org but explicitly unselected by the user are skipped — no school is created or linked for them.
   Source: `mashpia.com/public/api/auth/new_account.php:33-34`

---

## Open Questions

- The registration year passed to `School::build` during Chabad.org sign-up uses `GlobalSettings::getCurrentYear()` rather than `getRegistrationYear()` — unclear if this is intentional or if it could cause the school to be associated with the wrong year in edge cases near year boundaries.
