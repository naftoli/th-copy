<?php
class medalsSubjects
{
    function __construct() {
        $this->medals_subjects = [];
    }

    function setMedalsSubjects() {
        global $MASHPIA_DB;

        $sql = "select * from medals_subjects";
        $stmt = $MASHPIA_DB->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $this->medals_subjects[$row["subject_id"]][$row["medal_ord"]] = $row['missions_required'];
        }
    }
    function getMedalsSubjects() {
        if (empty($this->medals_subjects)) {
            $this->setMedalsSubjects();
        }
        return $this->medals_subjects;
    }

    function calcHighestMedal($subject_id, $num_missions) {
        $medals_subjects = $this->getMedalsSubjects();
        // loop through medals subjects to find out which medal ord is eligible based on num missions
        $eligible_medal_ord = 0;
        foreach ($medals_subjects[$subject_id] as $medal_ord => $missions_required) {
            if ($num_missions >= $missions_required) {
                $eligible_medal_ord = $medal_ord;
            } else {
                break; // Stop checking once we find the first medal that requires more missions than we have
            }
        }

        return $eligible_medal_ord;
    }
}