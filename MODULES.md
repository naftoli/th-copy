# Application Modules

Jewish youth organization (Torah heritage) management platform. Modules span user management, education, finance, logistics, competitions, and recognition.

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

## Backend — `mashpia.com/public/`

### REST API (`api/`)

#### Auth
| File | Purpose |
|------|---------|
| `auth/login.php` | Session login |
| `auth/current_user.php` | Fetch authenticated user |
| `auth/forgot.php` | Password reset flow |
| `auth/new_account.php` | Account creation |
| `auth/chabadorgredirect.php` | Chabad.org SSO redirect |

#### Core data (`api/core/`)
| Endpoint file | Entity |
|--------------|--------|
| `bases.php` | Bases / headquarters |
| `institutions.php` | Schools / organizations |
| `modules.php` | Curriculum modules |
| `platoons.php` + `platoon_transition.php` | Groups / divisions |
| `hachayols.php` | Soldiers / students |
| `parents.php` | Parent contacts |
| `staff.php` | Staff / instructors |
| `users.php` | User accounts |
| `id_cards.php` | Member ID cards |
| `school_contacts.php` | School contacts |
| `admin_auths.php` | Admin permission assignments |
| `homepage/` | Dashboard data |

#### Missions (`api/missions/`)
`mark`, `tasks`, `grid`, `personalize`, `labels`, `streaks`, `createStreak`, `streak-tasks`, `subjects`, `parshos`, `tehillim`

#### Rewards (`api/rewards/`)
`miles`, `prizes`, `orders`, `achievement_tasks`, `achievement_cards`, `subjects`, `templates`

#### Other API modules
| Module | Path | Purpose |
|--------|------|---------|
| Registration | `api/registration/` | User & school registration, store resets |
| Payments | `api/payments/profiles.php` | Authorize.Net payment profiles |
| Pesukim | `api/pesukim/` | Torah verse API |
| Points | `api/points/` | Point calculation / tracking |
| Print | `api/print/` | Duch print, missions print cache, send-to-Ohel |
| Upload | `api/upload/` | File upload handling |
| Header | `api/header/` | Navigation/header data |
| Tools — Emails | `api/tools/emails/` | Email templates and sending utilities |
| Tools — Functions | `api/tools/functions/` | Shared utility functions (files, formatting) |

#### ORM Models (`api/models/`)
`Admin`, `AdminAuth`, `AchievementCard`, `AchievementTask`, `Frequency`, `Institution`, `Label`, `Parsha`, `Platoon`, `Role`, `Soldier`, `School`, `SchoolRegistration`, `StorePrize`, `Subject`

---

### Legacy AJAX Handlers (`ajax/`)

| Module | Path | Purpose |
|--------|------|---------|
| Authorize | `ajax/authorize/` | Auth-related AJAX |
| Chabadkid | `ajax/chabadkid/` | Children/student system, chidon marks, file uploads |
| Chidon | `ajax/chidon/` | Competition system (bases, grades, login, register, schools, users) |
| Charidy | `ajax/charidy/` | Charity / fundraising |
| Tanya | `ajax/tanya/` | Talmudic studies |
| Hachayols | `ajax/hachayols/` | Elite-warriors program AJAX |
| Helpdesk | `ajax/helpdesk/` | Support ticket AJAX |
| Yearly Gift | `ajax/yearly_gift/` | Annual gift processing |

---

### Feature Modules (Page-level)

#### Registration & Enrollment
| Module | Path | Purpose |
|--------|------|---------|
| Registration | `registration/` | Student / base registration entry point |
| New CSV Students | `new_csv_students/` | Bulk student import via CSV |
| New Classes | `newClasses/` | Classroom / class management |

#### Financial
| Module | Path | Purpose |
|--------|------|---------|
| Accounting | `accounting/` (~15 files) | Payment/refund tracking, voucher management, charge summaries |
| Payment | `payment/` | Transaction processing UI |
| Donate | `donate/` | Donation collection and tracking |
| Sponsorships | `sponsorships/` | Sponsorship collection and tracking |

#### Education & Missions
| Module | Path | Purpose |
|--------|------|---------|
| Mission Report | `mission_report/` | Mission/task assignment sheets with per-user tracking |
| Tasks | `tasks/` (~48 files) | Task definition, scheduling, and analytics |
| Pesukim / PesukimApp | `pesukim/`, `pesukimApp/` | Torah verse study tracking and interactive app |
| Mishna | `mishna/` | Mishna study program |
| Tehillim | `tehillim/` | Psalms study program |
| Mivtzoim | `mivtzoim/`, `mivtzoim_purchases/` | Mitzva campaign management and supply inventory |

#### Competitions
| Module | Path | Notes |
|--------|------|-------|
| Chidon Tests | `chidonTests/` | Competition test management, scoring, eligibility, reports |
| Chidon (Legacy) | `chidonOld/` (~472 files) | Full legacy Chidon system — attendance, grading, enrollment, coupons, certificates |
| Chidon Shipping | `chidon_shipping/` | Material logistics for Chidon participants |
| Chayolei / School / Parent Shipping | `chayolei_shipping/`, `school_shipping/`, `parent_shipping/` | Audience-specific distribution |
| Chidon Game | `chidonGame/`, `chidonGame5778/` | Interactive game components |

#### Events & Programs
| Module | Path | Purpose |
|--------|------|---------|
| Camps | `camps/` (~155 files) | Camp enrollment, goals, management, scheduling |
| Rally | `rally/` | Event coordination |
| Chanuka | `chanuka/` | Chanuka holiday program |
| Lag BaOmer | `lagBaomer/` | Lag BaOmer program |
| Hakhel | `hakhel/` | Hakhel gathering program |
| Kiosk | `kiosk/` | Goal/display screens for public kiosks |
| Timeline | `timeline/` | Chronological event tracking |
| Kinderblast | `kinderblast/` | Kindergarten-focused program |

#### Awards & Recognition
| Module | Path | Purpose |
|--------|------|---------|
| Rewards | `rewards/` | Rewards program, points tracking |
| Auction | `auction/` (~70 files) | Prize auction with lottery ticket assignment and winners |
| Medals | `medals/`, `medal_ceremony/`, `medal_board/` | Medal/badge system, ceremonies, leaderboard |
| Rank Ceremony | `rank_ceremony/`, `rank_books/` | Rank advancement ceremonies and progress tracking |
| Supersoldier | `supersoldier/` | Advanced achievement tier |
| Certs | `certs/` | Certificate generation |
| Cards | `cards/` | ID/member card management |
| Yearly Prize | `yearly_prize/` | Annual prize tracking |

#### Community & User Management
| Module | Path | Purpose |
|--------|------|---------|
| Platoons | `platoons/` | Platoon (unit) organization |
| Hachayols | `hachayols/` (~17 files) | Elite-warriors program with scheduling |
| Non-TH Schools | `non_th_schools/` | Non-affiliated school integration |
| Ckids | `ckids/` | Children-specific program interface |
| Neshek | `neshek/` | Internal program |
| Light Triumphs | `lighttriumphs/` | Achievement / inspirational program |
| Battlefront | `battlefront/` (~4 files + API) | Battlefront engagement feature |
| World | `world/` | World-level program feature |
| KHK | `khk/` | KHK program |
| Duch | `duch/` | Record-management SPA (API-backed) |

#### Content & Storytelling
| Module | Path | Purpose |
|--------|------|---------|
| News | `news/` | Announcements and news management |
| My Storyteller | `mystoryteller/` | Story collection and narrative archive |
| Rebbe Stories | `rebbestories/` | Teachings and stories archive |

#### Reporting & Admin
| Module | Path | Purpose |
|--------|------|---------|
| Reports | `reports/` (~153 files) | Data aggregation, analytics, business intelligence |
| Helpdesk | `helpdesk/` (~303 files) | Support ticketing system with full frontend + models |
| Emails / Emailer | `emails/`, `emailer/` | Email template management and delivery |
| Screens | `screens/` | Public-display / kiosk presentation screens |
| Tools | `tools/` | Administrative utilities |
| Fixes | `fixes/` | Data-correction and migration utilities |
| Scripts | `scripts/` | Background processing and batch operations |

#### Legacy Classes (`classes/` — ~107 files)
Object classes used by legacy AJAX handlers: `email.php`, `school.php`, `user_mission.php`, `rank_updater.php`, Authorize.Net integration, etc.

---

## Modern Frontend — `front-end/src/`

### Pages

| Module | Path | Sub-views |
|--------|------|-----------|
| Login | `pages/login/` | Sign-in, forgot password, new account |
| Home | `pages/home/` | Dashboard with birthday, promotion, and registration widgets |
| Account | `pages/account/` | User profile and settings |
| Base Mgmt — Base | `pages/base-managment/base/` | Base/HQ configuration |
| Base Mgmt — Module | `pages/base-managment/module/` | Curriculum module management |
| Base Mgmt — Platoons | `pages/base-managment/platoons/` | Groups, divisions, platoon transitions |
| Base Mgmt — Soldiers | `pages/base-managment/soldiers/` | Records, registration, rank cards (`SoldierPage`, `SoldiersPage`, `RegistrationPage`, `RankCardsPage`) |
| Base Mgmt — Staff | `pages/base-managment/staff/` | Staff management |
| Base Mgmt — Parents | `pages/base-managment/parents/` | Parent contact management |
| Missions — Mark | `pages/missions/mark/` | Scoring / marking missions |
| Missions — Tasks | `pages/missions/tasks/` | Mission task configuration |
| Missions — Personalize | `pages/missions/personalize/` | Per-user mission customization |
| Missions — Streaks | `pages/missions/streaks/` | Streak tracking |
| Missions — Duch | `pages/missions/duch/` | Mission record keeping |
| Missions — Print | `pages/missions/print/` | Printable mission reports |
| Rewards — Miles | `pages/rewards/miles/` | Points / miles management |
| Rewards — Prizes | `pages/rewards/prizes/` | Store item catalog |
| Rewards — Orders | `pages/rewards/orders/` | Order management |
| Rewards — Cards | `pages/rewards/cards/` | Achievement cards |
| Rewards — Tasks | `pages/rewards/tasks/` | Achievement task configuration |
| Report Cards | `pages/reportCards/` | Student progress and achievement report cards |
| Errors | `pages/errors/` | 404, under construction, locked-access pages |

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
| `inputs/` | Form input components |
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
| Data | `data/` | Hardcoded reference data |
| Input masks | `components/masks.js` | Input masking config |
| Constants | `components/constants.js` | Shared component constants |

---

## PDF Service — `pdf-service/`

| File | Purpose |
|------|---------|
| `server.js` | Express HTTP server — accepts PDF generation requests |
| `worker.js` | Worker process — Puppeteer HTML→PDF conversion and pdf-lib manipulation |

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
