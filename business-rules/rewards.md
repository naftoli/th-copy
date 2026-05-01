# Business Rules: Rewards Program

Generated from source read on 2026-05-01.
Sources: PHP API (`mashpia.com/public/api/rewards/`), PHP models, legacy auction PHP classes (`mashpia.com/public/auction/`), SQL schemas (`SQLdump/`), React frontend (`front-end/src/pages/rewards/`, `front-end/src/store/rewards/`).

---

# Module: Miles / Points

## Business Rules

1. Miles are the single currency soldiers earn and spend across the rewards system; the same points value is used for both the store and the auction.
   Source: `pointsDB_user_points.sql` (schema), `api/rewards/miles.php`

2. Every point transaction is recorded as a row in `pointsDB.user_points` with a signed `points` value (positive = credit, negative = debit) and a `resource_name` that identifies the source.
   Source: `pointsDB_user_points.sql`

3. Known `resource_name` values that indicate how miles were credited or debited:
   - `admin_users_manual` — admin added/subtracted miles for all purposes
   - `transaction_manager_store` — store-only manual adjustment or order reversal
   - `auction_only_points` — miles restricted to auction use only
   - `store` — miles deducted when a store order is placed
   Source: `api/rewards/miles.php:40-46`, `api/rewards/orders.php:158,262`

4. A row in `user_points` may be flagged `auction_only_points = 1`, which marks the credit as usable only in the auction and not in the store.
   Source: `pointsDB_user_points.sql:column auction_only_points`, `pointsDB_achievement_cards.sql:column auction_only_points`

5. When an admin manually adds or subtracts miles, they must choose one of three scopes: All Miles, Store Only, or Auction Only; the selected scope determines the `resource_name` written to `user_points`.
   Source: `api/rewards/miles.php:18-46`, `front-end/src/pages/rewards/miles/MilesPage.jsx:55-57`

6. The manual miles form accepts values between 1 and 10,000 inclusive; values outside that range are flagged as invalid on the frontend.
   Source: `front-end/src/pages/rewards/miles/MilesPage.jsx:66`

7. A teacher's available miles for printing achievement cards come from the `miles_balance` column on their platoon record, not from `user_points`.
   Source: `api/rewards/miles.php:66-67`, `api/rewards/achievement_cards.php:28,64-66`

8. Only teachers have a displayable "available miles" balance on the card-generation page; base commanders and higher roles see `false` (no limit enforced by the server).
   Source: `api/rewards/miles.php:63-70`, `api/rewards/achievement_cards.php:32-38`

9. When a teacher deletes unused (not-yet-scanned) cards, the points value of those cards is returned to the teacher's `miles_balance`.
   Source: `api/rewards/achievement_cards.php:98-107`

10. The Miles management page is not accessible to teachers (the page returns a 404 for the `TEACHER` role).
    Source: `front-end/src/pages/rewards/miles/MilesPage.jsx:63-64`

11. A store order deducts miles atomically: `user_points` is credited a negative amount equal to `prize.points × quantity`, and `prize_count` is decremented, inside a database transaction; if any step fails the whole transaction rolls back.
    Source: `api/rewards/orders.php:131-199`

12. The `admin_credits` table stores time-bounded credits (with `start_epoch` / `end_epoch`) assigned to a user or institution; these are separate from the main `user_points` ledger and likely represent promotional allocations.
    Source: `pointsDB_admin_credits.sql`

## Open Questions

- How exactly is a soldier's spendable store balance calculated? The code calls `$user->storeMiles()` but that method is not in the provided files. It is unclear whether `auction_only_points` rows are excluded from the store balance at the DB query level or in PHP. (Source: `api/rewards/orders.php:117,118`, `Soldier` model not provided)
- The `member_points` table in `mashpiadb` records a `points` value per `user_id` per `points_date`. Its relationship to `pointsDB.user_points` is not shown in any provided file. (Source: `mashpiadb_member_points.sql`)
- `user_withdraw` and `user_store_withdraw` appear to be legacy withdrawal/voucher tables with `print_date` and `scan_date`. Their current role (if any) in the active rewards flow is not demonstrated in any provided code. (Source: `mashpiadb_user_withdraw.sql`, `mashpiadb_user_store_withdraw.sql`)

---

# Module: Prizes / Store

## Business Rules

1. Every store prize belongs to exactly one institution (school), identified by `institution_id`.
   Source: `pointsDB_prizes.sql:column institution_id`

2. A prize has a `prize_count` (stock). When stock reaches 0 the prize is no longer purchasable; the store query filters `prize_count > 0`.
   Source: `api/rewards/orders.php:55`, `api/rewards/orders.php:168-172`

3. A prize with `is_active = 0` is hidden from store shoppers but is still visible (and sortable by active status) in the admin prize list.
   Source: `api/rewards/prizes.php:27` (ORDER BY `is_active DESC`), `pointsDB_prizes.sql:column is_active`

4. Prizes can be restricted to one or more platoons via the `prize_classes` join table. A teacher only sees prizes that have no platoon restriction or that include their platoon.
   Source: `api/rewards/prizes.php:42-45`, `api/models/StorePrize.php:51-68`

5. When a teacher creates a prize, the system automatically limits that prize to the teacher's own platoon (by inserting into `prize_classes`) even if no explicit platoon was specified.
   Source: `api/rewards/prizes.php:87-89`

6. A teacher can edit a prize only if all three conditions are true: (a) the prize has `teacher_edit = 1`, (b) the prize is limited to exactly one platoon, and (c) that platoon is the requesting teacher's platoon.
   Source: `api/models/StorePrize.php:113-117`

7. The `teacher_edit` flag on a prize can only be set when the prize is limited to exactly one platoon; the frontend disables the toggle otherwise.
   Source: `front-end/src/pages/rewards/prizes/PrizeForm.jsx:148-149`

8. Prize names must be 3–50 characters; prize cost must be 1–999,999 miles; stock count must be 0–99,999,999,999.
   Source: `front-end/src/pages/rewards/prizes/PrizeForm.jsx:68,75,76`

9. A prize supports an optional discount expressed either as a flat miles reduction (`discount_type = 'points'`) or a percentage off (`discount_type = 'percent'`), stored in `discount_amount` and `discount_type` columns.
   Source: `pointsDB_prizes.sql:columns discount_amount, discount_type`, `front-end/src/pages/rewards/prizes/PrizeForm.jsx:176-215`

10. A prize can be set to allow only one purchase per soldier (`one_per_user = 1`). The store query filters such prizes out once the soldier has an existing non-reversed order for them.
    Source: `api/rewards/orders.php:57-64` (`HAVING (one_per_user = 0 OR ordered = 0)`), `pointsDB_prizes.sql:column one_per_user`

11. A prize can additionally cap per-soldier purchases via `num_per_user`; 0 means unlimited.
    Source: `pointsDB_prizes.sql:column num_per_user`, `front-end/src/pages/rewards/prizes/PrizeForm.jsx:156-172`

12. When a prize is deleted it is permanently removed from `pointsDB.prizes` and an audit record (admin, school, prize id, name, points) is written to `mashpiadb.deleted_prizes`.
    Source: `api/rewards/prizes.php:155-169`

13. Prize images must be JPG, JPEG, or PNG; other file types are rejected.
    Source: `api/models/StorePrize.php:83-84`

14. Only the HQ role can create or edit prize templates. Non-HQ users accessing the Templates page are redirected to the Prizes page.
    Source: `api/rewards/templates.php:27,53`, `front-end/src/pages/rewards/prizes/TemplatesPage.jsx:109-110`

15. A template is a prize with `prize_type = 'Template'`, `institution_id = 1`, and `parent_prize_id = 0`. Templates serve as starting points that bases can copy into their own store.
    Source: `api/rewards/templates.php:36-38`, `pointsDB_prizes.sql:column prize_type`

16. Only prizes with a non-empty `prize_name`, `prize_type = 'Template'`, and `parent_prize_id = 0` appear in the templates list.
    Source: `api/rewards/templates.php:13`

17. Admins (non-BC, non-Teacher roles) are redirected away from the Orders page to the Templates page.
    Source: `front-end/src/pages/rewards/orders/OrdersPage.jsx:84-85`

18. A Base Commander can open or close their school's store via the `school_store` flag. The flag is surfaced to the frontend as `school_store` alongside the prize list.
    Source: `api/rewards/prizes.php:18,50-53`, `api/rewards/prizes.php:138-153`

19. The legacy `mashpiadb.prizes_store` and `mashpiadb.global_prizes` tables are structurally present but no active API code in the provided files reads from them; the active store catalogue lives in `pointsDB.prizes`.
    Source: `mashpiadb_prizes_store.sql`, `mashpiadb_global_prizes.sql`

## Open Questions

- The `discount_amount` / `discount_type` fields are stored on the prize but no server-side code in the provided files applies the discount when computing order cost. It is unclear whether the discount is applied client-side only, in an unprovided code path, or is a feature under development. (Source: `api/rewards/orders.php:97-106`, `pointsDB_prizes.sql`)
- `num_per_user` is stored and exposed but no server-side order validation checks it; only `one_per_user` is enforced in the store query. (Source: `api/rewards/orders.php:57-64`, `pointsDB_prizes.sql:column num_per_user`)

---

# Module: Orders

## Business Rules

1. An order starts in status `Checked Out` and can transition to `Redeemed`; it can also be reversed (soft-deleted via `is_reversed = 1`). The schema defines a third status value `Printed` but no active code sets it.
   Source: `pointsDB_user_prizes.sql:column status enum('Checked Out','Printed','Redeemed')`, `api/rewards/orders.php`

2. Placing an order requires at least one item; an order with zero items is rejected.
   Source: `api/rewards/orders.php:80-82`

3. Each item quantity must be ≥ 1; a quantity of 0 is rejected.
   Source: `api/rewards/orders.php:92-94`

4. The total cost of all items in the order must not exceed the soldier's available store miles; if it does, the order is rejected with the total cost and available balance included in the error message.
   Source: `api/rewards/orders.php:117-119`

5. All items in a multi-item order share one random 10-digit serial number generated per order; the serial is stored on each `user_prizes` row.
   Source: `api/rewards/orders.php:122-128,146`

6. On order placement, for each item a `user_prizes` row (status = `Checked Out`) and a corresponding negative `user_points` row (resource = `store`) are inserted, and `prizes.prize_count` is decremented—all within a single database transaction.
   Source: `api/rewards/orders.php:131-192`

7. Redeeming orders sets `status = 'Redeemed'` and records the `redeemed_by` admin ID; un-redeeming resets `status = 'Checked Out'` and clears `redeemed_by`.
   Source: `api/rewards/orders.php:204-231`

8. Reversing an order sets `is_reversed = 1` and records `reversed_by`, inserts a compensating positive `user_points` row (resource = `transaction_manager_store`), and adds the ordered quantity back to `prize_count`.
   Source: `api/rewards/orders.php:234-275`

9. Reversed orders are excluded from all order list queries (`WHERE is_reversed = 0`).
   Source: `api/rewards/orders.php:31`

10. A Base Commander sees orders for their entire school; a teacher sees only orders for their platoon. Any other role gets an Access Denied response.
    Source: `api/rewards/orders.php:17-21`

11. The default order list view shows `Checked Out` orders; users can switch to viewing `Redeemed` orders by passing `?redeemed=true`.
    Source: `api/rewards/orders.php:12-16`, `front-end/src/pages/rewards/orders/OrdersPage.jsx:33-42`

12. The frontend order form disables a prize option if the prize's cost exceeds the soldier's current available miles.
    Source: `front-end/src/pages/rewards/orders/OrderForm.jsx:27-29`

13. The frontend enforces `one_per_user` by capping the quantity field at 1 for such prizes and showing "1 per soldier" validation feedback.
    Source: `front-end/src/pages/rewards/orders/OrderForm.jsx:47-53,68-69,97-98`

14. The frontend automatically adjusts an item's quantity downward (or removes it entirely) if adding it would push the order total above the soldier's available miles.
    Source: `front-end/src/pages/rewards/orders/OrderModal.jsx:63-84`

15. A separate `pointsDB.orders` table exists (status: `Pending`/`Redeemed`) with a currency field (`CAD`/`USD`) and a `total_price`. This is structurally distinct from `user_prizes` and appears to relate to a financial-purchase flow rather than the miles-spend flow.
    Source: `pointsDB_orders.sql`

## Open Questions

- The schema has `user_prizes.actual_points` (nullable). No provided code sets this column. Its intended meaning (actual cost after discount?) is unclear. (Source: `pointsDB_user_prizes.sql:column actual_points`)
- `pointsDB.orders` and `pointsDB.purchases` exist alongside `user_prizes` but no active API code in the provided files uses them. They may belong to a payment/purchasing flow for buying prize packages. (Source: `pointsDB_orders.sql`, `pointsDB_purchases.sql`, `pointsDB_student_purchases.sql`)

---

# Module: Achievement Tasks

## Business Rules

1. A task must be associated with exactly one campaign (subject); creating a task without a `subject_id` is rejected.
   Source: `api/rewards/achievement_tasks.php:39-40`

2. Tasks are filtered to subjects of type `''`, `'WWTC'`, `'Tanya'`, or `'achievement'`; other subject types are excluded.
   Source: `api/rewards/achievement_tasks.php:11-14`

3. Task names must be 3–75 characters and miles value must be 1–1,000.
   Source: `front-end/src/pages/rewards/tasks/TaskModal.jsx:77-78`, `front-end/src/pages/rewards/tasks/TaskModal.jsx:99`

4. When a Base Commander creates a task, it is automatically scoped to their base (`task.base = login.id`).
   Source: `api/rewards/achievement_tasks.php:32-33`

5. When a Teacher creates a task, it is automatically scoped to their school as base and their login ID as platoon.
   Source: `api/rewards/achievement_tasks.php:34-37`

6. An Institution (INST) user can only create tasks within campaigns that belong to their institution; attempts on other campaigns are rejected.
   Source: `api/rewards/achievement_tasks.php:42-45`

7. A task can be flagged `auction_only_points`; when set, miles earned from scanning a card for this task are restricted to auction use and cannot be spent in the store.
   Source: `mashpiadb_achievement_tasks.sql:column auction_only_points`, `front-end/src/pages/rewards/tasks/TaskModal.jsx:107-113`

8. Only the fields `subject_id`, `task`, `points`, and `auction_only_points` may be updated via the task update endpoint; other fields are ignored.
   Source: `api/rewards/achievement_tasks.php:56-59`

9. Task editability is role-scoped: HQ can edit any task; INST can edit tasks belonging to their institution's bases or their own campaigns; BC can only edit tasks where `base = login.id`; Teachers can only edit tasks where `platoon = login.id`.
   Source: `api/models/AchievementTask.php:30-42`

10. The Tasks page only renders the subset of tasks the logged-in user is permitted to edit (`editable = true`); read-only tasks are not shown.
    Source: `front-end/src/pages/rewards/tasks/TasksPage.jsx:98,172`

## Open Questions

- `achievement_tasks.base` defaults to `1` and `achievement_tasks.platoon` defaults to `1` in the schema; these appear to be sentinel values meaning "All Bases" / "All Platoons" respectively. The exact scoping logic for values of 1 versus a real ID is only partially visible. (Source: `mashpiadb_achievement_tasks.sql`, `api/models/AchievementTask.php:22-23`)

---

# Module: Achievement Cards

## Business Rules

1. Card serials are 20-character strings that always begin with the digit `4`, generated by combining two random numbers. Uniqueness is checked against the existing `achievement_cards` table before creation.
   Source: `api/rewards/achievement_cards.php:14-20`

2. Printing cards costs miles from the teacher's `miles_balance`; the cost is `task.points × card_count`. The server checks this balance before generating cards and rejects the request if insufficient.
   Source: `api/rewards/achievement_cards.php:28-39`

3. If the teacher has exactly 0 miles, the specific message "You do not have any miles left. Please come back at the beginning of the month when you will be given more miles." is returned (implying miles are refreshed monthly).
   Source: `api/rewards/achievement_cards.php:32-33`

4. The frontend computes the maximum printable card count as `floor(miles / task.points)` and disables the Print button if the requested count would exceed that.
   Source: `front-end/src/pages/rewards/cards/CardsPage.jsx:71-76`

5. After cards are generated, the teacher's `miles_balance` is immediately decremented by `task.points × card_count`.
   Source: `api/rewards/achievement_cards.php:62-66`

6. A card is tagged with `institution_id`, `campaign_id` (subject), `task_id`, `class_id`, `card_points`, and `created_by` admin ID at creation.
   Source: `api/rewards/achievement_cards.php:43-52`

7. Cards have a `card_type` set to `'Teacher'` when created by a teacher, or `'Institution Administrator'` otherwise.
   Source: `api/rewards/achievement_cards.php:49` (`$login->code == 'TEACHER' ? 'Teacher' : 'Institution Administrator'`)

8. Cards start with `status = 'not scanned'`; scanning changes this to `'scanned'`. A third status `'weblink'` exists in the schema.
   Source: `pointsDB_achievement_cards.sql:column status enum('scanned','not scanned','weblink')`

9. An admin can delete cards that are (a) created by them, (b) `status = 'not scanned'`, (c) belong to their school/institution, (d) belong to their class, and (e) were created on or before a chosen date.
   Source: `api/rewards/achievement_cards.php:83-94`

10. Only teacher-created unscanned cards refund miles back to `miles_balance` on deletion; institution-administrator or other types do not trigger a miles refund.
    Source: `api/rewards/achievement_cards.php:98-108`

11. Institution (INST) admins deleting cards can delete across all schools belonging to their institution (or cards with `institution_id = 0`).
    Source: `api/rewards/achievement_cards.php:85-86`

## Open Questions

- The card-generation endpoint in `achievement_cards.php` inserts records using `AchievementCard::create()` but the print action itself is a separate legacy endpoint at `/api/print/achievement_cards` (a form POST). The server-side logic for that print endpoint is not among the provided files. (Source: `front-end/src/pages/rewards/cards/CardsPage.jsx:99`)
- The `auction_only_points` flag on `achievement_cards` is present in the schema but no code in the provided files sets it during card creation; it defaults to 0. It is unclear if this flag is set elsewhere (e.g., during the scan/redeem flow). (Source: `pointsDB_achievement_cards.sql:column auction_only_points`)
- `achievement_cards.extra_serial` and `achievement_cards.campaign_image_id` columns are defined but never referenced in the provided PHP or React code. (Source: `pointsDB_achievement_cards.sql`)

---

# Module: Auction

## Business Rules

1. Auctions are defined in `mashpiadb.auctions` with a Hebrew-calendar date (`auction_date`), an optional points window (`auction_points_start_date`), and a `max_prize_points` cap. Each auction also carries `auction_ran` (0/1) and `approved` (0/1) flags.
   Source: `mashpiadb_auctions.sql`

2. Auction prizes are drawn from `prizes_auction`, which stores name, point cost, ratio, grade range (`min_grade` / `max_grade`), gender (`M`/`F`/`B`), stock (`in_stock`), and optional category. A prize can be archived (`archived = 1`).
   Source: `mashpiadb_prizes_auction.sql`

3. Prizes with `prize_points >= 72` are classified as "big prizes" (grand prizes); prizes with `prize_points < 72` are "small prizes". This threshold is the sole criterion distinguishing the two tiers.
   Source: `auction/auction.php:43`, `auction/class.auction.php:56`

4. The big-prize winner-selection algorithm picks the user from the pool of ticket-holders for that prize who comes from the school with the fewest big prizes won so far, breaking ties by the individual user's big-prizes-won count. Each win increments both the school's and the user's `big_prizes_won` counter.
   Source: `auction/auction.php:66-108`

5. Schools with IDs 12, 13, and 14 are excluded from both big-prize and small-prize award eligibility.
   Source: `auction/auction.php:73,151`

6. A student who has already won 10 or more small prizes (tracked in `users.small_prizes_won`) is excluded from further small-prize selection.
   Source: `auction/auction.php:150,216`

7. Within a single auction run, a student may win at most 3 prizes in total (`numWon < 3` check), and cannot win the same specific prize more than once.
   Source: `auction/auction.php:211-216`

8. The school's proportional share of prizes is enforced via a ratio: the school's share of total prizes awarded must not exceed (`school_users / total_users × 100`) + 2 percentage points. If the ratio is exceeded, the school is skipped for that prize slot.
   Source: `auction/auction.php:289-344`

9. The `NewAuction` class represents a ticket-based (raffle) auction variant: each user holds a number of tickets equal to their `quantity` in `auction_user_prizes`, and a random ticket index is drawn. Users who have already won in the same run are removed from the ticket pool for subsequent draws (in the `run()` method but not in `setWinners()` / `runLast()`).
   Source: `auction/class.newAuction.php:17-80`

10. `NewAuction.setWinners()` (used by `runLast`) does NOT enforce a global "one win per user" constraint across all prizes in that batch — a user can win multiple prizes in a single batch run.
    Source: `auction/class.newAuction.php:108-135` (no check against `usersWon`)

11. The legacy `Auction` class (`class.auction.php`) prioritises users who already hold tickets for the prize (from `auction_user_prizes`) as the first pool; if that pool is exhausted it falls back to users with ≥ 1,200 points, then to users with any points at all.
    Source: `auction/class.auction.php:750-766`

12. Grand prizes in the legacy `Auction` class are assigned one per school, chosen randomly from the eligible schools list, with no school receiving two grand prizes.
    Source: `auction/class.auction.php:770-797`

13. Winners are persisted to `auction_winners` with `(auction_id, user_id, prize_id)` as the primary key; the `shipped` flag and `shipment_id` track fulfilment.
    Source: `mashpiadb_auction_winners.sql`

14. The `prize_classes` table (`prizes_auction_types`) associates auction prizes with school types, allowing different prize catalogues per school type.
    Source: `mashpiadb_prizes_auction_types.sql`, `mashpiadb_school_auction_prizes.sql`

15. An auction can be scoped to a single school (`auctions.school_id`); if `school_id` is NULL the auction is system-wide.
    Source: `mashpiadb_auctions.sql:column school_id`

16. Auctions can be flagged `kiosk_auction = 1` and optionally have a `show_mobile` datetime controlling when they appear in the mobile app.
    Source: `mashpiadb_auctions.sql:columns kiosk_auction, show_mobile`

17. `RaffleTicket.calculateTickets()` grants each user one ticket per mission completed (`SUM(mission_count)`) within the auction's date window; this provides an alternative points-to-ticket conversion path for raffle-style runs.
    Source: `auction/class.raffleTicket.php:26-42`

## Open Questions

- The auction run interface (`auction_run.php`) only shows auctions where `auction_ran = 0`, but no code in the provided files sets `auction_ran = 1` after running — it may be set manually or by an unprovided script. (Source: `auction/auction_run.php:39-40`)
- `prizes_auction.prize_ratio` is stored but never referenced in the provided auction runner code. Its original purpose (weighting tickets by points ratio) is commented out. (Source: `mashpiadb_prizes_auction.sql:column prize_ratio`)
- Grade and gender filtering columns exist on `prizes_auction` (`min_grade`, `max_grade`, `gender`) but the provided auction runner code does not filter by them. It is unclear if this is enforced elsewhere. (Source: `mashpiadb_prizes_auction.sql`)
- The `Auction` class hard-codes school-to-prize-quantity mappings in PHP arrays (`setPrizesSchools`, `setPrizeSchoolRel`). Whether these reflect the current active configuration or historical data is not determinable from code alone. (Source: `auction/class.auction.php:97-612`)
