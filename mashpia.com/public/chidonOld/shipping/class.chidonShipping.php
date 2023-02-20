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
 * each item needs to have item/qty/size/color/name keys
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

        $cat = 'brochures';
        $item = 'brochure';
        $id = $this->getItemID($cat, $item);

        foreach ($rows as $row) {
            $info[$row['user_id']][] = [
                'item'  => $item,
                'size'  => '',
                'color' => '',
                'name'  => '',
                'id'    => $id ,
                'cat'   => $cat
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

        $cat = 'books';
        $item = 'yahadus book';
        $id = $this->getItemID($cat, $item);

        foreach ($rows as $row) {
            $info[$row['user_id']][] = [
                'item'  => $item,
                'size'  => '',
                'color' => '',
                'name'  => '',
                'id'    => $id,
                'cat'   => $cat
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
    public function getRecruitmentPrizes($gender, $school, $limitTo = []) {
        $info = [];
        // get list of prizes
        $prizes = $this->getListofRecruitmentPrizes();
        // get list of id's per prize
        $ids = [];
        $cat = 'recruitment prizes';
        foreach ($prizes as $prize) {
            if ($prize == 'watch') {
                if ($gender == 'm') $gender = 'boys';
                else if ($gender == 'f') $gender = 'girls';
                $id = $this->getItemID($cat, $prize, $gender);
                $ids[$prize][$gender] = $id;
            } else {
                $id = $this->getItemID($cat, $prize);
                $ids[$prize] = $id;
            }
        }

        // find out list of children and how many credits they have
        $children  = $this->getChildrenRecruitments($gender, $school);

        foreach ($children as $user_id => $credits) {
            if ($credits > 5) $credits = 5;
            $prize = $prizes[$credits];
            if (in_array(strtolower($prize), $limitTo)) {
                $color = '';
                if ($prize == 'watch') $color = $gender == 'm' ? 'blue' : $gender == 'f' ? 'burgundy' : '';
                $info[$user_id][] = [
                    'item'  => $prize,
                    'size'  => '',
                    'color' => $color,
                    'name'  => '',
                    'id'    => $color ? $ids[$prize][$color] : $ids[$prize],
                    'cat'   => $cat
                ];
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
            $prizes[$row['credits']] = strtolower($row['prize']);
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
        $colors = [
            '1'   => 'blue',
            '2'   => 'red',
            '3'   => 'purple',
            '4'   => 'green',
            '5'   => 'yellow'
        ];
        $info = [];
        $sql = "select user_id, book from th_chidon where year = :year";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $book = $row['book'];
            $cat = 'test prizes';
            foreach ($prizes as $prize) {
                if (! $book) echo $row['user_id'] . ' - ' . $book . "<br />";
                if (in_array($prize, ['kop cards game', 'leather book mark'])) $id = $this->getItemID($cat, $prize, $colors[$book]);
                else $id = $this->getItemID($cat, $prize);
                $info[$row['user_id']][] = [
                    'item'  => $prize,
                    'size'  => '',
                    'color' => '',
                    'name'  => '',
                    'id'    => $id,
                    'cat'   => $cat
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

        $genders = [
            'M' => 'boys',
            'F' => 'girls'
        ];

        $cat = 'children sweaters';
        $item = 'children sweaters';
        foreach ($rows as $row) {
            $size = $row['size'];
            $id = $this->getItemID($cat, $item, $genders[$row['gender']], $size);
            $info[$row['user_id']][] = [
                'item'  => $item,
                'size'  => $size,
                'color' => $row['gender'] == 'M' ? 'blue' : $row['gender'] == 'F' ? 'burgundy' : '',
                'name'  => '',
                'id'    => $id,
                'cat'   => $cat
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
        if ($method == 'bySchool') $sql .= " and ep.shipping_amount != 10";
        else if ($method == 'byFamily') $sql .= " and ep.shipping_amount = 10";
        if ($items && count($items)) {
            $fields = [];
            foreach ($items as $item) {
                if ($item == 'sweaters') $fields[] = "sweater";
                if ($item == 'celebration boxes') $fields[] = 'celeb_box';
            }
            $sql .= " and item in ('" . implode("','", $fields) . "')";
        }
//        echo $sql;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();

        $cat = 'extra purchases';
        foreach ($rows as $row) {
            if ($row['item'] == 'celeb_box') {
                $size = '';
                $item = 'celebration boxes';
                $id = $this->getItemID($cat, $item);
            } else {
                $size = $row['size'];
                $item = 'sweaters';
                $id = $this->getItemID($cat, $item, ($row['type_of_sweater'] . ' sweater'), $size);
            }
            $purchases[$row['admin_id']][] = [
                'qty'   => intval($row['amount']),
                'item'  => $row['item'] == 'celeb_box' ? 'celebration boxes' : ($row['type_of_sweater'] . ' sweater'),
                'size'  => $size,
                'color' => '',
                'name'  => '',
                'id'    => $id,
                'cat'   => $cat
            ];
        }

        if ($method == 'bySchool') {
            // find out oldest child for each admin ID
            $admin_info = $this->getOldestChild(array_keys($purchases), $gender, $school);
            foreach ($purchases as $admin_id => $more) {
                foreach ($more as $purchase) {
                    if (isset($admin_info[$admin_id])) $info[$admin_info[$admin_id]][] = $purchase;
                }
            }
        } else if ($method == 'byFamily') {
            $info = $purchases;
        }
//        echo "<pre>"; print_r($info); echo "</pre>";
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
        if (! count($gifts)) $gifts = ['yarmulka', 'jewelry', 'personalized bottle'];

        $info = [];
        $sql = "select * from th_chidon tc 
                join users u using (user_id) 
                where tc.date_paid > 0 and tc.year = :year";
        if ($gender == 'm') $sql .= " and u.gender = 'M'";
        if ($gender == 'f') $sql .= " and u.gender = 'F'";
        if ($school > 0) $sql .= " and u.school_id = " . $school;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        $cat = 'gifts';

        foreach ($rows as $row) {
            if ($gifts && count($gifts)) {
                foreach ($gifts as $gift) {
                    $add = false;
                    if ($gift == 'yarmulka' && $row['gender'] == 'M' && ($gender == 'm' || $gender == 0)) $add = true;
                    else if ($gift == 'jewelry' && $row['gender'] == 'F' && ($gender == 'f' || $gender == 0)) $add = true;
                    else if ($gift == 'personalized bottle') $add = true;
                    if ($add) {
                        $color = '';
                        $name = '';
                        if ($gift == 'personalized bottle') {
                            if ($row['gender'] == 'M') {
                                $color = 'blue';
                                $gender = 'boys';
                            } else if ($row['gender'] == 'F') {
                                $color = 'pink';
                                $gender = 'girls';
                            }
                            $name = $row['name_pref'];
                            $id = $this->getItemID($cat, $gift, $gender);
                        } else if ($gift == 'yarmulka') {
                            $id = $this->getItemID($cat, $gift, $row['yarmulka']);
                        } else {
                            $id = $this->getItemID($cat, $gift);
                        }
                        $info[$row['user_id']][] = [
                            'item'  => $gift,
                            'size'  => $gift == 'yarmulka' ? $row['yarmulka'] > 0 ? $row['yarmulka'] : '' : '',
                            'color' => $color,
                            'name'  => $name,
                            'id'    => $id,
                            'cat'   => $cat
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

        $cat = 'ID cards';
        $item = 'ID card';
        $id = $this->getItemID($cat, $item);

        foreach ($rows as $row) {
            $info[$row['user_id']][] = [
                'item'  => 'ID card',
                'size'  => '',
                'color' => '',
                'name'  => '',
                'id'    => $id,
                'cat'   => $cat
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

        $ids = [];
        $cat = 'awards';
        foreach ($rows as $row) {
            $award = $this->getAward($row, $awards);
            if ($award) {
                $award_info = explode('/', $award);
                if (count($award_info) && count($award_info) > 0) {
                    foreach ($award_info as $item) {
                        $ids[] = $this->getItemID($cat, $item);
                    }
                } else {
                    $ids[] = $this->getItemID($cat, $award);
                }
                $info[$row['user_id']][] = [
                    'item'  => $award,
                    'size'  => '',
                    'color' => '',
                    'name'  => '',
                    'id'    => $ids,
                    'cat'   => $cat
                ];
            }
        }
//        echo "</pre>"; print_r($info); echo "</pre>";
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

        $info = [];
        $sql = "SELECT 
                    cup.user_id, cup.he_name, cp.prize_id, cp.prize_name, cp.size, cp.color, tc.ultimate_trip  
                FROM
                    chidon_user_prizes cup
                        JOIN
                    chidon_prizes cp USING (prize_id)
                        JOIN 
                    users u USING (user_id) 
                        JOIN 
                    th_chidon tc ON tc.user_id = u.user_id AND tc.year = cup.year 
                        LEFT JOIN
                    th_chidon_info tci ON u.user_id = tci.user_id AND tc.year = tci.year 
                WHERE
                    cup.year = :year AND tc.date_paid > 0 
                        AND tc.ultimate_trip = 0 AND tci.highest_track != 'yesod'";
//        if (count($limitTo)) $sql .= " and cup.prize_id in (" . implode(',', $ids) . ")";
        if ($gender == 'm') $sql .= " and u.gender = 'M'";
        if ($gender == 'f') $sql .= " and u.gender = 'F'";
        if ($school > 0) $sql .= " and u.school_id = " . $school;
//        echo $sql;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            // make sure it's one of the prizes selected to show
            if (count($limitTo)) {
                $found = false;
                $p = strtolower($row['prize_name']);
                foreach ($limitTo as $toShow) {
                    if (strpos($p, $toShow) !== false) {
                        $found = true;
                        break;
                    }
                }
            }
            if ($found) {
                $id = 'CHI' . $row['prize_id'];
                $info[$row['user_id']][] = [
                    'item' => $row['prize_name'],
                    'size' => $row['size'],
                    'color' => $row['color'],
                    'name' => $row['he_name'],
                    'id' => $id,
                    'cat' => 'prizes'
                ];
            }
        }
//        echo "<pre>"; print_r($info); echo "</pre>";
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

    public function getCategories() {
        $categories = [
            'brochures', 'books', 'guides', 'recruitment prizes', 'test prizes', 'children sweaters', 'extra purchases',
            'gifts', 'ID cards', 'awards', 'prizes'
        ];
        return $categories;
    }

    public function getItems() {
        $items = [
            'brochures'             => ['brochure'],
            'books'                 => ['yahadus book'],
            'guides'                => ['study guides', 'khk guides'],
            'recruitment prizes'    => ['book light', 'rechargeable fan', 'watch', 'neck pillow', 'mini duffle bag'],
            'test prizes'           => ['kop cards game', 'leather book mark', 'drawstring bag', 'shape shifting cube'],
            'children sweaters'     => ['children sweaters'],
            'extra purchases'       => ['celebration boxes', 'sweaters'],
            'gifts'                 => ['yarmulka', 'personalized bottle', 'jewelry'],
            'ID cards'              => ['ID card'],
            'awards'                => ['certificate', 'plaque', 'medal', 'glass trophy', 'khk plaque'],
            'prizes'                => ['remote control helicopter', 'video drone', 'bracelet', 'necklace', 'earrings',
                'chidon T-shirt', 'chidon art set', 'chidon juggling set', 'chidon soccer ball', 'chidon basket ball',
                'chidon football', 'framed rebbe picture', 'chidon cap', 'der rebbe ret tzu kinder',
                'chidon leather sefer hamitzvos', 'chidon leather chitas', 'chidon leather siddur', 'chidon leather tehillim',
                'chidon leather machzor', 'chidon base ball', 'chidon carry-on', 'chidon towel', 'personalized name bracelet',
                'chidon pogo ball', 'the jewish underground volume 1', 'the jewish underground volume 2', 'iron curtain vol 1',
                'iron curtain vol 2', 'escape from europe', 'the rebbe & the mazkir', 'chidon towel', 'chocolate mold', 'backpack', 'waffle maker',
                'chidon cookie cutters', 'reb binyomin kletzker', 'reb shmuel munkis', 'the slavita brothers', 'reb hillel paritcher'],
        ];
        return $items;
    }

    public function getItemID($cat, $item, $deep = '', $deeper = '') {
        // keys to find IDs are category / item / (color/gender) / size - has to match the items array from prev function
        $item_ids = [
            'brochures' => [
                'brochure'  => 'CHI009'
            ],
            'books' => [
                'yahadus book'  => 'CHI010'
            ],
            'guides'    => [
                'study guides'  => 'CHI011',
                'khk guide'     => 'CHI012'
            ],
            'recruitment prizes'    => [
                'book light'    => 'CHI013',
                'rechargeable fan'  => 'CHI014',
                'neck pillow'  => 'CHI017',
                'mini duffle bag'  => 'CHI018',
                'watch'  => [
                    'blue' => 'CHI015',
                    'burgundy' => 'CHI016'
                ]
            ],
            'test prizes'   => [
                'kop cards game'    => [
                    'blue'      => 'CHI019',
                    'red'       => 'CHI020',
                    'purple'    => 'CHI021',
                    'green'     => 'CHI022',
                    'yellow'    => 'CHI023'
                ],
                'leather book mark' => [
                    'blue'      => 'CHI025',
                    'red'       => 'CHI026',
                    'purple'    => 'CHI027',
                    'green'     => 'CHI028',
                    'yellow'    => 'CHI029'
                ],
                'drawstring bag'    => 'CHI024',
                'shape shifting cube'   => 'CHI030'
            ],
            'children sweaters'     => [
                'children sweaters' => [
                    'boys'  => [
                        'children xs'   => 'CHI031',
                        'children s'    => 'CHI032',
                        'children m'    => 'CHI033',
                        'children l'    => 'CHI034',
                        'children xl'   => 'CHI035',
                        'adult xs'      => 'CHI036',
                        'adult s'       => 'CHI037',
                        'adult m'       => 'CHI038',
                        'adult l'       => 'CHI039',
                        'adult xl'      => 'CHI040',
                        'adult xxl'     => 'CHI041',
                        'adult xxxl'    => 'CHI042'
                    ],
                    'girls' => [
                        'children xs'   => 'CHI043',
                        'children s'    => 'CHI044',
                        'children m'    => 'CHI045',
                        'children l'    => 'CHI046',
                        'children xl'   => 'CHI047',
                        'adult xs'      => 'CHI048',
                        'adult s'       => 'CHI049',
                        'adult m'       => 'CHI050',
                        'adult l'       => 'CHI051',
                        'adult xl'      => 'CHI052',
                        'adult xxl'     => 'CHI053',
                        'adult xxxl'    => 'CHI054'
                    ]
                ]
            ],
            'extra purchases'   => [
                'celebration boxes' => 'CHI115',
                'sweaters'  => [
                    'bubby sweater' => [
                        'xs'            => 'CHI095',
                        'small'         => 'CHI091',
                        'medium'        => 'CHI092',
                        'large'         => 'CHI093',
                        'xl'            => 'CHI094'
                    ],
                    'zaidy sweater' => [
                        'xs'        => 'CHI096',
                        'small'     => 'CHI097',
                        'medium'    => 'CHI098',
                        'large'     => 'CHI099',
                        'xl'        => 'CHI100'
                    ],
                    'mother sweater' => [
                        'xs'        => 'CHI102',
                        'small'     => 'CHI103',
                        'medium'    => 'CHI104',
                        'large'     => 'CHI105',
                        'xl'        => 'CHI106'
                    ],
                    'father sweater' => [
                        'xs'        => 'CHI108',
                        'small'     => 'CHI109',
                        'medium'    => 'CHI110',
                        'large'     => 'CHI111',
                        'xl'        => 'CHI112'
                    ]
                ]
            ],
            'gifts' => [
                'yarmulka'  => [
                    '4' => 'CHI116',
                    '5' => 'CHI117',
                    '6' => 'CHI118'
                ],
                'personalized bottle'   => [
                    'boys'  => 'CHI120',
                    'girls' => 'CHI121'
                ],
                'jewelry'  => 'CHI119'
            ],
            'ID cards'  => [
                'ID card'   => 'CHI122'
            ],
            'awards'    => [
                'certificate'   => 'CHI127',
                'plaque'        => 'CHI128',
                'medal'         => 'CHI129',
                'glass trophy'  => 'CHI130',
                'khk plaque'    => 'CHI131',
                'trophy'    => [
                    'gold'      => 'CHI132',
                    'silver'    => 'CHI133',
                    'bronze'    => 'CHI134'
                ]
            ],
            'prizes'    => [
                'remote control helicopter' => 'CHI135',
                'video drone'   => 'CHI136',
                'bracelet'  => 'CHI137',
                'necklace'  => 'CHI138',
                'earrings'  => 'CHI139',
                'chidon T-shirt'    => [
                    'boys'  => [
                        'children s'    => 'CHI140',
                        'children m'    => 'CHI141',
                        'children l'    => 'CHI142',
                        'children xl'   => 'CHI143',
                        'adult s'       => 'CHI144',
                        'adult m'       => 'CHI145',
                        'adult l'       => 'CHI146'
                    ],
                    'girls' => [
                        'children s'    => 'CHI147',
                        'children m'    => 'CHI148',
                        'children l'    => 'CHI149',
                        'children xl'   => 'CHI150',
                        'adult s'       => 'CHI151',
                        'adult m'       => 'CHI152',
                        'adult l'       => 'CHI153'
                    ]
                ],
                'chidon art set'    => 'CHI154',
                'chidon juggling set'   => 'CHI155',
                'chidon soccer ball'    => 'CHI156',
                'chidon basket ball'    => 'CHI157',
                'chidon football'   => 'CHI158',
                'framed rebbe picture' => 'CHI159',
                'chidon cap'    => [
                    'boys'  => 'CHI160',
                    'girls' => 'CHI161'
                ],
                'der rebbe ret tzu kinder'  => 'CHI162',
                'chidon leather sefer hamitzvos'    => [
                    'boys'  => 'CHI164',
                    'girls' => 'CHI163'
                ],
                'chidon leather chitas' => [
                    'boys'  => 'CHI165',
                    'girls' => 'CHI166'
                ],
                'chidon leather siddur' => [
                    'boys'  => 'CHI167',
                    'girls' => 'CHI168'
                ],
                'chidon leather tehillim'   => [
                    'boys'  => 'CHI169',
                    'girls' => 'CHI170'
                ],
                'chidon leather machzor'    => [
                    'boys'  => 'CHI172',
                    'girls' => 'CHI171'
                ],
                'chidon baseball'   => 'CHI173',
                'chidon carry-on'   => 'CHI174',
                'personalized name bracelet'    => 'CHI175',
                'chidon pogo ball'  => 'CHI176',
                'the jewish underground vol 1'  => 'CHI177',
                'the jewish underground vol 2'  => 'CHI178',
                'iron curtain vol 1'    => 'CHI179',
                'iron curtain vol 2'    => 'CHI180',
                'escape from europe'    => 'CHI181',
                'the rebbe and the mazkir'  => 'CHI182',
                'chidon towel'  => [
                    'boys'  => 'CHI183',
                    'girls' => 'CHI184'
                ],
                'chocolate mold'    => 'CHI185',
                'backpack'  => [
                    'boys'  => 'CHI187',
                    'girls' => 'CHI186'
                ],
                'waffle maker'  => 'CHI188',
                'chidon cookie cutters' => 'CHI189',
                'reb binyomin kletzker' => 'CHI190',
                'reb shmuel munkes' => 'CHI191',
                'the slavita brothers'  => 'CHI192',
                'reb hillel paritcher'  => 'CHI193'
            ]
        ];

        if (! empty($deeper)) return $item_ids[$cat][$item][$deep][$deeper];
        else if (! empty($deep)) return $item_ids[$cat][$item][$deep];
        else return $item_ids[$cat][$item];
    }
}