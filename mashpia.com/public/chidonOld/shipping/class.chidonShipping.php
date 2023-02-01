<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

class ChidonShipping
{
    private $db, $year, $schools, $grades, $users;

    public function __construct() {
        global $MASHPIA_DB;
        $this->db = $MASHPIA_DB;
        $this->year = GlobalSettings::getChidonYear();
    }

    public function setYear($yr) {
        $this->year = $yr;
    }

    public function setSchools($schools) {
        $this->schools = $schools;
    }

    public function setGrades($grades) {
        $this->grades = $grades;
    }

    public function setUsers($users) {
        $this->users = $users;
    }

    public function getChildren() {
        /**
         * set all children that can possibly get anything based off schools / grades / users
         * returns array with school/grade/user keys
         */

        $info = [];
        $sql = "select * from users u 
                join schools s using (school_id) 
                join classes c on c.class_id = s.school_id 
                join th_chidon tc using (user_id) 
                where tc.year = :year";
        if ($this->schools) $sql .= " and u.school_id in (" . implode(',', $this->schools) . ")";
        if ($this->grades) $sql .= " and c.class_id in (" . implode(',', $this->grades) . ")";
        if ($this->users) $sql .= " and u.user_id in (" . implode(',', $this->users) . ")";
        $sql .= " order by s.school_name, c.class_grade, c.class_sub, u.last, u.first";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
//        $stmt->debugDumpParams();
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $info[$row['school_id']][$row['class_id']][$row['user_id']] = $row;
        }
        return $info;
    }

    public function getBrochures($gender, $school, $brochures = [], $early = false) {
        /**
         * get all brochures that need to be sent out
         *
         * QUALIFICATIONS:
         * all children signed up to TH between grades 4-8
         * (or 3-7) if doing it before end of yr
         *
         * @return array all user info from users db with the user id as the key
         */

        $info = [];
        $in_grades = "('4', '5', '6', '7', '8')";
        if ($early) $in_grades = "('3', '4', '5', '6', '7')";
        $sql = "SELECT * FROM users u 
                JOIN classes c ON c.class_id = u.class_id 
                WHERE c.class_grade in $in_grades 
                AND u.user_registered > 0";
        if ($gender == 'm') $sql .= " and u.gender = 'm'";
        if ($gender == 'f') $sql .= " and u.gender = 'f'";
        if ($school > 0) $sql .= " and u.school_id = " . $school;

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $info[$row['user_id']] = 1;
        }
        return $info;
    }

    public function getBooks($gender, $school, $books = []) {
        /**
         * get all books purchased for specific year
         *
         * QUALIFICATIONS:
         * any one that ordered it
         *
         * @return array all book info from db with user id as the key
         */

        $info = [];
        $sql = "SELECT * FROM yahadus_book_purchases 
                JOIN users u USING (user_id) 
                WHERE year = :year";
        if ($gender == 'm') $sql .= " and u.gender = 'm'";
        if ($gender == 'f') $sql .= " and u.gender = 'f'";
        if ($school > 0) $sql .= " and u.school_id = " . $school;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $info[$row['user_id']] = $row;
        }
        return $info;
    }

    public function getGuides($gender, $school, $guides = []) {
        /**
         * get all guides to send out
         *
         * QUALIFICATIONS:
         *
         */
    }

    public function getRecruitmentPrizes($gender, $school, $prizes = []) {
        /**
         * get list of children and which prizes they should get for specific year
         */

        $info = [];
        // get list of prizes
        $prizes = $this->getListofRecruitmentPrizes();
        // find out list of children and how many credits they have
        $children  = $this->getChildrenRecruitments($gender, $school);
        foreach ($children as $user_id => $credits) {
            if ($credits > 5) $credits = 5;
            $info[$user_id][] = $prizes[$credits];
        }

        // limit return to $prizes
        foreach ($info as $user => $prize_names) {
            foreach ($prize_names as $idx => $prize) {
                if (! in_array($prize, $prizes)) unset($info[$user][$idx]);
            }
        }
        return $info;
    }

    private function getListofRecruitmentPrizes() {
        /**
         * get list of prizes in system with how many credits is needed for each prize
         */

        $prizes = [];
        $sql = "select * from chidon_credit_prizes where year = :year";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $prizes[$row['credits']] = $row['prize'];
        }
        return $prizes;
    }

    private function getChildrenRecruitments($gender, $school, $limitTo = []) {
        /**
         * finds out which children recruited others since 5782 and how many they recruited
         */

        $children = [];
        $start = 5782;
        $sql = "select u.user_id, count(*) as credits from users u 
                join th_chidon tc on u.user_serial = tc.recruited_by 
                where year >= :start";
        if ($gender == 'm') $sql .= " and u.gender = 'm'";
        if ($gender == 'f') $sql .= " and u.gender = 'f'";
        if ($school > 0) $sql .= " and u.school_id = " . $school;
        $sql .= " group by u.user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['start' => $start]);
//        $stmt->debugDumpParams();
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $children[$row['user_id']] = $row['credits'];
        }
        return $children;
    }

    public function getTestPrizes($gender, $school, $prizes = []) {
        /**
         * get list of prizes children should be receiving for each test & final
         */

        $info = [];
        $sql = "select user_id from th_chidon where year = :year";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            foreach ($prizes as $prize) {
                $info[$row['user_id']][] = $prize;
            }
        }
        return $info;
    }

    public function getChildrenSweaters($gender, $school, $sweaters = []) {
        /**
         * get all children that signed up to chidon with their sweater size
         * need to know size/color/school for personalization
         */

        $info = [];
        $sql = "SELECT 
                    user_id, size, gender, s.school_name
                FROM
                    th_chidon tc
                        JOIN
                    users u USING (user_id)
                        JOIN
                    schools s ON u.school_id = s.school_id
                WHERE
                    year = :year";
        if ($gender == 'm') $sql .= " and u.gender = 'm'";
        if ($gender == 'f') $sql .= " and u.gender = 'f'";
        if ($school > 0) $sql .= " and u.school_id = " . $school;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $details = [];
            $details['color'] = strtolower($row['gender']) == 'm' ? 'blue' : strtolower($row['gender']) == 'f' ? 'burgundy' : '';
            $details['school'] = $row['school_name'];
            $info[$row['user_id']] = $details;
        }
        return $info;
    }

    public function getHQSweaters($gender, $school, $sweaters = []) {

    }

    public function getTripStaffSweaters($gender, $school, $sweaters = []) {

    }

    public function getExtraPurchases($gender, $school, $items = []) {
        /**
         * get list of extra purchases per family
         *
         * @param item specific item to look for - can be 'sweater' or 'celeb box'
         */

        $info = [];
        $sql = "select * from extra_purchases where year = :year";
        if ($item) $sql .= " and item = :item";
        $stmt = $this->db->prepare($sql);
        if ($item) $stmt->execute(['year' => $this->year, 'item' => $item]);
        else $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $info[$row['admin_id']][$row['item']][] = $row;
        }
        return $info;
    }

    public function getTripItems($gender, $school, $items = []) {

    }

    public function getGifts($gender, $school, $gifts = []) {
        /**
         * gets gifts for all children registered end of chidon
         * 1. yarmulka for boys
         * 2. bracelet for girls
         * 3. personalized water bottle for all (blue/pink)
         */

        $info = [];
        $sql = "select * from th_chidon 
                join users u using (user_id) 
                where date_paid > 0 and year = :year";
        if ($gender == 'm') $sql .= " and u.gender = 'm'";
        if ($gender == 'f') $sql .= " and u.gender = 'f'";
        if ($school > 0) $sql .= " and u.school_id = " . $school;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $gift = [];
            $name = $row['name_pref'];
            if (strtolower($row['gender']) == 'm') $gift[] = 'Yarmulka size: ' . $row['yarmulka'];
            else if (strtolower($row['gender']) == 'f') $gift[] = 'Bracelet';
            $gift[] = "Personalized Bottle (" . $name . ")";
            $info[$row['user_id']] = $gift;
        }
        return $info;
    }

    public function getIDCards($gender, $school, $cards = []) {
        /**
         * get all children that need ID card
         */

        $info = [];
        $sql = "select * from th_chidon 
                join users u using (user_id) 
                where date_paid > 0 and year = :year";
        if ($gender == 'm') $sql .= " and u.gender = 'm'";
        if ($gender == 'f') $sql .= " and u.gender = 'f'";
        if ($school > 0) $sql .= " and u.school_id = " . $school;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $info[$row['user_id']] = $row;
        }
        return $info;
    }

    public function getAwards($gender, $school, $awards = []) {
        /**
         * gets award needed for each child
         * based off the highest track saved in db - th_chidon_info
         */

        $info = [];
        $sql = "select * from th_chidon_info 
                join users u using (user_id) 
                where year = :year";
        if ($gender == 'm') $sql .= " and u.gender = 'm'";
        if ($gender == 'f') $sql .= " and u.gender = 'f'";
        if ($school > 0) $sql .= " and u.school_id = " . $school;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $award = '';
            switch ($row['highest_track']) {
                case 'yesod':
                    $award = 'certificate';
                    break;
                case 'yediah':
                    $award = 'plaque';
                    break;
                case 'havonah':
                    $award = 'medal';
                    break;
                case 'iyun':
                    $award = 'medal / trophy';
                    break;
            }
            $info[$row['user_id']] = $award;
        }
        return $info;
    }

    public function getPrizes($gender, $school, $limitTo = []) {
        /**
         * gets the prizes the children chose when signing up
         * db tables = chidon_user_prizes / chidon_prizes
         */

        // get list of prizes in system with prize ids
        $prizes = $this->getChidonPrizes();

        // get prize ids based on prize names
        $ids = [];
        foreach ($limitTo as $prize) {
            $id = array_search($prize, $prizes);
            if ($id !== false) $ids[] = $id;
        }

        $info = [];
        $sql = "SELECT 
                    *
                FROM
                    chidon_user_prizes cup
                        JOIN
                    chidon_prizes cp USING (prize_id)
                        JOIN 
                    users u USING (user_id)
                WHERE
                    cup.year = :year";
        if (count($limitTo)) $sql .= " and cup.prize_id in (" . implode(',', $ids) . ")";
        if ($gender == 'm') $sql .= " and u.gender = 'm'";
        if ($gender == 'f') $sql .= " and u.gender = 'f'";
        if ($school > 0) $sql .= " and u.school_id = " . $school;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $info[$row['user_id']][] = $row;
        }
        return $info;
    }

    private function getChidonPrizes() {
        /**
         * gets list of child prizes with name and ID
         */

        $info = [];
        $sql = "select prize_id, prize_name from chidon_prizes where year = :year";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $info[$row['prize_id']] = $row['prize_name'];
        }
        return $info;
    }

    public function getEventItems() {

    }
}