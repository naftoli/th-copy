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
        $id = 0;
//        $id = $this->getItemID([$cat][$item]);

        foreach ($rows as $row) {
            $info[$row['user_id']][] = [
                'item'  => $item,
                'size'  => '',
                'color' => '',
                'name'  => '',
                'id'    => $id
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

        $cat = 'yahadus books';
        $item = 'yahadus book';
        $id = 0;
//        $id = $this->getItemID([$cat][$item]);

        foreach ($rows as $row) {
            $info[$row['user_id']][] = [
                'item'  => 'yahadus book',
                'size'  => '',
                'color' => '',
                'name'  => '',
                'id'    => $id
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
                if ($gender == 'm') $color = 'blue';
                else if ($gender == 'f') $color = 'burgundy';
                $id = 0;
//                $id = $this->getItemID([$cat][$prize][$color]);
//                $ids[$prize][$color] = $id;
            } else {
                $id = 0;
//                $id = $this->getItemID([$cat][$prize]);
//                $ids[$prize] = $id;
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
//                    'id'    => $color ? $ids[$prize][$color] : $ids[$prize]
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
                $id = 0;
//                $id = $this->getItemID([$cat][$prize][$colors[$book]]);
                $info[$row['user_id']][] = [
                    'item'  => $prize,
                    'size'  => '',
                    'color' => '',
                    'name'  => '',
                    'id'    => $id
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
        $item = ['children sweaters'];
        foreach ($rows as $row) {
            $size = $row['size'];
            $id = 0;
//            $id = $this->getItemID([$cat][$item][$genders[$row['gender']]][$size]);
            $info[$row['user_id']][] = [
                'item'  => $item,
                'size'  => $size,
                'color' => $row['gender'] == 'M' ? 'blue' : $row['gender'] == 'F' ? 'burgundy' : '',
                'name'  => '',
                'id'    => $id
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
            $sql .= " and item in ('" . implode("','", $tmp) . "')";
        }
//        echo $sql;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();

        $cat = 'extra purchases';
        foreach ($rows as $row) {
            if ($row['item'] == 'celeb_box') {
                $size = '';
                $id = 0;
//                $id = $this->getItemID([$cat]['celebration boxes']);
            } else {
                $size = $row['size'];
                $id = 0;
//                $id = $this->getItemID([$cat]['sweaters'][$row['type_of_sweater'] . ' sweater'][$size]);
            }
            $purchases[$row['admin_id']][] = [
                'qty'   => intval($row['amount']),
                'item'  => $row['item'] == 'celeb_box' ? 'celebration box' : 'sweater',
                'size'  => $size,
                'color' => '',
                'name'  => '',
                'id'    => $id
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
        if (! count($gifts)) $gifts = ['yarmulka', 'bracelet', 'personalized bottle'];

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
                    else if ($gift == 'bracelet' && $row['gender'] == 'F' && ($gender == 'f' || $gender == 0)) $add = true;
                    else if ($gift == 'personalized bottle') $add = true;
                    if ($add) {
                        $color = '';
                        $name = '';
                        if ($gift == 'personalized bottle') {
                            if ($row['gender'] == 'M') $color = 'blue';
                            else if ($row['gender'] == 'F') $color = 'pink';
                            $name = $row['name_pref'];
                            $id = 0;
//                            $id = $this->getItemID([$cat][$gift][$color]);
                        } else if ($gift == 'yarmulka') {
                            $id = 0;
//                            $id = $this->getItemID([$cat][$gift][$row['yarmulka']]);
                        } else {
                            $id = 0;
//                            $id = $this->getItemID([$cat][$gift]);
                        }
                        $info[$row['user_id']][] = [
                            'item'  => $gift,
                            'size'  => $row['yarmulka'] > 0 ? $row['yarmulka'] : '',
                            'color' => $color,
                            'name'  => $name,
                            'id'    => $id
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
        $id = 0;
//        $id = $this->getItemID([$cat][$item]);

        foreach ($rows as $row) {
            $info[$row['user_id']][] = [
                'item'  => 'ID card',
                'size'  => '',
                'color' => '',
                'name'  => '',
                'id'    => $id
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
//                        $ids[] = $this->getItemID([$cat][$item]);
                    }
                } else {
//                    $ids[] = $this->getItemID([$cat][$award]);
                }
                $info[$row['user_id']][] = [
                    'item'  => $award,
                    'size'  => '',
                    'color' => '',
                    'name'  => '',
                    'id'    => $ids
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

        // get prize ids based on prize names
        $ids = [];
        foreach ($limitTo as $prize) {
            $id = array_search(ucwords($prize), $prizes);
            if ($id !== false) $ids[] = $id;
        }

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
                    th_chidon tc on tc.user_id = u.user_id and tc.year = cup.year 
                WHERE
                    cup.year = :year";
        if (count($limitTo)) $sql .= " and cup.prize_id in (" . implode(',', $ids) . ")";
        if ($gender == 'm') $sql .= " and u.gender = 'M'";
        if ($gender == 'f') $sql .= " and u.gender = 'F'";
        if ($school > 0) $sql .= " and u.school_id = " . $school;
//        echo $sql;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            if (intval($row['ultimate_trip']) == 1) continue; // ultimate trip kids do NOT get prizes
            $id = 'CHI' . $row['prize_id'];
            $info[$row['user_id']][] = [
                'item'  => $row['prize_name'],
                'size'  => $row['size'],
                'color' => $row['color'],
                'name'  => $row['he_name'],
                'id'    => $id,
                'prize_id'  => $row['prize_id']
            ];
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
            'recruitment prizes'    => ['book light', 'rechargeable fan', 'neck pillow', 'mini duffle bag', 'watch'],
            'test prizes'           => ['kop cards game', 'leather book mark', 'drawstring bag', 'shape shifting cube'],
            'children sweaters'     => ['children sweaters'],
            'extra purchases'       => ['celebration boxes', 'sweaters'],
            'gifts'                 => ['yarmulka', 'personalized bottle', 'bracelet'],
            'ID cards'              => ['ID card'],
            'awards'                => ['certificate', 'plaque', 'medal', 'glass trophy', 'khk plaque'],
            'prizes'                => ['remote control helicopter', 'video drone', 'bracelet', 'necklace', 'earrings',
                'chidon T-shirt', 'chidon art set', 'chidon juggling set', 'chidon soccer ball', 'chidon basket ball',
                'chidon football', 'framed rebbe picture 5782', 'chidon cap', 'der rebbe ret tzu kinder',
                'chidon leather sefer hamitzvos', 'chidon leather chitas', 'chidon leather siddur', 'chidon leather tehillim',
                'chidon leather machzor', 'chidon baseball', 'chidon carry-on', 'personalized name bracelet', 'chidon pogo ball',
                'the jewish underground vol 1', 'the jewish underground vol 2', 'iron curtain vol 1', 'iron curtain vol 2',
                'escape from europe', 'the Rebbe and the mazkir', 'chidon towel', 'chocolate mold', 'backpack', 'waffle maker',
                'chidon cookie cutters', 'reb binyomin kletzker', 'reb shmuel munkes', 'the slavita brothers', 'reb hillel paritcher'],
        ];
        return $items;
    }

    public function getItemID($info) {
        // keys to find IDs are category / item / (color/gender) / size - has to match the items array from prev function
        $item_ids = [
            'brochures' => [
                'brochure'  => [
                    'id'    => 'CHI009'
                ]
            ],
            'books' => [
                'yahadus book'  => [
                    'id'    => 'CHI010'
                ]
            ],
            'guides'    => [
                'study guides'  => [
                    'id'    => 'CHI011'
                ],
                'khk guide' => [
                    'id'    => 'CHI012'
                ]
            ],
            'recruitment prizes'    => [
                'book light'    => [
                    'id'    => 'CHI013'
                ],
                'rechargeable fan'  => [
                    'id'    => 'CHI014'
                ],
                'neck pillow'  => [
                    'id'    => 'CHI017'
                ],
                'mini duffle bag'  => [
                    'id'    => 'CHI018'
                ],
                'watch'  => [
                    'blue' => [
                        'id'    => 'CHI015'
                    ],
                    'burgundy' => [
                        'id'    => 'CHI016'
                    ]
                ]
            ],
            'test prizes'   => [
                'kop cards game'    => [
                    'blue'      => [
                        'id'    => 'CHI019'
                    ],
                    'red'       => [
                        'id'    => 'CHI020'
                    ],
                    'purple'    => [
                        'id'    => 'CHI021'
                    ],
                    'green'     => [
                        'id'    => 'CHI022'
                    ],
                    'yellow'    => [
                        'id'    => 'CHI023'
                    ]
                ],
                'leather book mark' => [
                    'blue'      => [
                        'id'    => 'CHI025'
                    ],
                    'red'       => [
                        'id'    => 'CHI026'
                    ],
                    'purple'    => [
                        'id'    => 'CHI027'
                    ],
                    'green'     => [
                        'id'    => 'CHI028'
                    ],
                    'yellow'    => [
                        'id'    => 'CHI029'
                    ]
                ],
                'drawstring bag'    => [
                    'id'        => 'CHI024'
                ],
                'shape shifting cube'   => [
                    'id'        => 'CHI030'
                ]
            ],
            'children sweaters'     => [
                'children sweaters' => [
                    'boys'  => [
                        'children xs'   => [
                            'id'    => 'CHI031'
                        ],
                        'children s'    => [
                            'id'    => 'CHI032'
                        ],
                        'children m'    => [
                            'id'    => 'CHI033'
                        ],
                        'children l'    => [
                            'id'    => 'CHI034'
                        ],
                        'children xl'   => [
                            'id'    => 'CHI035'
                        ],
                        'adult xs'  => [
                            'id'    => 'CHI036'
                        ],
                        'adult s'   => [
                            'id'    => 'CHI037'
                        ],
                        'adult m'   => [
                            'id'    => 'CHI038'
                        ],
                        'adult l'   => [
                            'id'    => 'CHI039'
                        ],
                        'adult xl'  => [
                            'id'    => 'CHI040'
                        ],
                        'adult xxl' => [
                            'id'    => 'CHI041'
                        ],
                        'adult xxxl'    => [
                            'id'    => 'CHI042'
                        ]
                    ],
                    'girls' => [
                        'children xs'   => [
                            'id'    => 'CHI043'
                        ],
                        'children s'    => [
                            'id'    => 'CHI044'
                        ],
                        'children m'    => [
                            'id'    => 'CHI045'
                        ],
                        'children l'    => [
                            'id'    => 'CHI046'
                        ],
                        'children xl'   => [
                            'id'    => 'CHI047'
                        ],
                        'adult xs'  => [
                            'id'    => 'CHI048'
                        ],
                        'adult s'   => [
                            'id'    => 'CHI049'
                        ],
                        'adult m'   => [
                            'id'    => 'CHI050'
                        ],
                        'adult l'   => [
                            'id'    => 'CHI051'
                        ],
                        'adult xl'  => [
                            'id'    => 'CHI052'
                        ],
                        'adult xxl' => [
                            'id'    => 'CHI053'
                        ],
                        'adult xxxl'    => [
                            'id'    => 'CHI054'
                        ]
                    ]
                ]
            ],
            'extra purchases'   => [
                'celebration boxes' => [
                    'id'    => 'CHI115'
                ],
                'sweaters'  => [
                    'bubby sweater'    => [
                        'adult s'   => [
                            'id'    => 'CHI091'
                        ],
                        'adult m'   => [
                            'id'    => 'CHI092'
                        ],
                        'adult l'   => [
                            'id'    => 'CHI093'
                        ],
                        'adult xl'  => [
                            'id'    => 'CHI094'
                        ],
                        'adult xxl' => [
                            'id'    => 'CHI095'
                        ],
                        'adult xxxl'    => [
                            'id'    => 'CHI096'
                        ]
                    ],
                    'zaidy sweater' => [
                        'adult s'   => [
                            'id'    => 'CHI097'
                        ],
                        'adult m'   => [
                            'id'    => 'CHI098'
                        ],
                        'adult l'   => [
                            'id'    => 'CHI099'
                        ],
                        'adult xl'  => [
                            'id'    => 'CHI100'
                        ],
                        'adult xxl' => [
                            'id'    => 'CHI101'
                        ],
                        'adult xxxl'    => [
                            'id'    => 'CHI102'
                        ]
                    ],
                    'mother sweater' => [
                        'adult s'   => [
                            'id'    => 'CHI103'
                        ],
                        'adult m'   => [
                            'id'    => 'CHI104'
                        ],
                        'adult l'   => [
                            'id'    => 'CHI105'
                        ],
                        'adult xl'  => [
                            'id'    => 'CHI106'
                        ],
                        'adult xxl' => [
                            'id'    => 'CHI107'
                        ],
                        'adult xxxl'    => [
                            'id'    => 'CHI108'
                        ]
                    ],
                    'father sweater' => [
                        'adult s'   => [
                            'id'    => 'CHI109'
                        ],
                        'adult m'   => [
                            'id'    => 'CHI110'
                        ],
                        'adult l'   => [
                            'id'    => 'CHI111'
                        ],
                        'adult xl'  => [
                            'id'    => 'CHI112'
                        ],
                        'adult xxl' => [
                            'id'    => 'CHI113'
                        ],
                        'adult xxxl'    => [
                            'id'    => 'CHI114'
                        ]
                    ]
                ]
            ],
            'gifts' => [
                'yarmulka'  => [
                    '4' => [
                        'id'    => 'CHI116'
                    ],
                    '5' => [
                        'id'    => 'CHI117'
                    ],
                    '6' => [
                        'id'    => 'CHI118'
                    ]
                ],
                'personalized bottle'   => [
                    'boys'  => [
                        'id'    => 'CHI120'
                    ],
                    'girls' => [
                        'id'    => 'CHI121'
                    ]
                ],
                'bracelet'  => [
                    'id'    => 'CHI119'
                ]
            ],
            'ID cards'  => [
                'ID card'   => [
                    'id'    => 'CHI122'
                ]
            ],
            'awards'    => [
                'certificate'   => [
                    'id'    => 'CHI127'
                ],
                'plaque'    => [
                    'id'    => 'CHI128'
                ],
                'medal'     => [
                    'id'    => 'CHI129'
                ],
                'glass trophy'  => [
                    'id'    => 'CHI130'
                ],
                'khk plaque'    => [
                    'id'    => 'CHI131'
                ],
                'trophy'    => [
                    'gold'  => [
                        'id'    => 'CHI132'
                    ],
                    'silver'    => [
                        'id'    => 'CHI133'
                    ],
                    'bronze'    => [
                        'id'    => 'CHI134'
                    ]
                ]
            ],
            'prizes'    => [
                'remote control helicopter' => [
                    'id'    => 'CHI135'
                ],
                'video drone'   => [
                    'id'    => 'CHI136'
                ],
                'bracelet'  => [
                    'id'    => 'CHI137'
                ],
                'necklace'  => [
                    'id'    => 'CHI138'
                ],
                'earrings'  => [
                    'id'    => 'CHI139'
                ],
                'chidon T-shirt'    => [
                    'boys'  => [
                        'children s'    => [
                            'id'    => 'CHI140'
                        ],
                        'children m'    => [
                            'id'    => 'CHI141'
                        ],
                        'children l'    => [
                            'id'    => 'CHI142'
                        ],
                        'children xl'   => [
                            'id'    => 'CHI143'
                        ],
                        'adult s'   => [
                            'id'    => 'CHI144'
                        ],
                        'adult m'   => [
                            'id'    => 'CHI145'
                        ],
                        'adult l'   => [
                            'id'    => 'CHI146'
                        ]
                    ],
                    'girls' => [
                        'children s'    => [
                            'id'    => 'CHI147'
                        ],
                        'children m'    => [
                            'id'    => 'CHI148'
                        ],
                        'children l'    => [
                            'id'    => 'CHI149'
                        ],
                        'children xl'   => [
                            'id'    => 'CHI150'
                        ],
                        'adult s'   => [
                            'id'    => 'CHI151'
                        ],
                        'adult m'   => [
                            'id'    => 'CHI152'
                        ],
                        'adult l'   => [
                            'id'    => 'CHI153'
                        ]
                    ]
                ],
                'chidon art set'    => [
                    'id'    => 'CHI154'
                ],
                'chidon juggling set'   => [
                    'id'    => 'CHI155'
                ],
                'chidon soccer ball'    => [
                    'id'    => 'CHI156'
                ],
                'chidon basket ball'    => [
                    'id'    => 'CHI157'
                ],
                'chidon football'   => [
                    'id'    => 'CHI158'
                ],
                'framed rebbe picture 5782' => [
                    'id'    => 'CHI159'
                ],
                'chidon cap'    => [
                    'boys'  => [
                        'id'    => 'CHI160'
                    ],
                    'girls' => [
                        'id'    => 'CHI161'
                    ]
                ],
                'der rebbe ret tzu kinder'  => [
                    'id'    => 'CHI162'
                ],
                'chidon leather sefer hamitzvos'    => [
                    'boys'  => [
                        'id'    => 'CHI164'
                    ],
                    'girls' => [
                        'id'    => 'CHI163'
                    ]
                ],
                'chidon leather chitas' => [
                    'boys'  => [
                        'id'    => 'CHI165'
                    ],
                    'girls' => [
                        'id'    => 'CHI166'
                    ]
                ],
                'chidon leather siddur' => [
                    'boys'  => [
                        'id'    => 'CHI167'
                    ],
                    'girls' => [
                        'id'    => 'CHI168'
                    ]
                ],
                'chidon leather tehillim'   => [
                    'boys'  => [
                        'id'    => 'CHI169'
                    ],
                    'girls' => [
                        'id'    => 'CHI170'
                    ]
                ],
                'chidon leather machzor'    => [
                    'boys'  => [
                        'id'    => 'CHI172'
                    ],
                    'girls' => [
                        'id'    => 'CHI171'
                    ]
                ],
                'chidon baseball'   => [
                    'id'    => 'CHI173'
                ],
                'chidon carry-on'   => [
                    'id'    => 'CHI174'
                ],
                'personalized name bracelet'    => [
                    'id'    => 'CHI175'
                ],
                'chidon pogo ball'  => [
                    'id'    => 'CHI176'
                ],
                'the jewish underground vol 1'  => [
                    'id'    => 'CHI177'
                ],
                'the jewish underground vol 2'  => [
                    'id'    => 'CHI178'
                ],
                'iron curtain vol 1'    => [
                    'id'    => 'CHI179'
                ],
                'iron curtain vol 2'    => [
                    'id'    => 'CHI180'
                ],
                'escape from europe'    => [
                    'id'    => 'CHI181'
                ],
                'the Rebbe and the mazkir'  => [
                    'id'    => 'CHI182'
                ],
                'chidon towel'  => [
                    'boys'  => [
                        'id'    => 'CHI183'
                    ],
                    'girls' => [
                        'id'    => 'CHI184'
                    ]
                ],
                'chocolate mold'    => [
                    'id'    => 'CHI185'
                ],
                'backpack'  => [
                    'boys'  => [
                        'id'    => 'CHI187'
                    ],
                    'girls' => [
                        'id'    => 'CHI186'
                    ]
                ],
                'waffle maker'  => [
                    'id'    => 'CHI188'
                ],
                'chidon cookie cutters' => [
                    'id'    => 'CHI189'
                ],
                'reb binyomin kletzker' => [
                    'id'    => 'CHI190'
                ],
                'reb shmuel munkes' => [
                    'id'    => 'CHI191'
                ],
                'the slavita brothers'  => [
                    'id'    => 'CHI192'
                ],
                'reb hillel paritcher'  => [
                    'id'    => 'CHI193'
                ]
            ]
        ];

        return $item_ids[$info['id']];
    }
}