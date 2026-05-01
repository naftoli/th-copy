# System Modules

This is a Jewish youth organization (Torah heritage) management platform. Modules span user management, education, finance, logistics, and recognition.

---

## PHP Backend Modules (`/mashpia.com/public/`)

### Registration & Enrollment
- **registration** — Student and base (unit) registration with form processing and validation
- **new_csv_students** — Bulk student import via CSV
- **newClasses** — Classroom/class management

### Financial
- **accounting** — Payment/refund tracking, voucher management, base charge summaries
- **payment** — Transaction processing
- **donate** — Donation collection and tracking
- **sponsorships** — Sponsorship collection and tracking

### Education & Missions
- **mission_report** — Mission/task assignment sheets with per-user tracking
- **tasks** — Task definition, assignment, scheduling, and analytics
- **pesukim / pesukimApp** — Torah verse study tracking and interactive app
- **mishna** — Mishna study program
- **tehillim** — Psalms study program
- **mivtzoim / mivtzoim_purchases** — Mitzva campaign management and supply inventory

### Competitions
- **chidonTests** — Chidon (Torah knowledge competition) test management, scoring, eligibility, and reports
- **chidon_shipping / chayolei_shipping / school_shipping / parent_shipping** — Material logistics and distribution per audience
- **chidonGame / chidonGame5778** — Interactive game components for Chidon engagement
- **chidonOld** — Legacy Chidon archive

### Events & Programs
- **camps** — Summer camp enrollment, goals, and management
- **rally** — Rally/event coordination
- **chanuka** — Chanuka holiday program
- **lagBaomer** — Lag BaOmer holiday program
- **hakhel** — Hakhel gathering program
- **kiosk** — Kiosk/display campaign and goal screens
- **timeline** — Chronological event tracking

### Awards & Recognition
- **medals / medal_ceremony / medal_board** — Medal/badge system, award ceremonies, and leaderboard
- **rank_ceremony / rank_books** — Rank advancement ceremonies and progress tracking
- **rewards** — Rewards program and points tracking
- **auction** — Prize auction with lottery ticket assignment and winner management
- **yearly_prize** — Annual prize tracking
- **supersoldier** — Advanced achievement tier
- **certs** — Certificate generation
- **cards** — ID/card management

### Community & User Management
- **platoons** — Platoon (unit) organization
- **hachayols** — Chayol (soldier) program with scheduling
- **non_th_schools** — Non-affiliated school integration
- **ckids** — Children-specific program interface
- **neshek** — Internal Hebrew-named program
- **lighttriumphs** — Achievement/inspirational program
- **kinderblast** — Kindergarten-focused program

### Content & Storytelling
- **news** — Announcements and news management
- **mystoryteller** — Story collection and narrative archive
- **rebbestories** — Teachings and stories archive

### Reporting & Admin
- **reports / reporters** — Data aggregation, analytics, and business intelligence
- **helpdesk** — Support ticket management
- **emails / emailer** — Email template and delivery
- **screens** — Public display/kiosk presentation screens
- **tools** — Administrative utilities
- **fixes** — Data correction and migration utilities
- **scripts** — Background processing and batch operations

### Misc/Unclear
- **battlefront, shimmy, sudoku, khk, tbp, bp, world, zalman, duch, sweaters** — Various programs or engagement systems (purpose unclear from naming alone)

---

## React Frontend Pages (`/front-end/src/pages/`)

| Module | Description |
|---|---|
| **login** | Authentication, password reset, new account |
| **home** | Dashboard with birthday, promotion, and registration widgets |
| **account** | User profile and account settings |
| **base-management** | Hub for base, soldiers, parents, platoons, staff, and educational modules |
| **missions** | Mark, personalize, print, streaks, tasks, and duch sub-views |
| **reportCards** | Student progress and achievement report cards |
| **rewards** | Cards, miles balance, orders, prize catalog, and reward tasks |
| **errors** | 404, under construction, locked access pages |

---

## Redux Store Slices (`/front-end/src/store/`)

`base`, `home`, `login`, `missions`, `payments`, `rewards`

---

## Standalone Services

| Service | Description |
|---|---|
| **pdf-service** | Express + Puppeteer microservice for PDF generation and merging |
| **base-commander** | Legacy CRA frontend (being phased out — same domain coverage as front-end) |
