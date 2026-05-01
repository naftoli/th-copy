# Module: Chidon (Torah Knowledge Competition System)

## Business Rules

---

# Enrollment / Registration

1. A child registers for Chidon on a per-year basis; a new `th_chidon` row is created for each `(user_id, year)` combination.
   Source: `SQLdump/mashpiadb_th_chidon.sql`:table definition; `ajax/chidon/register/index.php`:75

2. A child selects one of five Yahadus books at registration (1–5), corresponding to grades 4–8.
   Source: `ajax/chidon/register/index.php`:112–113 (confirmation email text); `SQLdump/mashpiadb_th_chidon.sql`:book enum('1','2','3','4','5')

3. At registration the child's sweater size is collected and stored on the `th_chidon` row; gender-specific sweaters are maintained in a separate `chidon_sweaters` table.
   Source: `ajax/chidon/register/index.php`:75–76; `SQLdump/mashpiadb_chidon_sweaters.sql`:gender enum('M','F')

4. A school must confirm its Chidon student list before child registrations open; if a school has not pressed "Confirm", children in that school cannot register for the Shabbaton.
   Source: `chidonOld/newReports/eligibility_registered_report.php`:73–74

5. Confirming a school's eligibility is a one-time, irreversible action — once confirmed, no changes are permitted.
   Source: `chidonOld/newReports/eligibility_registered_report.php`:70–73

6. Once a school submits its Chidon confirmation a row is inserted into `chidon_confirmations`; that same row is used to lock score entry for that school (i.e., a "confirmed" school can no longer have scores entered by its base commander).
   Source: `chidonTests/enterScores.php`:113–120; `SQLdump/mashpiadb_chidon_confirmations.sql`

7. School registration for the Shabbaton requires a $500 authorization hold placed on the school's credit card via Authorize.Net; the hold transaction ID is saved to `schools.chidon_hold_id`.
   Source: `ajax/chidon/registerSchool.php`:122–130

8. A child's `date_paid` field on `th_chidon` must be set (non-zero) for the child to appear on sweater and finals reports and for KHK finals marks to be enterable.
   Source: `chidonOld/newReports/sweaters.php`:23–24; `chidonTests/finals.php`:235, 97–98

9. Individual student records, platoon (class) averages, and school-wide averages are maintained completely independently — changing a higher-level setting does not cascade to lower levels.
   Source: `chidonTests/settings.php`:82–96

10. Per-school open-registration status is controlled per year through the `chidon_open_reg` table; each school has an independent on/off flag.
    Source: `SQLdump/mashpiadb_chidon_open_reg.sql`:table definition

11. Per-child subsidies can be applied from donor funds through the `chidon_user_subsidies` table, reducing the amount charged to the family.
    Source: `SQLdump/mashpiadb_chidon_user_subsidies.sql`:table definition

---

# Test Scoring

12. There are four regular Chidon tests per year and one finals exam; test scores are stored in `th_chidon_marks` keyed by `(th_chidon_id, test_type, test_number)`.
    Source: `SQLdump/mashpiadb_th_chidon_marks.sql`:UNIQUE KEY on (th_chidon_id, test_type, test_number)

13. The four test tracks are: **maven** (Yesod, 10 questions), **pro** (Yediah, 10 questions), **expert** (Havonah, 20 questions), **genius** (Iyun, 10 questions).
    Source: `chidonTests/class.chidonTests.php`:33–45 (testQuestions array)

14. Scores are recorded as the raw number of questions answered correctly; the system converts this to a percentage mark (answered_correctly / total_questions × 100).
    Source: `chidonTests/class.chidonTests.php`:361–373 (`calculateMarks`); `chidonTests/enterScores.php`:165 (infobox text)

15. Each of the four regular test marks (questions answered correctly) is capped at the question count for that track — 10 for Yesod/Yediah/Iyun, 20 for Havonah. The UI enforces these caps via JavaScript.
    Source: `chidonTests/enterScores.php`:280–286 (max=10), 295–301 (max=20)

16. Score entry windows for regular tests (non-super users) open and close on specific dates per test number:
    - Test 1: opens 2025-10-19, closes 2025-11-13
    - Test 2: opens 2025-12-02, closes 2025-12-25
    - Test 3: opens 2026-01-21, closes 2026-01-30
    - Test 4 (Finals): entry closes 2026-02-24
    Source: `chidonTests/class.chidonTests.php`:1036–1053 (`getOpeningDates`, `getClosingDates`); `chidonTests/finals.php`:151–156

17. A non-super base commander can only enter scores within the open window; scores submitted outside that window are silently discarded.
    Source: `chidonTests/enterScores.php`:69–97

18. The system resolves the "current test number" based on the test date ranges hardcoded in the constructor; tests run in three periods: 09/16–11/05/2025, 11/06–12/10/2025, and 12/11/2025–01/21/2026.
    Source: `chidonTests/class.chidonTests.php`:47–61, 67–80

19. The Iyun (genius) track input is disabled in the UI unless the child is on test level 2; if a genius mark is entered for a level-1 child, the system forces the mark to 0 before saving.
    Source: `chidonTests/class.chidonTests.php`:297–321 (`insertScores`); `chidonTests/enterScores.php`:222–224, 345–351

20. Tests are conducted at two difficulty levels (Level 1 and Level 2). The default level is Level 1 if no setting has been saved.
    Source: `chidonTests/class.chidonTests.php`:889 (default fallback to 1); `chidonTests/settings.php`:275–283

21. The passing average (threshold) for each track is configurable per user, per class, or per school. The lookup priority is: individual user setting → class setting → school setting → global default of 80%.
    Source: `chidonTests/class.chidonTests.php`:839–853 (`getPassingAvgs`)

22. Non-super (base commander) users may set passing averages between 70% and 100% in increments of 5%; super admins may set them from 0%. The minimum a base commander can choose is 70%.
    Source: `chidonTests/settings.php`:151–158, 71 (infobox text)

23. The global HQ standard passing threshold is 80% for all tracks.
    Source: `chidonTests/settings.php`:71 (infobox text)

24. The Iyun (genius) track passing threshold for base commanders is fixed at 80% (non-cumulative) and cannot be lowered. Only super admins can modify it.
    Source: `chidonTests/settings.php`:221–228; 257–263

25. A child's "average to date" for any test is calculated as the cumulative sum of that track's marks divided by the number of tests taken so far.
    Source: `chidonTests/marks.php`:126–133

26. The finals exam scores are stored in a separate table (`th_chidon_finals`) with four track columns (`track_1` through `track_4`) plus a `khk` column; they are separate from regular test marks.
    Source: `SQLdump/mashpiadb_th_chidon_finals.sql`:table definition

27. In the finals entry form, a child can only have marks entered up to the track corresponding to their highest track passed in regular tests (or their `final_type` override). Higher track columns are disabled.
    Source: `chidonTests/finals.php`:244–255

28. Finals entry is blocked by a closing date of 2026-02-24 for non-super users.
    Source: `chidonTests/finals.php`:149–156

---

# Eligibility

29. A child's eligibility for prizes is determined by their average across all four regular tests (not just one test); the system computes `round(total / 4, 2)` for each track.
    Source: `chidonTests/eligibility.php`:78–89; `chidonTests/save_eligibility.php`:43–56

30. Passing the **Yesod (maven)** track earns eligibility for a **sweater**.
    Source: `chidonTests/eligibility_check.php`:106–107; `chidonTests/save_eligibility.php`:99–101

31. Passing the **Yediah (pro)** track earns eligibility for **gifts** (in addition to sweater).
    Source: `chidonTests/eligibility_check.php`:110–111; `chidonTests/save_eligibility.php`:102–104

32. Passing the **Havonah (expert)** track earns eligibility for **trips** (in addition to sweater and gifts).
    Source: `chidonTests/eligibility_check.php`:113–115; `chidonTests/save_eligibility.php`:105–107

33. A child registered on the **pro** track who achieves a passing average on pro is also granted the same trip/prize eligibility as expert (i.e., the system promotes their prize level to expert when their test_type is 'pro' and pro_elig is 'yes').
    Source: `chidonTests/eligibility_check.php`:106, 118–119; `chidonTests/save_eligibility.php`:77–78

34. Trophy eligibility requires: (a) passing Havonah (expert_elig == 'yes') AND a Trophy track average >= 80, OR (b) a separate "3-parts average" (trophy_extra) >= 80.
    Source: `chidonTests/eligibility_check.php`:109–115; `chidonTests/eligibility.php`:109–115

35. There are hardcoded per-school and per-class minimum passing thresholds used in a legacy eligibility calculation (separate from the configurable `chidon_passing_avgs` table):
    - School 5: all tracks require 85%
    - School 106: non-maven tracks require 80%
    - School 255: non-maven tracks require 75%; maven requires 80%
    - School 4: all tracks require 77%
    - Classes 6088, 6089, 6090: all tracks require 75%
    - Classes 6061, 6260, 6376, 6579, 6821: all tracks require 80%
    - Default: 70%
    Source: `chidonTests/eligibility.php`:14–23; `chidonTests/save_eligibility.php`:14–23; `chidonTests/eligibility_check.php`:14–23

36. The system tracks which prize level each child earned via boolean flags on `th_chidon`: `shabbaton_maven`, `shabbaton_pro`, `shabbaton_expert`, `shabbaton_trophy`. These are set by the `save_eligibility.php` batch process.
    Source: `chidonTests/save_eligibility.php`:95–111; `SQLdump/mashpiadb_th_chidon.sql`:shabbaton_* columns

37. The reward track awarded to a child (for prizes) is derived from `reward_type` on `th_chidon` if set; otherwise from the child's highest track passed. If `reward_type` is set to a higher track than what was actually passed, the higher track is used.
    Source: `chidonTests/class.chidonTests.php`:629–645 (`getHighestTrackPassed`)

38. The `can_enroll` flag on `th_chidon` is set to 1 when any eligibility level is achieved; children without this flag cannot enroll in the Shabbaton.
    Source: `chidonTests/save_eligibility.php`:100,103,106,109

---

# KHK (Kol HaTorah Kulo) Track

39. The KHK enrollment fee is $18.
    Source: `chidonTests/class.chidonTests.php`:1059 (`KHK::$khkFee = 18`)

40. To be eligible to enroll in the KHK track, a child must have been enrolled in Chidon for each of the last 4 years. Exceptions can be manually entered in `khk_enrollment_eligibility`.
    Source: `chidonTests/class.chidonTests.php`:1062–1077 (`KHK::enrollmentEligibility`)

41. KHK enrollment eligibility exceptions are stored per `(user_id, year)` in `khk_enrollment_eligibility`; a child with a record there is considered eligible regardless of their 4-year enrollment history.
    Source: `chidonTests/class.chidonTests.php`:1079–1087 (`KHK::getEligibilityExceptions`); `SQLdump/mashpiadb_khk_enrollment_eligibility.sql`

42. KHK track marks (th_khk_marks) are separate from regular Chidon test marks and cover 4 tests; the average is computed only over tests where a mark was actually entered (divide by count of marks > 0).
    Source: `chidonTests/khk_tests.php`:137–148; `chidonTests/finals.php`:93–105

43. KHK score entry windows (for the current year) are distinct from regular test windows:
    - Test 1: 2025-10-19 to 2025-10-31
    - Test 2: 2025-11-11 to 2025-11-18
    - Test 3: 2025-12-29 to 2026-01-06
    - Test 4: 2026-01-27 to 2026-01-30
    Source: `chidonTests/khk_tests.php`:66–78

44. A child is eligible to sit the KHK final exam only if they are registered for KHK (`khk_reg = 1`) AND they have passed the KHK regular tests with an average of at least 70%.
    Source: `chidonTests/finals.php`:93–105 (`passedKhk` function)

45. KHK final marks are stored in `th_chidon_finals.khk`; the input is only enabled in the UI for schools with ID 61 or 269, or for super admins.
    Source: `chidonTests/finals.php`:263–268

---

# KHK Ultimate Trip Eligibility

46. The "Ultimate Trip" is awarded to grade-7 and grade-8 children who have been enrolled in Chidon for 4 consecutive years AND have passed at a track of at least Yediah (pro) — i.e., not just Yesod — in each of those years.
    Source: `chidonTests/class.chidonTests.php`:1127–1188 (`KHK::getUltimateTripEligibility`); `chidonOld/newReports/khk_eligibility.php`

47. A child who passed at the Yesod (maven) level only — the lowest track — does NOT qualify for the ultimate trip in that year.
    Source: `chidonTests/class.chidonTests.php`:1169–1171

48. For the current year, "passing" for ultimate trip purposes is defined as reaching the expert or genius track (Havonah or Iyun); reaching only maven or pro in the current year disqualifies the child.
    Source: `chidonTests/class.chidonTests.php`:1232–1244 (`KHK::getCurrentYrPassing`)

49. A super admin can override KHK ultimate trip eligibility for an individual child using the `khk_override` flag on `th_chidon`; this bypasses the normal 4-year enrollment and track requirements.
    Source: `chidonTests/class.chidonTests.php`:1102–1118 (`KHK::eligibility`); `chidonOld/newReports/khk_eligibility.php`:134–137

50. For grade-8 children who have reached at least Havonah or Iyun in the current year, the system checks their previous year's highest track. If the previous year's highest track was not Yesod, the child is listed as "Khk Trip" eligible for the eligibility/registered report.
    Source: `chidonOld/newReports/eligibility_registered_report.php`:113–118

51. The next-year ultimate trip eligibility preview report is restricted to super admins only and covers current grade-7 students.
    Source: `chidonOld/newReports/nextYrEligibility.php`:10–13, 21

---

# Prizes

52. Prizes are defined in the `chidon_prizes` table with name, quantity, size, color, price, and a `made_possible_by` sponsor field.
    Source: `SQLdump/mashpiadb_chidon_prizes.sql`

53. Each child can be assigned multiple prizes tracked in `chidon_user_prizes` per `(user_id, prize_id, year)`. The prizes can be personalized with a Hebrew name engraving stored in the `he_name` column.
    Source: `SQLdump/mashpiadb_chidon_user_prizes.sql`; `chidonTests/confirmations.php`:163–169

54. The prize/reward track assignment to a child is determined by `reward_type` on `th_chidon` if explicitly set; otherwise the child's highest passing track is used. A manually set `reward_type` can be overridden upward but not used if it equals 'highest track passed'.
    Source: `chidonTests/class.chidonTests.php`:628–645

55. Prize credits for the Shabbaton are $75 for children at the Yediah, Havonah, or Iyun level.
    Source: `chidonOld/newReports/eligibility_registered_report.php`:97–101

56. Award types at the Shabbaton are:
    - Yesod: certificate
    - Yediah: plaque
    - Havonah: medal / plaque
    - Iyun: medal / plaque / blue trophy
    Source: `chidonTests/finals.php`:129–135 (`getAward` function)

57. Trophy type is stored as an enum `('bronze','gold','silver')` on `th_chidon`; a separate `blue_trophy` boolean exists in `th_chidon_winners`.
    Source: `SQLdump/mashpiadb_th_chidon.sql`:trophy_type; `SQLdump/mashpiadb_th_chidon_winners.sql`:blue_trophy

---

# Shipping

58. Shipped items are tracked in `th_chidon_shipping` per `(user_id, item_id, year, item_num, shipment_number)`. The `status` field records shipping state; `date_shipped` records when items were sent.
    Source: `SQLdump/mashpiadb_th_chidon_shipping.sql`

59. Sweaters are shipped to schools; the `sweater_shipped` flag on `th_chidon` defaults to 1 (shipped). If a school unchecks a child's sweater as missing, it triggers a replacement shipment (`sweater_replaced` flag).
    Source: `chidonOld/newReports/sweaters.php`:51–60 (infobox text), 99–111

60. A school must "review" its sweater inventory and confirm receipt before the system considers sweaters for that school as verified; confirmation status is stored in `schools.sweaters_confirmed_5782`.
    Source: `chidonOld/newReports/sweaters.php`:17–18, 116–132

61. Walking zone assignment for the Shabbaton is address-based; zone boundaries are defined by street name and even/odd house number ranges in `chidon_walking_zones`.
    Source: `SQLdump/mashpiadb_chidon_walking_zones.sql`

---

# Finals / Representatives

62. Each Chidon child is assigned a test type (track) from: maven, pro, expert, trophy, or genius. This is stored on `th_chidon.test_type` and defaults to 'expert'.
    Source: `SQLdump/mashpiadb_th_chidon.sql`:test_type enum default 'expert'

63. Three levels of representation are tracked: school representative, regional representative, and international representative. They are independent flags on `th_chidon` (`school_rep`, `regional_rep`, `intl_rep`).
    Source: `SQLdump/mashpiadb_th_chidon.sql`:school_rep, regional_rep, intl_rep; `chidonTests/setReps.php`:139–196

64. Base commanders can set school representatives for their own school; only super admins can set regional and international representatives.
    Source: `chidonTests/setReps.php`:172–196 (disabled attribute for non-super)

65. Each representative can be assigned to one of five teams: Sefer Hamitzvos, Mishna Torah, Moreh Nevuchim, Pirush Hamishnayos, Igeres Horambam. Team assignment is independent at each rep level (school, regional, intl).
    Source: `chidonTests/setReps.php`:91–92 ($teams array); columns school_team, regional_team, intl_team on `th_chidon`

66. The representative sorting algorithm orders children first by track (Iyun → Havonah → Yediah → Yesod → none), then by average descending within each track and grade.
    Source: `chidonTests/setReps.php`:27–32 ($trackOrder), 76–88 (sort logic)

67. A school has the option to record either 1 or 5 videos for school representatives; this is stored in `schools.num_chidon_videos`.
    Source: `chidonTests/setReps.php`:119–126

68. The `final_type` field on `th_chidon` can override which finals columns are active for a specific child, independent of their achieved test_type.
    Source: `chidonTests/finals.php`:230–234

69. For the Iyun (genius) track, a child can qualify via either their per-test non-cumulative score (needing 80% on the Iyun final's passing avg) or via cumulative score across all tests (needing 90% cumulative on the genius track).
    Source: `chidonTests/class.chidonTests.php`:380–387 (`calculateCumulative`); `chidonTests/marks.php`:92–98

70. The cumulative score for the Iyun track is calculated as all correctly-answered questions across all prior tests for all tracks combined, divided by the total questions across all tracks multiplied by the number of tests taken.
    Source: `chidonTests/class.chidonTests.php`:389–426 (`getCumulativeScore`)

71. Finals entry via the ChabadKid (parent-side) API is only permitted before the closing date for tests 1–3; test 4 (finals) has no date restriction via that API. A child can only have final marks saved for tracks at or below their highest passing track.
    Source: `ajax/chabadkid/chidon/marks/index.php`:38–48, 32–35 (`checkTrackAndSchool`)

---

# Attendance

72. Attendance at Shabbaton sessions is tracked in `th_chidon_attendance_marks`; each record links an attendance time slot (`att_time_id`) to a specific child (`th_chidon_id`) with a `marked` boolean and the ID of who marked them.
    Source: `SQLdump/mashpiadb_th_chidon_attendance_marks.sql`

---

## Open Questions

- The `eligibility.php` / `save_eligibility.php` files contain a hardcoded `getNeededMark()` function with school- and class-specific overrides (schools 5, 4, 106, 255; classes 6088–6090, 6061, 6260, 6376, 6579, 6821). It is unclear whether these are still actively used alongside the newer configurable `chidon_passing_avgs` table, or whether they represent legacy data that predates the configurable system.
  Source: `chidonTests/eligibility.php`:14–23; `chidonTests/eligibility_check.php`:14–23

- The `reward_type` and `final_type` fields on `th_chidon` appear to perform similar functions (overriding which track determines prizes vs. which track determines finals eligibility). The exact distinction in business intent between the two is not fully clear from the code alone.
  Source: `SQLdump/mashpiadb_th_chidon.sql`:reward_type, final_type; `chidonTests/finals.php`:230–234; `chidonTests/class.chidonTests.php`:628–645

- The `chidon_reg` table (legacy) uses different type values (`winner`, `parent`, `runnerUp`, `runnerUpP`, `contestant`) that do not map to the current `th_chidon` track system. It is unclear whether this table is still written to or is purely historical.
  Source: `SQLdump/mashpiadb_chidon_reg.sql`:type enum

- The `th_chidon_avgs` table stores per-school, per-grade aggregate averages. How and when this table is populated, and whether it drives any eligibility or reporting decisions, is not apparent from the files reviewed.
  Source: `SQLdump/mashpiadb_th_chidon_avgs.sql`

- School 176 has a client-side password protection ("laky") applied to the marks entry and review pages. This is acknowledged in a code comment as non-secure; the business reason for this school's special treatment is unknown.
  Source: `chidonTests/marks.php`:143–155; `chidonTests/enterScores.php`:300–309

- The `chidon_lanyards` table stores lanyard colors and codes per serial number per year. The rule for which color is assigned to which child (based on track or prize level) is not present in the reviewed files.
  Source: `SQLdump/mashpiadb_chidon_lanyards.sql`

- The `chidon_refunds` table tracks refund amounts and donation breakdowns. The conditions under which a refund is issued (e.g., child drops out, amount formula) are not visible in the reviewed files.
  Source: `SQLdump/mashpiadb_chidon_refunds.sql`
