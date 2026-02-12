# Duch Print Flow – Database Activity Analysis

## Flow Overview

```
duch.php (HTML) → fetch printDuchAll.php → Missions::createMissions → foreach user: get_user_tracks 
    → user_track_for_duch::get_date_tasks_missions_for_duch 
    → date_tasks_mission_for_duch::get_*_tasks 
    → daily_task::set_date_tasks_marks, weekly_task::set_date_task_mark, etc.
    → missionDisplay::printDuch (get_medals, get_ranks, get_all_medals)
```

**Note:** `user->for_duch` is never set in `Missions::createMissions`; `for_duch` is passed but not applied. So the duch flow may be using `user_track` (not `user_track_for_duch`) unless set elsewhere. Both paths use the same task classes (daily_task, weekly_task, etc.) which have the heavy query issues below.

---

## 1. Heavy Database Areas

### 1.1 Daily task marks – per-day loop (CRITICAL)

**File:** `classes/daily_task.php` lines 69–97

```php
function set_date_tasks_marks($user_id, $start_date, $end_date) {
    for ($mark_date = $start_date; $mark_date <= $end_date; $mark_date++) {
        $sql = "SELECT * FROM date_tasks_marks WHERE user_id=... AND date_task_id=... AND mark_date=" . $mark_date;
        $query = mysql_query($sql);
        // ...
    }
}
```

- One query per day per daily task.
- For a 30-day range: ~30 queries per daily task.
- For a user with ~30 daily tasks, ~5 tracks: ~4,500 queries per user.
- For 300 users: ~1.35M queries per duch print.

### 1.2 Task exceptions – per task

**File:** `class.taskExceptions.php` – `isException($taskID, $userID)`

- Up to 4 queries per call: `users`, `school_subjects`, `school_task_exceptions`, `class_task_exceptions`, `user_task_exceptions`.
- Called for each task in `user_track_for_duch::get_date_tasks_missions_for_duch` when `allowPersonalization` is true.
- With hundreds of tasks per user: thousands of queries per user.

### 1.3 Defaults – per mission/task

**File:** `class.defaults.php` – `isOn($id, $table)`

- Constructor: 1 query per user to get school/class.
- `isOn()`: 1–3 queries per mission and per task.
- Also called for each mission and task in `get_date_tasks_missions_for_duch`.

### 1.4 Per-user task queries

**File:** `classes/date_tasks_mission_for_duch.php`

- `get_daily_tasks`, `get_weekly_tasks`, `get_shabbos_tasks`, `get_no_label_tasks`, `get_pesukim_tasks`: each runs a separate query per mission.
- `$all_date_tasks` caches by `(mission_id, type)` but not per user.
- Task marks are user-specific, so `set_date_tasks_marks` / `set_date_task_mark` runs per user.

### 1.5 Per-user print phase

**File:** `mission_report/classes/missionDisplay.php` – `printDuch()`

- `$user->get_all_medals()` – 1 query per user.
- `get_medals($track->subject_id, ...)` – 1 query per track per user.
- `get_ranks(...)` – 1 query per user.
- `School::find` / `Platoon::find` – per user (may be cached by ActiveRecord).

### 1.6 User loading

**File:** `printDuchAll.php` / `Soldier::find_all_by_class_id`

- One query per class when loading users by class.
- `missions.php` uses `mysql_query` for users; separate queries for ranks and classes (already batched).

---

## 2. What’s Already Optimized

- `date_tasks_missions`: pre-loaded in `printDuchAll.php` into `$all_date_tasks_missions`.
- `birthday_cache`: pre-loaded in `build_mission_print_caches`.
- `Streaks::getStreaksForUsers`: batch loads all users.
- `Missions`: batch loads ranks and classes when there are multiple users.
- `$all_date_tasks`: caches tasks by mission+type in `get_date_tasks_missions_for_duch` (but marks are not cached).

---

## 3. Optimization Recommendations

### 3.1 (Highest impact) Batch-load `date_tasks_marks` for daily tasks

**Current:** One query per day per daily task.

**Change:** Batch load all marks for a user in the date range:

```php
// In a duch-specific cache or new function
$sql = "SELECT dtm.*, dt.date_task_id, dt.grid_id FROM date_tasks_marks dtm 
        JOIN date_tasks dt USING (date_task_id)
        WHERE dtm.user_id = ? AND dtm.mark_date >= ? AND dtm.mark_date <= ?";
```

Store in `$GLOBALS['duch_marks_cache'][user_id][date_task_id][mark_date]` or `[user_id][grid_id][mark_date]`.

**Update:** `daily_task::set_date_tasks_marks` reads from this cache instead of querying.

**Impact:** For 30 days × 30 tasks × 300 users: from ~270,000 queries to ~1 batch query per user (or a few chunks).

### 3.2 Batch-load `date_tasks_marks` for weekly/shabbos/no-label tasks

**Current:** One query per task per user.

**Change:** Same batch load as above; weekly/shabbos/no-label tasks can use the same cache.

### 3.3 Pre-load TaskExceptions and Defaults

**Current:** Many queries per task/user.

**Change:**

- Batch-load `school_task_exceptions`, `class_task_exceptions`, `user_task_exceptions` for the relevant schools/classes/users and tasks.
- Batch-load `user_defaults`, `class_defaults`, `school_defaults` for the same scope.
- Change `TaskExceptions::isException` and `Defaults::isOn` to use these caches.

### 3.4 Batch-load medals and ranks in print phase

**Current:** Per-user calls to `get_medals`, `get_ranks`, `get_all_medals`.

**Change:** Add batch loaders (e.g. `User::getMedalsForUsers`, `User::getRanksForUsers`) and pre-load before the `foreach ($objMissions as $obj)` loop. Use the cached data inside `printDuch`.

### 3.5 Optionally add task row cache (like missions print)

**Current:** `date_tasks_mission_for_duch::get_*_tasks` runs queries (mitigated by `$all_date_tasks` cache).

**Change:** Add `build_mission_print_task_cache()` for duch (similar to `missions_print_cache.php`), populating `$GLOBALS['duch_print_tasks']` or similar. Make `date_tasks_mission_for_duch` read from this when set.

### 3.6 Fix `for_duch` flag

**Current:** `Missions::createMissions` receives `for_duch` but never sets `$user->for_duch = true`.

**Change:** In `createMissions`, before `get_user_tracks`:

```php
if ($for_duch) {
    $user->for_duch = true;
}
```

This ensures duch uses `user_track_for_duch` and its caching logic.

---

## 4. Priority Order

1. Batch-load `date_tasks_marks` for all task types (daily, weekly, shabbos, no-label).
2. Batch-load TaskExceptions and Defaults.
3. Batch-load medals and ranks before the print loop.
4. Add task row cache for duch (if not already covered by `$all_date_tasks`).
5. Fix `for_duch` flag.

---

## 5. Estimated Query Reduction

| Component        | Before (300 users) | After (batch) |
|----------------|--------------------|---------------|
| Daily marks     | ~270,000+          | ~300          |
| Weekly/shabbos  | ~50,000+           | ~300          |
| TaskExceptions | ~100,000+          | ~10–20        |
| Defaults       | ~50,000+           | ~10–20        |
| Medals/ranks    | ~1,500             | ~5            |

Total: from hundreds of thousands to on the order of hundreds of queries.
