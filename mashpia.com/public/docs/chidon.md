# Chidon HaSefer

The Chidon section manages the annual Torah knowledge competition. Follow the pages in order through the year: Settings → Enter Scores → Review Marks → Finals → Confirmations.

---

## Overview

Chidon HaSefer is a yearly competition where soldiers study Torah subjects and are tested at three levels:

**Test Tracks:**
| Track Name | Internal Name | Level |
|-----------|--------------|-------|
| Maven | Yesod | Beginner |
| Pro | Yediah | Intermediate |
| Expert | Havanah | Advanced |
| Genius / Iyun | Iyun | Expert (Level 2 only) |
| KHK | Kitzur Hilchos Kashrus | Special track |

**Test levels:**
- **Level 1** — Standard difficulty (Maven, Pro, Expert tracks)
- **Level 2** — Harder difficulty, unlocks the Genius/Iyun track

---

## Chidon Test Settings (`/chidonTests/settings.php`)

**Access:** BC, HQ

**Purpose:** Configure the passing thresholds and test difficulty before scores are entered. Must be done before test entry is enabled.

### What to Configure

- **Passing average for regular tests** — per track (Maven, Pro, Expert): set between 70–100% in 5% increments
- **Passing average for the final exam** — same tracks
- **Test level** — Level 1 (standard) or Level 2 (harder; enables Iyun/Genius track)

These can be set at three levels:
1. **School-wide** — applies to all students unless overridden
2. **Platoon** — overrides school setting for a specific class
3. **Individual student** — overrides platoon setting

> **Important:** Settings at different levels are **independent** — changing the school setting does not automatically update platoons or individual overrides, and vice versa.

> **Iyun track threshold** is controlled by HQ only (default: 80% non-cumulative / 90% cumulative).

### How to Use

1. Select the **Base** (or leave at your own base for BC)
2. Optionally drill down to a **Platoon** or individual **Soldier**
3. Select the **Hebrew year**
4. Set the passing averages and test level
5. Click **Save**
6. The "Enter Scores" link activates once settings are saved

---

## Enter Chidon Test Scores (`/chidonTests/enterScores.php?test_num=1`)

**Access:** BC, HQ

Use `?test_num=1`, `?test_num=2`, or `?test_num=3` for Tests 1, 2, and 3.

**Purpose:** Enter the number of correct answers for each student on a given test. The system converts raw answers to percentage marks automatically.

### How to Use

1. Select the **grade/class** to filter students
2. For each student, enter the **number of correct answers** for the applicable track columns:
   - Max **10** correct answers for Maven, Pro, Expert tracks
   - Max **20** correct answers for Genius/Iyun track
3. Set the **test level** per student (Level 1 or 2) — Level 2 enables the Genius/Iyun input
4. Optionally toggle **"Show Report Card on Parent Accounts"** to make results visible to parents
5. Click **Save**
6. You are automatically taken to the Marks review page after saving

> **Entry windows:** Score entry is only open during designated dates. Outside those dates, the form is read-only. Contact HQ if you need access outside the window.

> **Settings required:** If passing averages haven't been configured yet, you'll be redirected to the Settings page first.

---

## Review Marks (`/chidonTests/marks.php?test_num=1`)

**Access:** BC, HQ

**Purpose:** View the calculated percentage marks after scores are entered. This page is **read-only**.

### What You See

| Column | Description |
|--------|-------------|
| Serial # | Soldier's ID |
| Grade | Class/grade |
| Student | Name |
| Track | Assigned track |
| Mark per track | Percentage for each track column |
| Test Level | Level 1 or 2 |
| Average to date | Running cumulative average across tests taken |

**Color coding:**
- **Black** = the student's assigned track (passing or above threshold)
- **Red** = below the passing threshold for that track
- **Grey** = not the student's assigned track

> **HQ users** can switch years to view historical marks.

---

## KHK Test Marks (`/chidonTests/khk_tests.php`)

**Access:** BC, HQ

**Purpose:** Enter marks for the 4-test KHK (Kitzur Hilchos Kashrus) sequence. Only students enrolled in the KHK program appear.

### How to Use

1. Enter the **percentage mark** (0–100) for each of the 4 KHK tests per student
2. The system calculates the **running average** automatically
3. Click **Save**

**Entry windows** (4 per year):
- October 19–31
- November 11–18
- December 29 – January 6
- January 27–30

> Students with an average **at or above 70%** (shown in red) qualify for the KHK track in the finals.

> Entry is read-only outside the designated windows.

---

## Chidon Finals (`/chidonTests/finals.php`)

**Access:** BC, HQ

**Purpose:** Enter final exam scores for qualifying students. Only the tracks a student qualifies for are enabled.

### How to Use

1. Select a **grade/class**
2. For each student, enter scores for the tracks they qualify for:
   - **Tracks 1–4**: number of correct answers
   - **KHK Final**: score out of 200 (KHK-enrolled students who passed with avg ≥ 70%)
3. The system shows the projected award next to each student
4. Click **Save**

### Award Levels

| Track Passed | Award |
|-------------|-------|
| Maven (Track 1) | Certificate |
| Pro (Track 2) | Plaque |
| Expert (Track 3) | Medal + Plaque |
| Genius/KHK (Track 4) | Medal + Plaque + Blue Trophy |

> **Entry deadline:** Approximately February 24. Contact HQ for exceptions.

---

## Enrollment / Prize Confirmation (`/chidonTests/confirmations.php`)

**Access:** BC

**Purpose:** Print confirmation slips for enrolled students showing all their registration details for final review before the October 27 cut-off date.

### What's Shown Per Student

- English and Hebrew name
- Serial number, grade
- Chosen track
- Sweater size, book number, yarmulka size (boys)
- Selected prizes with personalizations (size, color, engraved Hebrew name)
- Certificate image preview

### How to Use

1. Open the page — it automatically generates one slip per enrolled student
2. Click Print (Ctrl+P / Cmd+P)
3. Distribute slips to students/parents for review
4. Students/parents can make corrections through their parent account before October 27

> This page is **read-only** — no data can be changed here.

---

## Choosing Reps (`/chidonTests/setReps.php`)

**Access:** BC (school reps), HQ (regional + international reps)

**Purpose:** Designate student representatives for the Chidon competition from your school, region, and internationally.

### How to Use

1. Students are ranked by track level and score
2. Check **School Rep** for students who will represent your school
3. Assign a **Team** to each rep (Sefer Hamitzvos / Mishna Torah / Moreh Nevuchim / etc.)
4. Select **number of videos** per school: 1 or 5
5. Save

> **HQ only** can designate Regional and International reps.

> Use the "Only Show Reps" button to filter the list to current reps only.

---

## Chidon Report Cards (`/chidonTests/reportCards`)

**Access:** BC, HQ

**Purpose:** View and print Chidon report cards for students showing their test performance.

---

## Enrollment Report (`/chidonOld/newReports/comprehensive_reg_report.php`)

**Access:** BC, HQ

**Purpose:** Full report of Chidon enrollment details including track selections, merchandise choices, and confirmation status.

---

## Confirmation Report (`/chidonOld/newReports/confirmationReport.php`)

**Access:** BC, HQ

**Purpose:** Report showing which students have confirmed their Chidon enrollment.

---

## Eligibility & Registered Report (`/chidonOld/newReports/registered_report.html`)

**Access:** BC, HQ

**Purpose:** Report showing which students are eligible and registered for Chidon.

---

## Marks/Levels Settings Report (`/chidonTests/reports/settings_report.html`)

**Access:** BC, HQ

**Purpose:** Report showing all configured Chidon settings (passing averages, test levels) by school.

---

## Chidon Shipping (`/chidon_shipping/`)

**Access:** BC, HQ

**Purpose:** Manage and track the shipping of Chidon prizes and merchandise.

---

## KHK Enrollment Eligibility Override (`/khk/enrollment_eligibility.php`)

**Access:** HQ only

**Purpose:** Override KHK enrollment eligibility for specific students who don't meet the standard criteria.

---

## Chidon History Report (`/reports/chidon/reg_history_school.php`)

**Access:** BC

**Purpose:** View historical Chidon registration and participation data by school across multiple years.

---

## Chidon Office Reports (`/reports/chidon/`)

**Access:** HQ only

**Purpose:** Access all Chidon-related administrative reports across the entire system.
