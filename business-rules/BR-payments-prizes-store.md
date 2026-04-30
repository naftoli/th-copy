# Business Rules: Payments, Orders, Prize Store, Auctions, Raffles, Shipping

**Extracted from:** SQL dump files (SQLdump/) and PHP source files (mashpia.com/public/)
**Extraction date:** 2026-04-30
**Databases covered:** `mashpiadb`, `pointsDB`, `mashpia_purchases`

> **Note:** This codebase is legacy PHP mixing procedural and OO styles across multiple databases. Rules inferred from schema structure, column names, enum values, constraints, and PHP logic. Completeness is not guaranteed.

---

## Payments & Transactions

```
Rule ID:      BR-PAY-001
Category:     Payments & Transactions
Description:  Every payment record must capture email, amount (unsigned decimal up to
              $99,999.99), a response string, and an invoice number. Phone, name, and
              address are optional. Amount cannot be negative (UNSIGNED constraint).
Source:       mashpiadb_payments.sql / table `payments`
DB Evidence:  `amount decimal(7,2) unsigned NOT NULL`, `email NOT NULL`, `invoice NOT NULL`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PAY-002
Category:     Payments & Transactions
Description:  A payment processing lock exists per admin and per user: each admin_id
              and each user_id may only have one active payment processing record at a
              time. This prevents duplicate concurrent payment submissions.
Source:       mashpiadb_payment_processing.sql / table `payment_processing`
DB Evidence:  UNIQUE KEY `admin` (`admin_id`), UNIQUE KEY `user` (`user_id`)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PAY-003
Category:     Payments & Transactions
Description:  A transaction record tracks the full monetary breakdown: a registration
              amount (`reg_amount`) and a separate shipping amount (`ship_amount`),
              both stored as unsigned integers (implying dollars/cents stored as
              integers or cents). Partial refunds are supported: a transaction can
              reference a refund transaction ID and record a separate refund amount
              and refund date.
Source:       mashpiadb_transactions.sql / table `transactions`
DB Evidence:  `reg_amount int(10) unsigned`, `ship_amount int(10) unsigned`,
              `refund_trans_id int(11)`, `refund_amount decimal(7,2)`, `refund_date datetime`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PAY-004
Category:     Payments & Transactions
Description:  Authorization transactions (used to pre-authorize charges) are linked to
              a specific admin and optionally to a program year. The `admin_id` is
              mandatory (NOT NULL), meaning only admins — not end-users — can initiate
              an authorization transaction.
Source:       mashpiadb_authorize_transactions.sql / table `authorize_transactions`
DB Evidence:  `admin_id int(10) unsigned NOT NULL`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PAY-005
Category:     Payments & Transactions
Description:  The pointsDB purchase payment lifecycle has three states: Pending,
              Completed, and Refused. New purchases default to Pending. A Refused
              payment implies the charge was declined.
Source:       pointsDB_purchases.sql / table `purchases`
DB Evidence:  `payment_status enum('Pending','Completed','Refused') DEFAULT 'Pending'`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PAY-006
Category:     Payments & Transactions
Description:  pointsDB purchases support two currencies: US dollars and Canadian
              dollars. The default currency is US. A separate `credit` field allows
              a credit amount to be applied against the purchase price.
Source:       pointsDB_purchases.sql / table `purchases`
DB Evidence:  `currency enum('US','CDN') DEFAULT 'US'`, `credit float DEFAULT NULL`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PAY-007
Category:     Payments & Transactions
Description:  pointsDB orders (prize redemptions) support two currencies: CAD and USD,
              with CAD as the default. Orders reference either a prize or a package
              via the `item_id_ref` enum, and have two lifecycle states: Pending and
              Redeemed (default Pending).
Source:       pointsDB_orders.sql / table `orders`
DB Evidence:  `currency enum('CAD','USD') DEFAULT 'CAD'`,
              `item_id_ref enum('prizes','packages') NOT NULL`,
              `status enum('Pending','Redeemed') NOT NULL DEFAULT 'Pending'`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PAY-008
Category:     Payments & Transactions
Description:  School invoice items can represent six types of line items: a school
              package purchase, a per-student fee, a payment received, a direct charge,
              a credit applied, or a free-text note. This enum drives the school
              billing ledger.
Source:       mashpiadb_invoice_items.sql / table `invoice_items`
DB Evidence:  `item_ref_type enum('school_packages','school_package_fees','payment',
              'charge','credit','note')`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PAY-009
Category:     Payments & Transactions
Description:  The mashpia_purchases database records payments made by admins (school
              administrators) for mivtzoim (outreach campaign) items. The `admin_id`
              foreign key references `mashpiadb.admins`, and purchase details reference
              individual mivtzoim items. A user foreign key references `mashpiadb.users`,
              making per-student line items traceable.
Source:       mashpia_purchases_purchases.sql, mashpia_purchases_purchase_details.sql
DB Evidence:  CONSTRAINT `admin` FOREIGN KEY (`admin_id`) REFERENCES `mashpiadb`.`admins`,
              CONSTRAINT `user` FOREIGN KEY (`user_id`) REFERENCES `mashpiadb`.`users`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PAY-010
Category:     Payments & Transactions
Description:  Each school can independently enable or disable purchases of individual
              mivtzoim items per year via `allow_purchases`. Schools may also have a
              custom per-item shipping charge configured at the school-item-year level.
Source:       mashpia_purchases_school_settings.sql / table `school_settings`
DB Evidence:  `allow_purchases tinyint(3) unsigned NOT NULL DEFAULT 0`,
              `shipping_charge decimal(5,2) unsigned NOT NULL DEFAULT 0.00`,
              `year int(10) unsigned NOT NULL`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PAY-011
Category:     Payments & Transactions
Description:  Manual point adjustments by admins are categorized by resource_name to
              distinguish their scope: 'admin_users_manual' affects all mile pools,
              'transaction_manager_store' affects only store miles, and
              'auction_only_points' affects only auction points. This allows
              fine-grained control over which pool a manual adjustment targets.
Source:       mashpia.com/public/api/rewards/miles.php
DB Evidence:  PHP conditional: `if ($storeOnly) $resource_name = 'transaction_manager_store';
              else if ($auctionOnly) $resource_name = 'auction_only_points';`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-PAY-012
Category:     Payments & Transactions
Description:  The extra_purchases table records parent-facing purchases of supplemental
              items (celebration boxes and sweaters). Sweater purchases require a
              `type_of_sweater` designation (mother, father, bubby, or zaidy) and may
              specify a size. Shipping can be sent to the admin's address or to a custom
              address. Items are year-scoped.
Source:       mashpiadb_extra_purchases.sql / table `extra_purchases`
DB Evidence:  `item enum('celeb_box','sweater')`,
              `type_of_sweater enum('mother','father','bubby','zaidy')`,
              `use_admin_shipping_address tinyint(3) unsigned DEFAULT 0`,
              `year int(10) unsigned NOT NULL`
Confidence:   High
SME Verified: No
```

---

## Prize Store

```
Rule ID:      BR-STR-001
Category:     Prize Store
Description:  Prize store items are scoped to a school: a prize name must be unique
              within a school (school_id + prize_name composite unique key). A prize
              is always active by default (`prize_current DEFAULT 1`). The available
              quantity is optional (NULL means unlimited or not tracked separately).
Source:       mashpiadb_prizes_store.sql / table `prizes_store`
DB Evidence:  UNIQUE KEY `prize_name` (`prize_name`, `school_id`),
              `prize_current tinyint(1) NOT NULL DEFAULT 1`,
              `prize_available smallint(5) unsigned DEFAULT NULL`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-STR-002
Category:     Prize Store
Description:  Each store prize has a point cost (`prize_points`) that is mandatory
              and unsigned (cannot be zero points or negative). Points are debited
              from the student's balance at purchase time.
Source:       mashpiadb_prizes_store.sql, pointsDB_prizes.sql
DB Evidence:  `prize_points int(10) unsigned NOT NULL` (prizes_store),
              PHP: `$item_total = intval($prize['points']) * $qty; ... points * -1`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-STR-003
Category:     Prize Store
Description:  A student cannot place a store order if the total point cost of the
              order exceeds their current available store miles (points balance).
              The system checks available balance before committing any order record.
Source:       mashpia.com/public/api/rewards/orders.php
DB Evidence:  PHP: `if ($total_cost > $user->storeMiles()) return json_error('Not Enough Miles...')`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-STR-004
Category:     Prize Store
Description:  Ordering a prize decrements that prize's `prize_count` in the prizes
              table by the quantity ordered. Reversing/canceling an order restores
              the stock by the ordered quantity. Stock is managed transactionally.
Source:       mashpia.com/public/api/rewards/orders.php
DB Evidence:  PHP: `UPDATE prizes SET prize_count = prize_count - ? WHERE prize_id = ?`
              and `UPDATE prizes SET prize_count = prize_count + $row['quantity']`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-STR-005
Category:     Prize Store
Description:  Each store order is recorded in `user_prizes` with a 10-digit random
              serial number. All items in a single checkout share the same serial
              (order number). The entire order is placed within a database transaction;
              failure on any item rolls back all items.
Source:       mashpia.com/public/api/rewards/orders.php
DB Evidence:  PHP: `$POINTS_DB->beginTransaction()`, `$POINTS_DB->commit()`,
              `$POINTS_DB->rollback()`, serial generated from `ROUND(RAND() * 9999999999)`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-STR-006
Category:     Prize Store
Description:  A store order item (user_prize) has two statuses managed by admins:
              'Checked Out' (placed but not yet physically handed out) and 'Redeemed'
              (physically dispensed to the student). Admins can move an order back
              from Redeemed to Checked Out (unredeem).
Source:       mashpia.com/public/api/rewards/orders.php
DB Evidence:  PHP: `SET status="Redeemed"` and `SET status="Checked Out"` on user_prizes
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-STR-007
Category:     Prize Store
Description:  An order can be reversed (soft-deleted). Reversal sets `is_reversed = 1`
              on the user_prize record, restores the points to the student via a new
              compensating user_points record, and restores the prize stock. The
              reversing admin's ID is recorded on the reversed record.
Source:       mashpia.com/public/api/rewards/orders.php
DB Evidence:  PHP: `SET is_reversed = 1, reversed_by = ?`, compensating INSERT into
              user_points with `points * -1` (net positive restoration), stock update
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-STR-008
Category:     Prize Store
Description:  A prize with `one_per_user = 1` can only be ordered once per student.
              When building the store view for a student, prizes with this flag are
              filtered out if the student already has an active (non-reversed) order
              for that prize.
Source:       mashpia.com/public/api/rewards/orders.php (store method)
DB Evidence:  PHP: `HAVING (one_per_user = 0 OR ordered = 0)` where ordered counts
              non-reversed user_prizes for that user and prize
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-STR-009
Category:     Prize Store
Description:  Prizes in the pointsDB store can be restricted to specific classes
              (platoons) via the `prize_classes` join table. Teachers only see prizes
              that include their own class_id in the prize_classes list (or prizes
              with no class restriction). Base commanders see all prizes for their school.
Source:       mashpia.com/public/api/rewards/prizes.php
DB Evidence:  PHP: `if (count($prize_platoons) > 0 && !in_array($login->id, $prize_platoons)) continue;`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-STR-010
Category:     Prize Store
Description:  A teacher who creates a prize is automatically restricted to their own
              platoon: their class_id is added to prize_classes. Teachers are also
              flagged on the prize record itself (`teacher_edit = 1`, `teacher_id` set).
Source:       mashpia.com/public/api/rewards/prizes.php
DB Evidence:  PHP: `$prize->teacher_edit = 1; $prize->teacher_id = $current_user->admin_id;`
              then `$prize->setPlatoons([ $login->id ])`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-STR-011
Category:     Prize Store
Description:  The Base Commander (BC) can toggle the school store open or closed via
              a `school_store` flag on the school record. This flag is returned to the
              frontend so the UI can show or hide the store.
Source:       mashpia.com/public/api/rewards/prizes.php
DB Evidence:  PHP: `$school_store = !!$login->model->school_store;` and
              `$school->school_store = $_POST['school_store']; $school->save();`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-STR-012
Category:     Prize Store
Description:  When a store prize is deleted, the deletion is audited: the admin_id,
              school_id, prize_id, prize_name, and prize_points are written to the
              `deleted_prizes` table before the prize record is removed.
Source:       mashpia.com/public/api/rewards/prizes.php,
              mashpiadb_deleted_prizes.sql
DB Evidence:  PHP: `INSERT INTO deleted_prizes (admin_id, school_id, prize_id, prize_name, prize_points)`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-STR-013
Category:     Prize Store
Description:  Prizes in the mashpiadb `prizes_store` are tracked with a scan_date:
              NULL means the prize has not been scanned/distributed; a non-null
              scan_date means it has been distributed (shipped to the student).
              The admin store management page uses this to separate "shipped" from
              "un-shipped" prize purchases.
Source:       mashpiadb_store_purchases.sql, mashpia.com/public/admin_store.php
DB Evidence:  `scan_date timestamp NULL DEFAULT NULL` on store_purchases;
              PHP: `WHERE sp.scan_date IS NULL` (unshipped) vs `WHERE sp.scan_date IS NOT NULL` (shipped)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-STR-014
Category:     Prize Store
Description:  A store purchase in the legacy system (store_purchases) is tied to a
              voucher code (`voucher_id`, default 0 meaning no voucher). A non-zero
              voucher_id links the purchase to a physical voucher printed and scanned
              at the kiosk.
Source:       mashpiadb_store_purchases.sql / table `store_purchases`
DB Evidence:  `voucher_id bigint(17) unsigned NOT NULL DEFAULT 0`
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-STR-015
Category:     Prize Store
Description:  Global prizes (in `global_prizes`) are not school-scoped and have no
              availability limit or gender restriction at the global level. They serve
              as templates from which camp-specific prizes are instantiated
              (`prizes_camp.global_prize_id` references them).
Source:       mashpiadb_global_prizes.sql, mashpiadb_prizes_camp.sql
DB Evidence:  `global_prizes` has no `school_id`, no `gender`, no `available` column;
              `prizes_camp.global_prize_id int(10) unsigned NOT NULL DEFAULT 0`
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-STR-016
Category:     Prize Store
Description:  Camp prize store items are installed per camp (`camp_id`). A prize name
              must be unique within a camp. The `installed` flag (default 1) controls
              whether the prize is active in that camp's store.
Source:       mashpiadb_prizes_camp.sql / table `prizes_camp`
DB Evidence:  UNIQUE KEY `prize_name` (`prize_name`, `camp_id`),
              `installed tinyint(1) NOT NULL DEFAULT 1`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-STR-017
Category:     Prize Store
Description:  Prizes in the pointsDB system support discounts. A discount can be
              expressed as a flat point reduction or a percentage off. Both a
              discounted price and a discount amount/type are tracked separately,
              allowing display of original vs. discounted price.
Source:       pointsDB_prizes.sql / table `prizes`
DB Evidence:  `prize_price decimal(15,2)`, `prize_discounted_price decimal(15,2)`,
              `discount_amount smallint(5)`, `discount_type enum('points','percent')`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-STR-018
Category:     Prize Store
Description:  pointsDB prizes can be scoped to specific classes via `prize_classes`
              and to specific school types via `prize_school_types`. A prize may also
              have add-on options restricted (`add_on_restricted = 1`) and can limit
              purchases to one per user (`one_per_user = 1`) or up to a maximum number
              per user (`num_per_user`).
Source:       pointsDB_prizes.sql, pointsDB_prize_classes.sql, pointsDB_prize_school_types.sql
DB Evidence:  `add_on_restricted tinyint(1) unsigned DEFAULT 0`,
              `one_per_user tinyint(1) unsigned DEFAULT 0`,
              `num_per_user tinyint(3) unsigned DEFAULT 0`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-STR-019
Category:     Prize Store
Description:  Prize items in pointsDB have three lifecycle types: Template (system-
              defined, not installed), School Installed (customized for a specific
              school), and Installable (available for schools to optionally install,
              with `installable_default_on` flag to auto-install for new schools).
Source:       pointsDB_prizes.sql / table `prizes`
DB Evidence:  `prize_type enum('Template','School Installed','Installable') DEFAULT 'Template'`,
              `installable_default_on tinyint(1) unsigned DEFAULT 0`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-STR-020
Category:     Prize Store
Description:  Auction prizes can be transferred into the store. When transferred,
              the prize is copied from `prizes_auction` into `prizes_store` with an
              initial available quantity of 0. Prizes already existing in the store
              (by name) are excluded from the transfer list.
Source:       mashpia.com/public/admin_store_prizes_transfer.php
DB Evidence:  PHP: `INSERT INTO prizes_store ... prize_available = 0`;
              SQL: `WHERE pa.prize_name NOT IN (SELECT ps.prize_name FROM prizes_store)`
Confidence:   High
SME Verified: No
```

---

## Auctions

```
Rule ID:      BR-AUC-001
Category:     Auctions
Description:  An auction date must be unique per school (or per global pool if
              school_id is NULL). No two auctions for the same school can share the
              same date. The system rejects new auctions or edits that would create
              a date conflict.
Source:       mashpiadb_auctions.sql, mashpia.com/public/admin_auction.php
DB Evidence:  UNIQUE KEY `auction_date` (`auction_date`, `school_id`);
              PHP: `SELECT 1 FROM auctions WHERE auction_date = $auction_date AND school_id <=> ...`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-AUC-002
Category:     Auctions
Description:  An auction has a configurable points window: an optional start date
              (earliest points that count) and a mandatory end date (latest points
              that count, inclusive). An optional trigger date requires a student to
              earn enough points AFTER that date to "activate" their earlier points.
              If left blank, all historical points count.
Source:       mashpia.com/public/admin_auction.php
DB Evidence:  PHP comment: "This is the start cut-off date (inclusive) for points";
              "Soldiers must earn enough points after this date in order to activate
              their previous points. Leave blank to not use this option."
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-AUC-003
Category:     Auctions
Description:  An auction can have an optional maximum prize points cap
              (`max_prize_points`). When set, this limits the most expensive prize a
              student can bid on during that auction. Leaving it blank means no limit.
Source:       mashpiadb_auctions.sql, mashpia.com/public/admin_auction.php
DB Evidence:  `max_prize_points int(10) unsigned DEFAULT NULL`;
              PHP comment: "Maximum points (most expensive item) that can be used on a
              prize for this auction. Leave blank for no limit."
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-AUC-004
Category:     Auctions
Description:  Each prize in an auction has a quantity available and a ratio. If a
              specific quantity is set in `auction_prizes.available`, that exact count
              is awarded. If `available` is NULL (left blank), the number of prizes
              awarded is computed as: ceil(total_tickets / prize_ratio). The
              prize_ratio is defined per prize in the `prizes_auction` table.
Source:       mashpia.com/public/admin_auction_run.php,
              mashpiadb_prizes_auction.sql, mashpiadb_auction_prizes.sql
DB Evidence:  PHP: `$num_prizes = is_null($prizes_row['available']) ?
              ceil($prizes_row['quantity']/$prizes_row['prize_ratio']) : $prizes_row['available']`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-AUC-005
Category:     Auctions
Description:  Auction ticket allocation: a student's points spent on a prize translate
              into tickets for the draw. The number of tickets is proportional to
              the quantity they bid. Winners are drawn pseudo-randomly using PHP's
              mt_rand. A student can win multiple quantities of the same prize
              (recorded via `ON DUPLICATE KEY UPDATE quantity = quantity + 1`).
Source:       mashpia.com/public/admin_auction_run.php
DB Evidence:  PHP: `$tickets[$row['school_id']]->append($row['user_id'], $row['quantity']*$num_prizes);`
              `INSERT INTO auction_winners ... ON DUPLICATE KEY UPDATE quantity = quantity + 1`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-AUC-006
Category:     Auctions
Description:  An auction can only be run once. Once run, `auction_ran` is set to 1
              and the auction cannot be edited or deleted. Editing or deleting an
              auction requires `auction_ran = 0`.
Source:       mashpiadb_auctions.sql, mashpia.com/public/admin_auction.php,
              mashpia.com/public/admin_auction_run.php
DB Evidence:  `auction_ran tinyint(1) unsigned NOT NULL DEFAULT 0`;
              PHP: `WHERE auction_id = $auction_id AND auction_ran = 0`;
              PHP: `"Can't edit"` / `"Can't delete"` shown when auction_ran = 1;
              PHP: `UPDATE auctions SET auction_ran = 1 WHERE auction_id=...`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-AUC-007
Category:     Auctions
Description:  Before running an auction, the system checks for students whose spent
              points exceed their available auction points balance. These students are
              listed as "Users with too many tickets" and must be corrected before the
              auction can proceed cleanly.
Source:       mashpia.com/public/admin_auction_run.php
DB Evidence:  PHP: `if($row['points'] > $auction_points['cur']) $users_over[...] = ...`
              Table displayed: "Users with too many tickets"
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-AUC-008
Category:     Auctions
Description:  After an auction runs, it must be approved by a super-admin before
              results are considered finalized. Winners appear on the auction_winners
              admin page only for auctions with `auction_ran = 1 AND approved = 0`
              (awaiting approval).
Source:       mashpiadb_auctions.sql, mashpia.com/public/admin_auction_winners.php
DB Evidence:  `approved tinyint(1) NOT NULL DEFAULT 0`;
              PHP SQL: `WHERE auction_ran=1 AND approved=0`
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-AUC-009
Category:     Auctions
Description:  Auction prizes can be gender-restricted (Male only, Female only, or
              Both). They can also have grade-range restrictions (min_grade and
              max_grade), with separate grade range enums for French schools
              (min_grade_fr / max_grade_fr). Stock is tracked per prize via `in_stock`.
Source:       mashpiadb_prizes_auction.sql / table `prizes_auction`
DB Evidence:  `gender enum('M','F','B') NOT NULL DEFAULT 'B'`,
              `min_grade enum(...)`, `max_grade enum(...)`,
              `min_grade_fr enum(...)`, `max_grade_fr enum(...)`,
              `in_stock int(11) NOT NULL DEFAULT 0`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-AUC-010
Category:     Auctions
Description:  Auction prizes can be restricted to specific school types via the
              `prizes_auction_types` join table, and to specific individual schools
              via `prizes_auction_schools`. Prizes can also be scoped to a single
              school directly via `prizes_auction.school_id` (NULL = all schools).
Source:       mashpiadb_prizes_auction_types.sql, mashpiadb_prizes_auction_schools.sql,
              mashpiadb_prizes_auction.sql
DB Evidence:  `prizes_auction_types (prize_id, school_type_id)`;
              `prizes_auction_schools (school_id, prize_id)`;
              `prizes_auction.school_id DEFAULT NULL`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-AUC-011
Category:     Auctions
Description:  Prizes can be sourced from either Amazon or a physical warehouse. This
              affects fulfillment routing.
Source:       mashpiadb_prizes_auction.sql / table `prizes_auction`
DB Evidence:  `location set('amazon','warehouse') DEFAULT NULL`
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-AUC-012
Category:     Auctions
Description:  Auction winners can be manually reassigned by admins after the auction
              runs. The winner record can be edited (quantity adjusted) or replaced
              with a non-winner participant via the reassign interface. Deleted winners
              are tracked in `auction_winners_deleted`.
Source:       mashpia.com/public/admin_auction_run.php,
              mashpiadb_auction_winners_deleted.sql
DB Evidence:  PHP: `assign()`, `reassign_prize()`, `save_quantity()` JS functions;
              `auction_winners_deleted` table with (auction_id, user_id, prize_id) PK
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-AUC-013
Category:     Auctions
Description:  Auction winners are tracked for shipping: each winner record has a
              `shipped` flag (default 0) and an optional `shipment_id` linking to
              the physical shipment batch.
Source:       mashpiadb_auction_winners.sql / table `auction_winners`
DB Evidence:  `shipped tinyint(1) NOT NULL DEFAULT 0`,
              `shipment_id int(10) DEFAULT NULL`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-AUC-014
Category:     Auctions
Description:  A user's auction bids are recorded in `auction_user_prizes` with a
              `won` flag (default 0) and a `system_awarded` flag (default 0).
              The `won` flag is set when the student is selected as a winner.
              `system_awarded` indicates the prize was assigned automatically rather
              than by the draw.
Source:       mashpiadb_auction_user_prizes.sql / table `auction_user_prizes`
DB Evidence:  `won tinyint(1) unsigned NOT NULL DEFAULT 0`,
              `system_awarded tinyint(3) unsigned NOT NULL DEFAULT 0`
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-AUC-015
Category:     Auctions
Description:  The auction ratio report calculates the percentage of winners per school
              versus the total registered students at that school. Schools whose
              winner ratio falls at or below a configured threshold are flagged so
              additional winners can be manually entered to balance representation.
Source:       mashpia.com/public/admin_auction_ratio.php
DB Evidence:  PHP: `$scool_ratio = round((($row['total'] / $student_numbers[$row['school_id']]) * 100),2);`
              `if ($scool_ratio <= $ratio) { ... "Enter New Winners" link }`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-AUC-016
Category:     Auctions
Description:  A kiosk auction mode exists (`kiosk_auction` flag). When enabled,
              the auction operates in kiosk mode (likely for in-person event use).
              Auctions can also be made visible on mobile apps from a specific
              date/time (`show_mobile` datetime).
Source:       mashpiadb_auctions.sql / table `auctions`
DB Evidence:  `kiosk_auction tinyint(1) NOT NULL DEFAULT 0`,
              `show_mobile datetime DEFAULT NULL`
Confidence:   Medium
SME Verified: No
```

---

## Raffles

```
Rule ID:      BR-RAF-001
Category:     Raffles
Description:  Raffles operate on three cadences: weekly, monthly, and yearly. Each
              raffle has a defined start_date and end_date (stored as 8-digit integers,
              likely Julian dates), a scheduled run_date (datetime), and an actual
              ran_date (set when it is executed). Raffles are year-scoped.
Source:       mashpiadb_raffles.sql / table `raffles`
DB Evidence:  `type enum('weekly','monthly','yearly') NOT NULL`,
              `start_date int(8) NOT NULL`, `end_date int(8) NOT NULL`,
              `run_date datetime NOT NULL`, `date_ran datetime DEFAULT NULL`,
              `year int(4) NOT NULL DEFAULT 5779`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-RAF-002
Category:     Raffles
Description:  Raffles have audience visibility flags: `show_for_hq`, `show_for_bc`,
              and `show_for_kids`. Each is independently toggleable. By default all
              three are 0 (hidden). The raffle can also be toggled for mobile display
              (`show_on_mobile`, default 0) and can have a scheduled display date
              (`date_to_show`).
Source:       mashpiadb_raffles.sql / table `raffles`
DB Evidence:  `show_on_mobile tinyint(4) NOT NULL DEFAULT 0`,
              `show_for_hq tinyint(3) unsigned DEFAULT 0`,
              `show_for_bc tinyint(3) unsigned DEFAULT 0`,
              `show_for_kids tinyint(3) unsigned DEFAULT 0`,
              `date_to_show datetime DEFAULT NULL`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-RAF-003
Category:     Raffles
Description:  Raffle eligibility is explicitly stored per user per raffle. The
              `eligible` column (tinyint unsigned) records whether a specific user is
              eligible for a specific raffle. The combination of raffle_id, user_id,
              and eligible must be unique, preventing duplicate eligibility records.
Source:       mashpiadb_raffle_eligibility.sql / table `raffle_eligibility`
DB Evidence:  UNIQUE KEY `raffle_id` (`raffle_id`, `user_id`, `eligible`)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-RAF-004
Category:     Raffles
Description:  Raffle prizes have a defined quantity (`qty`). A raffle can have
              multiple prizes, each with its own quantity. Prize-raffle combinations
              are unique (one prize can appear in a raffle only once).
Source:       mashpiadb_raffle_prizes.sql / table `raffle_prizes`
DB Evidence:  `qty int(10) unsigned NOT NULL`,
              UNIQUE KEY `raffle_id` (`raffle_id`, `prize_id`)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-RAF-005
Category:     Raffles
Description:  Raffle winners are tracked per raffle, prize, and user with a `shipped`
              flag (default 0) indicating whether the prize has been physically shipped.
              The combination of raffle_id, prize_id, user_id is unique — a student
              can win a given prize in a given raffle only once.
Source:       mashpiadb_raffle_winners.sql / table `raffle_winners`
DB Evidence:  UNIQUE KEY `raffle_id` (`raffle_id`, `prize_id`, `user_id`),
              `shipped tinyint(4) NOT NULL DEFAULT 0`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-RAF-006
Category:     Raffles
Description:  Monthly raffles have a school-specific prize mapping. The
              `raffles_monthly` table associates a raffle with a prize and a school,
              allowing different schools to receive different prizes in the same
              monthly raffle cycle.
Source:       mashpiadb_raffles_monthly.sql / table `raffles_monthly`
DB Evidence:  `raffle_id`, `prize_id`, `school_id` all nullable foreign references
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-RAF-007
Category:     Raffles
Description:  Raffle prizes (in the legacy prizes table) are categorized by cadence:
              weekly, monthly, or yearly. This is separate from the raffle record
              itself — prizes can be pre-classified by their intended cadence.
              Prizes also have gender (M or F) and an optional shipping code.
Source:       mashpiadb_prizes.sql / table `prizes`
DB Evidence:  `type_of_prize enum('weekly','monthly','yearly') NOT NULL`,
              `gender enum('M','F') DEFAULT NULL`,
              `shipping_code varchar(45) DEFAULT NULL`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-RAF-008
Category:     Raffles
Description:  A raffle can require a student to have completed a minimum number of
              task days to be eligible (`days_of_tasks` column). If NULL, no task
              completion requirement is enforced.
Source:       mashpiadb_raffles.sql / table `raffles`
DB Evidence:  `days_of_tasks smallint(6) unsigned DEFAULT NULL`
Confidence:   Medium
SME Verified: No
```

---

## Shipping & Fulfillment

```
Rule ID:      BR-SHP-001
Category:     Shipping & Fulfillment
Description:  Shipments go through four status stages: planned, in transit, delivered,
              and archived. The default status for a new shipment is 'in transit'.
              A separate boolean `delivered` flag also exists as a redundant indicator,
              as does an `archived` flag.
Source:       mashpiadb_shipments.sql / table `shipments`
DB Evidence:  `status enum('planned','in transit','delivered','archived') NOT NULL DEFAULT 'in transit'`,
              `delivered tinyint(4) DEFAULT 0`, `archived tinyint(4) DEFAULT 0`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SHP-002
Category:     Shipping & Fulfillment
Description:  A single shipment batch can contain multiple types of items: gifts,
              hachayol (soldier rank/military items), prizes, medals, and ranks. Each
              line item in a shipment is typed by this enum. Multiple items of varying
              types can belong to the same shipment.
Source:       mashpiadb_shipment_details.sql / table `shipment_details`
DB Evidence:  `type enum('gift','hachayol','prize','medal','rank') DEFAULT NULL`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SHP-003
Category:     Shipping & Fulfillment
Description:  Shipping rates are determined by a combination of package type, shipping
              zone, and child count. The unique constraint prevents duplicate rate
              entries for the same type+zone+child_count combination.
Source:       mashpiadb_shipping_rates.sql / table `shipping_rates`
DB Evidence:  UNIQUE KEY `noDuplicates` (`type`, `zone`, `child_count`)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SHP-004
Category:     Shipping & Fulfillment
Description:  The default shipping country is USA. Shipping addresses require name,
              address line 1, city, state, and zip code (all NOT NULL). Address line 2
              is optional.
Source:       mashpiadb_shipping_addresses.sql / table `shipping_addresses`
DB Evidence:  `country varchar(45) DEFAULT 'USA'`, `address_line_1 NOT NULL`,
              `city NOT NULL`, `state NOT NULL`, `zip NOT NULL`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SHP-005
Category:     Shipping & Fulfillment
Description:  Yearly prize shipping is tracked with both a `shipped` flag and a
              `distributed` flag (default 0). This implies a two-stage process: prizes
              are first shipped from the warehouse and then separately distributed
              (handed out) to students. Each combination of id+type is unique,
              preventing double-counting.
Source:       mashpiadb_yearly_prize_shipping.sql / table `yearly_prize_shipping`
DB Evidence:  `shipped tinyint(3) unsigned DEFAULT NULL`,
              `distributed tinyint(4) DEFAULT 0`,
              UNIQUE KEY `unique_id_type_index` (`id`, `type`)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SHP-006
Category:     Shipping & Fulfillment
Description:  Hachayol (military-themed soldier item) shipments are tracked per school
              with quantity and linked to a shipment batch. Hachayol items are not
              individually tracked by student but by school-level bulk quantity.
Source:       mashpiadb_hachayol_shipping.sql / table `hachayol_shipping`
DB Evidence:  `school_id`, `hachayol_id`, `qty`, `shipment_id` — no student-level key
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-SHP-007
Category:     Shipping & Fulfillment
Description:  Parent-facing shipments (for items ordered by school administrators for
              families) have a numeric status field (default 0) and track quantity via
              a tinyint (max 255). The item being shipped is referenced by a varchar
              item_id (not a FK), and the whole record is scoped to a year.
Source:       mashpiadb_parent_shipping.sql / table `parent_shipping`
DB Evidence:  `status tinyint(4) NOT NULL DEFAULT 0`,
              `qty tinyint(3) unsigned DEFAULT NULL`,
              `item_id varchar(45)`, `year int(10) unsigned`
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-SHP-008
Category:     Shipping & Fulfillment
Description:  Purchase addresses for extra_purchases are stored separately in
              `purchase_addresses`, linked to a purchase_id. This allows shipping to
              an address that differs from the admin's registered address.
Source:       mashpiadb_purchase_addresses.sql / table `purchase_addresses`
DB Evidence:  `purchase_id int(10) unsigned NOT NULL` (FK to extra_purchases),
              separate `address`, `city`, `state`, `zip`, `country` columns
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SHP-009
Category:     Shipping & Fulfillment
Description:  The shipping translation table stores Spanish translations of item names
              and details, referenced by a string item_id. This implies international
              shipping labels or customs forms may need item descriptions in Spanish.
Source:       mashpiadb_shipping_translation.sql / table `shipping_translation`
DB Evidence:  `item_id varchar(45)`, `spanish_name varchar(65)`, `spanish_detail varchar(55)`
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-SHP-010
Category:     Shipping & Fulfillment
Description:  School packages (academic year program bundles) have an associated fee
              structure. Each package has a base fee stored in `school_packages.fee`
              and may have additional per-student fees broken out in
              `school_package_fees` (with a `fee_each` decimal amount per fee line).
Source:       mashpiadb_school_packages.sql, mashpiadb_school_package_fees.sql
DB Evidence:  `fee decimal(8,2) unsigned NOT NULL` (school_packages);
              `fee_each decimal(8,2) unsigned NOT NULL` (school_package_fees)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SHP-011
Category:     Shipping & Fulfillment
Description:  Subscription packages (in the `packages` table) are linked to a
              specific child_type_id and can be activated or deactivated (`is_active`
              default 1). They reference catalog items via `item_id`.
Source:       mashpiadb_packages.sql / table `packages`
DB Evidence:  `is_active tinyint(1) DEFAULT 1`, `child_type_id int(11) NOT NULL`
Confidence:   Medium
SME Verified: No
```

```
Rule ID:      BR-SHP-012
Category:     Shipping & Fulfillment
Description:  Mivtzoim (outreach) catalog items have both an MSRP and a sale price
              and track available stock. Items also have a shipping_code for routing
              and can be associated with a holiday (yom_tov). Stock is stored as an
              integer (can be negative, implying backorder is possible).
Source:       mashpia_purchases_mivtzoim_items.sql / table `mivtzoim_items`
DB Evidence:  `msrp decimal(6,2) unsigned`, `sale decimal(6,2) unsigned`,
              `stock int(11) NOT NULL` (signed, not unsigned),
              `yom_tov varchar(45)`, `shipping_code varchar(45)`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-SHP-013
Category:     Shipping & Fulfillment
Description:  Items in the subscription catalog (`items` table) can be restricted by
              user age group ('all', 'young', or 'old') and can be marked as
              available for first-time purchasers only (`first_time_only = 1`).
              Items can also be deactivated (`is_active = 0`).
Source:       mashpiadb_items.sql / table `items`
DB Evidence:  `user_age enum('all','young','old') DEFAULT NULL`,
              `first_time_only tinyint(1) NOT NULL`,
              `is_active tinyint(1) DEFAULT 1`
Confidence:   High
SME Verified: No
```

---

## Discounts & Coupons

```
Rule ID:      BR-DIS-001
Category:     Discounts & Coupons
Description:  Discount records are scoped by year, and optionally to a specific school
              and/or a specific user. A discount is a dollar amount (unsigned decimal
              up to $9,999.99). The `used` datetime field is NULL until the discount
              is redeemed; once set, the discount is consumed.
Source:       mashpiadb_discounts.sql / table `discounts`
DB Evidence:  `amount decimal(6,2) unsigned DEFAULT NULL`,
              `used datetime DEFAULT NULL`,
              `year int(10) unsigned`, `school_id int(10) unsigned`, `user_id int(10) unsigned`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-DIS-002
Category:     Discounts & Coupons
Description:  Each coupon code is unique (enforced by UNIQUE KEY on `code`). A coupon
              has a type (free-text, not an enum), a monetary value (unsigned decimal),
              and a used flag (tinyint, default 0 = unused). Once redeemed, the
              `date_redeemed` timestamp is set. Coupons are year-scoped.
Source:       mashpiadb_coupon_codes.sql / table `coupon_codes`
DB Evidence:  UNIQUE KEY `coupon` (`code`), `used tinyint(3) unsigned NOT NULL DEFAULT 0`,
              `date_redeemed datetime DEFAULT NULL`, `year int(10) unsigned NOT NULL`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-DIS-003
Category:     Discounts & Coupons
Description:  Coupon codes are created by admins and include a reason for issuance
              and a creator identifier. An optional serial_num allows batch-generated
              coupons to be sequentially numbered.
Source:       mashpiadb_coupon_codes.sql / table `coupon_codes`
DB Evidence:  `admin_id int(10) unsigned NOT NULL DEFAULT 0`,
              `created_by varchar(65)`, `reason varchar(85)`,
              `serial_num int(10) unsigned DEFAULT NULL`
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-DIS-004
Category:     Discounts & Coupons
Description:  User store withdrawals (point balance cashouts via kiosk) are tracked
              in two tables: `user_store_withdraw` and `user_withdraw`. Both use a
              barcode/code_id as the primary key. A code_id is generated at print time
              and the scan_date is recorded when redeemed. Points must be a non-negative
              unsigned decimal.
Source:       mashpiadb_user_store_withdraw.sql, mashpiadb_user_withdraw.sql
DB Evidence:  `print_date timestamp`, `scan_date timestamp NULL DEFAULT NULL`,
              `points decimal(8,2) unsigned NOT NULL`;
              `user_withdraw` also stores `jul_print_date` (Julian date of printing)
Confidence:   High
SME Verified: No
```

```
Rule ID:      BR-DIS-005
Category:     Discounts & Coupons
Description:  The `user_withdraw` table stores withdrawals with a Julian print date
              (`jul_print_date`), suggesting withdrawals are keyed by the Hebrew
              calendar date for the school year context.
Source:       mashpiadb_user_withdraw.sql / table `user_withdraw`
DB Evidence:  `jul_print_date mediumint(8) unsigned NOT NULL`
Confidence:   Medium
SME Verified: No
```
