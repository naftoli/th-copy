<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class ModuleRouter {
    private $whitelist = ["hachayols", "medals_ranks"];

    private function filterClasses($filters) {
        global $current_user; global $MASHPIA_DB;
        $auth_filters = $current_user->login->getFilter( 's.', 'u.' );
        return $MASHPIA_DB->query(
            "SELECT c.class_id from classes JOIN schools s USING ( school_id )
            WHERE $auth_filters and $filters"
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    private function filterUsers($filters) {
        global $current_user; global $MASHPIA_DB;
        $auth_filters = $current_user->login->getFilter( 's.', 'u.' );
        return $MASHPIA_DB->query(
            "SELECT u.user_id from users 
            JOIN schools s USING ( school_id )
            LEFT JOIN classes c USING ( class_id )
            WHERE $auth_filters and $filters"
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    private function filterSchool($school_id) {
        global $current_user; global $MASHPIA_DB;
        $auth_filters = $current_user->login->getFilter( 's.', 'u.' );
        
        if ($school_id !== $login->school_id) {
            $school_id = $MASHPIA_DB->query(
                "SELECT s.school_id from schools s
                WHERE $filters and u.school_id = $school_id"
            )->fetchAll(PDO::FETCH_COLUMN)[0];
        }
    }

    public function show( $module ) {
        global $logger;
        $logger->debug("params", $_GET);
        if (!in_array($module, $this->whitelist)) {
            return json_error("module missing from whitelist.");
        }
        global $current_user; global $MASHPIA_DB;
        $filters = $current_user->login->getFilter( 's.', 'u.' );
        $result = [];

        $logger->debug("SELECT u.user_id, u.$module from users u
        JOIN schools s USING ( school_id )
        LEFT JOIN classes c USING ( class_id )
        where $filters");
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
            $result['schools'] = array_column($school, $module, "school_id");
        }

        json_response($result);
    }

    public function update( $module ){
        global $logger;
        $logger->debug("params", $_POST);
        global $current_user; global $MASHPIA_DB;
        $filters = $current_user->login->getFilter( 's.', 'u.' );

        try {
            if (is_set($_POST['user_ids'])) {
                $user_ids = array_map($MASHPIA_DB->quote, $_POST['user_ids']);
                $user_ids = $this->filterUsers("u.user_id in ( " . implode(',', $user_ids) . " )");
            } else if (is_set($_POST['class_ids'])) {
                $class_ids = array_map($MASHPIA_DB->quote, $_POST['class_ids']);
                $class_ids = $this->filterClasses("u.class_id in ( " . implode(',', $class_ids) . " )");
                $user_ids = $this->filterUsers("u.class_id in ( " . implode(',', $class_ids) . " )");
            } else if (is_set($_POST['school_id'])) {
                $school_id = $MASHPIA_DB->quote($_POST['school_id']);
                $school_id = $this->filterSchool($school_id);
                $class_ids = $this->filterClasses("u.school_id = $school_id");
                $user_ids = $this->filterUsers("u.school_id = $school_id");
            } else {
                throw new \InvalidArgumentException("Invalid Request");
            }
        } catch (\Throwable $th) {
            json_error( "invalid request or auth issue: {$th->getMessage()}" );
        }

        $result = [];
        $module = $MASHPIA_DB->quote($_POST['module']);
        $value = $MASHPIA_DB->quote($_POST['value']);
        // $statement = $MASHPIA_DB->query("update users set $module = $value where user_id in (" . implode(',', $user_ids) . ")");
        $result['users'] = $statement->rowCount();
        if (is_set($class_ids) && count($class_ids) > 0) {
            // $statement = $MASHPIA_DB->query("update classes set $module = $value where class_id in (" . implode(',', $class_ids) . ")");
            $result['classes'] = $statement->rowCount();
        }
        if (is_set($school_id) && count($school_id) > 0) {
            // $statement = $MASHPIA_DB->query("update schools set $module = $value where school_id = $school_id");
            $result['school'] = $statement->rowCount();
        }
        json_response( $result, $result['users'] > 0);
    }
}

rest_router( new ModuleRouter );
