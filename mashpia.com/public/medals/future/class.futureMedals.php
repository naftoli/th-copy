<?php
/**
 * gets future medals for a school
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/medals/class.medalsSubjects.php';

class FutureMedals {
    private $schools;
    private $class_id;
    private $end_date;
    private $ms; // medals subjects class
    private $users;
    private $user_subjects;
    private $missions_done;

    public function __construct($year, $end_date, $schools, $class_id = 0) {
        $this->end_date = $end_date;
        $this->schools = $schools;
        $this->class_id = $class_id;
        $this->ms = new MedalsSubjects();
        $this->setUsers($year);
        $this->user_subjects = $this->getUserSubjects();
        $this->missions_done = $this->getMissionsDone();
    }

    private function setUsers($year) {
        global $MASHPIA_DB;

        $this->users = [];
        $sql = "select * from users u 
                join user_registration ur on ur.user_id = u.user_id 
                where u.school_id in (" . implode(',', $this->schools) . ")
                and ur.year = :year 
                and u.user_registered > 0";
        if ($this->class_id > 0) {
            $sql .= " and u.class_id = :class_id";
        }
        $stmt = $MASHPIA_DB->prepare($sql);
        if ($this->class_id > 0) {
            $stmt->execute([
                ':year' => $year,
                ':class_id' => $this->class_id
            ]);
        } else {
            $stmt->execute([
                ':year' => $year
            ]);
        }
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (! $rows) {
            echo $stmt->debugDumpParams();
            exit;
        }
        foreach ($rows as $row) {
            $this->users[] = $row['user_id'];
        }
    }

    public function getUsers() {
        return $this->users;
    }

    private function getUserSubjects() {
        global $MASHPIA_DB;

        $subjects = [];
        $sql = "
            SELECT 
                user_id, subject_id
            FROM
                user_tracks ut
                    JOIN
                users u USING (user_id)
            WHERE
                ut.enrolled = 1 
                and u.school_id in (" . implode(',', $this->schools) . ")";
        if ($this->class_id > 0) {
            $sql .= " AND u.class_id = :class_id";
        }
        $sql .= " ORDER BY user_id";
        $stmt = $MASHPIA_DB->prepare($sql);
        if ($this->class_id > 0) {
            $stmt->execute([
                ':class_id' => $this->class_id
            ]);
        } else {
            $stmt->execute();
        }
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $subjects[$row['user_id']][] = $row['subject_id'];
        }

        return $subjects;
    }

    private function getMissionsDone() {
        global $MASHPIA_DB;

        // find out where the child is holding in terms of how many missions were already done for this subject
        $sql = "
            SELECT 
                user_id, subject_id, COUNT(*) AS total
            FROM
                date_tasks_mission_marks dtm
                    JOIN
                users u USING (user_id)
            WHERE
                u.school_id in (" . implode(',', $this->schools) . ")";
        if ($this->class_id > 0) {
            $sql .= " AND u.class_id = :class_id";
        }
        $sql .= " GROUP BY user_id , subject_id";
        $stmt = $MASHPIA_DB->prepare($sql);
        if ($this->class_id > 0) {
            $stmt->execute([
                ':class_id' => $this->class_id
            ]);
        } else {
            $stmt->execute();
        }
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (! $rows) {
            echo $stmt->debugDumpParams();
            exit;
        }
        $missions_by_subject = [];
        foreach ($rows as $row) {
            $missions_by_subject[$row['user_id']][$row['subject_id']] = intval($row['total']);
        }

        return $missions_by_subject;
    }

    private function getFutureMissions($user_id) {
        global $MASHPIA_DB;

        $missions = [];
        $sql = "
            SELECT 
                user_id, subject_id, COUNT(*) AS num_missions
            FROM
                date_tasks_missions dtm
                    JOIN
                date_tasks dt USING (date_tasks_mission_id)
                    JOIN
                user_tracks ut USING (subject_id)
                    JOIN
                users u USING (user_id)
            WHERE
                dtm.personal = 0
                    AND dtm.start_date >= :today 
                    AND dtm.end_date <= :end_date 
                    AND u.school_type_id = dtm.school_type_id
                    AND ut.track_id = dtm.track_id
                    AND ut.level = dtm.level
                    AND u.lang_id = dtm.lang_id 
                    AND dt.mandatory_qty = 1 
                    AND u.user_id = :user_id 
                    AND u.user_registered is not null 
                    AND u.user_registered > '0000-00-00 00:00:00' 
                    GROUP BY user_id, subject_id";
        $stmt = $MASHPIA_DB->prepare($sql);
        $stmt->execute([
            ':today'    => unixtojd(),
            ':end_date' => $this->end_date,
            ':user_id' => $user_id
        ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (! $rows) {
            echo $stmt->debugDumpParams();
            exit;
        }
        foreach ($rows as $row) {
            $missions[$row['user_id']][$row['subject_id']] = intval($row['num_missions']);
        }

        return $missions;
    }

    private function getEligibleMedals($user_id) {    
        $numMedals = 0;
        $future_missions = $this->getFutureMissions($user_id);

        // find out how many more medals can be earned by certain date by subject
        foreach ($this->user_subjects[$user_id] as $subject) {
            $current = $this->missions_done[$user_id][$subject] ?? 0;
            $future = $future_missions[$user_id][$subject] ?? 0;
            $total = $current + $future;
            $current_medal = $this->ms->calcHighestMedal($subject, $current);
            $future_medal = $this->ms->calcHighestMedal($subject, $total);
            $medal_difference = $future_medal - $current_medal;
            // make sure there's no negative even though that would be a big issue if there was
            if ($medal_difference < 0) $medal_difference = 0;
            $numMedals += $medal_difference;
        }
    
        return $numMedals;
    }

    public function getFutureMedals() {
        // calculate possible medals
        $future_medals = [];
        foreach ($this->users as $user_id) {
            $num_medals = $this->getEligibleMedals($user_id);
            $future_medals[$user_id] = $num_medals;
        }

        return $future_medals;
    }
}