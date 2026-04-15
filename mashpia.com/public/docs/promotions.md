# Promotions

The Promotions section manages medal ceremonies, rank promotions, and the reporting around them. Use these pages before and after each rally.

---

## Medal Ceremony Report (`/medal_rank_ceremony3.php`)

**Access:** BC, HQ

**Purpose:** The primary printable ceremony packet distributed to teachers before each rally. Lists all medals earned and rank promotions for the current reporting period.

### What You See

**Per class/platoon:**

*Medals Earned table:*
| Column | Description |
|--------|-------------|
| Soldier | Name with current rank |
| Medal | Medal color (Red, Orange, Yellow, etc.) |
| Subject | Mission category (Torah, Mitzvos, etc.) |
| # Required | How many missions were needed to earn this medal |
| Shipped | Checkbox — check when the medal has been physically shipped |

*Soldiers Who Went Up in Rank:*
- List of students who were promoted, grouped by their new rank

**School summary section:**
- Total medal count for the school
- Breakdown by subject and medal color
- List of all students at each rank

### How to Use

1. Open the page — it automatically shows the current reporting period
2. Use **"Previous Dates"** / **"Next Dates"** buttons to navigate between reporting periods
3. Check the **Shipped** checkbox for any medals that have been physically sent to soldiers — this saves automatically
4. Click **Print** to print the full packet

### Helpful Links on the Page
- **Medal Ceremony Video** — tutorial video for running the ceremony
- **Promotion Pictures Manual** — guide for submitting promotion photos

> Testing schools are excluded from this report.

---

## Teacher's Medal Ceremony Report (`/medal_rank_ceremony3.php`)

This is the same page as the Medal Ceremony Report above, shown under a different menu label for INST/BC/TEACHER users.

---

## Promotion Picture Report (`/promotion_report.php`)

**Access:** BC, HQ

**Purpose:** List all soldiers promoted to a new rank during the current period, formatted for preparing rally promotion photos.

### What You See

Per school — a table with:
- Grade, teacher name
- Student name (last, first)
- New rank
- Blank "Time" and "Date" columns (for ceremony notes)

Also includes:
- **Photo upload instructions** — Dropbox account details and folder structure
- **Photo guidelines** — 7 rules for taking promotion photos (landscape, white wall, etc.)
- **Submission deadline** — 8 days after the promotion date

### How to Use

1. **Select sort order:** By Grade or By Rank
2. Click Submit / Generate
3. Use the **Previous/Next** buttons to switch between reporting periods
4. Print and use for photo preparation

---

## Personalized Medal Boards (`/medal_board/options.php`)

**Access:** BC, HQ

**Purpose:** Generate personalized medal display boards for soldiers — visual boards showing which medals they've earned.

---

## Rank Report (`/rank_report.php`)

**Access:** BC, HQ

**Purpose:** View every ranked soldier's current rank and how close they are to their next rank — the definitive overview of rank progress across your base.

### What You See

Per student:
| Column | Description |
|--------|-------------|
| Photo | Profile thumbnail |
| Grade | Class/grade |
| Student | Name |
| Rank | Current rank name (color-coded) |
| Medals to Next Rank | Visual progress: filled circles = medals earned, empty = still needed |
| Totals | Count of medals earned toward next rank |

**School totals** by rank at the bottom. HQ sees grand totals across all schools.

### How to Use

1. Open the page — report loads automatically
2. **Toggle sort:** Use the sort button to switch between "By Grade" and "By Rank" views
3. Print the report

> **Browser note:** Use **Chrome or Firefox** for printing. Microsoft Edge does not display medal circles correctly. For Internet Explorer, enable "Print Background" in print settings.

---

## Mark Ranks / Medals as Received (`/admin_received_stats.php`)

**Access:** BC, HQ

**Purpose:** Track which medals, rank books, and rank cards have been physically shipped/received by soldiers.

### Filters

| Filter | Options |
|--------|---------|
| School | Select a school (HQ can choose any) |
| Subject | Filter by mission category |
| Platoon | Filter by class |
| Individual Soldier | Filter to one soldier |
| Medals | All / Awarded But Not Received / Awarded And Received |
| Rank Books | All / Received / Not Received |
| Rank Cards | All / Received / Not Received |
| Date Range | Filter by medal award date or rank promotion date |

### What You See

| Column | Description |
|--------|-------------|
| Serial # | Soldier ID |
| Soldier | Name |
| Grade | Class/grade |
| Subject | Mission category |
| Medal | Medal color |
| Date Earned | When the medal was earned |
| Medal Sent | Checkbox + date when checked |
| Rank | Current rank |
| Date Promoted | When they were promoted |
| Rank Book Shipped | Checkbox + date |
| Rank Card Shipped | Checkbox + date |

### How to Use

1. Apply desired filters
2. Click **checkboxes** to mark items as shipped — saves automatically
3. The date is recorded when you check the box
4. Print for records

---

## Missing Medals (`/missing_medals.php`)

**Access:** HQ, BC

**Purpose:** View a list of medals that have been awarded but not yet shipped/received — helps ensure no medals fall through the cracks.
