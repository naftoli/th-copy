# Missions

The Missions section lets you mark mission completions, print mission sheets, personalize missions for specific soldiers, view reports, and manage mission task definitions.

---

## Mark Missions (`/missions/mark`)

**Access:** HQ, INST, BC, TEACHER

**Purpose:** Record that soldiers have completed their missions.

The page works differently depending on your account type:

---

### For Teachers — Grid Marking

Teachers see a **3-tab grid interface**:

#### Daily Tab
- Select a date using the date picker (can go back up to 1 year)
- Quick buttons: **Today**, **Yesterday**
- The grid shows soldiers as rows and daily missions as columns
- Click a cell to mark a mission complete (or unmark it)
- Click **Refresh Grid** to reload

#### Weekly Tab
- Select the **Parsha** (Torah portion/week) from the dropdown
- The grid shows soldiers as rows and weekly missions as columns
- Mark mission completions by clicking cells

#### Tehillim Tab (only visible if Tehillim module is enabled)
- Select the **Shabbos Mevorchim** date
- Mark Tehillim completions in the grid

> **Important notes:**
> - Soldiers can also mark their own missions from their accounts — you don't need to mark everything yourself
> - Not all soldiers have all tasks; if a mark is invalid for a soldier it won't be saved
> - You can **Customize the Grid** (button at top) to choose which missions are shown

---

### For Base Commanders — Inline Marking

BCs see a **soldier-selection interface**:

1. Enter the soldier's **Serial Number** (format: 7xxxxxx) in the serial number box to quick-select them, OR use the soldier dropdown
2. Once a soldier is loaded, all their missions appear grouped by label/category
3. Click any mission to mark it complete (or click again to unmark)
4. Marks are saved immediately

**Alternative:** Click **Mark Printed Version** to use the legacy marking page which lets you mark mission sheets that were physically printed.

---

## Print Missions (`/missions/print`)

**Access:** HQ, INST, BC, TEACHER

**Purpose:** Generate printable mission sheets to distribute to soldiers.

### Options

| Option | Description |
|--------|-------------|
| Base | Select base (admin only; auto-filled for BC) |
| Platoon(s) | Filter to specific classes (multi-select) |
| Soldier(s) | Filter to specific soldiers (multi-select) |
| Parsha(s) | Select the Torah portion week(s) for the missions |
| Double-sided | Toggle for double-sided printing |
| Date format | None / Hebrew only / Hebrew + English |
| Min pages per soldier | Minimum sheet count (adds blanks if needed) |
| Batch printing | Print in separate print jobs per soldier |

### How to Use

1. Select your desired filters and options
2. Click **Print** — a printable page opens in a new tab
3. Print from that tab

> **Tip:** Click the **Instructions** button for a printing guide.

---

## Personalize Missions (`/missions/personalize`)

**Access:** HQ, INST, BC, TEACHER

**Purpose:** Turn individual missions on or off for specific soldiers, platoons, or your entire base.

> **Important:** Base or platoon-wide settings override individual soldier settings.

### How to Use

1. **Select Base** (auto-filled for BC)
2. **Select Platoon(s)** — leave blank to affect all platoons
3. **Select Soldier** — leave blank to affect all soldiers in selected platoons
4. **Select Parsha** — the Torah portion week
5. **Select Mission Type** and **Language**
6. Click **Load Campaigns**
7. The page shows all active campaigns and their tasks
8. Expand any **Campaign** to see its tasks
9. Expand any **Task** to see individual missions
10. Use the personalize controls to toggle missions on or off
11. Changes are saved when you click the save control for each item

### Scope Rules
- Changing at the **Base** level affects all soldiers
- Changing at the **Platoon** level affects all soldiers in those platoons, and overrides individual settings
- Changing at the **Soldier** level affects only that soldier (unless overridden by platoon/base settings)

---

## Duch — Mission Report (`/missions/duch`)

**Access:** HQ, INST, BC, TEACHER

**Purpose:** Generate a printable "Duch" (Hebrew for "report") summarizing mission accomplishments for your soldiers over a selected time period.

### Options

| Option | Description |
|--------|-------------|
| Base | Select base (admin only) |
| Platoon(s) | Filter by class (multi-select) |
| Soldier(s) | Filter to specific soldiers (multi-select) |
| Date range | Custom start/end dates |
| Last N days | Preset: Last 7, 30, 60, or 90 days |
| Hebrew month | Select a specific Hebrew month |
| Date format | None / Hebrew / Hebrew + English |

### How to Use

1. Select your scope (base / platoons / soldiers)
2. Choose a date range method (custom dates, last N days, or Hebrew month)
3. Select date format preference
4. Click **Generate** — the report opens in a new tab for printing

---

## Streaks (`/missions/streaks`)

**Access:** HQ, INST, BC, TEACHER

**Purpose:** Set up and track 90-day mission completion challenges for individual soldiers.

### How It Works

A streak is a 90-day challenge where a soldier aims to complete a specific mission task every day (or every applicable period). The page shows how many days have been completed out of 90.

### Setting Up a Streak

1. Select the **Base**
2. Select the **Platoon** (optional — narrows the soldier list)
3. Select the **Soldier**
4. Select the **Campaign** (mission category) — loads after soldier is selected
5. Select the **Task** — the specific mission to track
6. Click **Setup Streak**

> The system will alert you if the soldier already has an active, incomplete streak for that task.

### Viewing Active Streaks

Once a streak is active, the page shows:
- Campaign name and task name
- **Days Completed / 90** (e.g., "23 / 90 days")
- A visual progress bar

---

## Mission Tasks (`/missions/tasks`)

**Access:** HQ, INST, BC (not TEACHER)

**Purpose:** View and manage the task definitions that make up missions — the building blocks used when creating and personalizing missions.

### What You See

| Column | Description |
|--------|-------------|
| Grid ID | Internal task identifier |
| Language | Language ID for this task |
| Campaign | Which subject/campaign this task belongs to |
| Title | Short name (shown on mission sheet) |
| Details | Full description of the task |
| Miles | Miles awarded for completion |
| Category | Task category |
| On Mission Sheets | Whether this task appears on printed sheets (Y/N) |
| On Marking Grid | Whether this task appears in the marking grid (Y/N) |
| Mission Sheet Label | The label/section heading on the mission sheet |
| Base | Which base this task belongs to (custom tasks) |

### Actions

- **Add Task** — create a new custom mission task
- **Edit** — click the edit button on any row to modify
- **Download CSV** — export the full task list

### Creating / Editing a Task

When you click Add Task or Edit, a form opens with:
- **Campaign** (subject)
- **Short name** — title shown on mission sheet
- **Details** — full description
- **Miles value** — how many miles completing this task awards
- **Category** — task category
- **On mission sheets** — whether it appears on printed sheets
- **On marking grid** — whether it appears in the marking grid
- **Label name** — the section heading this task appears under on mission sheets

---

## Mission Checklist — Legacy (`/mission_sheets_checklist.php`)

**Access:** HQ, BC

Legacy page for viewing mission sheet checklists. Use the modern **Mark Missions** page for day-to-day marking.

---

## Mission Report — Legacy (`/missions_report.php`)

**Access:** HQ, BC

Legacy page showing missions completed. Use **Duch** for the modern version.

---

## Print Summer Missions — Legacy (`/print_missions_summer.php`)

**Access:** HQ, BC

Legacy page for printing summer mission sheets.
