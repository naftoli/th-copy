# Business Rules: Reports, Shipping, Accounting, Payments

Extracted from source code in `/mashpia.com/public/accounting/`, `/mashpia.com/public/reports/`, `/mashpia.com/public/chayolei_shipping/`, `/mashpia.com/public/chidon_shipping/`, `/mashpia.com/public/api/payments/`, `/mashpia.com/public/api/points/`, and related SQL schemas.

---

## Accounting / Financial

```
Module: Accounting — School Payment Entry
Rule:   A payment can only be added to a school account if that school has already registered for the current year; attempting to add a payment to an unregistered school is rejected with an error.
Source: accounting/api/addPayment.php:63
```

```
Module: Accounting — School Payment Entry
Rule:   Only admins with auth level "super" are permitted to add school payments; all other roles are denied.
Source: accounting/api/addPayment.php:6-8
```

```
Module: Accounting — School Settings
Rule:   Each school has five configurable fee fields that drive billing: chayolei_fee, chidon_fee, prior balance, child_fee (per-student fee), and early_bird deadline; only super admins may update these.
Source: accounting/api/saveBase.php
```

```
Module: Accounting — Registration Report Balance Calculation
Rule:   The per-soldier registration fee is calculated via GlobalSettings::calculateChildFee(), which takes registration type (Tuition/Guaranteed/Regular), the school's custom child_fee, and whether the soldier registered before the early-bird deadline, and whether the school is school_id 61 or 269 (special-case schools); this fee is then multiplied by the number of registered soldiers to produce the school's total owed.
Source: accounting/api/create_report.php:357-376; reports/registration/registration.php:327-347
```

```
Module: Accounting — Balance Computation
Rule:   A school's total balance is: (total owed) - (total paid) - (total discounts used). A positive balance means the school still owes money; a negative balance means the school has overpaid.
Source: accounting/api/create_report.php:336-337; reports/registration/registration.php:348
```

```
Module: Accounting — Discount Tracking
Rule:   Discounts are tracked at two levels: school-level (SchoolDiscount, linked to school_id) and student/soldier-level (StudentDiscount, linked to user_id); they are stored in the discounts table with year, amount, reason, created_by, and a used timestamp.
Source: reports/registration/Discount.php:19-75; SQLdump/mashpiadb_discounts.sql
```

```
Module: Accounting — Discount Creation
Rule:   A discount requires a positive numeric amount, a valid Hebrew year (5780 or later), a school or soldier selection, a reason, and a created_by name; all fields are mandatory and validated before the discount is persisted.
Source: accounting/api/createDiscount.php:26-39
```

```
Module: Accounting — Discount Creation Authorization
Rule:   Only super admins may create discounts (both school-level and student-level).
Source: accounting/api/createDiscount.php:9-12
```

```
Module: Accounting — Registration Charge Totals (Report)
Rule:   The registration charges summary report excludes refunded charges (WHERE refunded = 0) when summing totals by charge type.
Source: reports/registration/registration_charges.php:15-25; reports/registration/reg_charges_new.php:15-25
```

```
Module: Accounting — Registration Types
Rule:   Schools are assigned one of three registration types that affect fee calculation: 1=Tuition, 2=Guaranteed, 3=Regular.
Source: accounting/api/create_report.php:246-249; reports/registration/registration.php:51-55
```

```
Module: Accounting — School Payments (Base Registration)
Rule:   School payments from the base_reg_payments table support four methods of payment: cash, check, credit, and wire.
Source: SQLdump/mashpiadb_base_reg_payments.sql
```

```
Module: Accounting — Payment Processing Lock
Rule:   The payment_processing table enforces unique constraints on both admin_id and user_id, meaning only one in-flight payment process is allowed per admin or per user at a time.
Source: SQLdump/mashpiadb_payment_processing.sql
```

```
Module: Points — Task Earnings
Rule:   Each completed date-task (recorded in date_tasks_marks) earns the user 0.5 points.
Source: api/points/details.php:41
```

```
Module: Points — Payment Processing (PointsDB)
Rule:   Points-store payment processes are recorded in pointsDB.payment_processes with amount, response, created timestamp, and created_by; they link to both a user_id and an institution_id.
Source: SQLdump/pointsDB_payment_processes.sql
```

---

## Payments

```
Module: Payments — Refund Authorization (Accounting Module)
Rule:   Only super admins may process refunds; additionally, refunding a registration charge via the accounting module requires a special cookie ("naftoli") as a secondary authorization gate.
Source: accounting/api/refund.php:9-17
```

```
Module: Payments — Refund Charge Codes
Rule:   Refunds are only accepted for a predefined set of valid charge codes: AKLDBC, AKLDS, CB, CBS, CD, CT, CV, HACH, KHKE, LDE, MYSLDS, R, RRFAM, RRHVN, RRKHK, RRSCAN, RRSINT, RRSUSA, RRYDA, RRYSD, SW, SWS, THAKCAN, THAKINT, THAKUSA, THE, THMSCAN, THMSINT, THMSUSA, V, YB1–YB5; any other code is rejected.
Source: accounting/api/refund.php:22-29
```

```
Module: Payments — Refund Actions
Rule:   A refund can operate in two modes: full refund (marks refunded=1, reverses the associated registration record) or "refund only" (marks refunded=1 AND keep_charge=1, meaning the charge record is retained but the payment is returned without un-registering the student).
Source: accounting/api/refund.php:87-103
```

```
Module: Payments — Refund Atomicity
Rule:   All refund operations are wrapped in a database transaction; if any step fails (marking the charge as refunded, reversing enrollment/purchase, or processing the gateway refund), the entire transaction is rolled back.
Source: accounting/api/refund.php:31, 71-81
```

```
Module: Payments — Refund Side Effects by Code
Rule:   Each charge code triggers a specific enrollment reversal on refund:
        THE    → clears users.user_registered and deletes user_registration row for the year
        HACH   → deletes hachayols_to_give row for the year
        LDE/RRHVN/RRKHK/RRYDA/RRYSD → sets th_chidon.reg_date = NULL for the year
        KHKE   → sets th_chidon.khk_reg = 0
        CB/SW  → deletes extra_purchases row (item = celeb_box or sweater)
        CBS/SWS → zeroes extra_purchases_shipping.shipping_amount and removes purchase_addresses if admin address was used
        YB1–YB5 → deletes yahadus_book_purchases row (location = parent_account, LIMIT 1)
        RRFAM  → clears th_chidon.paid, date_paid, paid_by
        All other codes → only registration_charges.refunded is set to 1; no enrollment side effect.
Source: accounting/api/refund.php:33-68; reports/registration/refund.php:39-222
```

```
Module: Payments — Refund Reason Required
Rule:   A refund requires a text reason stored in registration_charges.refund_reason; if no reason is supplied, the refund is blocked client-side.
Source: reports/registration/refund.php:22; reports/registration/reg_charges_new.php (JS):167-170
```

```
Module: Payments — Duplicate Charge Refund
Rule:   Flagging a refund as a "duplicate charge" bypasses the enrollment-reversal step so that the student's registration status is preserved while only the financial charge is reversed; on the accounting side this is expressed as action = "refundOnly".
Source: reports/registration/refund.php:37-38; accounting/api/refund.php:32
```

```
Module: Payments — Payment Profiles
Rule:   When a school admin (BC auth) accesses payment profiles from a non-mobile session, the school's Authorize.Net customer profile is used; in all other cases the current user's own customer profile is used.
Source: api/payments/profiles.php:12-18
```

```
Module: Payments — Transaction Record Split
Rule:   Each payment transaction stores the registration amount and shipping amount as separate integer fields (reg_amount, ship_amount), enabling split-billing of enrollment vs. shipping fees within a single transaction.
Source: SQLdump/mashpiadb_transactions.sql
```

```
Module: Payments — Refund Fields on Transactions
Rule:   The transactions table records the refund transaction ID, refund amount, and refund date for each original transaction, creating a direct audit link between original charge and refund.
Source: SQLdump/mashpiadb_transactions.sql
```

---

## Shipping

```
Module: Shipping — Chayolei Hachayol Shipping Eligibility (Special Schools)
Rule:   For schools 61 and 269 (MyShliach / Anash Kinder), a Hachayol magazine is included in the shipping report for a student only if that student's parent/admin has a non-refunded charge with type matching THAK% or THMS% (i.e., they paid the applicable shipping fee); students whose parents have not paid shipping are excluded.
Source: chayolei_shipping/class.chayoleiShipping.php:248-255 (checkShippingStatus + getHachayols)
```

```
Module: Shipping — Chayolei Birthday Eligibility
Rule:   Birthday items (envelope, cards, Kapital cards) are only prepared for registered soldiers (user_registered is not null/empty/0000-00-00) whose Hebrew birthday falls between ages 6 and 12 inclusive during the current year.
Source: chayolei_shipping/class.chayoleiShipping.php:726-751
```

```
Module: Shipping — Chayolei Birthday Card Gender Split
Rule:   Boys Birthday Cards are only sent to users with gender = M; Girls Birthday Cards are only sent to users with gender = F; Birthday Envelopes are sent regardless of gender.
Source: chayolei_shipping/class.chayoleiShipping.php:668-700
```

```
Module: Shipping — Chayolei Kapital Card Age Matching
Rule:   A Kapital Card for a specific age (6–12) is only sent to a child whose calculated current age exactly matches that card's age.
Source: chayolei_shipping/class.chayoleiShipping.php:702-720
```

```
Module: Shipping — Hachayol Shipment Batches
Rule:   Hachayol magazines are grouped into named shipment batches (HACH01–HACH09 for batches 1–9, HACH10+ for ten and above); each batch covers a configurable range of issue numbers stored in the chayolei_hachayol_shipments table (year, shipment_num, issue_start, issue_end); if no configuration exists, the UI presents at least 10 shipment slots.
Source: chayolei_shipping/class.chayoleiShipping.php:96-151
```

```
Module: Shipping — Chayolei Extra Purchases Routing
Rule:   Extra purchases (sweaters, celebration boxes) for parent/grandparent accounts are assigned to the oldest child of the purchasing admin who is registered in Chayolei; if no Chayolei-registered child exists, the oldest Chidon-enrolled child is used. School 612 is always excluded from this look-up.
Source: chayolei_shipping/class.chayoleiShipping.php:542-580
```

```
Module: Shipping — Extra Purchases Standard vs. AK Routing
Rule:   Standard extra purchases exclude items with shipping_amount = 10; AK (Anash Kinder) extra purchases are fetched only when the parent is flagged myshliach_ak = 1 in chidon_parent_shipping, and include items where use_admin_shipping_address = 1 (to-ship batch) or shipping_amount = 0 (not yet paid batch).
Source: chayolei_shipping/class.chayoleiShipping.php:476-491 and 638-676
```

```
Module: Shipping — Chidon Sweater Eligibility
Rule:   Children's sweaters are only included in the Chidon shipping report for students where th_chidon.date_paid > 0 (enrollment fee paid).
Source: chidon_shipping/class.chidonShipping.php:419
```

```
Module: Shipping — Chidon Gift Eligibility
Rule:   End-of-Chidon gifts (yarmulka for boys, jewelry for girls) are only prepared for students where th_chidon.date_paid > 0.
Source: chidon_shipping/class.chidonShipping.php:728
```

```
Module: Shipping — Chidon ID Card Eligibility
Rule:   An ID card is only prepared for a student if: (a) they have a lanyard code assigned in chidon_lanyards, (b) they are NOT on the east-coast trip (trip != 'east') AND NOT on the ultimate trip (ultimate_trip = 0), and (c) before the final test cutoff date, they must have passed at least one track above Yesod level.
Source: chidon_shipping/class.chidonShipping.php:793-819
```

```
Module: Shipping — Chidon Prize Eligibility
Rule:   Prizes selected at enrollment are included in shipping only if: date_paid > 0, ultimate_trip = 0, and the student has passed at least one track above Yesod/Maven; students with reward_type = 'maven' explicitly receive no prizes.
Source: chidon_shipping/class.chidonShipping.php:1123-1150
```

```
Module: Shipping — Chidon KHK Guide Eligibility
Rule:   KHK study guides are only shipped to students where th_chidon.khk_reg = 1.
Source: chidon_shipping/class.chidonShipping.php:130-152
```

```
Module: Shipping — Chidon Award Tiers
Rule:   Chidon awards are assigned based on the highest final track passed:
        Yesod   → certificate
        Yediah  → plaque
        Havonah → medal + plaque
        Iyun    → medal + plaque + blue trophy
        A student achieves "Iyun" either by scoring at the genius average across all four individual tracks, or by a cumulative score of ≥90% across all four tracks combined (sum/80 × 100 ≥ 90).
Source: chidon_shipping/class.chidonShipping.php:876-1030
```

```
Module: Shipping — Chidon KHK Plaque Eligibility
Rule:   A KHK plaque is added to a student's award shipment if: khk_reg = 1, khk_final score >= 140, shipped flag (khk) = 0, AND ultimate_trip = 0.
Source: chidon_shipping/class.chidonShipping.php:1083-1090
```

```
Module: Shipping — Chidon Recruitment Prize Accumulation
Rule:   Recruitment prize credits accumulate from year 5782 onward; each newly enrolled student recruited earns the recruiter one credit. Credits are capped at 5 total across all years; a student who already has 5 or more credits from prior years receives no new prizes in the current year. The prize for each credit level (1–5) is configured in chidon_credit_prizes per year.
Source: chidon_shipping/class.chidonShipping.php:206-252
```

```
Module: Shipping — Chidon Recruitment Prize Gender Colors
Rule:   For the "watch" recruitment prize, boys receive a blue watch (CHI015) and girls receive a burgundy watch (CHI016).
Source: chidon_shipping/class.chidonShipping.php:237-241
```

```
Module: Shipping — Chidon Ambassador Prize Threshold
Rule:   A student qualifies for the ambassador prize (CHI194) if the sum of their subsidy contributions in chidon_user_subsidies for the current Chidon year totals $500 or more.
Source: chidon_shipping/class.chidonShipping.php:1317-1325
```

```
Module: Shipping — Shipping Status States
Rule:   Shipping item status values are: 0 = Not Yet Shipped, 1 = Shipped (sets date_shipped = NOW()), 2 = Received by school, 4 = Damaged (requires a description), 5 = Archived/Re-shipped. Non-super admins cannot revert an item to status 0 and cannot set status 4 (Damaged).
Source: chayolei_shipping/report.php:566-602; chidon_shipping/report.php:588-602
```

```
Module: Shipping — Shipment Number Control
Rule:   Only super admins can change the shipment number on an item; the shipment number field is disabled for school-level admins.
Source: chayolei_shipping/report.php:597-602; chidon_shipping/report.php:597-602
```

```
Module: Shipping — Medal Shipped Callback
Rule:   When a Chayolei medal item is marked as Shipped (action = 1) and subject/medal data are present, the system also updates medal_marks.date_shipped and medal_marks.date_received to NOW(), recording delivery in the medals subsystem.
Source: chayolei_shipping/ajax/saveShipping.php:79-86, 154-160
```

```
Module: Shipping — Previously-Shipped Rank Medal Exclusion
Rule:   Rank medals that appear in the rank_medals_shipped table are excluded from new shipping batches to prevent double-sending of medals shipped before the system began tracking.
Source: chayolei_shipping/class.chayoleiShipping.php:544-546
```

```
Module: Shipping — Rank Book Duplication Guard
Rule:   Rank books are excluded from a shipping batch if the user/book combination already appears in the rank books shipped table.
Source: chayolei_shipping/class.chayoleiShipping.php:594-596
```

```
Module: Shipping — Shipping Rate Structure
Rule:   Shipping rates are stored per (type, zone, child_count) triplet with a unique key; rates are selected by combining shipping type, geographic zone, and number of enrolled children.
Source: SQLdump/mashpiadb_shipping_rates.sql
```

```
Module: Shipping — Tracking Number Carriers
Rule:   Tracking numbers must be associated with one of three supported carriers: UPS, USPS, or Amazon.
Source: SQLdump/mashpiadb_tracking_numbers.sql
```

```
Module: Shipping — Shipment Lifecycle
Rule:   Shipments progress through four statuses: planned → in transit → delivered → archived; the default status on creation is "in transit."
Source: SQLdump/mashpiadb_shipments.sql
```

```
Module: Shipping — School Shipping Record Uniqueness
Rule:   Each school can have only one shipping record per (year, school_id, item_id) combination; duplicate entries are rejected by a unique database key.
Source: SQLdump/mashpiadb_school_shipping.sql
```

```
Module: Shipping — Chayolei Domestic vs. International Export
Rule:   When generating CSV export files, Chayolei shipping can be split into USA-only (admin_country = 'USA') and international (admin_country != 'USA') files; when "all" is selected both files are generated and zipped together.
Source: chayolei_shipping/report.php:73-88, 141-144
```

```
Module: Shipping — Chidon Domestic vs. International Filter
Rule:   Chidon shipping reports can filter to domestic (admin_country IN 'USA'/'US'/'United States'/'U.S.A'/'Unites States of America') or international (all others) based on the parent admin's country field.
Source: chidon_shipping/report.php:99-118
```

```
Module: Shipping — Gear Direct-to-Parent vs. To-School Split
Rule:   Chayolei gear items (TH Sweater, TH Cap, TH Rank Patch) are split into two batches based on whether a custom delivery address exists; items without an address ship to the school, items with a custom address ship directly to the family.
Source: chayolei_shipping/class.chayoleiShipping.php:1457-1460
```

---

## Reports / Exports

```
Module: Reports — Access Control
Rule:   All reports and accounting pages require auth = "super"; any other role is denied access.
Source: accounting/api/addPayment.php:6; accounting/api/refund.php:9; accounting/api/createDiscount.php:9; reports/registration/registration.php:5; reports/registration/refund.php:9; reports/registration/discounts.php:6; chidon_shipping/report.php:8
```

```
Module: Reports — Test Schools Excluded
Rule:   The soldier registration report excludes schools where test_school = 1; only real, active schools appear.
Source: reports/registration/registration.php:22-24
```

```
Module: Reports — Registration Report Scope
Rule:   The registration report includes only soldiers whose user_registered flag is set (not null, empty, or 0000-00-00) for schools registered in the current year.
Source: reports/registration/registration.php:22-24
```

```
Module: Reports — Accounting Report Types
Rule:   The accounting module supports four report views: "base" (school-level fees, payments, discounts, balance), "soldier" (per-student enrollment fee and balance), "details" (individual charge line items with refund status), and "settings" (school fee configuration).
Source: accounting/api/create_report.php:21-83
```

```
Module: Reports — Soldier Report Early-Bird Fee Determination
Rule:   In the soldier-level report, the registration fee for each student is re-calculated using the student's actual registration date; if that date is on or before the system's early-bird deadline, the early-bird rate applies.
Source: accounting/api/create_report.php:357-375
```

```
Module: Reports — Discount Display
Rule:   School-level discounts are displayed as absolute (positive) values in the base report to represent the reduction in amount owed.
Source: accounting/api/create_report.php:349
```

```
Module: Reports — Grand Summary Access
Rule:   The cross-school grand summary (totals by item across all schools) is rendered only for super admins; school admins can only see their own school's summary.
Source: chayolei_shipping/report.php:404; chidon_shipping/report.php:430
```

```
Module: Reports — Report Visibility States (reports table)
Rule:   Reports in the reports table have three visibility states: "all" (visible to everyone), "process" (in-progress or restricted), and "none" (hidden); supported report types are WWTC, Hakhel, Auction, and mission_cover_sheet.
Source: SQLdump/mashpiadb_reports.sql
```

```
Module: Reports — School Registration Feature Flags
Rule:   The registration table records per-school, per-year feature opt-ins as boolean flags: whatsapp, tutorial, chavrusaEn, chavrusaHe, library, birthday, mishmor; these affect which program features are active for the school.
Source: SQLdump/mashpiadb_registration.sql
```

```
Module: Reports — Points Detail History Sources
Rule:   The points detail API combines two data sources: date_tasks_marks (task completions at 0.5 points each, filtered by Julian date range) and pointsDB.user_points (store purchases/returns, achievement cards, scratch cards, and admin manual adjustments), merging them into a single chronologically sorted list.
Source: api/points/details.php
```
