<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

class PesukimTotals {
    private $totalRecruits;
    private $totalMinutes;
    private $todayMinutes;
    private $year;
    private $grid_id;
    private $dates;

    public function __construct() {
        $this->totalRecruits = 0;
        $this->totalMinutes = 0;
        $this->todayMinutes = 0;
        $this->grid_id = 15036;
        $this->year = GlobalSettings::getCurrentYear();
        $this->dates = GlobalSettings::getCurYearDates();
    }

    public function setMinutes($today = false) {
        global $MASHPIA_DB;
        $sql = "SELECT IFNULL(SUM(done_qty), 0) as total 
                FROM date_tasks_marks 
                JOIN date_tasks dt USING (date_task_id) 
                WHERE dt.grid_id = :grid_id";
        if ($today) $sql .= " AND mark_date = :today";
        else $sql .= " AND mark_date >= :start AND mark_date <= :end";
        $stmt = $MASHPIA_DB->prepare($sql);
        if ($today) $stmt->execute(['today' => unixtojd(), 'grid_id' => $this->grid_id]);
        else $stmt->execute(['start' => $this->dates['start'], 'end' => $this->dates['end'], 'grid_id' => $this->grid_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($today) $this->todayMinutes = intval($row['total']);
        else $this->totalMinutes = intval($row['total']);
    }

    public function getTotalMinutes() {
        if (empty($this->totalMinutes)) $this->setMinutes();
        return $this->totalMinutes;
    }

    public function getTodayMinutes() {
        if (empty($this->todayMinutes)) $this->setMinutes(true);
        return $this->todayMinutes;
    }

    public function getMinutesBySchool() {
        global $MASHPIA_DB;

        $sql = "SELECT school_id, IFNULL(SUM(done_qty), 0) as total 
                FROM date_tasks_marks 
                JOIN date_tasks dt USING (date_task_id) 
                JOIN users u USING (user_id)
                JOIN schools s USING (school_id)
                WHERE dt.grid_id = :grid_id 
                AND mark_date >= :start 
                AND mark_date <= :end
                GROUP BY school_id";
        $stmt = $MASHPIA_DB->prepare($sql);
        $stmt->execute(['grid_id' => $this->grid_id, 'start' => $this->dates['start'], 'end' => $this->dates['end']]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $minutes[$row['school_id']] = $row['total'];
        }
        return $minutes;
    }

    public function getMinutesByRegion() {
        global $MASHPIA_DB;

        $sql = "SELECT 
                    CASE
                        WHEN a.admin_country = 'USA' THEN CONCAT('USA - ', a.admin_state)
                        ELSE a.admin_country
                    END AS region,
                    IFNULL(SUM(done_qty), 0) as total
                FROM date_tasks_marks 
                JOIN date_tasks dt USING (date_task_id) 
                JOIN users u USING (user_id)
                JOIN admin_auths aa ON aa.id = u.user_id
                JOIN admins a USING (admin_id) 
                WHERE dt.grid_id = :grid_id 
                AND mark_date >= :start AND mark_date <= :end
                GROUP BY region";
        $stmt = $MASHPIA_DB->prepare($sql);
        $stmt->execute(['grid_id' => $this->grid_id, 'start' => $this->dates['start'], 'end' => $this->dates['end']]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $minutes[$row['region']] = $row['total'];
        }
        return $minutes;
    }

    public function getMinutesByUser($schools = []) {
        global $MASHPIA_DB;

        $sql = "SELECT user_id, IFNULL(SUM(done_qty), 0) as total 
                FROM date_tasks_marks 
                JOIN date_tasks dt USING (date_task_id) 
                JOIN users u USING (user_id)
                WHERE dt.grid_id = :grid_id 
                AND mark_date >= :start 
                AND mark_date <= :end";
        if (!empty($schools)) $sql .= " AND u.school_id IN (" . implode(',', $schools) . ")";
        $sql .= " GROUP BY user_id";
        $stmt = $MASHPIA_DB->prepare($sql);
        $stmt->execute(['grid_id' => $this->grid_id, 'start' => $this->dates['start'], 'end' => $this->dates['end'], 'schools' => $schools]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $minutes[$row['user_id']] = $row['total'];
        }
        return $minutes;
    }

    public function getRecruitsBySchool() {
        global $MASHPIA_DB;

        $recruits = [];
        $sql = "
            SELECT 
                school_id, school_name, IFNULL(COUNT(*), 0) AS total
            FROM
                pesukim_recruits r
                    JOIN
                users u ON u.user_id = r.recruiter_id
                    JOIN
                schools s USING (school_id)
            WHERE
                r.year = :year
            GROUP BY school_id
            ORDER BY total DESC, school_name";
        $stmt = $MASHPIA_DB->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $recruits[$row['school_id']] = $row['total'];
        }
        return $recruits;
    }

    public function getRecruitsByRegion() {
        global $MASHPIA_DB;

        $recruits = [];
        $sql = "
            SELECT 
                CASE
                    WHEN a.admin_country = 'USA' THEN CONCAT('USA - ', a.admin_state)
                    ELSE a.admin_country
                END AS region,
                IFNULL(COUNT(*), 0) AS total
            FROM
                pesukim_recruits r
                    JOIN
                users u ON u.user_id = r.recruiter_id
                    JOIN
                admin_auths aa ON aa.id = u.user_id
                    JOIN
                admins a USING (admin_id)
            WHERE
                r.year = :year
            GROUP BY region
            ORDER BY total DESC , region";
        $stmt = $MASHPIA_DB->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $recruits[$row['region']] = $row['total'];
        }
        return $recruits;
    }

    public function setTotalRecruits() {
        global $MASHPIA_DB;
        $sql = "SELECT IFNULL(COUNT(*), 0) as total FROM pesukim_recruits WHERE year = :year";
        $stmt = $MASHPIA_DB->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->totalRecruits = intval($row['total']);
    }

    public function getTotalRecruits() {
        if (empty($this->totalRecruits)) $this->setTotalRecruits();
        return $this->totalRecruits;
    }
}