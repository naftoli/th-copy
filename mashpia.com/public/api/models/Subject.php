<?php
include_once( __DIR__ . '/traits/BuildModel.php' );
// ! Legacy Code!!
require_once( API_ROOT . '/../classes/rank_updater.php' );
require_once( API_ROOT . '/../classes/medal_updater.php' );

class Subject extends ActiveRecord\Model implements JsonSerializable {
    use \traits\BuildModel;

    private $achievement_tasks = [];

    // * get the name for the campaign
    public function name() {
        if ( $this->subject_id == 1 ) 
            return "תהילים";
        else if ( $this->subject_id == 27 ) 
            return "תניא";
        return $this->subject_name;
    }

    // * logoPath
    public function logoPath() {
        if ( $this->subject_logo )
            return '/mobile/img_new/campaign-logos-bw/'.$this->subject_logo;
        if ( $this->subject_image_id )
            return '/file_view.php?id='.$this->subject_image_id;
        return '/mobile/img_new/campaign-logos-bw/Footsteps.gif';
    }

    // * setMissions - update the missions for the given user
    // TODO: update his medals/ranks
    public function setMissions( $user_id, $missions ) {
        global $MASHPIA_DB;
        // remove all previous manual changes
        $remove_previous_query = $MASHPIA_DB->prepare(
             'DELETE FROM date_tasks_mission_marks WHERE user_id = :user_id '
            .'AND date_tasks_mission_id = :mission_id;'
        );
        // insert the new change into the dbs
        $insert_query = $MASHPIA_DB->prepare(
            'INSERT INTO date_tasks_mission_marks SET '
                .'date_tasks_mission_id = :mission_id, '
                .'user_id = :user_id, subject_id = :subject_id, '
                .'mission_value = :count, mission_count = :count, '
                .'mark_date = :date;'
        );
        // get the mission id that we add fake missions to for this task
        $mission_id = $this->getCustomizedId();
        // remove all previous marks for that mission
        $remove_previous_query->execute([
            ':mission_id' => $mission_id,
            ':user_id' => $user_id
        ]);
        // load how many they actually did and what the difference is
        $compleated = $this->getCompleatedMissions( $user_id );
        $count = intval( $missions ) - intval( $compleated );
        // insert the change if we need to
        if ( $count !== 0 )
            $insert_query->execute([
                ':subject_id' => $this->subject_id,
                ':mission_id' => $mission_id,
                ':user_id' => $user_id,
                ':date' => unixtojd(),
                ':count' => $count
            ]);
        // update the medal
        $medal_updater = new medal_updater();
        $medal_updater->update_medal_two( $user_id );
        // update the rank
        $rank_updater = new rank_updater();
        $rank_updater->update_rank_two( $user_id );

        return true;
    }

    private function getCompleatedMissions( $user_id ) {
        global $MASHPIA_DB;

        $get_compleated_query = $MASHPIA_DB->prepare(
            'SELECT IFNULL( SUM( mission_count ), 0 ) as total FROM date_tasks_mission_marks WHERE subject_id = ? AND user_id = ?;'
        );
        $get_compleated_query->execute([ $this->subject_id, $user_id ]);
        return $get_compleated_query->fetch()['total'];
    }

    private function getCustomizedId() {
        global $MASHPIA_DB;
        // see if we can find the subject
        $find_query = $MASHPIA_DB->prepare(
            'SELECT date_tasks_mission_id FROM date_tasks_missions WHERE subject_id = ? AND level = 0 AND start_date = 0'
        );
        // insert the mission if we do not have it
        $insert_query = $MASHPIA_DB->prepare(
            'INSERT INTO date_tasks_missions SET subject_id = ?, level = 0;'
        );
        
        $find_query->execute([ $this->subject_id ]);

        if ( $find_query->rowCount() > 0 ) {
            return $find_query->fetch()['date_tasks_mission_id'];
        } else {
            $insert_query->execute([ $this->subject_id ]);
            return $MASHPIA_DB->lastInsertId();
        }
    }

    // * get the achivement tasks that are tied to this campaign
    public function getAchievementTasks( $login ) {
        $inst_id = 2; // default institution id.
        $base_filter = 'base = 1 ';
        $platoon_filter = 'platoon = 1 ';
        // generate the filters
        if ( $login->code == 'INST' ) {
            $base_filter .= 'OR base IN ( SELECT school_id FROM schools WHERE inst_id = '.$login->inst_id.' ) ';
        } else if ( $login->code == 'BC' ) {
            $base_filter .= 'OR base = ' . $login->school_id;
        } else if ( $login->code == 'TEACHER' ) {
            $base_filter .= 'OR base = ' . $login->school_id;
            $platoon_filter .= 'OR platoon = ' . $login->class_id;
        }
        
        return $this->achievement_tasks = AchievementTask::all([
            'conditions' => [
                "subject_id = ? AND ( $base_filter ) AND ( $platoon_filter )",
                $this->subject_id
            ]
        ]);
    }

    // serialize to json
    public function jsonSerialize() {
        return $this->to_array([
            'only' => [ 'subject_name', 'subject_id', 'subject_type', 'inst_id' ],
        ]);
    }

    public function includeTasksJSON( $login ) {
        $res = $this->to_array([
            'only' => [ 'subject_name', 'subject_id', 'subject_type', 'inst_id' ],
        ]);

        $res['achievement_tasks'] = $this->getAchievementTasks( $login );

        return $res;
    }
}