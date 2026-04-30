# Tzivos Hashem — Business Rules Index

**Extracted:** 2026-04-30  
**Method:** Automated analysis of SQL schema dumps + PHP source code  
**Total Rules:** ~402 across 7 domain files  
**SME Verified:** None (all rules require human sign-off)

---

## How to Read These Documents

Each rule follows this format:

```
Rule ID:      BR-XXX-001
Category:     [Domain]
Description:  Plain-English statement of the rule
Source:       File or table where the rule was observed
DB Evidence:  Column, constraint, enum, or default value supporting the rule
Confidence:   High / Medium / Low
SME Verified: No
```

**Confidence levels:**
- **High** — Enforced by DB constraint (PK, FK, UNIQUE, NOT NULL, CHECK, ENUM) or explicit code logic
- **Medium** — Inferred from column naming, defaults, or code patterns
- **Low** — Implied by data shape or partial evidence; needs SME confirmation

---

## Domain Files

| File | Domain | Rules | Key Topics |
|------|--------|-------|------------|
| [BR-users-registration.md](BR-users-registration.md) | Users & Registration | ~63 | User accounts, admin accounts, auth, password reset, school/user enrollment, KHK eligibility |
| [BR-chidon.md](BR-chidon.md) | Chidon Competition | ~70 | Registration (grades 4–8), scoring, passing thresholds, prizes, attendance, payments, subsidies, shipping, chaperones |
| [BR-points-missions-tasks.md](BR-points-missions-tasks.md) | Points, Missions & Tasks | ~57 | Point precision, daily/weekly/monthly tasks, mission completion logic, streaks, medals, ranks |
| [BR-payments-prizes-store.md](BR-payments-prizes-store.md) | Payments, Prizes & Store | ~57 | Payment lifecycle, prize store, auctions, raffles, shipping, discounts, coupons |
| [BR-schools-groups-staff.md](BR-schools-groups-staff.md) | Schools, Groups & Staff | ~52 | School hierarchy, class structure, group/division model, staff permissions, role-based access |
| [BR-camps-fundraising-learning.md](BR-camps-fundraising-learning.md) | Camps, Fundraising & Learning | ~44 | Camp structure, Charidy/donations, Tanya learning, Tehillim, Mishnah, Pesukim, Kapitel |
| [BR-campaigns-cards-system.md](BR-campaigns-cards-system.md) | Campaigns, ID Cards & System | ~59 | Campaign types, hachayol/ID cards, system dates (Julian), translations, announcements, add-ons, platoon transitions |

---

## High-Priority Findings for SME Review

These items were flagged during extraction as high-impact or potentially problematic:

### Security
| ID | Issue | File |
|----|-------|------|
| BR-ADM | Hardcoded HMAC secret in `admin_auth.php` shared across legacy and modern auth | `admin_auth.php` |
| BR-RORD-003 | CCV values stored in plaintext in `registration_orders` table | `mashpiadb_registration_charges` |

### Hardcoded Dates / Annual Maintenance
| ID | Issue | File |
|----|-------|------|
| BR-CHID | Chidon close date hardcoded as `2027-02-12` in `Soldier.php::turnOffChidon()` | `Soldier.php` |
| BR-SYS | All system dates stored as Julian Day numbers — Hebrew calendar-native | `mashpiadb_system_dates` |

### Data Integrity / Ambiguity
| ID | Issue | Source |
|----|-------|--------|
| BR-SCH | School type `0` in `school_registrations.type` is a valid DB value with no documented business meaning | `mashpiadb_school_registrations` |
| BR-AUC | Auction can only be run once (run_auction lock); no re-run path documented | `admin_auction_run.php` |
| BR-STR | Prize stock allows negative values (backorder scenario undocumented) | `mashpiadb_prizes_store` |
| BR-CAM | Three separate campaign systems coexist with overlapping purpose | `global_campaigns`, `campaigns`, `line_campaigns` |
| BR-MDL | Medal thresholds are cumulative-additive, not per-medal — non-obvious | `mashpiadb_medals` |

### Parallel / Duplicate Systems
| Issue | Details |
|-------|---------|
| Two points systems | `mashpiadb.points` (legacy) and `pointsDB.user_points` (modern) — rules differ |
| Two rank systems | mashpiadb uses medal counts; pointsDB uses point totals |
| Two user tables | `mashpiadb.users` and `pointsDB.users` with a `pointsDB.legacy_lookup` bridge |
| Four card programs | Rank/ID cards, achievement cards, CKids mission cards, scratch cards — all independent |

---

## Coverage Gaps

The following areas were not fully analyzed due to scope or file count:
- **FAQ/Help system** (`faq*` tables) — content management, not core business rules
- **Reports system** (`reports`, `report_marks`, `report_subjects`) — output/display rules only
- **Legacy `admin2.php`** — large procedural file; only partially sampled
- **`accounting/` folder** — not analyzed

