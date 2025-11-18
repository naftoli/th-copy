<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

class TeachTotals {
    private $taughtByRegion;
    private $taughtBySchool;
    private $year;
    private $grid_ids;

    public function __construct() {
        $this->taughtByRegion = [];
        $this->taughtBySchool = [];
        $this->year = GlobalSettings::getCurrentYear();
        $this->grid_ids = [15037, 15038, 15039, 15040, 15041, 15042, 15043, 15044, 15045, 15046, 15047, 15048];
    }

    public function getTotalTaught() {
        $total = 0;
        $this->setTaughtBySchool();
        foreach ($this->taughtBySchool as $row) {
            $total += $row['total'];
        }
        return $total;
    }

    public function setTaughtByRegion() {
        global $MASHPIA_DB;
        $sql = "
            SELECT
                region,
                IFNULL(COUNT(*), 0) AS total
            FROM (
                SELECT
                    t.user_id,
                    t.mechunach_id,
                    CASE
                        WHEN a.admin_country = 'USA' THEN CONCAT('USA - ', a.admin_state)
                        ELSE a.admin_country
                    END AS region
                FROM (
                    SELECT 
                        user_id,
                        mechunach_id,
                        SUM(done_qty) AS summed_qty
                    FROM date_tasks_marks dtm
                    JOIN date_tasks dt USING (date_task_id)
                    WHERE dt.grid_id IN (
                        " . implode(',', $this->grid_ids) . "
                    )
                    GROUP BY user_id, mechunach_id
                    HAVING summed_qty >= 12
                ) AS t
                JOIN admin_auths aa ON aa.id = t.user_id
                JOIN admins a ON a.admin_id = aa.admin_id
            ) AS sub
            GROUP BY region
            ORDER BY total desc, region";
        $stmt = $MASHPIA_DB->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->taughtByRegion = $rows;
    }

    public function setTaughtBySchool() {
        global $MASHPIA_DB;
        $sql = "
            SELECT 
                school_id, school_name, school_city, school_state, school_country, IFNULL(COUNT(*), 0) AS total
            FROM
                (SELECT 
                    dtm.user_id,
                        dtm.mechunach_id,
                        SUM(dtm.done_qty) AS summed_qty
                FROM
                    date_tasks_marks dtm
                JOIN date_tasks dt USING (date_task_id)
                WHERE
                    dt.grid_id IN (" . implode(',', $this->grid_ids) . ")
                GROUP BY dtm.user_id , dtm.mechunach_id
                HAVING summed_qty >= 12) AS t
                    JOIN
                users u USING (user_id)
                    JOIN
                schools s USING (school_id)
            GROUP BY u.school_id
            ORDER BY total DESC , school_name";
        $stmt = $MASHPIA_DB->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->taughtBySchool = $rows;
    }

    public function getTaughtByRegion() {
        if (empty($this->taughtByRegion)) $this->setTaughtByRegion();
        return $this->taughtByRegion;
    }

    public function getTaughtBySchool() {
        if (empty($this->taughtBySchool)) $this->setTaughtBySchool();
        return $this->taughtBySchool;
    }
}