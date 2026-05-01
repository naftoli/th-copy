# Application Modules

Jewish youth organization (Torah heritage) management platform. Modules span user management, education, finance, logistics, competitions, and recognition.

Section order and naming follows `front-end/src/data/menu.json`. "Legacy" marks items still served by the PHP backend (`mashpia.com/public/`); modern items are React SPA routes under `/vite/`.

User types: **HQ** (headquarters), **INST** (institution), **BC** (base commander), **TEACHER**, **BLANK**

---

## Repository Layout

| Path | Role | Status |
|------|------|--------|
| `mashpia.com/` | PHP/Apache/MySQL backend | Active |
| `front-end/` | React 19 + Vite frontend | Active |
| `base-commander/` | Legacy Create React App frontend | Phasing out |
| `pdf-service/` | Standalone Node.js/Express PDF service | Active |
| `deploy/` | CI/CD and deployment scripts | — |
| `business-rules/` | Plain-language business rule documentation | — |
| `SQLdump/` | Database dump snapshots | — |

---

## Navigation Modules (menu order)

### 1. Home
- **Path:** `/` | **Users:** all
- Dashboard with birthday, promotion, and registration widgets.
- Frontend: `pages/home/`, `store/home/`

---

### 2. Base Management
**Users:** HQ, INST, BC, TEACHER | `module: chayolei`

| Item | Path | Users | Notes |
|------|------|-------|-------|
| Soldiers — View/Edit | `/bm/soldiers` | all | |
| Soldiers — Registration | `/bm/soldiers/registration` | BC | |
| Soldiers — Rank Cards | `/bm/soldiers/cards` | all | |
| Platoons | `/bm/platoons` | all | |
| Parents | `/bm/parents` | all | |
| Staff | `/bm/staff` | all | |
| Bases | `/bm/base` | HQ, INST | |
| Base | `/bm/base` | BC | same page, label differs |
| Modules | `/bm/modules` | HQ | curriculum module config |

- Frontend: `pages/base-managment/`, `store/base/`
- Backend API: `api/core/` (bases, institutions, modules, platoons, soldiers/hachayols, parents, staff, users, id_cards, admin_auths)
- **Platoon Management** — separate top-level entry for TEACHER users pointing to `/bm/soldiers`

---

### 3. Missions
**Users:** HQ, INST, BC, TEACHER | `module: chayolei`

| Item | Path | Legacy | Users |
|------|------|--------|-------|
| Print | `/missions/print` | | all |
| Print Summer Missions | `/print_missions_summer.php` | ✓ | HQ, BC |
| Mark | `/missions/mark` | | all |
| Personalize | `/missions/personalize` | | all |
| Duch | `/missions/duch` | | all |
| Streaks | `/missions/streaks` | | all |
| Tasks | `/missions/tasks` | | HQ, INST, BC |
| Mission Checklist (old) | `/mission_sheets_checklist.php` | ✓ | HQ, BC |
| Mission Report (old) | `/missions_report.php` | ✓ | HQ, BC |

- Frontend: `pages/missions/`, `store/missions/`
- Backend API: `api/missions/` (mark, tasks, grid, personalize, labels, streaks, subjects, parshos, tehillim)

---

### 4. Rewards Program
**Users:** HQ, INST, BC, TEACHER | `module: rewards`

| Item | Path | Legacy | Users |
|------|------|--------|-------|
| Achievement Cards | `/rewards/cards` | | all |
| Tasks | `/rewards/tasks` | | all |
| Prizes | `/rewards/prizes` | | all |
| Prize Templates | `/rewards/templates` | | HQ |
| Orders | `/rewards/orders` | | BC, TEACHER |
| Add / Subtract Miles | `/rewards/miles` | | HQ, INST, BC |
| Miles Report by Date | `/miles_details.php` | ✓ | HQ, BC |

- Frontend: `pages/rewards/`, `store/rewards/`
- Backend API: `api/rewards/` (miles, prizes, orders, achievement_tasks, achievement_cards, subjects, templates)

---

### 5. Chidon
**Legacy** | `module: chidon`

Competition (Torah knowledge quiz) system.

#### Tests sub-menu
| Item | Path |
|------|------|
| Marks/Levels Settings | `/chidonTests/settings.php` |
| Marks/Levels Settings Report | `/chidonTests/reports/settings_report.html` |
| Limud Evaluation Report | `/chidonOld/newReports/limudEval.html` |
| Enter Chidon Test Scores — Test #1/2/3 | `/chidonTests/enterScores.php?test_num=1/2/3` |
| Review Marks — Test #1/2/3 | `/chidonTests/marks.php?test_num=1/2/3` |
| KHK Tests | `/chidonTests/khk_tests.php` |
| Finals | `/chidonTests/finals.php` |
| Report Cards | `/chidonTests/reportCards` |

#### Other Chidon items
| Item | Path |
|------|------|
| Enrollment Report | `/chidonOld/newReports/enrollment_report.php` |
| Comprehensive Enrollment Report | `/chidonOld/newReports/comprehensive_reg_report.php` |
| Enrollment / Prize Confirmation | `/chidonTests/confirmations.php` |
| Confirmation Report | `/chidonOld/newReports/confirmationReport.php` |
| Chidon Shipping | `/chidon_shipping/` |
| Eligibility & Registered Report | `/chidonOld/newReports/registered_report.html` |
| KHK Enrollment Eligibility Override | `/khk/enrollment_eligibility.php` |
| Choosing Reps | `/chidonTests/setReps.php` |
| Chidon History Report | `/reports/chidon/reg_history_school.php` |
| Chidon Office Reports | `/reports/chidon/` (HQ only) |

- Backend: `chidonOld/` (~472 files), `chidonTests/`, `ajax/chidon/`, `ajax/chabadkid/`

---

### 6. Reports
**Legacy** | `module: chayolei`

| Item | Path | Users |
|------|------|-------|
| Office Reports | `/reports/` | HQ |
| Chayolei Shipping | `/chayolei_shipping/` | all |
| Hachayol Reports | `/reports/hachayol/` | all |
| Registered Report | `/registered_report.php` | all |
| Parents Report | `/parent_report.php` | all |
| Not Yet Registered Report | `/non_registered_report.php` | all |
| Barcodes Report | `/barcodes_report.php` | all |
| Rank Books Report | `/isserRanks.php` | all |
| Miles Report | `/miles.php` | all |
| Auction Miles Report | `/auctionMiles.php` | all |
| Missions Done Report | `/missions_report.php` | all |
| Stickers — Total Earned | `/stickers_report.php` | all |
| Stickers — Earned By Date | `/stickers_report_by_week.php` | all |
| Stickers — Earned By Child | `/stickers_report_by_child.php` | all |
| Birthday Cards | `/school_birthdays.php` | all |
| Birthday Report | `/names_report.php` | all |
| Birthdays By Date Range | `/find_birthdays_report.php` | all |

- Backend: `reports/` (~153 files)

---

### 7. Shipping Reports
**Legacy** | `module: chayolei`
- Path: `/reports/shipping/`
- Backend: `reports/shipping/`, `chayolei_shipping/`, `chidon_shipping/`

---

### 8. Campaigns
**Legacy**

#### Tanya (`module: tanya`)
| Item | Path |
|------|------|
| Individual Marking | `/editSoldierLines2.php` |
| Yud Aleph Nissan Reports | `/yud_alef_nissan_choose.php` |
| Last Yr Report | `/yan_last_yr_all.php` |

#### Tehillim (`module: tehillim`)
| Item | Path | Users |
|------|------|-------|
| Whatsapp Tehillim Reports | `/tehillim_whatsapp.php` | HQ |
| Mark Shabbos Mevorchim Tehillim | `/mark_tehillim2.php` | all |
| Shabbos Mevorchim Report | `/choose_sm_report.php` | all |
| Shabbos Mevorchim HQ Report | `/shabbos_mevorchim_by_class_hq.php` | HQ |
| Check Your Tehillim Quotas | `/tehillim_quotas.php` | all |
| Change Tehillim Ladder/Quota | `/admin_users_track.php` | all |
| Shabbos Mevorchim Tutorial Video | `/sm_video.php` | all |
| Quota Cards | `/quota_cards.php` | all |

#### Mivtzoim (`module: chayolei`)
| Item | Path |
|------|------|
| Marking | `/mivtzoim` |
| Leaderboard | `/mivtzoim/reports` |
| Mivtza Lulav Settings | `/mivtzoim/lulav_settings.php` |
| Mivtza Chanuka Settings | `/mivtzoim_purchases/chanuka_settings.php` |
| Mivtzoim Orders | `/mivtzoim_purchases/reports/` |

#### 12 Pesukim (`module: chayolei`)
| Item | Path |
|------|------|
| Marking | `/pesukim/marking` |

#### TH Drive (`module: chayolei`)
| Item | Path |
|------|------|
| Letter To Parents | `/charidy/pr/letter_to_parents.php` |

#### Mishna (`module: chayolei`)
| Item | Path |
|------|------|
| Chevras Mishnayos | `/mishna/join.php` |

- Backend: `ajax/tanya/`, `ajax/charidy/`, `mivtzoim/`, `mivtzoim_purchases/`, `pesukim/`, `mishna/`, `charidy/`
- Store: `store/missions/parshos/`, `store/missions/shabbos_mevorchim/`

---

### 9. Rebbe's Gift
**Legacy**

| Item | Path |
|------|------|
| Chayol Pledge Card | `/bp/pledge_cards.php` |
| Class Pledge Card | `/bp/classPledge.php` |
| Change History | `/bp/changeLines.php` |
| Mark Tanya/Mishnayos Lines | `/editSoldierLines2.php` |
| Our Birthday Gift | `/world` |

- Backend: `bp/`, `world/`

---

### 10. Promotions
**Legacy** | `module: chayolei`

HQ-only top-level menu; INST/BC/TEACHER see a trimmed "Promotions (Rally)" version.

| Item | Path |
|------|------|
| Medal Ceremony | `/menu-pages/promotions/metal-ceremony` |
| Hachayol | `/menu-pages/promotions/hachayol` |
| Rallies | `/menu-pages/promotions/rallies` |
| Teacher's Medal Ceremony Report | `/medal_rank_ceremony3.php` |
| Promotion Picture Report | `/promotion_report.php` |
| Medal Ceremony Slideshow | `/medal_ceremony/choose_slides.php` |
| Personalized Medal Boards | `/medal_board/options.php` |
| Rank Report | `/rank_report.php` |
| Mark Ranks / Medals as Received | `/admin_received_stats.php` |
| Missing Medals | `/missing_medals.php` |
| Misc | `/menu-pages/promotions/misc` |

- Backend: `medals/`, `medal_ceremony/`, `medal_board/`, `hachayols/`, `rank_ceremony/`, `rank_books/`, `ajax/hachayols/`

---

### 11. Mission Marathon
**Legacy** | `module: chayolei`
- Path: `/raffles/`
- Raffle/auction component tied to mission completion.
- Backend: `raffles/`, `auction/` (~70 files)

---

### 12. Yearly Gift
**Legacy** | `module: chayolei`
- Path: `/yearly_prize/reports/eligible_students.php`
- Annual prize eligibility and tracking.
- Backend: `yearly_prize/`, `ajax/yearly_gift/`

---

### 13. Manuals
**Legacy** | `module: chayolei`
- Path: `/manuals`

---

### 14. Support
- Path: `/helpdesk/?p=open`
- Backend: `helpdesk/` (~303 files — full frontend, models, API, scripts)

---

### 15. HQ Links
**Legacy** | `module: chayolei` | **Users:** HQ only
- Path: `/admin.php?oldsite=1`
- Gateway to admin/legacy pages not in the main nav.

---

## Additional Backend Modules (not in main nav)

These exist in the PHP codebase but are not represented as top-level menu items.

| Module | Path | Purpose |
|--------|------|---------|
| Accounting | `accounting/` (~15 files) | Payment/refund tracking, voucher management, charge summaries |
| Donate | `donate/` | Donation collection |
| Camps | `camps/` (~155 files) | Camp enrollment, goals, scheduling |
| Chanuka | `chanuka/` | Chanuka holiday program |
| Lag BaOmer | `lagBaomer/` | Lag BaOmer program |
| Hakhel | `hakhel/` | Hakhel gathering program |
| Kiosk | `kiosk/` | Goal/display screens for public kiosks |
| Timeline | `timeline/` | Chronological event tracking |
| Kinderblast | `kinderblast/` | Kindergarten-focused program |
| Certs | `certs/` | Certificate generation |
| Cards | `cards/` | Member card management |
| Supersoldier | `supersoldier/` | Advanced achievement tier |
| Non-TH Schools | `non_th_schools/` | Non-affiliated school integration |
| Ckids | `ckids/` | Children-specific program interface |
| Neshek | `neshek/` | Internal program |
| Light Triumphs | `lighttriumphs/` | Achievement / inspirational program |
| Battlefront | `battlefront/` (~4 files + API) | Battlefront engagement feature |
| News | `news/` | Announcements and news management |
| My Storyteller | `mystoryteller/` | Story collection and narrative archive |
| Rebbe Stories | `rebbestories/` | Teachings and stories archive |
| Screens | `screens/` | Public-display / kiosk presentation screens |
| Registration | `registration/` | Student / base registration entry point |
| Reports (admin) | `reports/` (~153 files) | Additional report scripts beyond nav items |
| Fixes | `fixes/` | Data-correction and migration utilities |
| Scripts | `scripts/` | Background processing and batch operations |
| Tools | `tools/` | Administrative utilities |
| Legacy Classes | `classes/` (~107 files) | Shared object classes (email, school, user_mission, rank_updater, Authorize.Net, etc.) |

---

## Modern Frontend — `front-end/src/`

### Redux Store Slices

| Slice | Path |
|-------|------|
| Bases | `store/base/bases/` |
| Institutions | `store/base/institutions/` |
| Modules | `store/base/modules/` |
| Platoons + Transitions | `store/base/platoons/` |
| Soldiers (+ ID cards, registration) | `store/base/soldiers/` |
| Staff | `store/base/staff/` |
| Parents | `store/base/parents/` |
| Login / Auth | `store/login/` |
| Home | `store/home/` |
| Missions Grid | `store/missions/grid/` |
| Missions Mark | `store/missions/mark/` |
| Missions Tasks | `store/missions/tasks/` |
| Missions Personalize | `store/missions/personalize/` |
| Missions Subjects | `store/missions/subjects/` |
| Missions Parshos | `store/missions/parshos/` |
| Missions Shabbos Mevorchim | `store/missions/shabbos_mevorchim/` |
| Rewards Miles | `store/rewards/miles/` |
| Rewards Orders | `store/rewards/orders/` |
| Rewards Prizes | `store/rewards/prizes/` |
| Rewards Subjects | `store/rewards/subjects/` |
| Rewards Achievement Tasks | `store/rewards/achievement_tasks/` |
| Payments | `store/payments/` |

### Component Library (`components/`)

| Group | Contents |
|-------|---------|
| `buttons/` | Button variants |
| `inputs/` | Form inputs |
| `selects/redux/` | Redux-connected dropdowns |
| `selects/static/` | Plain dropdowns |
| `functional/` | Feature-specific components, payment forms |
| `missions/` | Mission-specific UI |
| `modals/` | Popup dialogs |
| `navigation/Navbar/` | Top navigation bar |
| `navigation/Sidebar/` | Side navigation |
| `navigation/dashboard/` | Dashboard layout navigation |
| `rows/` | Table rows and list items |
| `ui/loading/` | Loading spinners / states |

### Shared Utilities

| Item | Path | Purpose |
|------|------|---------|
| API client | `api/api.js` | Axios-based HTTP client |
| Hebrew date picker | `heDatePicker/` | Custom Hebrew/Gregorian date picker |
| Utility functions | `functions/utils/` | Shared helpers |
| Data | `data/` | Hardcoded reference data (includes `menu.json`) |

---

## PDF Service — `pdf-service/`

| File | Purpose |
|------|---------|
| `server.js` | Express HTTP server — accepts PDF generation requests |
| `worker.js` | Worker process — Puppeteer HTML→PDF, pdf-lib manipulation |

Used for: report cards, certificates, mission record exports, print features.

---

## Cross-Cutting Concerns

| Concern | Where handled |
|---------|--------------|
| Authentication | `api/auth/`, `store/login/`, Redis sessions (Predis) |
| Authorization | `api/core/admin_auths.php`, `AdminAuth` model |
| Hebrew calendar | `heDatePicker/`, `jewish-dates-core`, `store/missions/parshos/` |
| Payments | Authorize.Net via `classes/authorize/`, `api/payments/`, `store/payments/` |
| Email | PHPMailer via `api/tools/emails/` and legacy `classes/` |
| PDF generation | `pdf-service/` (Puppeteer), FPDF/FPDI, DocRaptor (PHP-side) |
| Caching / Sessions | Redis via Predis |
| Logging | Monolog |
| CI/CD | CircleCI (`.circleci/config.yml`), `deploy/deploy.sh` |
| Staging | `test.mashpia.com:522` |
| Production | `ssh.mashpia.com:522` |
