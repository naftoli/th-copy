# Business Rules: Helpdesk, Registration, Camps, Donate, Screens

## Directory Inventory

- `/mashpia.com/public/helpdesk/` — Third-party helpdesk system (MsgSuite). Key files: `close-tickets.php`, `email-digest.php`
- `/mashpia.com/public/registration/` — Admin-facing registration lookup and unregister tools: `getRegistration.php`, `unregister.php`
- `/mashpia.com/public/camps/` — Full camp management system: registration, campaigns, missions, tasks, divisions, prizes, scoring
- `/mashpia.com/public/donate/` — Standalone donation pages: `donate.php`, `donate2.php`
- `/mashpia.com/public/screens/` — Digital screen kiosk management: `display.php`, `index.php`, `hq/`, `ajax/`
- `/mashpia.com/public/api/registration/user_registration.php` — Parent-facing user registration API
- `/mashpia.com/public/api/registration/school_registration.php` — School-facing school registration API
- `/mashpia.com/public/api/registration/store_resets.php` — Static date reference for store reset info

---

## Helpdesk / Support

```
Module: Helpdesk
Rule:   A ticket is only eligible for auto-close if its most recent reply was made by an admin (not the visitor), the ticket is still open, it is not flagged as spam, it is not in "waiting" assignment state, and the admin's last reply occurred at least N days ago (where N is the configurable `autoClose` setting).
Source: helpdesk/close-tickets.php:27-75
```

```
Module: Helpdesk
Rule:   When an auto-close is triggered, the system logs a history entry on the ticket recording that the ticket was automatically closed after N days of inactivity.
Source: helpdesk/close-tickets.php:93
```

```
Module: Helpdesk
Rule:   If auto-close email notification is enabled (`autoCloseMail = 'yes'`), the system sends a single grouped email to the visitor covering all tickets that were auto-closed in that batch run, rather than one email per ticket.
Source: helpdesk/close-tickets.php:99-195
```

```
Module: Helpdesk
Rule:   If a closed ticket was originally opened via an IMAP source and the department has a configured IMAP email, the auto-close notification reply-to address is set to that IMAP address so that visitor replies re-open the ticket via email.
Source: helpdesk/close-tickets.php:111-116
```

```
Module: Helpdesk
Rule:   If disputes are enabled and the auto-closed ticket was disputed, the system also sends a separate notification to all other participants in the dispute thread.
Source: helpdesk/close-tickets.php:119-158
```

```
Module: Helpdesk
Rule:   The email digest cron job always runs auto-close first before sending the digest, ensuring the digest counts reflect the most current ticket states.
Source: helpdesk/email-digest.php:33
```

```
Module: Helpdesk
Rule:   The digest email is only sent to a staff member if that staff member has both `notify = 'yes'` and `digest = 'yes'` set on their account.
Source: helpdesk/email-digest.php:40-42
```

```
Module: Helpdesk
Rule:   The digest email is only dispatched if there is at least one ticket to report on (i.e., the sum of all ticket category counts is greater than zero).
Source: helpdesk/email-digest.php:554
```

```
Module: Helpdesk
Rule:   The digest categorizes tickets into eight buckets: (1) awaiting assignment, (2) new/no replies, (3) awaiting staff response, (4) awaiting visitor response, (5) new disputes, (6) disputes awaiting staff, (7) disputes awaiting visitor, (8) spam (IMAP-sourced only).
Source: helpdesk/email-digest.php:108-548
```

```
Module: Helpdesk
Rule:   Only staff members with user ID 1 (super admin) or with the 'assign' page permission see the "awaiting assignment" bucket; only those with the 'spam' page permission see the spam bucket.
Source: helpdesk/email-digest.php:80-88
```

```
Module: Helpdesk
Rule:   Non-super-admin staff members who are not set to receive all-assigned tickets are scoped to only their assigned departments; those set to receive all-assigned tickets see only tickets explicitly assigned to them.
Source: helpdesk/email-digest.php:95-102, 230-231
```

```
Module: Helpdesk
Rule:   FAQ entries have a date-based visibility window: `date_start` defaults to '00000000000000' (always visible from the past) and `date_end` defaults to '99991231235959' (never expires), with active/inactive controlled separately by the `active` field.
Source: SQLdump/mashpiadb_faqdata.sql:42-43
```

```
Module: Helpdesk
Rule:   FAQ users have a unique login and a unique session_id; sessions are tracked with a timestamp and source IP, enabling concurrent session detection.
Source: SQLdump/mashpiadb_faquser.sql:25-37
```

---

## Registration (User)

```
Module: Registration (User)
Rule:   Only authenticated parents (users with at least one child in `admin_auths` with type 'user') can access the user registration API.
Source: api/registration/user_registration.php:25-28
```

```
Module: Registration (User)
Rule:   A user (child) is only surfaced to the parent for registration if the child has a `school_id` set; children without a school assignment are silently excluded.
Source: api/registration/user_registration.php:45-47
```

```
Module: Registration (User)
Rule:   The system enforces that each cart item's `paid` amount is numeric before any payment is attempted; a non-numeric amount causes the entire registration to abort with an error.
Source: api/registration/user_registration.php:165-168
```

```
Module: Registration (User)
Rule:   If the parent's total charge is non-zero, the system attempts to use an existing payment profile if one is provided; otherwise it creates a new Authorize.Net payment profile on the spot before charging.
Source: api/registration/user_registration.php:190-200
```

```
Module: Registration (User)
Rule:   If an installment plan is selected, the installment amount is calculated only from line items with type 'advance registration'; the installment subscription is created before the remaining balance is charged.
Source: api/registration/user_registration.php:202-233
```

```
Module: Registration (User)
Rule:   If the installment subscription creation fails or the subsequent card charge fails, the entire transaction is rolled back atomically and the subscription is cancelled if it was already created.
Source: api/registration/user_registration.php:264-272
```

```
Module: Registration (User)
Rule:   A Chayolei registration is only written if the child's current registration status shows they are not already registered for the Chayolei program for that year; duplicate registrations are silently skipped.
Source: api/registration/user_registration.php:316-319
```

```
Module: Registration (User)
Rule:   For Chidon registrations, if the `editingOnly` flag is set the system performs an update of an existing Chidon record rather than creating a new one; new registrations and edits both invoke the same `registerChidon` method but follow different confirmation paths.
Source: api/registration/user_registration.php:346-399
```

```
Module: Registration (User)
Rule:   When a Chidon registration or edit changes the recruiter, the system sends a recruitment notification email to the newly assigned recruiter.
Source: api/registration/user_registration.php:371-376, 437-441
```

```
Module: Registration (User)
Rule:   For students enrolled in Anash Kinder (school_id 269), the system sends a separate notification email to anash@tzivoshashem.org on every Chayolei or Chidon registration.
Source: api/registration/user_registration.php:335-338, 444-447
```

```
Module: Registration (User)
Rule:   For Chidon registrations from MyShliach (school_id 61), the confirmation email sent to the parent is also CC'd to chidon@myshliach.com.
Source: api/registration/user_registration.php:771-778
```

```
Module: Registration (User)
Rule:   Shipping charges apply only when the child's school is MyShliach (school_id 61) or Anash Kinder (school_id 269); children in all other schools do not incur a shipping charge.
Source: api/registration/user_registration.php:84-101
```

```
Module: Registration (User)
Rule:   Shipping fees for Anash Kinder are zone-based with a base rate (USA $67, Canada $100, International $167) plus $20 per additional child beyond the first.
Source: api/registration/user_registration.php:104-126
```

```
Module: Registration (User)
Rule:   Shipping fees for MyShliach are flat by zone (USA $35, Canada $40, International $45) regardless of child count.
Source: api/registration/user_registration.php:126-145
```

```
Module: Registration (User)
Rule:   If the family has already paid a shipping charge for the current year (any charge type in THAKUSA, THAKCAN, THAKINT, THMSUSA, THMSCAN, THMSINT), the shipping calculation returns false and no shipping fee is presented.
Source: api/registration/user_registration.php:62-81
```

```
Module: Registration (User)
Rule:   For MyShliach and Anash Kinder Chidon registrations, an additional line-item charge code for the study guide (MYSLDS-10 or AKLDS-10, $10) is broken out separately; Anash Kinder also has an additional book charge (AKLDBC-20, $20).
Source: api/registration/user_registration.php:405-425
```

```
Module: Registration (User)
Rule:   For Chidon registrations from MyShliach or Anash Kinder schools, the confirmation email notes that the Study Guide and Chidon Kop will be shipped to the home; for all other schools it states materials are shipped to the school.
Source: api/registration/user_registration.php:616-620
```

```
Module: Registration (User)
Rule:   When a book is purchased at the time of registration, the purchase is recorded with location, store name, store city, and book version; purchases made through a parent's account are recorded with location 'parent_account'.
Source: api/registration/user_registration.php:428-433, 472-473
```

```
Module: Registration (User)
Rule:   The Chidon has four participation tracks: maven (Yesod), pro (Yediah), expert (Havonah), and genius (Iyun); the selected track is stored at registration time and included in the confirmation email.
Source: api/registration/user_registration.php:561-565
```

```
Module: Registration (User)
Rule:   Registration confirmation emails are BCC'd to enrollment@tzivoshashem.org on every successful enrollment or update.
Source: api/registration/user_registration.php:769
```

```
Module: Registration (User)
Rule:   Each user can only be registered once per year; the `user_registration` table enforces a unique constraint on (user_id, year).
Source: SQLdump/mashpiadb_user_registration.sql:35
```

```
Module: Registration (User)
Rule:   Newly registered users are tracked in the `newly_registered` table with shipment status fields (cards_shipped, cards_received, stickers_shipped, stickers_received) to manage physical material fulfillment.
Source: SQLdump/mashpiadb_newly_registered.sql:25-33
```

```
Module: Registration (User)
Rule:   Registration can be kept open beyond the normal cutoff for specific schools, classes, individual users, or admins by inserting an override record into `keep_reg_open`; the override is year-specific.
Source: SQLdump/mashpiadb_keep_reg_open.sql:25-31
```

```
Module: Registration (User)
Rule:   Unregistering a user from Chayolei removes the `user_registered` timestamp, deletes the `user_registration` row, and deletes associated 'THE' registration charges — all within a transaction that rolls back on any failure.
Source: registration/unregister.php:56-76
```

```
Module: Registration (User)
Rule:   Unregistering a user from Chidon deletes the `th_chidon` row and the `chidon_user_prizes` rows for that user and year — all within a transaction.
Source: registration/unregister.php:40-57
```

```
Module: Registration (User)
Rule:   Only a super admin can perform unregistration; school-level admins are explicitly blocked.
Source: registration/unregister.php:5-8
```

```
Module: Registration (User)
Rule:   A registration confirmation record (reg_confirmations) links an admin, a user, and a year, providing an audit trail of who confirmed each registration.
Source: SQLdump/mashpiadb_reg_confirmations.sql:25-31
```

---

## Registration (School)

```
Module: Registration (School)
Rule:   School registration requires authentication; only logged-in admin users can call the registration endpoint.
Source: api/registration/school_registration.php:2-3
```

```
Module: Registration (School)
Rule:   If the payment amount is greater than zero, the system charges the school's card on file via Authorize.Net before recording the registration; a zero amount skips the payment step and goes directly to registration.
Source: api/registration/school_registration.php:17-43
```

```
Module: Registration (School)
Rule:   A failed Authorize.Net charge with error code E00040 (invalid payment profile) is translated to a user-friendly message "Invalid Card on File, Please go back and update it" rather than the raw API error.
Source: api/registration/school_registration.php:21-23
```

```
Module: Registration (School)
Rule:   After a successful charge and registration, an email notification is sent to cth@tzivoshashem.org with the school name and registration type description.
Source: api/registration/school_registration.php:48-61
```

```
Module: Registration (School)
Rule:   Three school registration types exist: type 1 = tuition school (pays for all students), type 2 = school that guarantees all children will register by the early-bird deadline, type 3 = non-tuition school where parents pay separately.
Source: api/registration/school_registration.php:55-60
```

```
Module: Registration (School)
Rule:   School registration payment line items can be of types: 'chayolei', 'chidon', 'tanya', 'rewards', 'past_due', or 'discount', as recorded in `school_registration_details`.
Source: SQLdump/mashpiadb_school_registration_details.sql:27
```

```
Module: Registration (School)
Rule:   Payment methods accepted for school registrations are: cash, check, credit_card, or wire.
Source: SQLdump/mashpiadb_school_registration_details.sql:29
```

```
Module: Registration (School)
Rule:   Each school registration record tracks a child_fee, a total fee, a balance, an early-bird deadline datetime, and a JSON modules blob for enabling/disabling program modules.
Source: SQLdump/mashpiadb_school_registrations.sql:35-41
```

---

## Camps

```
Module: Camps
Rule:   Each camp must have a globally unique camp_number (auto-incremented from the maximum existing value) and a unique name within its institution.
Source: SQLdump/mashpiadb_camps.sql:68-71, camps/register_camp.php:38
```

```
Module: Camps
Rule:   When a new camp is registered, its admin account is created with `auth = 'inactive'`; the camp director cannot log in until the account is manually activated.
Source: camps/register_camp.php:63
```

```
Module: Camps
Rule:   When a new camp is registered, the system automatically installs default group types and their associated default divisions from the `default_group_types` and `default_divisions` tables; camps do not start empty.
Source: camps/register_camp.php:71-88
```

```
Module: Camps
Rule:   Camp registration prevents duplicate camp names — before submitting the form, the UI checks the camp name against existing records and blocks submission if the name is taken.
Source: camps/register_camp.php:163-190
```

```
Module: Camps
Rule:   Each camp must declare a gender: M (Boys), F (Girls), or B (Both); gender is enforced at the camp level, not at the member level.
Source: SQLdump/mashpiadb_camps.sql:34
```

```
Module: Camps
Rule:   Camps support two shipping methods: 'pickup' or 'deliver'; the shipping contact and address are stored separately from the camp address.
Source: SQLdump/mashpiadb_camps.sql:52-61
```

```
Module: Camps
Rule:   Camps can optionally be split into two sessions (session_one and session_two), each with their own start and end dates, independently of the overall camp start/end dates.
Source: SQLdump/mashpiadb_camps.sql:63-67
```

```
Module: Camps
Rule:   The 'home_camp' setting on a camp is a flag stored as a set column, marking whether the camp is the home institution's main camp.
Source: SQLdump/mashpiadb_camps.sql:32
```

```
Module: Camps
Rule:   Global campaigns define the master template (name, point value, group_task flag, camp_type_id) that camps install locally; when a camp installs a global campaign it becomes a `camp_campaign` record specific to that camp.
Source: SQLdump/mashpiadb_global_campaigns.sql:25-33, camps/camp_campaigns.php:23-42
```

```
Module: Camps
Rule:   A campaign can be deactivated at the camp level (setting `active = 0`) without deleting it; deactivated campaigns are excluded from active scoring.
Source: SQLdump/mashpiadb_camp_campaigns.sql:32, camps/camp.php:175-183
```

```
Module: Camps
Rule:   Each campaign combination (camp_id, campaign_id) is unique within a camp — the same global campaign cannot be installed more than once per camp.
Source: SQLdump/mashpiadb_camp_campaigns.sql:34
```

```
Module: Camps
Rule:   Missions within a campaign carry a `sequence` field that controls the order in which missions are presented within that campaign; sequence is mandatory.
Source: SQLdump/mashpiadb_camp_missions.sql:27
```

```
Module: Camps
Rule:   Tasks within a mission carry both a `camp_type_id` (the type of camper/group) and a `level_id` (camper level), allowing the same mission to have different tasks for different camper types or levels.
Source: SQLdump/mashpiadb_camp_tasks.sql:29-30
```

```
Module: Camps
Rule:   Task names within a mission must be unique (unique constraint on camp_mission_id + task_name); the same task can appear in different missions but not twice in the same mission.
Source: SQLdump/mashpiadb_camp_tasks.sql:35
```

```
Module: Camps
Rule:   When assigning a task to a group, the system assigns it to every member currently active in that group (end_date = 0) across every weekday in the task's period, inserting one `member_tasks` row per member per task date.
Source: camps/camp.php:36-76
```

```
Module: Camps
Rule:   Task assignment can target either a group_type (all groups of a given type across the camp, or all groups in the camp) or a single division; these are mutually exclusive targeting modes.
Source: camps/camp.php:14-26
```

```
Module: Camps
Rule:   When installing a prize, a camp copies a global prize template to its local `prizes_camp` table with `installed = 1`; uninstalling sets `installed = 0` rather than deleting.
Source: camps/camp.php:109-127
```

```
Module: Camps
Rule:   Division names within a group type must be unique; attempting to add a division with an existing name in the same group type returns error_code 1 without inserting.
Source: camps/campDivision.php:73-100
```

---

## Donate / Sponsorships

```
Module: Donate
Rule:   All donation submissions must pass Google reCAPTCHA verification before any payment or database action is taken; a failed captcha aborts the process immediately.
Source: donate/donate.php:21-40, donate/donate2.php:21-40
```

```
Module: Donate
Rule:   If the donor selects the custom "other" amount (represented as -1 in the amount field), the system uses the value from the separate `other` input field as the donation amount.
Source: donate/donate.php:43-46, donate/donate2.php:43-46
```

```
Module: Donate
Rule:   The donation amount must be a positive integer; a zero or negative amount aborts the submission with the error "You have not entered a valid amount!".
Source: donate/donate.php:46-50, donate/donate2.php:46-50
```

```
Module: Donate
Rule:   All fields (card number, expiry, first name, last name, address, city, state, zip, email, phone, CVV) are mandatory; any missing field aborts the submission with "All fields are mandatory, please try again."
Source: donate/donate.php:69-73, donate/donate2.php:69-73
```

```
Module: Donate
Rule:   A donation confirmation email is sent to the donor after a successful charge, stating the charged amount, the Authorize.Net authorization ID, and the tax-deductible receipt notice (Tzivos Hashem is 501(c)3, Tax ID: 11-2872082, no goods or services provided).
Source: donate/donate.php:116-118, donate/donate2.php:116-118
```

```
Module: Donate
Rule:   Successful donation transactions are recorded in `all_donations` with email, phone, amount, Authorize.Net response, donor name, and address.
Source: donate/donate.php:99-106, donate/donate2.php:99-106
```

```
Module: Donate
Rule:   Requests originating from IP 39.53.201.236 are blocked before any processing occurs and are redirected with a "Go Away!" message.
Source: donate/donate.php:7-11, donate/donate2.php:7-10
```

```
Module: Donate
Rule:   The `donations` table supports a reason and family field for donor intent, and a dedication field for in-honor-of or in-memory-of notes.
Source: SQLdump/mashpiadb_donations.sql:38-40
```

```
Module: Donate
Rule:   Sponsorships have a date range (start_date, end_date), a sponsor name, a reason, and an optional honoree name and image; they track the amount paid separately from the sponsorship details.
Source: SQLdump/mashpiadb_sponsorships.sql:25-38
```

---

## Screens / Kiosks

```
Module: Screens/Kiosks
Rule:   Every screen access requires a PIN (stored as plain text); visitors who have not entered the PIN see a PIN entry form and cannot view screen content.
Source: screens/display.php:62-161, screens/ajax/addScreen.php:50
```

```
Module: Screens/Kiosks
Rule:   Screen authentication is session-based and scoped to the combination of school_id and screen_slug, meaning the same browser must re-authenticate for each distinct screen.
Source: screens/display.php:67-82
```

```
Module: Screens/Kiosks
Rule:   Screens are scoped to a school; a (school_id, url) pair must be globally unique, preventing two screens at the same school from having the same URL slug.
Source: SQLdump/mashpiadb_screens.sql:37, screens/ajax/addScreen.php:62-68
```

```
Module: Screens/Kiosks
Rule:   A screen URL is auto-generated by slugifying the screen name (lowercase, non-alphanumeric characters replaced by hyphens) if not manually specified; manual URLs are cleaned through the same slugification.
Source: screens/ajax/addScreen.php:5-11, 53-59
```

```
Module: Screens/Kiosks
Rule:   Screen size must be selected from a fixed set of values: 1920x1080, 1366x768, 1280x720, 1024x768, or 800x600; the rendered content uses exact pixel dimensions from the selected size.
Source: screens/display.php:163-178, screens/index.php:389-396
```

```
Module: Screens/Kiosks
Rule:   Screen content is organized into six sections: Announcements (top band), Promotions, Birthdays, Tehillim quota completions, Screen Images, and two placeholder stats sections (Chayolei Stats, Chidon Stats — not yet implemented).
Source: screens/display.php:553-631
```

```
Module: Screens/Kiosks
Rule:   Announcements are only fetched and displayed when at least one of `show_chidon` or `show_chayolei` is enabled for the screen; if both are disabled, the announcements section shows "Announcements disabled".
Source: screens/display.php:861-903
```

```
Module: Screens/Kiosks
Rule:   Announcements are filtered to the current date range using from_date and to_date; only announcements where today falls within their date range are shown.
Source: screens/ajax/getScreenAnnouncements.php:15-17
```

```
Module: Screens/Kiosks
Rule:   If only Chidon announcements are enabled (not Chayolei), the query filters to type = 'chidon'; if only Chayolei is enabled, it filters to type = 'chayolei'; if both are enabled, no type filter is applied and all current announcements are shown.
Source: screens/ajax/getScreenAnnouncements.php:19-24
```

```
Module: Screens/Kiosks
Rule:   Promotions can be filtered by gender (Boys, Girls, or All) at the screen level; the gender preference is stored per screen in `screen_settings`.
Source: screens/display.php:644-646, SQLdump/mashpiadb_screen_settings.sql:34
```

```
Module: Screens/Kiosks
Rule:   Birthdays can be filtered by gender (Boys, Girls, or All) independently of the promotions gender filter; each screen stores separate gender preferences for promotions and birthdays.
Source: screens/display.php:726-728, SQLdump/mashpiadb_screen_settings.sql:38
```

```
Module: Screens/Kiosks
Rule:   Promotions and birthdays each have a configurable lookback window (1–90 days); the promotions_days and birthdays_days settings default to 7 days if not set.
Source: SQLdump/mashpiadb_screen_settings.sql:30-35, screens/index.php:478-513
```

```
Module: Screens/Kiosks
Rule:   The screen content auto-refreshes every 5 minutes (300,000 ms) without requiring a page reload.
Source: screens/display.php:926
```

```
Module: Screens/Kiosks
Rule:   The Tehillim section shows students who have met or exceeded their Shabbos Mevorchim Tehillim quota for the most recently passed Shabbos Mevorchim; it always reflects the latest SM period, not all time.
Source: screens/ajax/getTehillim.php:20-48
```

```
Module: Screens/Kiosks
Rule:   Scrolling animation is only applied to a content list when it contains more than 8 items; fewer than 8 items display statically.
Source: screens/display.php:636
```

```
Module: Screens/Kiosks
Rule:   Screen images are visibility-controlled per screen through a JSON metadata file keyed by school_id; only images where `visible = true` and `screen_id` matches the current screen are displayed.
Source: screens/display.php:186-203
```

```
Module: Screens/Kiosks
Rule:   Screens have independent toggles for showing Chayolei announcements and Chidon announcements from HQ; both default to enabled (checked) when creating a new screen.
Source: screens/index.php:586-598, SQLdump/mashpiadb_screen_settings.sql:37-38
```

```
Module: Screens/Kiosks
Rule:   Announcement entries can carry optional text (with configurable font size) and/or an image (with configurable max height) independently; a missing text or image simply omits that element from display.
Source: screens/display.php:874-886
```

```
Module: Screens/Kiosks
Rule:   Announcements can optionally be scoped to specific schools or specific classes via `limit_to_schools` and `limit_to_classes` fields (though the display query does not yet filter on these; they are stored for future use).
Source: SQLdump/mashpiadb_screen_announcements.sql:28-29
```

```
Module: Screens/Kiosks
Rule:   HQ-created announcements are distinguished from school-created ones via the `created_by_hq` flag (default 1 = HQ); the flag is present but display logic currently does not separate them differently.
Source: SQLdump/mashpiadb_screen_announcements.sql:27
```

---

## Campaigns (pointsDB)

```
Module: Campaigns (pointsDB)
Rule:   A campaign can be set as `default_installed` (flag = 1), meaning it is automatically available to all institutions without an explicit install action.
Source: SQLdump/pointsDB_campaigns.sql:29
```

```
Module: Campaigns (pointsDB)
Rule:   Each campaign has independent feature flags for points, medals, and ranks; all three default to enabled (1) but can each be individually disabled.
Source: SQLdump/pointsDB_campaigns.sql:44-46
```

```
Module: Campaigns (pointsDB)
Rule:   A campaign marked `is_editable = 0` (the default) cannot be modified by non-HQ users; only campaigns with `is_editable = 1` can be edited.
Source: SQLdump/pointsDB_campaigns.sql:47
```

```
Module: Campaigns (pointsDB)
Rule:   Each campaign can belong to either a specific institution or a specific network, but not both simultaneously (both fields nullable); campaigns without either are global templates.
Source: SQLdump/pointsDB_campaigns.sql:30-31
```

```
Module: Campaigns (pointsDB)
Rule:   The `ladder` field on a campaign controls a point threshold or progression level for that campaign; it is optional and may be null if the campaign has no ladder mechanic.
Source: SQLdump/pointsDB_campaigns.sql:43
```
