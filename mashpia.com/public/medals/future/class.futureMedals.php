<?php
/**
 * gets future medals for a school
 */
require_once 'class.medalsSubjects.php';

class FutureMedals {
    private $schools;
    private $end_date;
    private $ms; // medals subjects class
    private $users;
    private $user_subjects;
    private $missions_done;

    public function __construct($year, $end_date, $schools) {
        $this->end_date = $end_date;
        $this->schools = $schools;
        $this->ms = new MedalsSubjects();
        $this->users = $this->getUsers($year);
        $this->user_subjects = $this->getUserSubjects();
        $this->missions_done = $this->getMissionsDone();
        echo "<pre>";
        print_r($this->users);
        print_r($this->user_subjects);
        print_r($this->missions_done);
        echo "</pre>";
        exit;
    }

    private function getUsers($year) {
        global $MASHPIA_DB;

        $users = [];
        $sql = "select * from users u 
                join user_registration ur on ur.user_id = u.user_id 
                where u.school_id in (" . implode(',', $this->schools) . ")
                and ur.year = :year 
                and u.user_registered > 0";
        $stmt = $MASHPIA_DB->prepare($sql);
        $stmt->execute(['year' => $year]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (! $rows) echo $stmt->debugDumpParams();
        exit;
        foreach ($rows as $row) {
            $users[] = $row['user_id'];
        }

        return $users;
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
                and u.school_id in (" . implode(',', $this->schools) . ")
            ORDER BY user_id";
        $result = $MASHPIA_DB->query($sql);
        $rows = $result->fetchAll(PDO::FETCH_ASSOC);
        if (! $rows) echo $stmt->debugDumpParams();
        exit;
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
                u.school_id in (" . implode(',', $this->schools) . ")
            GROUP BY user_id , subject_id
        ";
        $stmt = $MASHPIA_DB->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (! $rows) echo $stmt->debugDumpParams();
        exit;
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
                COUNT(*) AS num_missions
            FROM
                date_tasks_missions dtm
                    JOIN
                date_tasks dt USING (date_tasks_mission_id)
                    JOIN
                user_tracks ut USING (subject_id)
                    JOIN
                users u USING (user_id)
            WHERE
                dtm.subject_id = :subject 
                    AND dtm.start_date >= :today 
                    AND dtm.end_date <= :end_date 
                    AND u.user_id = :user
                    AND u.school_type_id = dtm.school_type_id
                    AND ut.track_id = dtm.track_id
                    AND ut.level = dtm.level
                    AND u.lang_id = dtm.lang_id 
                    AND dt.mandatory_qty = 1 
                    AND dtm.personal = 0";

        $stmt = $MASHPIA_DB->prepare($sql);
        foreach ($this->user_subjects[$user_id] as $subject_id) {
            $stmt->execute([
                ':subject'  => $subject_id,
                ':today'    => unixtojd(),
                ':end_date' => $this->end_date,
                ':user'     => $user_id
            ]);
            if (! $row) echo $stmt->debugDumpParams();
            exit;
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $missions[$subject_id] = intval($row['num_missions']);
        }

        return $missions;
    }

    private function getEligibleMedals($user_id) {    
        $numMedals = 0;
        $future_missions = $this->getFutureMissions($user_id);

        // find out how many more medals can be earned by certain date by subject
        foreach ($this->user_subjects[$user_id] as $subject) {
            $current = $this->missions_done[$user_id][$subject] ?? 0;
            $future = $future_missions[$subject] ?? 0;
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