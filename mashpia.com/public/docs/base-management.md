# Base Management

The Base Management section is the core of the platform — it's where you manage all the people and settings for your base.

---

## Soldiers — View / Edit (`/bm/soldiers`)

**Access:** HQ, INST, BC, TEACHER (teachers see "Platoon Management" in the menu, same page)

**Purpose:** View all soldiers in your base (or all bases for HQ/INST), edit their details, and manage their enrollment.

### What You See

The soldiers table displays the following columns (some depend on enabled modules):

| Column | Description |
|--------|-------------|
| Profile | Profile picture (click to upload/change) |
| First Name | Links to the soldier's detail page |
| Last Name | Links to the soldier's detail page |
| Serial Number | The soldier's unique 7-digit ID |
| Rank | Current rank (if Chayolei module enabled) |
| Date of Birth | Soldier's birthday |
| Family ID | Linked parent account ID |
| Registered | Registration date (Chayolei module) |
| CTH | Toggle to enroll/unenroll in the main program |
| Chidon | Toggle to enroll/unenroll in Chidon (if enabled) |
| Tanya | Toggle to enroll/unenroll in Tanya program (if enabled) |
| Platoon | Class name (BC view) |
| Base | School name (HQ/INST view only) |

### Actions

- **Add Soldier** — Opens the "Create Soldier" form (see below)
- **Bulk Upload** — Import soldiers from a CSV file
- **Refresh** — Reload the soldier list
- **Download CSV** — Export the full list to Excel
- **Filter** — Use the filter row under each column header to search/filter
- **CTH / Chidon / Tanya toggles** — Click any toggle to instantly enroll or unenroll a soldier

### Creating a New Soldier

Click **Add Soldier** to open the form. Fill in:

1. **Profile Picture** — optional; click the avatar to upload
2. **Gender** — Male or Female
3. **First Name / Last Name** (English)
4. **First Name / Last Name** (Hebrew) — used for certificates and reports
5. **Date of Birth**
6. **Mission Type** — depends on gender and school type
7. **Mission Language** — the language for mission sheets
8. **Base** — auto-filled for BC; selectable for HQ/INST
9. **Platoon** — the class this soldier belongs to
10. **Parent Admin ID** — optional; links to an existing parent account
11. **Parent Email** — optional; used to create/invite parent account

Click **Save** to create the soldier.

---

## Soldier Detail Page (`/bm/soldiers/:id`)

**Access:** HQ, INST, BC, TEACHER

**Purpose:** View and edit all details for a single soldier.

The page is organized into tabs:

### Tab 1: Personal
- View and edit the soldier's name (English and Hebrew), date of birth
- Upload or change profile picture

### Tab 2: Settings
- **Mission Settings:** Change mission type and language
- **Enrollment:** Toggle CTH, Chidon, Tanya enrollment
- **Custom Parent Tasks:** Allow or disallow parent-created custom tasks
- **Mission Sheet Type:** No pictures / Small pictures / Day school
- **Connected Parent Account:** Link or unlink a parent account
- **Remove from School** (BC only): Move soldier to unassigned
- **Update Birthday Missions** (BC only): Recalculate birthday mission assignments

### Tab 3: Rank
- View the soldier's current rank and total miles earned
- Visual rank board showing progression

### Tab 4: Medals (BC only)
- View all medals earned across all campaigns
- Edit mission counts per campaign if corrections are needed
- Click **Save** to update

### Tab 5: Registration
- View registration history: member-since date, latest CTH registration date
- View all registration charges with details (type, year, amount, date, transaction ID)

### Tab 6: Transactions
- Search miles transactions by date range or Hebrew month
- Filter by type: Achievement Cards, Store, Tasks, Admin/BC Adjustments
- Table shows: Date, Points (+ or −), Description

---

## Soldier Registration (`/bm/soldiers/registration`)

**Access:** BC only

**Purpose:** Register unregistered soldiers for the current year and process the registration payment.

### How to Use

1. The page shows all soldiers who have **not yet registered** this year
2. Each soldier shows their name, serial number, registration fee, and platoon
3. **Select soldiers** by clicking their checkboxes (or "Select All")
4. The running **Total** and **Soldier count** update as you select
5. Click **Pay and Register**
6. A payment modal opens — enter or select a credit card on file
7. Confirm to complete registration

### Other Actions
- **Refresh** — reload the list
- **Download CSV** — export the unregistered list to Excel (includes name, serial number, fee, platoon)

> **Note:** A payment method must be on file before you can register. If you don't have one, go to **Base Management → Base → Credit Cards** tab to add one.

---

## Rank Cards (`/bm/soldiers/cards`)

**Access:** HQ, INST, BC

**Purpose:** Generate and print military-style rank cards for soldiers. HQ ships permanent cards; BCs can print temporary cards locally.

### Options

| Option | Description |
|--------|-------------|
| Base | Select which base (HQ only) |
| Platoon | Filter by class |
| Rank | Filter by specific rank or show all |
| Serial Number | Filter to one specific soldier |
| Show ranks | Current rank only, or all ranks ever earned |
| Print type | Temporary or Permanent |
| Date filter | Only show ranks earned on or before a selected Hebrew date |
| Hide Already Printed | Skip cards already marked as printed |

### How to Use

1. Set your desired filter options
2. Click **Generate Rank Cards**
3. Review the cards displayed on screen
4. Click **Print** to print them (credit-card size: 3.37in × 2.12in)
5. HQ can click **Mark All As Printed** after printing, then **Sync Printed Updates** to save

> **Note:** Only **registered** soldiers have rank cards. Unregistered soldiers will not appear.

> **Firefox users:** Click the menu (≡) → Print to get a print preview.

---

## Platoons (`/bm/platoons`)

**Access:** HQ, INST, BC

**Purpose:** View and manage all platoons (classes) in your base.

### What You See

| Column | Description |
|--------|-------------|
| Grade | Grade level |
| Class | Class subdivision/name |
| Teacher | Assigned teacher name |
| Cell Phone | Teacher's phone |
| E-mail | Teacher's email |
| Soldiers | Number of soldiers in the platoon |
| Staff | Number of staff accounts linked |
| Miles Balance | Total miles held by soldiers in this platoon |
| Base | School name (HQ/INST only) |

### Actions
- **Click any row** to open the platoon detail page
- **Add Platoon** — create a new platoon
- **Platoon Transition** — bulk-move soldiers between platoons (see below)
- **Print Teacher Letters** — print letters for teachers (legacy page)
- **Download CSV** — export platoon list

---

## Platoon Detail (`/bm/platoons/:id`)

**Access:** HQ, INST, BC

**Purpose:** View and edit all settings for a single platoon.

### Tabs

**Tab 1: Platoon**
- Edit: grade, class name, teacher name, contact info
- **Save** button to apply changes
- **Delete** button (with confirmation) to remove the platoon

**Tab 2: Teacher Accounts**
- View all staff accounts linked to this platoon as teachers
- Add a staff account as a teacher (creates login access)
- Remove a teacher from this platoon

**Tab 3: Soldiers**
- View the list of soldiers currently in this platoon
- Only shown if the platoon has soldiers

> **Tip:** To connect a staff member to a platoon so they can log in as a teacher, go to Tab 2 (Teacher Accounts) and add them there.

> The page will warn you if you try to leave with unsaved changes.

---

## Platoon Transition (`/bm/platoons/transition`)

**Access:** HQ, INST, BC

**Purpose:** Bulk-move soldiers from one platoon to another — typically used at the end of the school year.

### Step-by-Step

**Step 1: Select Source**
- Choose the "From" base and platoon
- Click **Load Soldiers** to see the soldiers in that platoon

**Step 2: Select Soldiers**
- Check the soldiers you want to move
- Or use **Discharge** to remove soldiers from the program entirely (moves them to "Unassigned School")

**Step 3: Select Destination**
- Choose the "To" base and platoon
- Click **Move** to queue the transition

**Step 4: Deploy**
- Repeat Steps 1–3 for any additional platoons you want to transition
- When ready, click **Make All Transitions Live**
- The system will report how many soldiers were transitioned

> **Important:** Transitions are only finalized when you click "Make All Transitions Live" in Step 4. You can queue up multiple moves before deploying.

---

## Parents (`/bm/parents`)

**Access:** HQ, INST, BC

**Purpose:** View all parent accounts connected to soldiers at your base.

### What You See

| Column | Description |
|--------|-------------|
| Parent ID | Unique parent account ID |
| First Name | Parent's first name |
| Last Name | Parent's last name |
| Username | Login username |
| Cell Phone | Father's cell phone |
| E-mail | Email address |
| Children | Number of linked soldiers |

### Actions
- **Click any row** to open the parent detail page (manage linked children)
- **Create Parent Account** — opens the creation form
- **Download CSV** — exports full parent list including address, city, state, zip, country, and children names

> **Security note:** Passwords cannot be viewed once created. To add or remove children from a parent account, click the parent's name and have the soldier's **Serial Number** ready.

---

## Staff (`/bm/staff`)

**Access:** HQ, INST, BC

**Purpose:** View and manage staff accounts (teachers, administrators) connected to your base.

### What You See

| Column | Description |
|--------|-------------|
| Username | Staff login username |
| Password | Current password (visible for admin use) |
| First Name | First name |
| Last Name | Last name |
| E-mail | Email address |
| Cell Phone | Phone number |
| Position | Role/title |
| Platoon | Linked platoon (if teacher) |
| Base | School (HQ/INST view only) |

### Actions
- **Click username** to open staff detail page (edit info, change password, link to platoons)
- **Create Staff Account** — create a new login for a teacher or administrator
- **Download CSV** — export the staff list

---

## Base Settings (`/bm/base`)

**Access:** BC (for own base), HQ/INST (for any base)

**Purpose:** View and edit all settings for a base. BC users are automatically taken to their own base.

The page is organized into 5 tabs:

### Tab 1: Base
- Base name, base number, address, city, state, country
- Base logo (upload)
- Campaigns active for this base
- **Save** button to apply changes

### Tab 2: Settings
- General base settings and preferences
- HQ-specific overrides (HQ only)
- Commander account management (add/remove base commanders)

### Tab 3: Shipping
- Shipping address for materials (medals, rank books, etc.)
- Shipping preferences

### Tab 4: Credit Cards
- View and manage payment profiles on file
- Required for processing soldier registration fees

### Tab 5: Commanders
- View all accounts linked to this base as commanders
- Add or remove commander access for staff accounts

> The page will warn you if you try to leave with unsaved changes.

---

## Bases — All Bases (`/bm/base`) for HQ/INST

**Access:** HQ, INST only (BCs are redirected to their own base)

**Purpose:** View all bases across the system in a table.

### What You See
Base number, name, city, state, country, soldier count, logo, and status toggles.

### Actions
- **Click any base** to open its detail page
- **Edit logo** — click a base logo to open the image cropper and upload a new logo
- **Download CSV** — export the bases list

---

## Modules (`/bm/modules`)

**Access:** HQ only

**Purpose:** Turn specific modules on or off for individual soldiers, entire platoons, or a whole base.

### Available Modules

| Module | What Toggling Off Does |
|--------|----------------------|
| **Physical Medals / Rankbooks** | Removes the soldier(s) from HQ medal and rankbook reports |
| **Hachayols** | Removes the soldier(s) from the Hachayol mailing list |

### How to Use

1. **Select Base** (required)
2. **Select Platoon(s)** — leave blank to apply to all platoons in the base
3. **Select Soldier(s)** — leave blank to apply to all soldiers in the selected platoons
4. Use the toggle controls next to each module to turn it on or off for the selected scope

> **Scope priority:** If you select individual soldiers, the change applies only to them. If you select platoons (no soldiers), the change applies to all soldiers in those platoons. If you select only a base, the change applies to all soldiers in the base.
