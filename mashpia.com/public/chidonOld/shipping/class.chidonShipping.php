<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

/**
 * Class ChidonShipping
 *
 * list of functions needed for figuring out what to ship
 * all functions return an array with the user ID as the key and the info in the value
 */

class ChidonShipping
{
    private $db, $year, $schools, $grades, $users;

    public function __construct() {
        global $MASHPIA_DB;
        $this->db = $MASHPIA_DB;
        $this->year = GlobalSettings::getChidonYear();
        $this->schools = [];
        $this->grades = [];
        $this->users = [];
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

    /**
     * get list of user IDs that need to have brochures sent out
     *
     * QUALIFICATIONS:
     * all children signed up to TH between grades 4-8
     * (or 3-7) if doing it before end of yr
     *
     * @param $gender
     * @param $school
     * @param $brochures
     * @param $early
     * @return array - all user info from users db with the user id as the key
     */
    public function getBrochures($gender, $school, $brochures = [], $early = false) {
        $info = [];
        $in_grades = "('4', '5', '6', '7', '8')";
        if ($early) $in_grades = "('3', '4', '5', '6', '7')";
        $sql = "SELECT user_id FROM users u 
                JOIN classes c ON c.class_id = u.class_id 
                WHERE c.class_grade in $in_grades 
                AND u.user_registered > 0";
        if ($gender == 'm') $sql .= " and u.gender = 'M'";
        if ($gender == 'f') $sql .= " and u.gender = 'F'";
        if ($school > 0) $sql .= " and u.school_id = " . $school;

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $brochure = 'brochure';
            if ($brochures && count($brochures)) $brochure = implode(',', $brochures);
            $info[$row['user_id']] = $brochure;
        }
        return $info;
    }

    /**
     * get all books purchased this yr
     *
     * @param $gender
     * @param $school
     * @param $books
     * @return array - all book info from db with user IDs as the key
     * TODO - DBL CHECK ABOUT BOOKS BEING BOUGHT
     */
    public function getBooks($gender, $school, $books = []) {
        $info = [];
        $sql = "SELECT * FROM yahadus_book_purchases 
                JOIN users u USING (user_id) 
                WHERE year = :year";
        if ($gender == 'm') $sql .= " and u.gender = 'M'";
        if ($gender == 'f') $sql .= " and u.gender = 'F'";
        if ($school > 0) $sql .= " and u.school_id = " . $school;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $info[$row['user_id']] = $row;
        }
        return $info;
    }

    /**
     * @param $gender
     * @param $school
     * @param $guides
     * @return void
     */
    public function getGuides($gender, $school, $guides = []) {

    }

    /**
     * @param $gender
     * @param $school
     * @param $prizes
     * @return array - list prizes to give per user ID
     */
    public function getRecruitmentPrizes($gender, $school, $prizes = []) {
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

    /**
     * get list of prizes in system with how many credits is needed for each prize
     * @return array
     */
    private function getListofRecruitmentPrizes() {
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

    /**
     * finds out which children recruited others since 5782 and how many they recruited
     *
     * @param $gender
     * @param $school
     * @return array
     */
    private function getChildrenRecruitments($gender, $school) {
        $children = [];
        $start = 5782;
        $sql = "select u.user_id, count(*) as credits from users u 
                join th_chidon tc on u.user_serial = tc.recruited_by 
                where year >= :start";
        if ($gender == 'm') $sql .= " and u.gender = 'M'";
        if ($gender == 'f') $sql .= " and u.gender = 'F'";
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

    /**
     * get list of prizes children should be receiving for each test & final
     *
     * @param $gender
     * @param $school
     * @param $prizes
     * @return array
     */
    public function getTestPrizes($gender, $school, $prizes = []) {
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

    /**
     * get all children that signed up to chidon with their sweater size
     * need to know size/color/school for personalization
     *
     * @param $gender
     * @param $school
     * @param $sweaters
     * @return array
     */
    public function getChildrenSweaters($gender, $school, $sweaters = []) {
        $info = [];
        $sql = "SELECT 
                    user_id, size, gender  
                FROM
                    th_chidon tc
                        JOIN
                    users u USING (user_id)
                        JOIN
                    schools s ON u.school_id = s.school_id
                WHERE
                    year = :year";
        if ($gender == 'm') $sql .= " and u.gender = 'M'";
        if ($gender == 'f') $sql .= " and u.gender = 'F'";
        if ($school > 0) $sql .= " and u.school_id = " . $school;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $details = [];
            $details['item'] = 'sweater';
            $details['size'] = $row['size'];
            $details['color'] = $row['gender'] == 'M' ? 'blue' : $row['gender'] == 'F' ? 'burgundy' : '';
            $info[$row['user_id']][] = $details;
        }
        return $info;
    }

    public function getHQSweaters($gender, $school, $sweaters = []) {

    }

    public function getTripStaffSweaters($gender, $school, $sweaters = []) {

    }

    /**
     * @param $gender
     * @param $school
     * @param $items
     * @param $method
     * @return array
     */
    public function getExtraPurchases($gender, $school, $items = [], $method = 'bySchool') {
        $info = [];
        $purchases = [];
        $sql = "select * from extra_purchases ep 
                left join purchase_addresses pa using (purchase_id) 
                where ep.year = :year";
        if ($items && count($items)) {
            $tmp = [];
            foreach ($items as $item) {
                if ($item == 'sweaters') $tmp[] = "sweater";
                if ($item == 'celebration boxes') $tmp[] = 'celeb_box';
            }
            $sql .= " and item in ('" . implode(',', $tmp) . "')";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $purchases[$row['admin_id']][] = $row;
        }

        if ($method == 'bySchool') {
            // find out oldest child for each admin ID
            $admin_info = $this->getOldestChild(array_keys($purchases), $gender, $school);
            foreach ($purchases as $admin_id => $more) {
                foreach ($more as $purchase) {
                    $info[$admin_info[$admin_id]][] = $purchase;
                }
            }
        } else if ($method == 'byFamily') {
            $info = $purchases;
        }

        return $info;
    }

    /**
     * @param array $admin_ids
     * @param string - limit to gender
     * @param string - limit to school
     * @return array - list of admin IDs with oldest child's user ID
     */
    private function getOldestChild(array $admin_ids, $gender, $school) {
        // find oldest child in chidon
        $sql = "select user_id, dob from users u 
                join admin_auths aa on aa.id = u.user_id 
                join th_chidon tc using (user_id)
                where admin_id = :id and auth = 'user'";
        if ($gender == 'm') $sql .= " and u.gender = 'M'";
        if ($gender == 'f') $sql .= " and u.gender = 'F'";
        if ($school > 0) $sql .= " and u.school_id = " . $school;
        $stmt = $this->db->prepare($sql);

        // find oldest child registered in chayolei
        $sql2 = "select user_id, dob from users u 
                 join admin_auths aa on aa.id = u.user_id 
                 where admin_id = :id and auth = 'user' 
                 and u.user_registered > 0";
        if ($gender == 'm') $sql2 .= " and u.gender = 'M'";
        if ($gender == 'f') $sql2 .= " and u.gender = 'F'";
        if ($school > 0) $sql2 .= " and u.school_id = " . $school;
        $stmt2 = $this->db->prepare($sql2);

        $admin_info = [];
        foreach ($admin_ids as $id) {
            $stmt->execute(['id' => $id]);
            $rows = $stmt->fetchAll();

            if (! ($rows && count($rows))) {
                $stmt2->execute(['id' => $id]);
                $rows = $stmt2->fetchAll();
            }

            if ($rows && count($rows)) $admin_info[$id] = $this->getOldest($rows);
            else $admin_info[$id] = 0;
        }
        return $admin_info;
    }

    /**
     * @param $children
     * @return string - child's user ID
     */
    private function getOldest($children) {
        $child = [];
        foreach ($children as $row) {
            if (empty($child)) $child = $row;
            else {
                $d1 = new DateTime($child['dob']);
                $d2 = new DateTime($row['dob']);
                if ($d2 > $d1) $child = $row;
            }
        }
        return $child['user_id'];
    }

    public function getTripItems($gender, $school, $items = []) {

    }

    /**
     * gets gifts for all children registered end of chidon
     * 1. yarmulka for boys
     * 2. bracelet for girls
     * 3. personalized water bottle for all (blue/pink)
     *
     * @param $gender - limits to specific gender
     * @param $school - limits to specific school
     * @param $gifts - limits to certain gifts
     * @return array - list of user IDs with the gifts they should get
     */
    public function getGifts($gender, $school, $gifts = []) {
        if (! count($gifts)) $gifts = ['yarmulka', 'bracelet', 'personalized bottle'];

        $info = [];
        $sql = "select * from th_chidon 
                join users u using (user_id) 
                where date_paid > 0 and year = :year";
        if ($gender == 'm') $sql .= " and u.gender = 'M'";
        if ($gender == 'f') $sql .= " and u.gender = 'F'";
        if ($school > 0) $sql .= " and u.school_id = " . $school;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $to_give = [];
            $name = $row['name_pref'];
            if ($gifts && count($gifts)) {
                foreach ($gifts as $idx => $gift) {
                    switch ($gift) {
                        case 'yarmulka':
                            if ($row['gender'] == 'M' && ($gender == 'm' || $gender == 0)) $to_give[$idx]['item'] = 'Yarmulka Size: ' . $row['yarmulka'];
                            break;
                        case 'bracelet':
                            if ($row['gender'] == 'F' && ($gender == 'f' || $gender == 0)) $to_give[$idx]['item'] = 'Bracelet';
                            break;
                        case 'personalized bottle':
                            $to_give[$idx]['item'] = "Personalized Bottle";
                            $to_give[$idx]['name_pref'] = ucwords(trim($name));
                            break;
                    }
                }
            }
            $info[$row['user_id']] = $to_give;
        }
        return $info;
    }

    public function getIDCards($gender, $school, $cards = []) {
        $info = [];
        $sql = "select * from th_chidon 
                join users u using (user_id) 
                where date_paid > 0 and year = :year";
        if ($gender == 'm') $sql .= " and u.gender = 'M'";
        if ($gender == 'f') $sql .= " and u.gender = 'F'";
        if ($school > 0) $sql .= " and u.school_id = " . $school;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $info[$row['user_id']] = 'ID card';
        }
        return $info;
    }

    /**
     * gets award needed for each child
     * based off the highest track saved in db - th_chidon_info
     *
     * @param $gender
     * @param $school
     * @param $awards
     * @return array
     */
    public function getAwards($gender, $school, $awards = []) {
        $info = [];
        $sql = "select * from th_chidon_info 
                join users u using (user_id) 
                where year = :year";
        if ($gender == 'm') $sql .= " and u.gender = 'M'";
        if ($gender == 'f') $sql .= " and u.gender = 'F";
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

    /**
     * gets the prizes the children chose when signing up
     * db tables = chidon_user_prizes / chidon_prizes
     *
     * @param $gender
     * @param $school
     * @param $limitTo
     * @return array
     */
    public function getPrizes($gender, $school, $limitTo = []) {
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
                    cup.user_id, cup.he_name as name_pref, cp.prize_name as item, cp.size, cp.color
                FROM
                    chidon_user_prizes cup
                        JOIN
                    chidon_prizes cp USING (prize_id)
                        JOIN 
                    users u USING (user_id)
                WHERE
                    cup.year = :year";
        if (count($limitTo)) $sql .= " and cup.prize_id in (" . implode(',', $ids) . ")";
        if ($gender == 'm') $sql .= " and u.gender = 'M'";
        if ($gender == 'f') $sql .= " and u.gender = 'F'";
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