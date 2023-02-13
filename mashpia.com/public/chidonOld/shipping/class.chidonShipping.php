<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

/**
 * Class ChidonShipping
 *
 * list of functions needed for figuring out what to ship
 * all functions need to return an array or items
 * each item needs to have item/size/color/name keys
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
            $info[$row['user_id']][] = [
                'item'  => $brochure,
                'size'  => '',
                'color' => '',
                'name'  => ''
            ];
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
            $info[$row['user_id']][] = [
                'item'  => 'yahadus book',
                'size'  => '',
                'color' => '',
                'name'  => ''
            ];
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
            $info[$user_id][] = [
                'item'  => $prizes[$credits],
                'size'  => '',
                'color' => '',
                'name'  => ''
            ];
        }

        // limit return to $prizes
        foreach ($info as $user => $prize_names) {
            foreach ($prize_names as $idx => $prize) {
                if (! in_array($prize['item'], $prizes)) unset($info[$user][$idx]);
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
                $info[$row['user_id']][] = [
                    'item'  => $prize,
                    'size'  => '',
                    'color' => '',
                    'name'  => ''
                ];
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
            $info[$row['user_id']][] = [
                'item'  => 'sweater',
                'size'  => $row['size'],
                'color' => $row['gender'] == 'M' ? 'blue' : $row['gender'] == 'F' ? 'burgundy' : '',
                'name'  => ''
            ];
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
            $purchases[$row['admin_id']][] = [
                'item'  => $row['item'] == 'celeb_box' ? 'celebration box' : 'sweater',
                'size'  => $row['size'],
                'color' => $row['color'],
                'name'  => $row['']
            ];
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
            if ($gifts && count($gifts)) {
                foreach ($gifts as $gift) {
                    $add = false;
                    if ($gift == 'yarmulka' && $row['gender'] == 'M' && ($gender == 'm' || $gender == 0)) $add = true;
                    else if ($gift == 'bracelet' && $row['gender'] == 'F' && ($gender == 'f' || $gender == 0)) $add = true;
                    else if ($gift == 'personalized bottle') $add = true;
                    if ($add) {
                        $color = '';
                        $name = '';
                        if ($gift == 'personalized bottle') {
                            if ($row['gender'] == 'M') $color = 'blue';
                            else if ($row['gender'] == 'F') $color = 'pink';
                            $name = $row['name_pref'];
                        }
                        $info[$row['user_id']][] = [
                            'item'  => $gift,
                            'size'  => $row['yarmulka'] > 0 ? $row['yarmulka'] : '',
                            'color' => $color,
                            'name'  => $name
                        ];
                    }
                }
            }
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
            $info[$row['user_id']][] = [
                'item'  => 'ID card',
                'size'  => '',
                'color' => '',
                'name'  => ''
            ];
        }
        return $info;
    }

    /**
     * gets award needed for each child
     * awards are determined based off final
     *
     * @param $gender
     * @param $school
     * @param $awards
     * @return array
     */
    public function getAwards($gender, $school, $awards = []) {
        $info = [];
        $sql = "select *, tcf.khk as khk_final from th_chidon_finals tcf 
                join th_chidon tc using (user_id) 
                join users u using (user_id) 
                where tcf.year = :year";
        if ($gender == 'm') $sql .= " and u.gender = 'M'";
        if ($gender == 'f') $sql .= " and u.gender = 'F";
        if ($school > 0) $sql .= " and u.school_id = " . $school;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $award = $this->getAward($row, $awards);
            if ($award) {
                $info[$row['user_id']][] = [
                    'item' => $award,
                    'size' => '',
                    'color' => '',
                    'name' => ''
                ];
            }
        }
        return $info;
    }

    /**
     * @param $child
     * @return string
     */
    private function getAward($child, $limitTo) {
        $tracks = [
            1   => 'yesod',
            2   => 'yediah',
            3   => 'havonah',
            4   => 'iyun'
        ];
        $finals = [
            'yesod'     => 20,
            'yediah'    => 40,
            'havonah'   => 60,
            'iyun'      => 80,
            'khk'       => 200
        ];
        $needed = [
            'yesod'     => 60,
            'yediah'    => 70,
            'havonah'   => 80,
            'iyun'      => 90,
            'khk'       => 140
        ];
        $awards = [
            'yesod'     => 'certificate',
            'yediah'    => 'plaque',
            'havonah'   => 'medal / plaque',
            'iyun'      => 'medal / plaque / glass trophy',
            'khk'       => 'medal / plaque / khk trophy'
        ];

        $ct = new ChidonTests();
        $highest_track = $ct->getHighestTrackPassed($child)['highest_track'];
        // find out if award is same as before final or not
        $award = false;
        $key = array_search($highest_track, $tracks);
        if ($key !== false) {
            // go down from key to find where the child is holding
            $score = 0;
            for ($i = 1; $i <= $key; $i++) {
                $level = 'level_' . $i;
                if (isset($child[$level])) {
                    $score += $child[$level];
                }
            }
            for ($i = 1; $i <= $key; $i++) {
                $divide_by = $finals[$tracks[$i]];
                $final_score = number_format(($score / $divide_by) * 100, 2);
                if ($final_score >= $needed[$tracks[$i]]) {
                    $award = $tracks[$i];
                }
            }
            // check for khk trophy
            if (intval($child['khk_reg']) && intval($child['khk_final']) >= $needed['khk']) {
                if (intval($child['ultimate_trip']) == 0) $award = 'khk'; // only show khk trophy if NOT going on ultimate trip
                else if (intval($child['ultimate_trip']) == 1) $award = '';
            }
        }
        if ($award) {
            $show = false;
            switch ($award) {
                case 'yesod':
                    if (in_array('certificate', $limitTo)) $show = true;
                    break;
                case 'yediah':
                    if (in_array('plaque', $limitTo)) $show = true;
                    break;
                case 'havonah':
                    if (in_array('medal', $limitTo)) $show = true;
                case 'iyun':
                    if (in_array('glass trophy', $limitTo)) $show = true;
                    break;
                case 'khk':
                    if (in_array('khk trophy', $limitTo)) $show = true;
                    break;
            }
            if ($show) return $awards[$award];
        }
        return '';
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
                    cup.user_id, cup.he_name, cp.prize_name, cp.size, cp.color, th.ultimate_trip 
                FROM
                    chidon_user_prizes cup
                        JOIN
                    chidon_prizes cp USING (prize_id)
                        JOIN 
                    users u USING (user_id) 
                        JOIN 
                    th_chidon tc using (user_id, year) 
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
            if (intval($row['ultimate_trip']) == 1) continue; // ultimate trip kids do NOT get prizes
            $info[$row['user_id']][] = [
                'item'  => $row['prize_name'],
                'size'  => $row['size'],
                'color' => $row['color'],
                'name'  => $row['he_name']
            ];
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