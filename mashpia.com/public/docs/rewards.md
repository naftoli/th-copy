# Rewards Program

The Rewards Program lets soldiers earn **Miles** by completing missions and tasks, and spend those miles in the **Store** to purchase prizes. This section covers all pages for managing the rewards program.

---

## Achievement Cards (`/rewards/cards`)

**Access:** HQ, INST, BC, TEACHER

**Purpose:** Print physical achievement cards with a miles value printed on them. You hand these cards to soldiers when they complete a reward task — the soldier then "spends" the card at the store.

### How It Works

- Each card has a unique number and a miles value
- Teachers have a limited pool of **Available Miles** they can distribute each period
- Printing cards deducts miles from the teacher's available pool
- Cards that are never used can be deleted and their numbers recycled

### Generating Cards

1. Select a **Campaign** (only campaigns with achievement tasks appear)
2. Select a **Task** — the specific achievement; the miles value per card is shown
3. Enter the **Number of Cards** you want to print
   - Maximum = your available miles ÷ miles per card
4. Click **Print** — a printable card sheet opens in a new tab

> **Teachers:** Your available miles balance is shown at the top of the page. Once cards are printed, those miles are deducted immediately.

### Deleting Unused Cards

If you have cards that were printed but never given out or spent:

1. Set the **"Printed On Or Before"** date
2. Click **Delete Unused Cards**
3. Confirm the warning dialog

> **Warning:** Deleted cards are permanently removed and their numbers are recycled. This cannot be undone.

---

## Reward Tasks (`/rewards/tasks`)

**Access:** HQ, INST, BC, TEACHER

**Purpose:** View and manage the achievement tasks that soldiers can complete to earn Miles for the store.

> **Note:** These are different from **Mission Tasks** — reward tasks are specifically tied to the store and miles system.

### What You See

| Column | Description |
|--------|-------------|
| Task Name | Name of the achievement task |
| Miles | Miles awarded for this task |
| Auction Only | If checked, miles can only be used in auctions, not the store |
| Campaign | Which campaign this task belongs to |
| Base/Platoon | Which base or platoon owns this task (if custom) |

### Actions

- **Create Task** — opens a form to define a new reward task
- **Edit** — click the task name to edit it in a modal
- **Auction Only toggle** — check/uncheck to restrict miles usage
- **Download CSV** — export to Excel

---

## Prizes (`/rewards/prizes`)

**Access:** BC, TEACHER (HQ/INST are redirected to Prize Templates)

**Purpose:** Manage the prizes available in your base's store. Prizes come from HQ templates, and you activate/customize them for your store.

### What You See

| Column | Description |
|--------|-------------|
| Image | Prize photo |
| Prize Name | Name of the prize |
| Miles | Miles cost for soldiers to purchase |
| In Stock | Current stock quantity |
| Active | Whether this prize is available in your store |
| Max Per Soldier | How many times one soldier can buy this prize |
| Last Updated | Date of last change |

### Actions

- **Create Prize** (BC only) — create a custom prize for your store (not available to HQ/INST from this view)
- **Edit** — click to open the prize edit form
- **Edit Picture** — click the prize image to upload/crop a new photo
- **Toggle Active** — check/uncheck to show or hide a prize in your store
- **Edit Max Per Soldier** — click to edit inline
- **Open/Close Store** — toggle your store open or closed for soldiers
- **Apply Discount** — select prizes and apply a Miles discount or % off
- **Clear Discount** — remove discounts from selected prizes
- **Download CSV**

### Applying a Discount

1. Check the checkbox(es) next to the prizes you want to discount
2. Click **Apply Discount**
3. Choose discount type: **Miles Discount** (fixed reduction) or **Percentage Off**
4. Enter the discount amount
5. Click Apply

---

## Prize Templates (`/rewards/templates`)

**Access:** HQ only (others are redirected to `/rewards/prizes`)

**Purpose:** Create and manage the master prize catalog that all bases can pull from.

### What You See

| Column | Description |
|--------|-------------|
| Image | Prize photo |
| Prize Name | Name |
| Default Miles | Default cost when bases add it to their store |
| Default Stock | Default starting stock |
| Default Status | Active or Disabled by default |
| One Per Soldier | Whether each soldier can only buy it once |
| Last Updated | Date of last change |

### Actions

- **Create Template** — opens form to define a new prize template
- **Edit** — click to edit any template in a modal
- **Edit Picture** — click image to upload/crop a new photo
- **Download CSV**

### Creating / Editing a Template

- **Prize name**
- **Default Miles cost**
- **Default stock count**
- **Active / Disabled** — default state when bases add it
- **One Per Soldier** — yes/no
- **Image** — upload and crop

---

## Store Orders (`/rewards/orders`)

**Access:** BC, TEACHER (HQ/INST are redirected to Prize Templates)

**Purpose:** View and manage all prize orders placed by soldiers through the store (via kiosk, teacher, or parent portal).

### What You See

| Column | Description |
|--------|-------------|
| Date | When the order was placed |
| First Name | Soldier's first name |
| Last Name | Soldier's last name |
| Serial Number | Soldier's ID |
| Prize | What was ordered |
| Miles | Miles cost per item |
| Qty | Quantity ordered |
| Total | Total miles spent |
| Grade | Soldier's grade |
| Sub | Class subdivision |

### Actions

- **Create Order** — manually create an order for a soldier
- **Redeem** — mark selected orders as fulfilled (items have been given to soldiers)
- **Un-redeem** — reverse a redemption if needed
- **Delete** — delete orders and refund the miles (irreversible, requires confirmation)
- **Load Redeemed Orders / Load Open Orders** — toggle between pending and fulfilled orders
- **Download CSV**

### Typical Workflow

1. Open Orders are shown by default (all pending orders)
2. When you've given prizes to soldiers, select those orders
3. Click **Redeem** to mark them as fulfilled
4. To review past orders, click **Load Redeemed Orders**
5. If a soldier returns a prize or an error was made, use **Un-redeem** or **Delete**

---

## Add / Subtract Miles (`/rewards/miles`)

**Access:** HQ, INST, BC (not available to TEACHER)

**Purpose:** Manually adjust a soldier's miles balance — to correct errors, award bonus miles, or apply deductions.

### Options

| Field | Description |
|-------|-------------|
| Base | Select base (admin only; auto-filled for BC) |
| Platoon | Required; select a specific class |
| Soldier | Select a specific soldier, or "All" for bulk |
| Miles | Amount to add or subtract (1–10,000) |

### Action Buttons

| Button | What It Does |
|--------|-------------|
| Add (All) | Adds miles to all mile types (store + auction) |
| Add (Store Only) | Adds to store miles only |
| Add (Auction Only) | Adds to auction miles only |
| Subtract (All) | Subtracts from all mile types |
| Subtract (Store Only) | Subtracts store miles only |
| Subtract (Auction Only) | Subtracts auction miles only |

> **Tip:** Use "Store Only" for most adjustments. "Auction Only" is for special auction events. "All" affects both.

---

## Miles Report by Date — Legacy (`/miles_details.php`)

**Access:** HQ, BC

**Purpose:** View a detailed breakdown of miles earned and spent, filtered by date range. Legacy page — for quick balance checks, see the **Miles Report** under Reports.
