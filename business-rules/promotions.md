# Business Rules: Promotions — Medals, Ranks, Hachayol, Rallies

Generated from source code review. All rules describe what the system *decides*, *enforces*, or *allows*.

---

## Directory Inventory

| Directory | Key Files |
|---|---|
| `public/medals/` | `class.medalsSubjects.php`, `medal_report.php`, `set_as_shipped.php`, `shipped_report.php`, `medals_by_schools.php`, `get_users.php`, `future/class.futureMedals.php` |
| `public/medal_ceremony/` | `class.slides.php`, `choose_slides.php`, `slides.php` |
| `public/medal_board/` | `class.medalBoard.php`, `medal_board.php` |
| `public/hachayols/` | `class.ComprehensiveShipmentReport.php`, `report.php`, `school_hachayols.php`, `myshliach.php`, `ms_ak_catchup_report.php`, `findExtras.php`, `forPickup.php`, `reset_hachayols.php`, `updateMissingHachayols.php`, `api/setAsShipped.php` |
| `public/rank_ceremony/` | `index.php`, `generals.php`, `ot.php`, `createFile.php`, `createGenerals.php` |
| `public/rank_books/` | `catchup.php`, `set_as_shipped.php`, `update_shipped.php`, `upload_books_shipped.php` |
| `public/ajax/hachayols/` | `getHachayolsInfo.php`, `updateHachayols.php`, `getHachayolsPaid.php` |
| `public/supersoldier/` | `index.php` (redirects to Google Form) |
| `public/api/core/hachayols.php` | (auth header only — no domain logic) |

---

## Medals

### Medal Structure

```
Module: Medals
Rule:   Each medal belongs to a specific subject (campaign) and has a sequential ordinal (medal_ord); the same medal ordinal maps to different subject-specific images and mission thresholds.
Source: mashpiadb_medals.sql, mashpiadb_medals_subjects.sql
```

```
Module: Medals
Rule:   A soldier earns a medal in a given subject by completing a cumulative number of missions defined in medals_subjects.missions_required; thresholds are additive (each medal's required count is added to the sum of all previous medals' counts).
Source: public/medals/class.medalsSubjects.php:calcHighestMedal
```

```
Module: Medals
Rule:   The system calculates the highest medal a soldier is eligible for by iterating medals in order and accumulating missions_required; it stops at the first medal whose cumulative threshold exceeds the soldier's mission count and returns the previous medal ordinal.
Source: public/medals/class.medalsSubjects.php:calcHighestMedal (lines 25–42)
```

```
Module: Medals
Rule:   Medal eligibility is per subject — a soldier can earn different medal levels independently in each subject/campaign they are enrolled in.
Source: public/medals/class.medalsSubjects.php, public/medal_board/class.medalBoard.php
```

```
Module: Medals
Rule:   Each awarded medal is recorded in medal_marks with a Julian-day date_awarded, and separate timestamps for date_shipped and date_received.
Source: mashpiadb_medal_marks.sql
```

```
Module: Medals
Rule:   A medal award record is unique per (medal_ord, subject_id, user_id) — a soldier cannot be awarded the same medal in the same subject twice.
Source: mashpiadb_medal_marks.sql (PRIMARY KEY)
```

### Medal Inventory

```
Module: Medals — Inventory
Rule:   Physical medal inventory is tracked per (subject_id, medal_ord, medal_type); medal_type is either 'number_on_back' or 'picture_on_back'.
Source: mashpiadb_medals_inventory.sql
```

```
Module: Medals — Inventory
Rule:   Every change to inventory is logged as a detail event with type in: 'initial_entry', 'add_to_stock', 'remove_from_stock', 'earned', 'shipped'.
Source: mashpiadb_medals_inventory_details.sql
```

### Medal Shipping

```
Module: Medals — Shipping
Rule:   Only HQ-level admins (auth = 'super') may mark medals as shipped or view the shipped report.
Source: public/medals/set_as_shipped.php (lines 8–13), public/medals/shipped_report.php (lines 6–12)
```

```
Module: Medals — Shipping
Rule:   When marking medals as shipped, the system validates that the total count in the submitted payload matches the actual number of medal records in the payload; a mismatch causes the entire operation to be rejected.
Source: public/medals/set_as_shipped.php (lines 29–41)
```

```
Module: Medals — Shipping
Rule:   Marking medals as shipped sets both date_shipped and date_received to the current timestamp in a single atomic transaction; if any update fails, all changes are rolled back.
Source: public/medals/set_as_shipped.php (lines 15–75)
```

```
Module: Medals — Shipping
Rule:   The shipped report defaults to showing all medals awarded on or after Julian day 2460447 (May 17, 2024) when no date filter is provided.
Source: public/medals/shipped_report.php (lines 30–43)
```

```
Module: Medals — Missing
Rule:   A separate missing_medals table records medals that were not shipped as expected, keyed by school, user, subject name, and medal name (no foreign-key constraints).
Source: mashpiadb_missing_medals.sql
```

### Future Medal Projection

```
Module: Medals — Future Projection
Rule:   The system can project how many additional medals a soldier will earn by a target end date by counting upcoming mandatory missions (personal = 0, mandatory_qty = 1) that match the soldier's school_type_id, track_id, level, and lang_id.
Source: public/medals/future/class.futureMedals.php:getFutureMissions (lines 125–165)
```

```
Module: Medals — Future Projection
Rule:   A positive medal difference is counted only when a future total would push the soldier past a higher cumulative threshold; negative differences (e.g., data anomalies) are ignored.
Source: public/medals/future/class.futureMedals.php:getEligibleMedals (lines 167–184)
```

### Medal Ceremony / Slideshow

```
Module: Medals — Ceremony
Rule:   The medal ceremony slideshow can be filtered to show: (a) only medals earned in the current reporting period, (b) all medals ever earned, or (c) all medals earned but with prior-period medals visually greyed out.
Source: public/medal_ceremony/choose_slides.php (lines 82–85), public/medal_ceremony/class.slides.php (lines 75–82)
```

```
Module: Medals — Ceremony
Rule:   An option exists to restrict slideshow slides to soldiers who earned at least one medal in the current reporting period.
Source: public/medal_ceremony/choose_slides.php (line 85), public/medal_ceremony/class.slides.php:setUsers (lines 109–128)
```

```
Module: Medals — Ceremony
Rule:   Subject 106 is excluded from all medal ceremony slides.
Source: public/medal_ceremony/class.slides.php (line 54): `and s.subject_id != 106`
```

```
Module: Medals — Ceremony
Rule:   The medal board display shows each soldier's rank image(s), total medals earned, total miles (points), and for each subject the count of missions done vs. the subject maximum, plus how many missions remain until the next medal level.
Source: public/medal_board/medal_board.php (lines 148–199)
```

```
Module: Medals — Ceremony
Rule:   Subject maximums used for the medal board progress display are: subject 40 = 585 missions, subjects 1 and 12 = 95 missions, all other subjects = 375 missions.
Source: public/medal_board/class.medalBoard.php (lines 128–140)
```

```
Module: Medals — Ceremony
Rule:   The medal board uses two different campaign layouts depending on school_type_id: regular schools (types 2 and 3) use one set of 13 subject columns; Frum schools (types 12 and 13) use an alternate set.
Source: public/medal_board/medal_board.php:getPositioning (lines 81–100)
```

---

## Ranks

### Rank Structure

```
Module: Ranks
Rule:   A soldier is promoted to a rank when they have accumulated the required number of medals as configured in ranks.medals_required.
Source: mashpiadb_ranks.sql
```

```
Module: Ranks
Rule:   There are up to 14 rank levels (rank_ord auto-increments to 14); rank names include Sergeant, Sergeant Major, Second Lieutenant, First Lieutenant, Captain, Major, Colonel, General, and 1- through 5-Star General.
Source: public/rank_ceremony/createFile.php (lines 68–82), mashpiadb_ranks.sql (AUTO_INCREMENT=15)
```

```
Module: Ranks
Rule:   Each rank promotion is recorded in rank_marks with a Julian-day date_promoted and separate timestamps for book and card shipment/receipt.
Source: mashpiadb_rank_marks.sql
```

```
Module: Ranks
Rule:   A rank promotion record is unique per (rank_ord, user_id) — a soldier cannot be promoted to the same rank twice.
Source: mashpiadb_rank_marks.sql (PRIMARY KEY)
```

```
Module: Ranks
Rule:   A soldier's current rank is determined by taking the highest rank_ord from their rank_marks records.
Source: public/medal_board/medal_board.php:getRank (lines 32–43)
```

### Rank Books

```
Module: Ranks — Books
Rule:   When a soldier reaches a rank, a physical rank book is shipped; which book number is sent depends on the soldier's highest rank: rank_ord 1–8 → Book 1, rank_ord 9–11 → Book 2, rank_ord 12+ → Book 3.
Source: public/rank_books/catchup.php (lines 156–165, 202–211)
```

```
Module: Ranks — Books
Rule:   A rank book shipment is recorded in rank_books_shipped as (user_id, book) with a unique constraint; INSERT IGNORE prevents duplicate shipment records.
Source: public/rank_books/set_as_shipped.php (line 15), mashpiadb_rank_books_shipped.sql
```

```
Module: Ranks — Books
Rule:   The rank books catchup report only includes soldiers who: (a) are registered for the current year, (b) have rank_ord > 1, and (c) do NOT already have a record in rank_books_shipped.
Source: public/rank_books/catchup.php (lines 48–80): `HAVING rank_ord > 1` and `NOT IN (SELECT user_id FROM rank_books_shipped)`
```

```
Module: Ranks — Books
Rule:   Schools 61 and 269 are excluded from the catchup book report by default (MyShliach and AnashKinder handle books separately).
Source: public/rank_books/catchup.php (line 70): `AND u.school_id NOT IN (61, 269)`
```

```
Module: Ranks — Books
Rule:   Australian schools are excluded from the rank books catchup report.
Source: public/rank_books/catchup.php (line 47): `$exceptions = array_unique(array_merge([585, 808, 612], $australian))`
```

```
Module: Ranks — Books
Rule:   Books-shipped records can be uploaded in bulk via CSV (serial number, book number) or set individually via a toggle; both use INSERT IGNORE so re-uploading the same data is safe.
Source: public/rank_books/upload_books_shipped.php, public/rank_books/update_shipped.php
```

```
Module: Ranks — Books
Rule:   Only HQ-level admins (auth = 'super') may mark rank books as shipped or view the catchup report.
Source: public/rank_books/catchup.php (lines 13–15), public/rank_books/set_as_shipped.php (lines 9–13)
```

### Rank Ceremony

```
Module: Ranks — Ceremony
Rule:   The rank ceremony generates per-school CSV slide data structured as: promotions_intro slide, then one rank_intro slide per rank level, then one slide per promoted soldier (with name and photo), then an outro slide.
Source: public/rank_ceremony/createFile.php:generateFile (lines 65–172)
```

```
Module: Ranks — Ceremony
Rule:   Schools 61 and 269 (MyShliach, AnashKinder) produce two ceremony files — one for boys and one for girls; certain schools (54, 106, 255) produce one file per grade instead of per school.
Source: public/rank_ceremony/createFile.php (lines 42–50)
```

```
Module: Ranks — Ceremony
Rule:   The Generals ceremony (generals.php / createGenerals.php) generates a single cross-school CSV showing only rank_ord = 9 (General) promotions, sorted by school then by soldier.
Source: public/rank_ceremony/createGenerals.php (lines 45–52, 68–70)
```

```
Module: Ranks — Ceremony
Rule:   Ceremony files are generated for a user-specified date range (Julian day start/end); a soldier appears in the ceremony only if their rank promotion date falls within that range.
Source: public/rank_ceremony/index.php (lines 28–34), public/rank_ceremony/createFile.php (line 88)
```

```
Module: Ranks — Ceremony
Rule:   All ceremony file generation is restricted to HQ-level admins (auth = 'super').
Source: public/rank_ceremony/index.php (lines 9–12), public/rank_ceremony/generals.php (lines 7–10)
```

### Rank Medals Shipped

```
Module: Ranks — Medals
Rule:   Physical rank medals shipped are tracked in rank_medals_shipped as (user_id, rank_ord) with a unique constraint and a created timestamp.
Source: mashpiadb_rank_medals_shipped.sql
```

---

## Hachayol

### Hachayol Eligibility

```
Module: Hachayol
Rule:   A Hachayol (magazine) subscription is assigned per family (admin account), not per child; only one child per family receives the Hachayol by default.
Source: public/hachayols/findExtras.php (lines 38–48), public/ajax/hachayols/updateHachayols.php
```

```
Module: Hachayol
Rule:   For years before 5786, Hachayol eligibility is stored as a flag (hachayol = 1) directly on the users table. From year 5786 onward, eligibility is stored in the hachayols_to_give table as (user_id, year) records.
Source: public/ajax/hachayols/getHachayolsInfo.php (lines 21–54), public/hachayols/report.php (lines 37–85)
```

```
Module: Hachayol
Rule:   The hachayols_to_give table enforces a unique constraint on (user_id, year) — a soldier can only be marked to receive the Hachayol once per year.
Source: mashpiadb_hachayols_to_give.sql (UNIQUE KEY `hachayol`)
```

```
Module: Hachayol
Rule:   If a family has multiple children and additional children are marked to receive the Hachayol, they must have paid for extra copies (registration_charges record with type LIKE '%HACH%'); unpaid extras are stripped (hachayol flag set to 0).
Source: public/hachayols/findExtras.php (lines 32–61)
```

```
Module: Hachayol
Rule:   If a family has no child designated to receive the Hachayol, the system auto-assigns the first registered child in the family.
Source: public/hachayols/updateMissingHachayols.php (lines 33–59)
```

```
Module: Hachayol
Rule:   When resetting Hachayol assignments, the system assigns to the oldest child in grade 5 or below (including Pre1a); if no child qualifies at grade 5 or below, the last child in the sorted list is assigned.
Source: public/hachayols/reset_hachayols.php (lines 36–48)
```

```
Module: Hachayol
Rule:   Schools 66 and 112 are excluded from Hachayol missing/reset operations.
Source: public/hachayols/updateMissingHachayols.php (line 21), public/hachayols/reset_hachayols.php (line 23)
```

```
Module: Hachayol
Rule:   A parent (admin) can designate which of their children receives the Hachayol via the family report UI; the system uses a processing-guard (payment_processing table) to prevent concurrent duplicate submissions.
Source: public/ajax/hachayols/updateHachayols.php (lines 26–48)
```

```
Module: Hachayol
Rule:   School-level admins cannot reassign the Hachayol recipient if the currently designated child is enrolled in a different school from the one the admin manages; only HQ-level admins (super) can override school boundaries.
Source: public/hachayols/report.php (lines 141–147), public/hachayols/detailed_family_report.php (lines 135–139)
```

```
Module: Hachayol
Rule:   The number of extra Hachayols a family may purchase is returned by the getHachayolsPaid endpoint, which counts registration_charges records with type = 'HACH' for the current year.
Source: public/ajax/hachayols/getHachayolsPaid.php (lines 18–36)
```

### Hachayol — Publication Structure

```
Module: Hachayol — Publication
Rule:   Each Hachayol issue is uniquely identified by (year, print_number, issue_number) and has an optional supplement field and a scheduled ship_date.
Source: mashpiadb_hachayols.sql
```

```
Module: Hachayol — Shipments (Chayolei)
Rule:   Chayolei Hachayol shipments are grouped into numbered shipments per year, each covering a range of issues (issue_start to issue_end); the combination of (year, shipment_num) is unique.
Source: mashpiadb_chayolei_hachayol_shipments.sql
```

```
Module: Hachayol — Shipping
Rule:   Each Hachayol shipment to a school is recorded in hachayol_shipping as (school_id, hachayol_id, qty, shipment_id).
Source: mashpiadb_hachayol_shipping.sql
```

```
Module: Hachayol — Base Allocation
Rule:   A base allocation of Hachayols per school is stored in base_hachayols, broken down by: CTH boys+girls combined, Chidon boys+girls combined, CTH boys only, CTH girls only, Chidon boys only, Chidon girls only.
Source: mashpiadb_base_hachayols.sql
```

### Hachayol — MyShliach / AnashKinder Shipping

```
Module: Hachayol — MyShliach/AK Shipping
Rule:   For MyShliach (school 61) and AnashKinder (school 269), Hachayol shipments to families are only sent if the family has paid a shipping fee; the charge type pattern is 'THMS%' for MyShliach and 'THAK%' for AnashKinder.
Source: public/hachayols/class.ComprehensiveShipmentReport.php:paidForShipping (lines 153–168)
```

```
Module: Hachayol — MyShliach/AK Shipping
Rule:   For all other schools, paidForShipping() returns false — meaning the comprehensive shipment report's payment check is only relevant for MyShliach and AnashKinder.
Source: public/hachayols/class.ComprehensiveShipmentReport.php:paidForShipping (line 165): `return false`
```

```
Module: Hachayol — MyShliach/AK Shipping
Rule:   For the comprehensive shipment report, a hardcoded list of family IDs is used to override the first shipment (shipment 1) as already sent for those families, regardless of what the th_chidon_shipping table says.
Source: public/hachayols/class.ComprehensiveShipmentReport.php:checkShipmentOne (lines 115–151)
```

```
Module: Hachayol — MyShliach/AK Shipping
Rule:   Hachayol shipment records for individual children are written to th_chidon_shipping using item_id = 'HACH01' with a shipment_number; INSERT IGNORE prevents duplicate records.
Source: public/hachayols/api/setAsShipped.php (lines 17–18, 31–47)
```

```
Module: Hachayol — MyShliach/AK Shipping
Rule:   To qualify for inclusion in the comprehensive shipment report, a child must be registered for the current Chidon year, have an hachayols_to_give entry for that year, and belong to the specified school.
Source: public/hachayols/class.ComprehensiveShipmentReport.php:getFamilyShipmentDetails (lines 20–36)
```

```
Module: Hachayol — MS/AK Catchup
Rule:   The MyShliach/AnashKinder catchup report deducts one Hachayol from families appearing in the hardcoded family_ids list (families who received their first copy through a prior mechanism), and excludes those families entirely if they would be left with zero children.
Source: public/hachayols/ms_ak_catchup_report.php (lines 147–148)
```

---

## Rallies

```
Module: Rallies
Rule:   The rally_poll table records a soldier's (user_id) association with a rally_number; the user_id is the primary key, so each soldier can be associated with at most one rally number at a time.
Source: mashpiadb_rally_poll.sql
```

```
Module: Rallies — Super Soldier
Rule:   The Super Soldier registration page redirects directly to an external Google Form; no application-level eligibility check is performed at that entry point.
Source: public/supersoldier/index.php (line 2)
```

---

## Cross-Cutting Access Rules

```
Module: All Promotion Modules
Rule:   All medal reports, medal shipping, rank ceremony generation, rank book shipping, and Hachayol admin operations require HQ-level authentication (auth = 'super'); school-level admins cannot perform these actions.
Source: public/medals/set_as_shipped.php:8, public/medals/shipped_report.php:7, public/rank_books/catchup.php:13, public/rank_ceremony/index.php:9, public/rank_ceremony/generals.php:7, public/hachayols/findExtras.php:9, public/hachayols/updateMissingHachayols.php:9, public/hachayols/reset_hachayols.php:9
```

```
Module: All Promotion Modules
Rule:   School-level admins can access Hachayol reports for their own school but cannot modify Hachayol assignments when the designated recipient belongs to a different school.
Source: public/hachayols/report.php (lines 141–147)
```
