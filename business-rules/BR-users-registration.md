# Business Rules: Users & Registration

**Domain:** Users & Registration  
**Extracted from:** SQL dumps (mashpiadb, pointsDB) and PHP source files  
**Date extracted:** 2026-04-30  
**Analyst note:** This is legacy PHP + MySQL code mixing procedural and OO styles. Rules are inferred from table constraints, enum values, column names, and application logic. Code is not complete or well-structured; confidence ratings reflect that.

---

## User Accounts (mashpiadb.users)

```
Rule ID:      BR-REG-001
Category:     User Accounts
Description:  Every user must have a unique user_code (barcode). The system generates
              a random 64-bit integer as the barcode and retries up to 1,000 times if a
              collision is detected. Creation fails if a unique code cannot be found after
              1,000 attempts.
Source:       Soldier.php (generateBarcode), mashpiadb_users.sql
DB Evidence:  UNIQUE KEY `user_code` (`user_code`), VARCHAR(19)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-REG-002
Category:     User Accounts
Description:  Every user must have a unique user_serial. The serial is auto-assigned
              on create as MAX(user_serial) + 1.
Source:       Soldier.php (generateSerial), mashpiadb_users.sql
DB Evidence:  UNIQUE KEY `user_serial` (`user_serial`), mediumint(8) unsigned NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-REG-003
Category:     User Accounts
Description:  A user's gender must be either Male ('M') or Female ('F'). The field is
              optional (nullable) but when set it must be one of these two values.
Source:       mashpiadb_users.sql
DB Evidence:  `gender` enum('M','F') DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-REG-004
Category:     User Accounts
Description:  A user's default language is English. Supported languages include at minimum
              English, Hebrew, and French (implied by the multi-language architecture and
              the lang_id column).
Source:       mashpiadb_users.sql
DB Evidence:  `lang` varchar(20) NOT NULL DEFAULT 'english', `lang_id` int(10) unsigned NOT NULL DEFAULT 1
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-REG-005
Category:     User Accounts
Description:  A user's school_type_id is derived from gender at creation. Boys are assigned
              type_id 2, girls type_id 3. If an explicit type_id is provided, it is adjusted
              to match gender (e.g., type_id 2 for girls becomes 3, type_id 3 for boys
              becomes 2; same adjustment applies for ids 12 and 13).
Source:       Soldier.php (generateSchoolType)
DB Evidence:  `school_type_id` int(10) unsigned NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-REG-006
Category:     User Accounts
Description:  A user can only be deleted if they have zero earned miles (points). Any user
              with earned points cannot be deleted.
Source:       Soldier.php (canDestroy)
DB Evidence:  static $before_destroy = ['canDestroy']
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-REG-007
Category:     User Accounts
Description:  On creation a user is automatically enrolled in campaigns and birthday
              missions are set up for their date of birth. If the date of birth changes
              after creation, birthday missions are regenerated.
Source:       Soldier.php (after_create, updateBirthdayMissions)
DB Evidence:  static $after_create = ['enrollInCampaigns', 'setupBirthdayMissions']
              static $after_update = ['updateBirthdayMissions']
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-REG-008
Category:     User Accounts
Description:  On creation a user inherits their school's program flags: chayolei, chidon,
              and tanya (yan). These determine which programs the user participates in and
              must match the school's settings.
Source:       Soldier.php (afterCreate)
DB Evidence:  `chayolei` tinyint(3) NOT NULL DEFAULT 1, `chidon` tinyint(3) NOT NULL DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-REG-009
Category:     User Accounts
Description:  A user is assigned an initial rank of "Private" (rank_ord = 1) upon creation
              if they do not already have a rank entry.
Source:       Soldier.php (generateRank)
DB Evidence:  INSERT INTO rank_marks (rank_ord, ...) VALUES (1, ...)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-REG-010
Category:     User Accounts
Description:  Users have separate fields for English and Hebrew first and last names
              (first, last, first_he, last_he). Both are required (NOT NULL). Additionally,
              known English/Hebrew name variants are stored in first_known_en, last_known_en,
              first_known_he, and last_known_he.
Source:       mashpiadb_users.sql
DB Evidence:  `first` varchar(128) NOT NULL, `last` varchar(128) NOT NULL,
              `first_he` varchar(128) NOT NULL, `last_he` varchar(128) NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-REG-011
Category:     User Accounts
Description:  A user has Hebrew and Gregorian date of birth fields. The Hebrew date of
              birth (dob_he) is stored as a string and an optional offset (dob_he_offset)
              is stored as a tinyint, defaulting to 0.
Source:       mashpiadb_users.sql
DB Evidence:  `dob` date DEFAULT NULL, `dob_he` varchar(255) NOT NULL,
              `dob_he_offset` tinyint(1) unsigned NOT NULL DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-REG-012
Category:     User Accounts
Description:  Users can belong to optional sub-programs: Pushka, Chayolei, Yan (Tanya),
              Chidon, CKids. Each is a binary flag (tinyint). Defaults: chayolei=1,
              pushka=0, yan=0, chidon=0, ckids=0.
Source:       mashpiadb_users.sql
DB Evidence:  `pushka` tinyint(4) DEFAULT 0, `chayolei` tinyint(3) NOT NULL DEFAULT 1,
              `yan` tinyint(3) unsigned NOT NULL DEFAULT 0,
              `chidon` tinyint(3) unsigned NOT NULL DEFAULT 0,
              `ckids` tinyint(4) DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-REG-013
Category:     User Accounts
Description:  A user's kiosk editing can be restricted. Three states exist: unrestricted
              (empty string), 'off' (no kiosk editing), and 'frozen' (account frozen).
Source:       mashpiadb_users.sql
DB Evidence:  `kiosk_edit` enum('','off','frozen') NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-REG-014
Category:     User Accounts
Description:  A user can only be associated with one school at a time (school_id is a
              single integer column, not a list). A user may optionally belong to a class
              (class_id) and a team (team_id).
Source:       mashpiadb_users.sql
DB Evidence:  `school_id` int(10) DEFAULT NULL, `class_id` int(10) unsigned DEFAULT NULL,
              `team_id` int(10) unsigned DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-REG-015
Category:     User Accounts
Description:  A user who belongs to a non-TH (external) school can have the school name
              recorded as a free-text string (non_th_school) and optionally an ID
              (non_th_school_id). This allows tracking students in schools not in the TH system.
Source:       mashpiadb_users.sql
DB Evidence:  `non_th_school` varchar(65) DEFAULT NULL,
              `non_th_school_id` int(10) unsigned NOT NULL DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-REG-016
Category:     User Accounts
Description:  Users are eligible for three separate programs tracked via dedicated flags:
              chayolei_eligible, chidon_eligible, and khk_eligible. These default to 0
              (not eligible) and must be explicitly set.
Source:       mashpiadb_users.sql
DB Evidence:  `chayolei_eligible` tinyint(3) unsigned NOT NULL DEFAULT 0,
              `chidon_eligible` tinyint(3) unsigned NOT NULL DEFAULT 0,
              `khk_eligible` tinyint(3) unsigned NOT NULL DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-REG-017
Category:     User Accounts
Description:  Parent marking permission is enabled by default (parent_marking = 1). This
              can be toggled off. When fetching markable children for a parent, only users
              where parent_marking = 1 are returned.
Source:       mashpiadb_users.sql, classes/admin.php (get_markable_children)
DB Evidence:  `parent_marking` tinyint(1) unsigned NOT NULL DEFAULT 1
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-REG-018
Category:     User Accounts (pointsDB)
Description:  The pointsDB has a separate users table that mirrors basic user info
              (name, dob, email, address). Each user has an is_active flag (default 1)
              and an is_deleted flag. A deleted user's record is moved to the users_deleted
              table rather than being permanently removed, enabling soft deletion.
Source:       pointsDB_users.sql, pointsDB_users_deleted.sql
DB Evidence:  `is_active` tinyint(1) unsigned DEFAULT 1, `is_deleted` tinyint(4) unsigned DEFAULT NULL
              Table: users_deleted (mirrors users schema)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-REG-019
Category:     User Accounts (pointsDB)
Description:  In the pointsDB, email addresses must be unique per user.
Source:       pointsDB_users.sql
DB Evidence:  UNIQUE KEY `email` (`email`)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-REG-020
Category:     User Accounts (pointsDB)
Description:  Users in pointsDB can be identified by a barcode. Barcodes are indexed
              for fast lookups but are not enforced as unique at the DB level (unlike
              mashpiadb user_code).
Source:       pointsDB_users.sql
DB Evidence:  `bar_code` varchar(20) DEFAULT NULL, KEY `bar_code` (`bar_code`)
Confidence:   High
SME Verified: No
```

---

## Admin Accounts (mashpiadb.admins)

```
Rule ID:      BR-ADM-001
Category:     Admin Accounts
Description:  Admin usernames must be unique across all admins. When creating an account
              via the new_account endpoint, the admin's email address is automatically set
              as the username.
Source:       api/auth/new_account.php, mashpiadb_admins.sql
DB Evidence:  UNIQUE KEY `username` (`username`)
              Code: $admin->username = $admin->admin_email
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-ADM-002
Category:     Admin Accounts
Description:  Admin email addresses must be unique across all admins.
Source:       mashpiadb_admins.sql, api/models/Admin.php
DB Evidence:  UNIQUE KEY `admin_email_UNIQUE` (`admin_email`)
              static $validates_uniqueness_of = [['admin_email', ...]]
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-ADM-003
Category:     Admin Accounts
Description:  An admin account has one of four authorization levels: empty string (no
              level), 'inactive', 'super' (Tzivos Hashem HQ / full access), and
              'ckidssuper' (CKids super admin). Only 'super' admins are treated as HQ.
Source:       mashpiadb_admins.sql, api/models/Admin.php (isHQ), admin_auth.php
DB Evidence:  `auth` enum('','inactive','super','ckidssuper') NOT NULL
              Code: return $this->auth === 'super';
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-ADM-004
Category:     Admin Accounts
Description:  An admin can optionally be linked to a Chabad.org shliach account. Each
              Chabad.org shliach ID must be unique — one TH admin per Chabad.org shliach.
Source:       mashpiadb_admins.sql
DB Evidence:  UNIQUE KEY `chabad_org_shliach_id_UNIQUE` (`chabad_org_shliach_id`)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-ADM-005
Category:     Admin Accounts
Description:  An admin can optionally be linked to a Google account. Each Google ID must
              be unique — one TH admin per Google account.
Source:       mashpiadb_admins.sql
DB Evidence:  UNIQUE KEY `google_id_UNIQUE` (`google_id`)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-ADM-006
Category:     Admin Accounts
Description:  Admin passwords are stored in two forms: a plaintext-encrypted column
              (password, using a symmetric key) and a bcrypt hash (hashed_pass). The
              bcrypt hash is used for password verification. Both are updated together
              on create and when the password changes.
Source:       api/models/Admin.php (hashPassword, hashPasswordIfChanged, authenticate)
DB Evidence:  `hashed_pass` varchar(255) NOT NULL, `password` varchar(64) NOT NULL
              Code: password_hash($pass, PASSWORD_DEFAULT), password_verify(...)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-ADM-007
Category:     Admin Accounts
Description:  An admin can flag themselves as a parent (is_parent). Parent admins have
              access to the parent portal and can view/mark missions for their linked
              children (via admin_auths with auth='user').
Source:       mashpiadb_admins.sql, admin_auth.php, api/models/Admin.php
DB Evidence:  `is_parent` tinyint(1) unsigned NOT NULL DEFAULT 0
              Code: if ($type == "user" && $admin_user["is_parent"] == true) { $allow = true; }
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-ADM-008
Category:     Admin Accounts
Description:  New accounts created via the new_account.php endpoint are automatically
              flagged as beta accounts (beta = 1).
Source:       api/auth/new_account.php
DB Evidence:  `beta` tinyint(1) NOT NULL DEFAULT 0
              Code: $admin->beta = 1;
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-ADM-009
Category:     Admin Accounts
Description:  Admin accounts can optionally have a French title. The allowed French title
              values are: empty string, 'Rav', 'M.', 'Mme', 'Mlle'. The default English
              title is 'Rabbi'.
Source:       mashpiadb_admins.sql
DB Evidence:  `title` varchar(45) NOT NULL DEFAULT 'Rabbi'
              `title_fr` enum('','Rav','M.','Mme','Mlle') NOT NULL DEFAULT 'Rav'
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-ADM-010
Category:     Admin Accounts
Description:  When a new admin account is created, a corresponding Helpdesk account is
              automatically created via a before_create callback. This links the admin
              to the internal helpdesk/support portal.
Source:       api/models/Admin.php (createHelpdeskAccount)
DB Evidence:  static $before_create = ['hashPassword', 'createHelpdeskAccount']
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-ADM-011
Category:     Admin Accounts
Description:  Admin accounts can be linked to a Authorize.net customer profile for credit
              card billing. The Authorize.net customer profile ID is stored and used for
              future charges.
Source:       api/models/Admin.php (customerProfile, createPaymentProfile), mashpiadb_admins.sql
DB Evidence:  `authorize_customer_profile_id` varchar(30) DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-ADM-012
Category:     Admin Accounts
Description:  Admin shipping zone is determined by country. USA (or blank/us/united states)
              = Zone 1; Canada = Zone 2; all other countries = Zone 3. This zone is used
              to calculate shipping rates for kits and materials.
Source:       api/models/Admin.php (shippingZone)
DB Evidence:  `admin_country` varchar(255) NOT NULL
Confidence:   High
SME Verified: No
```

---

## Admin Authorization (mashpiadb.admin_auths)

```
Rule ID:      BR-AUTH-001
Category:     Admin Authorization
Description:  Admin authorization scopes (the resources an admin can access) are stored
              in the admin_auths table. The possible auth scope types are: 'institution',
              'school', 'class', 'team', 'user', 'camp', and 'staff'. Each row ties an
              admin to a specific resource ID within that scope.
Source:       mashpiadb_admin_auths.sql
DB Evidence:  `auth` enum('institution','school','class','team','user','camp','staff')
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-AUTH-002
Category:     Admin Authorization
Description:  The combination of (admin_id, auth, id) is the primary key of admin_auths,
              meaning an admin can have at most one auth record per (scope, resource ID)
              combination. An admin may have multiple schools, classes, etc.
Source:       mashpiadb_admin_auths.sql
DB Evidence:  PRIMARY KEY (`admin_id`,`auth`,`id`)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-AUTH-003
Category:     Admin Authorization
Description:  The access hierarchy from highest to lowest is: super > institution > school
              > class > team > user. A 'super' admin bypasses all scope checks. For school-
              level access, simply having any school auth record is sufficient. For class,
              team, and user scopes, the specific requested ID must match an auth record.
Source:       admin_auth.php (check_id_access)
DB Evidence:  Code: if($admin_user['auth'] == 'super') { return 'super'; }
              Code: if(in_array('school', $admin_auth) && count($admin_user['auths']['school'])) { ... }
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-AUTH-004
Category:     Admin Authorization
Description:  If a school-level admin has exactly one school, that school is automatically
              set as the default active school. If they have multiple schools, the system
              requires a valid school_id to be passed in the request.
Source:       admin_auth.php (check_id_access)
DB Evidence:  Code: if(count($admin_user['auths']['school']) == 1) { sgr($school, ...); }
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-AUTH-005
Category:     Admin Authorization
Description:  School accounts with no registered email address are blocked from accessing
              the system until an email is provided.
Source:       admin_auth.php
DB Evidence:  Code: elseif (!$admin_user['admin_email'] && ... count($admin_user['auths']['school']) > 0)
              { include('missing_email.php'); exit; }
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-AUTH-006
Category:     Admin Authorization
Description:  An admin's effective institution_ids are derived by joining admin_auths with
              schools, classes, teams, and users — not stored directly. This means an
              admin's institution access is always computed dynamically from their scope
              records.
Source:       admin_auth.php (check_auth_admin)
DB Evidence:  SQL: SELECT inst_id FROM schools JOIN admin_auths ON (schools.school_id = admin_auths.id)
              WHERE admin_id = X AND admin_auths.auth = 'school' UNION ...
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-AUTH-007
Category:     Admin Authorization
Description:  Invitations allow an existing admin to invite another person (by email) to
              access a specific resource. Invitation scopes are limited to: 'school',
              'class', 'team', and 'user'. An invitation tracks the inviter's admin_id,
              the target email, and the resource scope/id.
Source:       mashpiadb_invitations.sql
DB Evidence:  `auth` enum('school','class','team','user') NOT NULL
              `admin_id` int(10) unsigned NOT NULL COMMENT 'inviter'
Confidence:   High
SME Verified: No
```

---

## Authentication

```
Rule ID:      BR-AUTHN-001
Category:     Authentication
Description:  Login supports three methods: (1) username+password, (2) Chabad.org OAuth
              key, and (3) Google OAuth token. All prior session cookies are cleared on
              any login attempt (logout before login).
Source:       api/auth/login.php
DB Evidence:  Code: foreach ($_COOKIE as $key => $value) { setcookie($key, $value, $past, '/'); }
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-AUTHN-002
Category:     Authentication
Description:  Username lookup is case-insensitive. The system first attempts to find an
              admin by username, then falls back to finding by email if no username match
              is found.
Source:       api/auth/classes/Auth.php (login)
DB Evidence:  Code: $username = strtolower($username); Admin::find_by_username($username);
              then: Admin::find_by_admin_email($username)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-AUTHN-003
Category:     Authentication
Description:  The legacy authentication token (admin_auth cookie) is an HMAC-ripemd128
              hash of lowercase(username) concatenated with password, keyed with a fixed
              secret. This same algorithm is used in both the legacy PHP pages (admin_auth.php)
              and the modern API (Auth.php).
Source:       admin_auth.php, api/auth/classes/Auth.php (legacyKey)
DB Evidence:  Code: hash_hmac('ripemd128', strtolower($username).$password, '53fdc95857aac68970159dd07e7c3782')
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-AUTHN-004
Category:     Authentication
Description:  On successful login two tokens are returned: a 'legacy' token (HMAC of
              username+password) and a 'mobile' token (symmetric encryption of admin_id).
              These are stored as cookies (admin_auth and admin respectively).
Source:       api/auth/classes/Auth.php (generateKeys)
DB Evidence:  Code: $_COOKIE['admin'] = $mobile; $_COOKIE['admin_auth'] = $legacy; $_COOKIE['admin_id'] = ...
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-AUTHN-005
Category:     Authentication
Description:  Authentication session cookies (admin_id, admin_auth) are set to expire
              90 days (90 * 24 * 60 * 60 seconds) from the time of login.
Source:       admin_auth.php (check_login_admin)
DB Evidence:  Code: setcookie('admin_id', ..., time()+90*24*60*60, '/');
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-AUTHN-006
Category:     Authentication
Description:  If Chabad.org login is attempted but no TH account is linked to the
              Chabad.org shliach, the system returns the Chabad.org shliach's personal
              info and mosad list to allow the user to create a new TH account.
Source:       api/auth/login.php, api/auth/classes/Auth.php (chabadLogin)
DB Evidence:  Code: if ($login instanceof \ChabadShliach) { $data = $login; $login = false; }
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-AUTHN-007
Category:     Authentication
Description:  An admin can connect or disconnect a Chabad.org account or Google account
              to their TH account. Each external account type (chabad, google) can be
              connected to at most one TH admin (enforced by unique constraints).
              Connecting overwrites the existing link.
Source:       api/auth/current_user.php (connectAccount, disconnectAccount)
DB Evidence:  UNIQUE KEY `chabad_org_shliach_id_UNIQUE`, UNIQUE KEY `google_id_UNIQUE`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-AUTHN-008
Category:     Authentication
Description:  To change a username or password, the admin must provide their current
              password (current_password field). If not provided or incorrect, no updates
              are applied.
Source:       api/auth/current_user.php (create)
DB Evidence:  Code: if ($_POST['current_password'] !== $current_user->password)
              return json_error('Current Password incorrect. No updates applied');
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-AUTHN-009
Category:     Authentication
Description:  An admin's login context (the entity they are currently acting as) determines
              their access and module permissions. Login types are: HQ (super admin),
              institution, school (Base Commander / BC), class (Teacher), PARENT, and BLANK
              (no role). HQ and institution logins have access to all modules. School and
              class logins have access only to the modules their school has paid for.
Source:       api/auth/classes/Login.php (setModules, setup)
DB Evidence:  const LEGACY_ID = [2,4]; Code: 'chayolei' => !!$this->model->chayolei, ...
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-AUTHN-010
Category:     Authentication
Description:  A school login is considered 'active' only when the school_era field is NULL.
              If school_era is set (not null), the school is considered inactive/archived
              and the login's active flag is false.
Source:       api/auth/classes/Login.php (setup)
DB Evidence:  Code: $this->active = is_null($this->model->school_era);
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-AUTHN-011
Category:     Authentication
Description:  Mobile logins (parent portal access) are recorded in the mobile_logins table
              with the user_id, admin_id, and current point balance at the time of login.
Source:       mashpiadb_mobile_logins.sql
DB Evidence:  Table: mobile_logins with columns user_id, admin_id, cur_points, date
Confidence:   High
SME Verified: No
```

---

## Password Reset

```
Rule ID:      BR-PWD-001
Category:     Password Reset
Description:  Password reset is only available for admin accounts, not individual soldier
              (user) accounts. Reset is triggered by submitting a registered email address.
Source:       api/auth/forgot.php, api/password_reset.php
DB Evidence:  `admin_id` int(11) unsigned NOT NULL in password_reset table
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PWD-002
Category:     Password Reset
Description:  A password reset token consists of two parts: a 16-character hex selector
              (8 random bytes hex-encoded) and a 64-character hex validator (32 random
              bytes hex-encoded). The validator is stored in the DB as a SHA-256 hash.
Source:       api/models/Admin.php (resetPassword), api/password_reset.php
DB Evidence:  `selector` char(16) NOT NULL, `token` char(64) NOT NULL (sha256 hash of validator)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PWD-003
Category:     Password Reset
Description:  Password reset tokens expire after exactly 1 hour. Expired tokens cannot be
              used and the reset form is hidden with an expiration message.
Source:       api/models/Admin.php (resetPassword), api/password_reset.php
DB Evidence:  `expires` bigint(20) NOT NULL; Code: $expires->add(new DateInterval('PT01H'))
              Code: WHERE selector = :selector AND expires >= :time
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PWD-004
Category:     Password Reset
Description:  Only one active password reset request is allowed per admin at a time. When
              a new reset is requested, all previous reset records for that admin are deleted
              before the new one is inserted.
Source:       api/models/Admin.php (resetPassword)
DB Evidence:  Code: DELETE FROM password_reset WHERE admin_id = X
              UNIQUE KEY `selector_UNIQUE` (`selector`)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PWD-005
Category:     Password Reset
Description:  Once a password reset link is successfully used, all password_reset records
              for that admin are deleted to prevent link reuse.
Source:       api/password_reset.php (updatePassword)
DB Evidence:  Code: DELETE FROM password_reset WHERE admin_id = X
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PWD-006
Category:     Password Reset
Description:  New passwords must not be blank. The reset form requires both a new password
              and a confirmation that matches. Mismatched or blank passwords return an error
              and do not update the account.
Source:       api/password_reset.php
DB Evidence:  Code: if (!$password || $password !== $confirm) $status = 'Error Code: 001 (Mismatched Passwords)'
              Code: if (!$password) return 'Error Code: 006 (Invalid/Blank Password)'
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PWD-007
Category:     Password Reset
Description:  The password reset email is sent to the email address on file for the admin
              account (admin_email). The address is also recorded in the password_reset
              table (sent_to column) for auditing.
Source:       api/models/Admin.php (resetPassword), mashpiadb_password_reset.sql
DB Evidence:  `sent_to` varchar(255) NOT NULL; Code: MashpiaEmails::passwordReset($this->email, ...)
Confidence:   High
SME Verified: No
```

---

## School Registration (mashpiadb.school_registrations)

```
Rule ID:      BR-SREG-001
Category:     School Registration
Description:  A school registration record tracks the school's enrollment for a specific
              program year. The registration includes fee charged, balance owed, early bird
              deadline, optional payment date, and a JSON blob of enabled modules.
Source:       mashpiadb_school_registrations.sql
DB Evidence:  `year` int(10) unsigned NOT NULL, `fee` decimal(10,2) NOT NULL DEFAULT 0.00,
              `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
              `early_bird` datetime NOT NULL, `modules` varchar(255) NOT NULL DEFAULT '{}'
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SREG-002
Category:     School Registration
Description:  Schools must choose a registration type. Three types exist:
              Type 1 = tuition school (school pays for all students),
              Type 2 = school guarantees all children will register by the early-bird date,
              Type 3 = not a tuition school (parents pay the complete fee).
              The default type is 0 (unset).
Source:       api/registration/school_registration.php, mashpiadb_school_registrations.sql
DB Evidence:  `type` int(11) NOT NULL DEFAULT 0
              Code: if ($reg->type == 1) ... if ($reg->type == 2) ... if ($reg->type == 3) ...
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SREG-003
Category:     School Registration
Description:  School registration payment is processed through Authorize.net. If the
              registration amount is 0, no payment is charged but the registration record
              is still saved.
Source:       api/registration/school_registration.php
DB Evidence:  Code: if ($amount > 0) { $response = $customer_profile->chargeCard(...); }
              else { $statusTransaction = true; $response = false; }
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SREG-004
Category:     School Registration
Description:  School registration detail records track payment by program type. The valid
              program types that can be paid for are: 'chayolei', 'chidon', 'tanya',
              'rewards', 'past_due', and 'discount'.
Source:       mashpiadb_school_registration_details.sql
DB Evidence:  `type` enum('chayolei','chidon','tanya','rewards','past_due','discount') NOT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SREG-005
Category:     School Registration
Description:  Accepted payment methods for school registration detail records are: cash,
              check, credit_card, and wire transfer.
Source:       mashpiadb_school_registration_details.sql
DB Evidence:  `method_of_payment` enum('cash','check','credit_card','wire') DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SREG-006
Category:     School Registration
Description:  When a school registers, a notification email is sent to the TH office
              (cth@tzivoshashem.org) including the school's name, year, and registration type.
Source:       api/registration/school_registration.php
DB Evidence:  Code: $to = "cth@tzivoshashem.org"; @mail($to, "School Registration $year", ...)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SREG-007
Category:     School Registration
Description:  A school may have a registration kept open beyond the normal cutoff. The
              keep_reg_open table allows overriding the registration window for specific
              schools, classes, users, or admins.
Source:       mashpiadb_keep_reg_open.sql
DB Evidence:  `type` enum('school','class','user','admin') DEFAULT NULL
              `id` int(10) unsigned DEFAULT NULL, `year` int(10) unsigned DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SREG-008
Category:     School Registration
Description:  Each school can have a brochure count recorded per year. This is a one-
              row-per-school table (school_id is the primary key), so only the most recent
              year's brochure count is stored.
Source:       mashpiadb_registration_brochures.sql
DB Evidence:  PRIMARY KEY (`school_id`); columns: year, brochures
Confidence:   Medium
SME Verified: No
```

---

## User Registration (mashpiadb.user_registration)

```
Rule ID:      BR-UREG-001
Category:     User Registration
Description:  A user can only be registered once per year per program (Chayolei). The
              user_registration table enforces a unique constraint on (user_id, year).
Source:       mashpiadb_user_registration.sql
DB Evidence:  UNIQUE KEY `user` (`user_id`,`year`)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-UREG-002
Category:     User Registration
Description:  User registration must be associated with the admin who performed the
              registration (admin_id is NOT NULL). The registration also records whether
              payment was made (paid column, nullable).
Source:       mashpiadb_user_registration.sql
DB Evidence:  `admin_id` int(10) unsigned NOT NULL, `paid` decimal(6,2) unsigned DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-UREG-003
Category:     User Registration
Description:  If a user is being registered for a future year (next year's program), they
              are also automatically registered for the current year in the same transaction.
Source:       Soldier.php (registerChayolei)
DB Evidence:  Code: if ($year > $cur_yr) { also register for current year ... }
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-UREG-004
Category:     User Registration
Description:  A user must have a school_id assigned to be eligible for registration through
              the parent portal. Users without a school_id are excluded from the list of
              children available for registration.
Source:       api/registration/user_registration.php (getUsers)
DB Evidence:  Code: if (!$user->school_id) continue;
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-UREG-005
Category:     User Registration
Description:  For parent registrations, a registration charge is only inserted if the
              amount paid is greater than zero OR a discount is being applied. If both
              amount and discount are zero and the actor is a parent, only a registration
              confirmation record is stored (reg_confirmations table), not a full registration.
Source:       Soldier.php (registerChayolei)
DB Evidence:  Code: if (!$parent || ($parent && (floatval($amount) > 0 || $discount))) { ... INSERT user_registration }
              else { INSERT reg_confirmations ... }
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-UREG-006
Category:     User Registration
Description:  Base Commander (BC) registrations use the school's calculated soldier fee.
              Parent registrations use a fee calculated from the school's rate schedule.
              Both paths go through the Authorize.net payment gateway.
Source:       api/registration/users.php, api/registration/user_registration.php
DB Evidence:  Code: $fee = $school->soldierFee(); used in both create() and registerChayolei()
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-UREG-007
Category:     User Registration
Description:  Only Base Commanders (login code 'BC') are authorized to perform bulk
              user registration from the school management view.
Source:       api/registration/users.php (authenticate, create)
DB Evidence:  Code: return $current_user->login->code === 'BC';
              Code: if (!$admin->login->code === 'BC') json_error('Only Base Commanders can authorize registration.')
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-UREG-008
Category:     User Registration
Description:  Registration charges are recorded per transaction. Each charge includes the
              type code, amount, applicable year, school, and user. Discounts are tracked
              separately in the discount column and applied against the charge amount.
              Charges can be refunded and a reason for refund is stored.
Source:       mashpiadb_registration_charges.sql
DB Evidence:  `discount` decimal(6,2) unsigned NOT NULL DEFAULT 0.00,
              `refunded` tinyint(3) unsigned NOT NULL DEFAULT 0,
              `refund_reason` varchar(255) DEFAULT '0',
              `keep_charge` tinyint(3) unsigned DEFAULT 0
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-UREG-009
Category:     User Registration
Description:  Newly registered users' welcome kits (cards and stickers) are tracked in the
              newly_registered table, recording when kits were shipped and received. This
              is separate from the registration record itself.
Source:       mashpiadb_newly_registered.sql
DB Evidence:  columns: cards_shipped, cards_received, stickers_shipped, stickers_received (all date)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-UREG-010
Category:     User Registration
Description:  Users who join the Hachayols program have their join event and kit shipping
              tracked in newly_joined (join date, shipped date, received date).
Source:       mashpiadb_newly_joined.sql
DB Evidence:  columns: joined (int), shipped (date), received (date)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-UREG-011
Category:     User Registration
Description:  If a payment fails during registration, the entire registration transaction
              (including any previously created Authorize.net subscription/installment plan)
              is rolled back.
Source:       api/registration/user_registration.php (registerUsers)
DB Evidence:  Code: $MASHPIA_DB->beginTransaction(); ... $MASHPIA_DB->rollBack() on errors;
              Code: $subscription->cancelSubscription(); $subscription->removeFromDb(...)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-UREG-012
Category:     User Registration
Description:  Installment payment plans (subscriptions) are supported for certain
              registration types. The installment amount is subtracted from the total
              charged today, and a separate Authorize.net subscription is created. If the
              installment plan is created but payment fails, the subscription is cancelled.
Source:       api/registration/user_registration.php (registerUsers)
DB Evidence:  Code: $subscription = new Installments(...); $total -= $amount; $this->installmentsCreated = true
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-UREG-013
Category:     User Registration
Description:  A confirmation email is sent to the parent's registered email address after
              successful registration. The email is BCC'd to enrollment@tzivoshashem.org.
              If the child is from a MyShliach school and is registering for Chidon, a CC
              is sent to chidon@myshliach.com.
Source:       api/registration/user_registration.php (sendEmailToParents)
DB Evidence:  Code: $bcc = "enrollment@tzivoshashem.org"; if ($chidon && $detail['school'] == 61) $cc = 'chidon@myshliach.com'
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-UREG-014
Category:     User Registration
Description:  When a child from the Anash Kinder school (school_id = 269) registers for
              either Chayolei or Chidon, a separate notification email is sent to
              anash@tzivoshashem.org.
Source:       api/registration/user_registration.php (sendEmailToAK, registerUsers)
DB Evidence:  Code: if ($user->school_id == 269) { $this->sendEmailToAK($user, ...); }
              $to = 'anash@tzivoshashem.org'
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-UREG-015
Category:     User Registration
Description:  A family is only charged shipping once per year. Before calculating a
              shipping rate, the system checks if any registration_charges of shipping
              types (THAKUSA, THAKCAN, THAKINT, THMSUSA, THMSCAN, THMSINT) exist for
              any child linked to the parent admin for the current registration year.
Source:       api/registration/user_registration.php (getShipping)
DB Evidence:  Code: type IN ('THAKUSA','THAKCAN','THAKINT','THMSUSA','THMSCAN','THMSINT')
              Code: if ($result['paid']) json_response(false);
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-UREG-016
Category:     User Registration
Description:  Shipping rates for the Anash Kinder school are zone-based with a per-child
              increment: Zone 1 (USA) base $67 + $20/additional child; Zone 2 (Canada) base
              $100 + $20/additional child; Zone 3 (international) base $167 + $20/additional
              child. MyShliach school shipping is flat: $35 (Zone 1), $40 (Zone 2), $45
              (Zone 3).
Source:       api/registration/user_registration.php (getShipping)
DB Evidence:  Code: switch ($zone) { case 1: $base = 67; case 2: $base = 100; case 3: $base = 167 }
              Code: if ($myshliach) { case 1: $rate = 35; case 2: $rate = 40; case 3: $rate = 45 }
Confidence:   High
SME Verified: No
```

---

## Chidon Registration (via user_registration.php and Soldier.php)

```
Rule ID:      BR-CHID-001
Category:     Chidon Registration
Description:  Chidon registration is only available to students in grades 3 through 8
              (inclusive). Students outside this grade range are not eligible.
Source:       Soldier.php (registrationStatus)
DB Evidence:  Code: intval($this->platoon->class_grade) >= 3 && intval($this->platoon->class_grade) <= 8
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHID-002
Category:     Chidon Registration
Description:  Chidon registration is only available at schools that have chidon enabled
              (school.chidon = 1) OR for specific exception school IDs (currently 49 and
              192). Additionally, the individual user must have chidon enabled (users.chidon = 1).
Source:       Soldier.php (registrationStatus)
DB Evidence:  Code: (intval($row['school_chidon']) || in_array($this->school_id, [49, 192])) && intval($row['chidon'])
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHID-003
Category:     Chidon Registration
Description:  Chidon registration closes after a fixed deadline (currently set to
              2027-02-12 00:00:00 Eastern Time). After this date, no new Chidon
              registrations or edits are accepted from the parent portal.
Source:       Soldier.php (turnOffChidon, registrationStatus)
DB Evidence:  Code: $targetDate = new DateTime('2027-02-12 00:00:00', $timezone);
              Code: if (self::turnOffChidon()) { $result['chidon'] = true; $result['chidonEdit'] = false; }
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHID-004
Category:     Chidon Registration
Description:  A student can only be registered once for Chidon per year. On subsequent
              registration attempts for the same year, the record is updated (not re-inserted).
Source:       Soldier.php (registerChidon)
DB Evidence:  Code: SELECT user_id FROM th_chidon WHERE year = :year AND user_id = :user
              Code: if ($row) { UPDATE th_chidon ... } else { INSERT INTO th_chidon ... }
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHID-005
Category:     Chidon Registration
Description:  Certain school IDs are explicitly excluded from Chidon registration even if
              the grade and school chidon flag would otherwise qualify them (exception IDs:
              482, 544, 583).
Source:       Soldier.php (registrationStatus)
DB Evidence:  Code: $exceptions = [482,544,583]; ... && !in_array($this->school_id, $exceptions)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHID-006
Category:     Chidon Registration
Description:  Students from certain school IDs that are MyShliach (61) or Anash Kinder
              (269) have Chidon study materials (Study Guide and Chidon Kop Card Game)
              shipped to their home address. Students from other schools have materials
              shipped to their school.
Source:       api/registration/user_registration.php (getInfoForEmail)
DB Evidence:  Code: if (in_array($detail['school'], [61, 269])) "shipped to your house" else "shipped to your school"
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHID-007
Category:     Chidon Registration
Description:  Chidon registration includes four learning tracks: 'maven' (Yesod),
              'pro' (Yediah), 'expert' (Havonah), and 'genius' (Iyun). The selected
              track is stored per registration.
Source:       api/registration/user_registration.php (getInfoForEmail)
DB Evidence:  Code: $tracks = ['maven' => 'Yesod', 'pro' => 'Yediah', 'expert' => 'Havonah', 'genius' => 'Iyun']
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-CHID-008
Category:     Chidon Registration
Description:  Chidon registration records whether a student was recruited by another
              student. The recruiting student's ID is stored (recruited_by). If the recruiter
              changes on an edit, a notification email is sent to the recruiting student.
Source:       Soldier.php (registerChidon), api/registration/user_registration.php
DB Evidence:  Code: if ($row['recruited_by'] != $recruited_by) $this->newRecruit = true;
              Code: $r->sendEmail($recruitedChild)
Confidence:   High
SME Verified: No
```

---

## KHK Enrollment Eligibility

```
Rule ID:      BR-KHK-001
Category:     KHK Enrollment
Description:  A user can be granted KHK (Keren Hashana Kesher) program enrollment
              eligibility for a specific year. Each (user_id, year) combination is unique
              — a user can only be explicitly granted eligibility once per year.
Source:       mashpiadb_khk_enrollment_eligibility.sql
DB Evidence:  UNIQUE KEY `enrollment` (`user_id`,`year`)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-KHK-002
Category:     KHK Enrollment
Description:  During Chidon registration, the system checks a user's KHK eligibility. If
              the user is NOT found in the khk_enrollment_eligibility table for the current
              year, they are NOT eligible for the KHK track (result['khk'] = true means
              ineligible).
Source:       Soldier.php (registrationStatus)
DB Evidence:  Code: $eligible = KHK::enrollmentEligibility([$this->user_id]);
              Code: $result['khk'] = !$eligible[$this->user_id];
Confidence:   High
SME Verified: No
```

---

## Hachayol (Welcome Kit)

```
Rule ID:      BR-HACH-001
Category:     Hachayol
Description:  Only one Hachayol (welcome kit) is given per family per year. When a child
              registers, the system checks if any sibling linked to the same parent already
              has a Hachayol record for that year. If not, a Hachayol is assigned to the
              most appropriate child (youngest grade, grade <= 5 or Pre1a preferred).
Source:       Soldier.php (checkHachayol, getChildForHachayol)
DB Evidence:  Code: SELECT h.user_id FROM hachayols_to_give WHERE aa.admin_id = :admin_id AND h.year = :year
              Code: if (($isNumeric && $gradeNum <= 5) || strcasecmp($gradeRaw, 'Pre1a') === 0)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-HACH-002
Category:     Hachayol
Description:  Australian schools (as configured in GlobalSettings) are excluded from the
              Hachayol distribution logic. Students from Australian schools do not receive
              Hachayols.
Source:       Soldier.php (checkHachayol, getEligibleChildren)
DB Evidence:  Code: $australianSchools = GlobalSettings::getAustralian();
              Code: if (!in_array($this->school_id, $australianSchools)) { $this->addHachayol(...) }
Confidence:   High
SME Verified: No
```

---

## Registration Confirmation (mashpiadb.reg_confirmations)

```
Rule ID:      BR-CONF-001
Category:     Registration Confirmation
Description:  For tuition-type schools (reg_type = 1), when a parent confirms their child's
              registration information (without a payment), a confirmation record is stored
              in reg_confirmations. This records the admin (parent), user, and year, but
              does not insert a payment-bearing registration record.
Source:       Soldier.php (registerChayolei), mashpiadb_reg_confirmations.sql
DB Evidence:  Code: INSERT INTO reg_confirmations SET year = :year, admin_id = :admin, user_id = :user
Confidence:   High
SME Verified: No
```

---

## New Account Creation

```
Rule ID:      BR-NACC-001
Category:     New Account Creation
Description:  When creating a new admin account, passwords must be provided and must match
              a confirmation field. If they do not match, account creation fails.
Source:       api/auth/new_account.php
DB Evidence:  Code: if (!isset($_POST['password']) || !isset($_POST['confirm']) || $_POST['password'] !== $_POST['confirm'])
              return json_error('Could not create TH Account', ['Passwords do not match'])
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-NACC-002
Category:     New Account Creation
Description:  A valid email address is required to create a new admin account. The system
              validates the email with PHP's FILTER_VALIDATE_EMAIL filter.
Source:       api/auth/new_account.php
DB Evidence:  Code: if (!isset($_POST['admin_email']) || !filter_var($_POST['admin_email'], FILTER_VALIDATE_EMAIL))
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-NACC-003
Category:     New Account Creation
Description:  When a new admin is created through the new account flow (e.g., from Chabad.org
              sign-in), they can be linked to one or more Chabad.org mosdos (schools). For
              each selected mosad, a school record is created or found by mosad_id, and the
              admin is connected to that school with 'school' auth type. New schools created
              this way are assigned to institution_id 10 (C-kids base) and the current year
              as their school_era.
Source:       api/auth/new_account.php
DB Evidence:  Code: $school->inst_id = 10; $school->school_era = $year;
              AdminAuth::create(['auth' => 'school', ...])
Confidence:   High
SME Verified: No
```

---

## Registration Orders (pointsDB.registration_orders)

```
Rule ID:      BR-RORD-001
Category:     Registration Orders
Description:  Registration orders in pointsDB track the full order details for kiosk
              accessories and physical kit items: regular kiosks, camper kiosks, sponsored
              kiosks, kiosk rentals, scanners, and handbooks.
Source:       pointsDB_registration_orders.sql
DB Evidence:  `kioskaccessories_regular`, `kioskaccessories_campers`, `kioskaccessories_sponsored`,
              `kioskaccessories_rental`, `kioskaccessories_scanner`, `kioskaccessories_handbook`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-RORD-002
Category:     Registration Orders
Description:  Each registration order has a user-visible confirmation code (16 chars) and
              an API-generated confirmation code (up to 30 chars). Both may be present for
              cross-system traceability.
Source:       pointsDB_registration_orders.sql
DB Evidence:  `user_confirmation_code` varchar(16) NOT NULL DEFAULT '',
              `api_confirmation_code` varchar(30) DEFAULT ''
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-RORD-003
Category:     Registration Orders
Description:  Credit card data stored in registration_orders is masked — only the last 4
              digits of the card number are retained. The CCV field is also stored
              (up to 8 chars), which is a PCI compliance concern.
Source:       pointsDB_registration_orders.sql
DB Evidence:  `creditcard_number` varchar(4) DEFAULT NULL,
              `creditcard_ccv` varchar(8) DEFAULT NULL
Confidence:   Medium
SME Verified: No
```

---

## General Registration Workflow

```
Rule ID:      BR-WF-001
Category:     Registration Workflow
Description:  The registration table (mashpiadb.registration) tracks school-level program
              registrations. Each record ties a school to a year and includes flags for
              optional add-on modules: whatsapp, tutorial, chavrusaEn, chavrusaHe,
              library, birthday, and mishmor. These represent program features the school
              can opt into.
Source:       mashpiadb_registration.sql
DB Evidence:  `whatsapp` tinyint(3) unsigned NOT NULL DEFAULT 0,
              `tutorial` tinyint(3) unsigned NOT NULL DEFAULT 0,
              `chavrusaEn` tinyint(3) unsigned NOT NULL DEFAULT 0, etc.
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-WF-002
Category:     Registration Workflow
Description:  The registration record optionally captures the shipping option (ship_option),
              shipping destination (ship_dest), enrolled users list, and extra shipping notes.
              These are used to manage material distribution.
Source:       mashpiadb_registration.sql
DB Evidence:  `ship_option` tinyint(4) DEFAULT NULL, `ship_dest` varchar(45) DEFAULT NULL,
              `users` varchar(255) DEFAULT NULL, `extra_shipping` varchar(255) DEFAULT NULL
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-WF-003
Category:     Registration Workflow
Description:  When a parent registers a child, if there are pre-existing marks for that
              user in an archived marks table (date_tasks_marks_old), those marks are
              migrated to the active marks table (date_tasks_marks) as part of the
              registration process.
Source:       Soldier.php (registerChayolei, moveMarksFromArchive)
DB Evidence:  Code: INSERT INTO date_tasks_marks SELECT * FROM date_tasks_marks_old WHERE user_id = :user
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-WF-004
Category:     Registration Workflow
Description:  When registered via the standard Chayolei program (type 'THE'), users have
              hachayols and medals_ranks set to 1 (enabled). When registered via Chayolei
              Lite or CKids, these features are disabled (set to 0).
Source:       Soldier.php (registerChayolei)
DB Evidence:  Code: if ($type == 'THE') { $this->hachayols = 1; $this->medals_ranks = 1; }
              else { $this->hachayols = 0; $this->medals_ranks = 0; }
Confidence:   High
SME Verified: No
```
