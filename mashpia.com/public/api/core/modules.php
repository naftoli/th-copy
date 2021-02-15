<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class ModuleRouter {
    private $whitelist = ["hachayols", "medals_ranks"];

    private function filterClasses($filters) {
        global $current_user, $MASHPIA_DB;
        $auth_filters = $current_user->login->getFilter( 's.', 'c.' );
        $statement = $MASHPIA_DB->query(
            "SELECT c.class_id from classes c JOIN schools s USING ( school_id )
            WHERE $auth_filters and $filters"
        );
        return $statement ? $statement->fetchAll(PDO::FETCH_COLUMN) : false;
    }

    private function filterUsers($filters) {
        global $current_user, $MASHPIA_DB;
        $auth_filters = $current_user->login->getFilter( 's.', 'u.' );
        $statement =  $MASHPIA_DB->query(
            "SELECT u.user_id from users u
            JOIN schools s USING ( school_id )
            LEFT JOIN classes c USING ( class_id )
            WHERE $auth_filters and $filters"
        );
        return $statement ? $statement->fetchAll(PDO::FETCH_COLUMN) : false;
    }

    private function filterSchool($filters) {
        global $current_user, $MASHPIA_DB;
        $auth_filters = $current_user->login->getFilter( 's.', 'u.' );
        $statement = $MASHPIA_DB->query("SELECT s.school_id from schools s WHERE $auth_filters and $filters");
        return $statement ? $statement->fetch(PDO::FETCH_COLUMN) : false;
    }

    public function show( $module ) {
        global $current_user, $MASHPIA_DB;
        if (!in_array($module, $this->whitelist)) {
            json_error("Module not whitelisted.");
        }
        $filters = $current_user->login->getFilter( 's.', 'u.' );
        $result = [];

        $users = $MASHPIA_DB->query(
            "SELECT u.user_id, u.$module from users u
            JOIN schools s USING ( school_id )
            LEFT JOIN classes c USING ( class_id )
            where $filters"
        )->fetchAll();
        foreach ($users as &$user) {
            $user[$module] = (bool)$user[$module];
        }
        $result['users'] = array_column($users, $module, "user_id");

        $classes = $MASHPIA_DB->query(
            "SELECT c.class_id, c.$module from classes c
            JOIN schools s USING ( school_id )
            where $filters"
        )->fetchAll();
        foreach ($classes as &$class) {
            $class[$module] = (bool)$class[$module];
        }
        $result['classes'] = array_column($classes, $module, "class_id");

        if ($login->type !== 'class') {
            $schools = $MASHPIA_DB->query("SELECT s.school_id, s.$module from schools s where $filters")->fetchAll();
            foreach ($schools as &$school) {
                $school[$module] = (bool)$school[$module];
            }
            $result['schools'] = array_column($schools, $module, "school_id");
        }

        json_response($result);
    }

    public function update( $module ){
        global $MASHPIA_DB;
        if (!(isset($module, $_POST['value']) && (isset($_POST['user_ids']) || isset($_POST['class_ids']) || isset($_POST['school_id'])))) {
            json_error("Invalid params");
        }
        if (!in_array($module, $this->whitelist)) {
            json_error("Module not whitelisted.");
        }

        try {
            if (isset($_POST['user_ids'])) {
                $user_ids = $this->filterUsers("u.user_id in ( " . implode(',', array_map($MASHPIA_DB->quote, $_POST['user_ids'])) . " )");
            } else if (isset($_POST['class_ids'])) {
                $class_ids = $this->filterClasses("c.class_id in ( " . implode(',', array_map($MASHPIA_DB->quote, $_POST['class_ids'])) . " )");
                $user_ids = $this->filterUsers("u.class_id in ( " . implode(',', $class_ids) . " )");
            } else if (isset($_POST['school_id'])) {
                $school_id = $this->filterSchool("s.school_id = " . $MASHPIA_DB->quote($_POST['school_id']));
                $class_ids = $this->filterClasses("c.school_id = $school_id");
                $user_ids = $this->filterUsers("u.school_id = $school_id");
            }
        } catch (\Throwable $th) {
            json_error( "invalid request or auth issue: {$th->getMessage()}" );
        }

        $value = $_POST['value'] ? 1 : 0;
        $result = [];
        $success = false;

        if (isset($user_ids) && count($user_ids) > 0) {
            $statement = $MASHPIA_DB->query("update users set $module = $value where user_id in (" . implode(',', $user_ids) . ")");
            $result['users'] = $statement ? $statement->rowCount() : 0;
            if ($result['users'] > 0) $success = true;
        }

        if (isset($class_ids) && count($class_ids) > 0) {
            $statement = $MASHPIA_DB->query("update classes set $module = $value where class_id in (" . implode(',', $class_ids) . ")");
            $result['classes'] = $statement ? $statement->rowCount() : 0;
            if ($result['classes'] > 0) $success = true;
        }

        if (isset($school_id) && count($school_id) > 0) {
            $statement = $MASHPIA_DB->query("update schools set $module = $value where school_id = $school_id");
            $result['school']  = $statement ? $statement->rowCount() : 0;
            if ($result['school'] > 0) $success = true;
        }
        json_response( $result, $success);
    }
}

rest_router( new ModuleRouter );
