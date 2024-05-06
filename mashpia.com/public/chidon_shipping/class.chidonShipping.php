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

    public function __construct($yr = 0) {
        global $MASHPIA_DB;
        $this->db = $MASHPIA_DB;
        $this->year = $yr ?? GlobalSettings::getChidonRegYear();
        $this->schools = [];
        $this->grades = [];
        $this->users = [];
        $this->toExclude = [];
        $this->only = [];
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

    public function setToExclude($ids) {
        $this->toExclude = $ids;
    }

    public function setOnly($ids) {
        $this->only = $ids;
    }

    /**
     * get list of user IDs that need to have brochures sent out
     *
     * @param $gender
     * @param $school
     * @param $brochures
     * @param $early
     * @param $remove - user ids to remove
     * @return array - all user info from users db with the user id as the key
     */
    public function getBrochures($gender, $school, $brochures = []) {
        $info = [];
        $sql = "select * from th_chidon tc 
                join users u using (user_id) 
                where tc.year = :year";
        if ($gender == 'm') $sql .= " and u.gender = 'M'";
        if ($gender == 'f') $sql .= " and u.gender = 'F'";
        if ($school > 0) $sql .= " and u.school_id = " . $school;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
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
     * @param $gender
     * @param $school
     * @param $guides
     * @return void
     */
    public function getGuides($gender, $school, $guides = []) {
        $info = [];
        if (in_array('study guides', $guides)) {
            $sql = "select * from th_chidon tc 
                    join users u using (user_id) 
                    where tc.year = :year";
            if ($gender == 'm') $sql .= " and u.gender = 'M'";
            if ($gender == 'f') $sql .= " and u.gender = 'F'";
            if ($school > 0) $sql .= " and u.school_id = " . $school;
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['year' => $this->year]);
            $rows = $stmt->fetchAll();
            foreach ($rows as $row) {
                $cat = 'guides';
                $item = 'study guides';
                $book = $row['book'];
                $id = $this->getItemID($cat, $item, 'study guide ' . $book);
                $info[$row['user_id']][] = [
                    'item'  => $item,
                    'size'  => $book,
                    'color' => '',
                    'name'  => '',
                    'id'    => $id,
                    'cat'   => $cat
                ];
            }
        }
        if (in_array('khk guides', $guides)) {
            $sql = "select * from th_chidon tc 
                    join users u using (user_id) 
                    where khk_reg = 1 and year = :year";
            if ($gender == 'm') $sql .= " and u.gender = 'M'";
            if ($gender == 'f') $sql .= " and u.gender = 'F'";
            if ($school > 0) $sql .= " and u.school_id = " . $school;
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['year' => $this->year]);
            $rows = $stmt->fetchAll();
            foreach ($rows as $row) {
                $cat = 'guides';
                $item = 'khk guides';
                $id = $this->getItemID($cat, $item);
                $he_name = $row['first_he'] . ' ' . $row['last_he'];
                $info[$row['user_id']][] = [
                    'item'  => $item,
                    'size'  => '',
                    'color' => '',
                    'name'  => $he_name,
                    'id'    => $id,
                    'cat'   => $cat
                ];
            }
        }

        return $info;
    }

    /**
     * @param $gender
     * @param $school
     * @param $prizes
     * @return array - list prizes to give per user ID
     */
    public function getEnrollmentPrize($gender, $school, $limitTo = []) {
        $info = [];
        $sql = "select user_id, book from th_chidon 
                 join users u using (user_id) 
                 where year = :year";
        if ($gender == 'm') $sql .= " and u.gender = 'M'";
        if ($gender == 'f') $sql .= " and u.gender = 'F'";
        if ($school > 0) $sql .= " and u.school_id = " . $school;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $book = $row['book'];
            $cat = 'enrollment prize';
            $item = 'kop cards';
            if (in_array(strtolower($item), $limitTo)) {
                $id = $this->getItemID($cat, $item, ($item . ' #' . $book));
                $info[$row['user_id']][] = [
                    'item'  => $item,
                    'size'  => $book,
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
     * @param $gender
     * @param $school
     * @param $prizes
     * @return array - list prizes to give per user ID
     */
    public function getRecruitmentPrizes($gender, $school, $limitTo = []) {
        $info = [];
        // get list of prizes
        $prizes = $this->getListofRecruitmentPrizes();
        // find out list of children and how many credits they have
        $children  = $this->getChildrenRecruitments($gender, $school);

        $cat = 'recruitment prizes';
        foreach ($children as $user_id => $details) {
            if (in_array($user_id, $this->toExclude)) continue;
            if (!empty($this->only) && !in_array($user_id, $this->only)) continue;
            // skip if child did not recruit this yr
            if (intval($details['year']) != $this->year) continue;

            if ($details['credits'] > 5) $details['credits'] = 5;

            $credits = [$details['credits']];
            if ($user_id == 55248) $credits[] = 1;
            foreach ($credits as $credit) {
                $prize = $prizes[$credit];
                if (in_array(strtolower($prize), $limitTo)) {
                    $color = '';
                    if (strtolower($prize) == 'watch') {
                        if ($details['gender'] == 'M') $color = 'blue';
                        else if ($details['gender'] == 'F') $color = 'burgundy';
                    }
                    if (empty($color)) $id = $this->getItemID($cat, strtolower($prize));
                    else $id = $this->getItemID($cat, strtolower($prize), $color);
                    $info[$user_id][] = [
                        'item' => $prize,
                        'size' => '',
                        'color' => $color,
                        'name' => '',
                        'id' => $id,
                        'cat' => $cat
                    ];
                }
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
        $sql = "select * from chidon_credit_prizes where year = " . $this->year;
        $stmt = $this->db->query($sql);
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
        $sql = "select u.user_id, u.gender, count(*) as credits, MAX(year) as year 
                from users u 
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
            $children[$row['user_id']] = [
                'gender'    => $row['gender'],
                'credits'   => $row['credits'],
                'year'      => $row['year']
            ];
        }
        // add specific child as exception
        if ($school == 269 || !$school) {
            $children[55187] = [
                'gender'    => 'M',
                'credits'   => 4,
                'year'      => 5784
            ];
        }
        // add another child as exception
        if ($school == 690 || !$school) {
            $children[55248] = [
                'gender'    => 'F',
                'credits'   => 2,
                'year'      => 5784
            ];
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
            if (in_array($row['user_id'], $this->toExclude)) continue;
            if (!empty($this->only) && !in_array($row['user_id'], $this->only)) continue;

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

    public function getSweaters($gender, $school, $sweaters = []) {
        // 'children sweaters', 'staff sweaters', 'parent/grandparent sweaters'
        $info = [];
        foreach ($sweaters as $type) {
            switch ($type) {
                case 'children sweaters':
                    $info += $this->getChildrenSweaters($gender, $school);
                    break;
                case 'staff sweaters':
//                    $info += $this->getStaffSweaters($gender, $school);
                    break;
                case 'parent/grandparent sweaters':
                    $info += $this->getExtraPurchases($gender, $school, ['sweaters']);
                    break;
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
    public function getChildrenSweaters($gender, $school) {
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
                    year = :year AND tc.date_paid > 0";
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

        $cat = 'sweaters';
        $item = 'children sweaters';
        foreach ($rows as $row) {
            if (in_array($row['user_id'], $this->toExclude)) continue;
            if (!empty($this->only) && !in_array($row['user_id'], $this->only)) continue;

            $size = $row['size'];
            $id = $this->getItemID($cat, $item, $genders[$row['gender']], $size);
            $info[$row['user_id']][] = [
                'item'  => $item,
                'size'  => $size,
                'color' => $genders[$row['gender']],
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

    public function getStaffSweaters($gender, $school) {
        // get from csv file
    }

    public function getCelebrationItems($gender, $school, $items = []) {
        $info = $this->getExtraPurchases($gender, $school, ['celebration boxes']);
        return $info;
    }

    /**
     * @param $gender
     * @param $school
     * @param $items
     * @param $method
     * @return array
     */
    public function getExtraPurchases($gender, $school, $items = [], $showMissing = false) {
        $info = [];
        $purchases = [];
        $sql = "select * from extra_purchases ep 
                left join purchase_addresses pa using (purchase_id) 
                where ep.year = :year 
                and (ep.shipping_amount != 10 or ep.shipping_amount is null)";
        if ($items && count($items)) {
            $fields = [];
            foreach ($items as $item) {
                if ($item == 'sweaters') $fields[] = "sweater";
                if ($item == 'celebration boxes') $fields[] = 'celeb_box';
            }
            $sql .= " and item in ('" . implode("','", $fields) . "')";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();

        foreach ($rows as $row) {
            if ($row['item'] == 'celeb_box') {
                $size = '';
                $cat = 'celebration items';
                $item = 'celebration boxes';
                $id = $this->getItemID($cat, $item);
            } else {
                $cat = 'sweaters';
                $size = $row['size'];
                $item = 'parent/grandparent sweaters';
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

        $admin_info = $this->getOldestChild(array_keys($purchases));
        if ($showMissing) $admin_info = $this->getOldestChildMissing(array_keys($purchases));
//        echo "<pre>"; print_r($admin_info); echo "</pre>"; exit;
        foreach ($purchases as $admin_id => $more) {
            foreach ($more as $purchase) {
                if (isset($admin_info[$admin_id])) {
                    $user_id = $admin_info[$admin_id]['user_id'];
                    $school_id = $admin_info[$admin_id]['school_id'];
                    if ($school > 0 && $school != $school_id) continue;
                    $info[$user_id][] = $purchase;
                }
            }
        }
        return $info;
    }

    /**
     * @param array $admin_ids
     * @return array - list of admin IDs with oldest child's user ID
     */
    private function getOldestChild(array $admin_ids) {
        // find oldest child registered in chayolei
        $sql2 = "select user_id, dob, school_id from users u 
                 join admin_auths aa on aa.id = u.user_id 
                 where admin_id = :id and auth = 'user' 
                 and u.user_registered > 0 
                 and u.school_id != 612";
//        if ($gender == 'm') $sql2 .= " and u.gender = 'M'";
//        if ($gender == 'f') $sql2 .= " and u.gender = 'F'";
//        if ($school > 0) $sql2 .= " and u.school_id = " . $school;
        $stmt2 = $this->db->prepare($sql2);

        // find oldest child in chidon
        $sql = "select user_id, dob, u.school_id from users u 
                join admin_auths aa on aa.id = u.user_id 
                join th_chidon tc using (user_id)
                where admin_id = :id and auth = 'user' 
                and u.school_id != 612 
                group by user_id";
//        if ($gender == 'm') $sql .= " and u.gender = 'M'";
//        if ($gender == 'f') $sql .= " and u.gender = 'F'";
//        if ($school > 0) $sql .= " and u.school_id = " . $school;
        $stmt = $this->db->prepare($sql);

        $admin_info = [];
        foreach ($admin_ids as $id) {
            $stmt2->execute(['id' => $id]);
            $rows = $stmt2->fetchAll();

            if (! ($rows && count($rows))) {
                $stmt->execute(['id' => $id]);
                $rows = $stmt->fetchAll();
            }

            if ($rows && count($rows)) $admin_info[$id] = $this->getOldest($rows);
            else $admin_info[$id] = 0;
        }
        return $admin_info;
    }

    private function getOldestChildMissing(array $admin_ids) {
        // find oldest child registered in chayolei
        $sql2 = "select user_id, dob, school_id from users u 
                 join admin_auths aa on aa.id = u.user_id 
                 where admin_id = :id and auth = 'user' 
                 and u.user_registered > 0 
                 and u.school_id != 612";
//        if ($gender == 'm') $sql2 .= " and u.gender = 'M'";
//        if ($gender == 'f') $sql2 .= " and u.gender = 'F'";
//        if ($school > 0) $sql2 .= " and u.school_id = " . $school;
        $stmt2 = $this->db->prepare($sql2);

        // find oldest child in chidon
        $sql = "select user_id, dob, u.school_id from users u 
                join admin_auths aa on aa.id = u.user_id 
                join th_chidon tc using (user_id)
                where admin_id = :id and auth = 'user' 
                and u.school_id != 612 
                group by user_id";
//        if ($gender == 'm') $sql .= " and u.gender = 'M'";
//        if ($gender == 'f') $sql .= " and u.gender = 'F'";
//        if ($school > 0) $sql .= " and u.school_id = " . $school;
        $stmt = $this->db->prepare($sql);

        $admin_info = [];
        foreach ($admin_ids as $id) {
            $stmt2->execute(['id' => $id]);
            $rows = $stmt2->fetchAll();

            if (! ($rows && count($rows))) {
                $stmt->execute(['id' => $id]);
                $rows = $stmt->fetchAll();
                if ($rows && count($rows)) $admin_info[$id] = $this->getOldest($rows);
                else $admin_info[$id] = 0;
            }
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
                if ($d2 < $d1) $child = $row;
            }
        }
        return $child;
    }

    public function getExtraPurchasesAK($toShip = true) {
        $info = [];
        $purchases = [];
        if ($toShip) {
            $sql = "SELECT 
                        *
                    FROM
                        extra_purchases ep
                            JOIN
                        purchase_addresses pa USING (purchase_id)
                    WHERE
                        year = :year
                            AND admin_id IN (SELECT 
                                parent_id
                            FROM
                                chidon_parent_shipping
                            WHERE
                                myshliach_ak = 1 AND year = :year) 
                    AND use_admin_shipping_address = 1";
        } else {
            $sql = "SELECT 
                        *
                    FROM
                        extra_purchases 
                    WHERE
                        year = :year
                            AND admin_id IN (SELECT 
                                parent_id
                            FROM
                                chidon_parent_shipping
                            WHERE
                                myshliach_ak = 1 AND year = :year)
                            AND shipping_amount = 0";
        }
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

        $admin_info = $this->getOldestChild(array_keys($purchases));
        foreach ($purchases as $admin_id => $more) {
            foreach ($more as $purchase) {
                if (isset($admin_info[$admin_id])) $info[$admin_info[$admin_id]][] = $purchase;
            }
        }
        return $info;
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
        if (! count($gifts)) $gifts = ['yarmulka', 'jewelry'];

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
            if (in_array($row['user_id'], $this->toExclude)) continue;
            if (!empty($this->only) && !in_array($row['user_id'], $this->only)) continue;

            if ($gifts && count($gifts)) {
                foreach ($gifts as $gift) {
                    $add = false;
                    if ($gift == 'yarmulka' && $row['gender'] == 'M' && ($gender == 'm' || $gender == 0)) $add = true;
                    else if ($gift == 'jewelry' && $row['gender'] == 'F' && ($gender == 'f' || $gender == 0)) $add = true;
                    if ($add) {
                        $color = '';
                        $name = '';
                        if ($gift == 'yarmulka') {
                            if ($row['yarmulka'] == 0) $row['yarmulka'] = 5; // make sure yarmulka has a size
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

        // get extra info for ID cards
        $extra_info = $this->getLanyardInfo();

        foreach ($rows as $row) {
//            if (in_array($row['user_id'], $this->toExclude)) continue;
//            if (!empty($this->only) && !in_array($row['user_id'], $this->only)) continue;
            if (! isset($extra_info[$row['user_serial']]['code'])) continue;
            if ($row['trip'] == 'east' || intval($row['ultimate_trip'])) continue;
            $info[$row['user_id']][] = [
                'item'  => 'ID card',
                'size'  => '',
                'color' => $extra_info[$row['user_serial']]['color'] ?? '', // lanyard color
                'name'  => '',
                'id'    => $id,
                'cat'   => $cat,
                'code'  => $extra_info[$row['user_serial']]['code'] ?? ''
            ];
        }
        return $info;
    }

    private function getLanyardInfo()
    {
        $info = [];
        $sql = "select * from chidon_lanyards where year = :year";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $info[$row['user_serial']] = [
                'color' => $row['color'],
                'code'  => $row['code']
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
    public function getAwards($gender, $school, $limitTo = []) {
        if (empty($limitTo)) $limitTo = ['certificate', 'plaque', 'medal', 'blue trophy'];

        $info = $this->getAwardsByFinals($gender, $school, $limitTo);
//        if (in_array('plaque', $limitTo) || in_array('medal', $limitTo)) {
//            $info += $this->getAwardsByTests($gender, $school, $limitTo);
//        }
//        else $info += $this->getAwardsByFinals($gender, $school, $limitTo);

        return $info;
    }

    private function getAwardsByFinals($gender, $school, $limitTo) {
        $info = [];
        $sql = "select *, tcf.khk as khk_final from th_chidon_finals tcf 
                join th_chidon tc using (user_id, year) 
                join users u using (user_id) 
                where tcf.year = :year 
                    AND (track_1 > 0 OR track_2 > 0
                    OR track_3 > 0
                    OR track_4 > 0
                    OR tcf.khk > 0)";
        if ($gender == 'm') $sql .= " and u.gender = 'M'";
        if ($gender == 'f') $sql .= " and u.gender = 'F";
        if ($school > 0) $sql .= " and u.school_id = " . $school;
        $sql .= " GROUP BY user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();

        $awards = [
            'yesod' => 'certificate',
            'yediah' => 'plaque',
            'havonah' => 'medal / plaque',
            'iyun' => 'medal / plaque / blue trophy',
        ];

        $cat = 'awards';
        foreach ($rows as $row) {
            // remove any child from ms/ak that is coming to the east coast trip
//            if (in_array($row['school_id'], [61, 269]) && $row['trip'] == 'east') continue;
            $awardTrack = $this->getAwardTrack($row);
            $award = $awardTrack ? $awards[$awardTrack] : '';
            if (empty($award)) continue;

            // check for khk plaque
            if ($this->addKHK($row)) $award .= ' / khk plaque';
            $award_info = explode(' / ', $award);
            foreach ($award_info as $item) {
                // make sure item was chosen
                if (! in_array($item, $limitTo)) continue;
                // remove any child getting khk plaque that is going on ultimate trip
                if ($item == 'khk plaque' && intval($row['ultimate_trip'])) continue;
                $id = $this->getItemID($cat, $item);
                $info[$row['user_id']][] = [
                    'item' => $item,
                    'size' => '',
                    'color' => '',
                    'name' => '',
                    'id' => $id,
                    'cat' => $cat,
                    'name' => $item == 'medal' ? '' : ($row['first_he'] . ' ' . $row['last_he'])
                ];
            }
        }
        return $info;
    }

    private function getAwardsByTests($gender, $school, $limitTo) {
        $info = [];
        $sql = "
            SELECT 
                *
            FROM
                th_chidon tc
                    JOIN
                users u USING (user_id)
            WHERE
                tc.year = :year AND tc.date_paid > 0 
        ";
        if ($gender == 'm') $sql .= " and u.gender = 'M'";
        if ($gender == 'f') $sql .= " and u.gender = 'F";
        if ($school > 0) $sql .= " and u.school_id = " . $school;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();

        $awards = [
            'yesod' => 'certificate',
            'yediah' => 'plaque',
            'havonah' => 'medal / plaque',
            'iyun' => 'medal / plaque / blue trophy',
        ];

        foreach ($rows as $row) {
            $highest_track = $this->getTrackByTests($row);
            $award = $highest_track ? $awards[$highest_track] : '';
            if (empty($award)) continue;

            $award_info = explode(' / ', $award);
            foreach ($award_info as $item) {
                // make sure item was chosen
                if (! in_array($item, $limitTo)) continue;
                $id = $this->getItemID('awards', $item);
                $info[$row['user_id']][] = [
                    'item' => $item,
                    'size' => '',
                    'color' => '',
                    'name' => '',
                    'id' => $id,
                    'cat' => 'awards'
                ];
            }
        }
        return $info;
    }

    /**
     * @param $child
     * @return string
     */
    public function getAwardTrack($child) {
        $ct = new ChidonTests();
        $types = array_combine(
            array_keys($ct->getTypes()),
            array_map(function($v) { return strtolower($v); }, $ct->getTypes())
        );

        $tracks = [
            1   => 'yesod',
            2   => 'yediah',
            3   => 'havonah',
            4   => 'iyun'
        ];

        $award = '';
        $max = 20;
        $needed = $ct->getPassingAvgs($child['user_id'], 'finals');
        for ($i = 1; $i <= 4; $i++) {
            $level = 'track_' . $i;
            if (isset($child[$level])) {
                $score = $child[$level];
                $avg_needed = $needed[array_search($tracks[$i], $types)];
                $mark = round(($score / $max) * 100);
//                echo "User ID: " . $child['user_id'] . " - Score: " . $score . " - Mark: " . $mark . " - Avg Needed: " . $avg_needed . " - Award: " . $award . "<br />";
                if ($mark >= $avg_needed) {
                    $award = $tracks[$i];
                } else {
                    break;  // if one level is not passed, then no need to check the rest
                }
            }
        }

        if ($award == 'iyun') {
            // make sure that all four levels are also based on the iyun avg
            for ($j = 1; $j <= 4; $j++) {
                $level = 'track_' . $j;
                $mark = round(($child[$level] / $max) * 100);
                if ($mark < $needed['genius']) {
                    $award = 'havonah';
                    break;
                }
            }
        }
        if ($award != 'iyun') {
            // check cumulative
            if ($this->passedIyunFinalCumulative($child)) $award = 'iyun';
        }
        return $award;
    }

    private function passedIyunFinalCumulative($child) {
        $mark = 0;
        for ($i = 1; $i <= 4; $i++) {
            $level = 'track_' . $i;
            if (isset($child[$level])) {
                $mark += $child[$level];
            }
        }
        $avg = intval(($mark / 80) * 100);
        return $avg >= 90;
    }

    public function getTrackByTests($row, $forIyun = false) {
        $ct = new ChidonTests();
        $types = $ct->getTypes();

        if (!isset($row['highest_track']) || ($row['highest_track'] == 'iyun' && $forIyun)) {
            $ct->setStudents($row['school_id'], $row['class_id'], $row['user_id']);
            $ct->setScores();
            $ct->calculateMarks();
            $marks = $ct->getMarks();
            if (!isset($marks[$row['th_chidon_id']])) return '';
            $ht = $ct->getHighestTrack($marks[$row['th_chidon_id']], $row['user_id']);
            if ($ht == 'genius' && $forIyun) $ht = $ct->getHighestTrack($marks[$row['th_chidon_id']], $row['user_id'], false, 3, false, true);
        } else {
            $ht = array_search(ucwords($row['highest_track']), $types);
        }

        $highest_track = empty($ht) ? false : $types[$ht];
        $highest_track2 = !empty($row['reward_type']) && $row['reward_type'] != 'highest track passed' ? $types[$row['reward_type']] : false;
        $highest_track3 = !empty($row['award_type']) && $row['award_type'] != 'highest final passed' ? $types[$row['award_type']] : false;

        // find a way of comparing them
        $indexes = array_values($ct->getTypes());
        $key1 = array_search($highest_track, $indexes);
        $key2 = array_search($highest_track2, $indexes);
        $key3 = array_search($highest_track3, $indexes);

//        if ($row['user_serial'] == 7774619) {
//            echo "highest track: " . $highest_track . "<br />";
//            echo "highest track2: " . $highest_track2 . "<br />";
//            echo "highest track3: " . $highest_track3 . "<br />";
//            echo "key1: " . $key1 . "<br />";
//            echo "key2: " . $key2 . "<br />";
//            echo "key3: " . $key3 . "<br />";
//        }

//        echo "highest track: " . $highest_track . ' key1: ' . $key1 . "<br />";
//        echo "highest track2: " . $highest_track2 . ' key2: ' . $key2 . "<br />";
//        echo "highest track3: " . $highest_track3 . ' key3: ' . $key3 . "<br />";

        // find out which one is highest
        if ($key1 !== false && $key2 !== false && $key3 !== false) {
            $highest_track = $key2 > $key1 ? $key3 > $key2 ? $highest_track3 : $highest_track2 : $highest_track;
        } else if ($key1 !== false && $key2 !== false) {
            $highest_track = $key2 > $key1 ? $highest_track2 : $highest_track;
        } else if ($key3 !== false) {
            $highest_track = $key3 > $key1 ? $highest_track3 : $highest_track;
        }

        return strtolower($highest_track) ?? '';
    }

    public function addKHK($child) {
        $needed = 140;
        // check for khk trophy
        // only show khk plaque if NOT going on ultimate trip
        if (intval($child['khk_reg']) && intval($child['khk_final']) >= $needed && intval($child['khk']) == 0)
            return true;
        else
            return false;
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
                    cup.user_id, cup.he_name, cp.prize_id, cp.prize_name, cp.size, cp.color, 
                    tc.ultimate_trip, u.first, u.last, u.user_serial, tci.highest_track, c.class_grade, c.class_sub  
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
                        JOIN 
                    classes c ON u.class_id = c.class_id 
                WHERE
                    cup.year = :year AND tc.date_paid > 0 
                        AND tc.ultimate_trip = 0 AND (tci.highest_track is null OR tci.highest_track != 'yesod')";
//        if (count($limitTo)) $sql .= " and cup.prize_id in (" . implode(',', $ids) . ")";
        if ($gender == 'm') $sql .= " AND u.gender = 'M'";
        if ($gender == 'f') $sql .= " AND u.gender = 'F'";
        if ($school > 0) $sql .= " AND u.school_id = " . $school;
//        echo $sql; exit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            if (in_array($row['user_id'], $this->toExclude)) continue;
            if (!empty($this->only) && !in_array($row['user_id'], $this->only)) continue;

            // make sure it's one of the prizes selected to show
            $found = true;
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
                    'id'    => $id,
                    'item'  => $row['prize_name'],
                    'size'  => $row['size'],
                    'color' => $row['color'],
                    'name'  => $row['he_name'],
                    'first' => $row['first'],
                    'last'  => $row['last'],
                    'track' => $row['highest_track'],
                    'grade' => $row['class_grade'] . ($row['class_sub'] ? '-' . $row['class_sub'] : ''),
                    'serial' => $row['user_serial'],
                    'cat'   => 'prizes'
                ];
            }
        }
//        echo "<pre>"; print_r($info); echo "</pre>";
        return $info;
    }

    private function getChidonPrizes() {
        /**
         * gets list of chidon prizes with name and ID
         */

        $info = [];
        $sql = "select prize_id, prize_name from chidon_prizes where year = :year";
        $stmt = $this->db->prepare($sql);
        if ($this->year == '0') $this->year = GlobalSettings::getChidonYear();
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $info[$row['prize_id']] = $row['prize_name'];
        }
        return $info;
    }

    public function getEventItems() {

    }

    public function getStatus() {
        $info = [];
        $sql = "select * from th_chidon_shipping 
                where year = :year 
                order by user_id, item_id, item_num";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $info[$row['user_id']][$row['item_id']][$row['item_num']] = $row;
        }
        return $info;
    }

    public function getChildrenToRemove($paid = false, $notPaid = false) {
        $admins = [];
        $sql = "SELECT 
                    admin_id
                FROM
                    extra_purchases
                WHERE
                    year = :year
                        AND admin_id IN (SELECT 
                            parent_id
                        FROM
                            chidon_parent_shipping
                        WHERE
                            myshliach_ak = 1 AND year = :year";
        if ($paid) $sql .= " and date_paid is not null)";
        else if ($notPaid) $sql .= " and date_paid is null)";
        else $sql .= ")";
        $sql .= " GROUP BY admin_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $admins[] = $row['admin_id'];
        }

        $remove = [];
        $sql = "select id from admin_auths where admin_id in (" . implode(',', $admins) . ")";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $remove[] = $row['id'];
        }
        return $remove;
    }

    public function getExtraPurchasesToShip($myshliach = false) {
        $sql = "SELECT 
                    *
                FROM
                    extra_purchases ep
                        JOIN
                    purchase_addresses pa USING (purchase_id) 
                        JOIN
                    admins a USING (admin_id)
                WHERE
                    year = :year
                        AND admin_id ";
        if (! $myshliach) $sql .= "NOT";
        $sql .= " IN (SELECT 
                            parent_id
                        FROM
                            chidon_parent_shipping
                        WHERE
                            myshliach_ak = 1 AND year = :year)";
        if ($myshliach) $sql .= " AND use_admin_shipping_address = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        return $rows;
    }

    public function createCSVFromExtraPurchases($info) {
        $i = 0;
        $csv[$i++] = ['Order Number', 'Recipient Full Name', 'Recipient First Name', 'Recipient Last Name', 'Recipient Phone',
            'Recipient Company', 'Address Line 1', 'Address Line 2', 'Address Line 3', 'City', 'State', 'Postal Code',
            'Country Code', 'Item SKU', 'Item Name 1', 'Item Quantity', 'Item Options', 'Recipient Email'];
        $csv[$i++] = ['Family ID', 'Parent Full Name', 'Parent First Name', 'Parent Last Name', 'Recipient Phone', 'School - Shipping Type',
            'Address Line 1', 'Address Line 2', 'Address Line 3', 'City', 'State', 'Postal Code', 'Country Code', 'CHI Number',
            'Full Item Name', 'Quantity', 'Child Name - Serial #', 'Recipient Email'];
        foreach ($info as $row) {
            $phone = $row['admin_phone_mobile'] ?? $row['admin_phone_work'] ?? $row['admin_phone_home'] ?? '';
            $shipping = 'shipping';
            $qty = $row['amount'];
            if ($row['item'] == 'celeb_box') {
                $itemDesc = 'celebration box(es)';
                // get item it
                $item_id = $this->getItemID('extra purchases', 'celebration boxes');
            } else {
                $itemDesc = $row['type_of_sweater'] . ' ' . $row['size'] . ' sweater';
                // get item it
                $item_id = $this->getItemID('extra purchases', 'sweaters', ($row['type_of_sweater'] . ' sweater'), $row['size']);
            }

            $csv[$i++] = [$row['admin_id'], ($row['first'] . ' ' . $row['last']), $row['first'], $row['last'],
                $phone, ucwords($shipping), $row['address'], '', '', $row['city'], $row['state'], $row['zip'],
                $row['country'], $item_id, $itemDesc, $qty, '', $row['admin_email']];
        }
        return $csv;
    }

    public function getAmbassadorPrizes($gender, $school, $limit = []) {
        $info = [];
        $sql = "SELECT 
                    user_id, SUM(subsidy_amount) AS total
                FROM
                    chidon_user_subsidies cus
                        JOIN 
                    users u USING (user_id) 
                WHERE
                    chidon_year = :year";
        if ($gender == 'm') $sql .= " and u.gender = 'M'";
        if ($gender == 'f') $sql .= " and u.gender = 'F'";
        if ($school > 0) $sql .= " and u.school_id = " . $school;
        $sql .= " GROUP BY user_id
                HAVING total >= 500";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();

        $cat = 'ambassador prizes';
        $item = 'ambassador prize';
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

        // hardcode exceptions
        $exceptions = [55187, 57065, 62354];
        foreach ($exceptions as $user_id) {
            $info[$user_id][] = [
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
     * get yahadus book purchases
     */
    public function getYahadusBooks($gender, $school, $items = []) {
        $info = [];
        $purchases = [];
        $cat = 'yahadus books';

        foreach ($items as $item) {
            if ($item == 'during enrollment') {
                $sql = "SELECT * FROM registration_charges rc 
                        join users u using (user_id) 
                        WHERE type like 'YB%' AND year = :year";
                if ($gender == 'm') $sql .= " and u.gender = 'M'";
                if ($gender == 'f') $sql .= " and u.gender = 'F'";
                if ($school > 0) $sql .= " and u.school_id = " . $school;
                $stmt = $this->db->prepare($sql);
                $stmt->execute(['year' => $this->year]);
                $rows = $stmt->fetchAll();

                foreach ($rows as $row) {
                    // get last letter of type
                    $book = substr($row['type'], -1);
                    $itemDesc = 'yahadus book ' . $book;
                    $id = $this->getItemID($cat, $item, $itemDesc);
                    $info[$row['user_id']][] = [
                        'item'  => $itemDesc,
                        'size'  => $book,
                        'color' => '',
                        'name'  => '',
                        'id'    => $id,
                        'cat'   => $cat
                    ];
                }
            } else if ($item == 'end of yr sale') {
                $yom_tov = 'Yahadus Book Sale';
                $sql = "
                    SELECT 
                        *
                    FROM
                        mashpia_purchases.purchases p
                            JOIN
                        mashpia_purchases.purchase_details pd USING (purchase_id)
                            JOIN
                        mashpia_purchases.mivtzoim_items mi ON mi.mivtzoim_item_id = pd.item_id
                            JOIN
                        users u using (user_id)
                    WHERE
                        mi.yom_tov = :yom_tov AND p.year = :year
                ";
                if ($gender == 'm') {
                    $sql .= " AND u.gender = 'M'";
                } else if ($gender == 'f') {
                    $sql .= " AND u.gender = 'F'";
                }
                if ($school > 0) {
                    $sql .= " AND u.school_id = " . $school;
                }

                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'yom_tov' => $yom_tov,
                    'year'    => $this->year
                ]);
                $rows = $stmt->fetchAll();
                foreach ($rows as $row) {
                    $purchases[$row['admin_id']][] = $row;
                }

                foreach ($purchases as $rows) {
                    foreach ($rows as $row) {
                        $itemDesc = strtolower($row['item']);
                        $id = $this->getItemID($cat, $item, $itemDesc);
                        $info[$row['user_id']][] = [
                            'item'  => $itemDesc,
                            'size'  => '',
                            'color' => '',
                            'name'  => '',
                            'id'    => $id,
                            'cat'   => $cat
                        ];
                    }
                }
            }
        }

        return $info;
    }

    public function getGear($gender, $school, $items, $addresses = false) {
        $sql = "select * from family_sweaters s 
                join users u using (user_id) 
                where year = :year";
        if ($gender == 'm') {
            $sql .= " AND u.gender = 'M'";
        } else if ($gender == 'f') {
            $sql .= " AND u.gender = 'F'";
        }
        if ($school > 0) {
            $sql .= " AND u.school_id = " . $school;
            $sql .= " AND (s.school_id = 0 OR s.school_id = " . $school . ")";
        }
        if ($addresses) $sql .= " AND (address is not null AND address != '')";
        else $sql .= " AND (address is null OR address = '')";
//        echo $sql . "<br />";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'year'      => $this->year
        ]);
        $rows = $stmt->fetchAll();
//        echo "<pre>"; print_r($rows); echo "</pre>";

        $info = [];
        $cat = 'gear';
        foreach ($rows as $row) {
            $gender = $row['gender'] == 'M' ? 'boys' : $row['gender'] == 'F' ? 'girls' : '';
            foreach ($items as $item) {
                if ($item == 'th sweater') {
                    $id = $this->getItemID($cat, $item, $gender, $row['size']);
                    $info[$row['user_id']][] = [
                        'item'  => $item,
                        'size'  => $row['size'],
                        'color' => $row['color'],
                        'name'  => '',
                        'id'    => $id,
                        'cat'   => $cat,
                        'rank'  => $row['rank'],
                        'address' => $row['address']
                    ];
                } else if ($item == 'th cap') {
                    $id = $this->getItemID($cat, $item, $gender);
                    $info[$row['user_id']][] = [
                        'item'  => $item,
                        'size'  => '',
                        'color' => $row['cap'],
                        'name'  => '',
                        'id'    => $id,
                        'cat'   => $cat,
                        'rank'  => $row['rank'],
                        'address' => $row['address']
                    ];
                } else if ($item == 'th rank patch') {
                    $id = $this->getItemID($cat, $item, $row['rank']);
                    $info[$row['user_id']][] = [
                        'item'  => $item,
                        'size'  => '',
                        'color' => '',
                        'name'  => '',
                        'id'    => $id,
                        'cat'   => $cat,
                        'rank'  => $row['rank'],
                        'address' => $row['address']
                    ];
                }
            }
        }
//        echo "<pre>"; print_r($info); echo "</pre>"; exit;
        return $info;
    }

    public function getCategories() {
        $categories = [
            'brochures', 'guides', 'yahadus books', 'enrollment prize', 'recruitment prizes', 'sweaters', 'celebration items',
            'gifts', 'ID cards', 'prizes', 'awards', 'ambassador prizes', 'gear'
        ];
        return $categories;
    }

    public function getItems() {
        $p = $this->getChidonPrizes();
        $prizes = [];
        foreach ($p as $prize) {
            if (! in_array($prize, $prizes)) $prizes[] = $prize;
        }

        $items = [
            'brochures'             => ['brochure'],
            'guides'                => ['study guides', 'khk guides'],
            'yahadus books'         => ['during enrollment', 'end of yr sale'],
            'enrollment prize'      => ['kop cards'],
            'recruitment prizes'    => ['book light', 'rechargeable fan', 'watch', 'neck pillow', 'mini duffle bag'],
//            'test prizes'           => ['kop cards game', 'leather book mark', 'drawstring bag', 'shape shifting cube'],
            'sweaters'              => ['children sweaters', 'parent/grandparent sweaters'],
            'celebration items'     => ['celebration boxes'],
            'gifts'                 => ['yarmulka', 'jewelry'],
            'ID cards'              => ['ID card'],
            'prizes'                => $prizes,
            'awards'                => ['certificate', 'plaque', 'medal', 'blue trophy', 'khk plaque'],
            'ambassador prizes'     => ['ambassador prize'],
            'raffles'               => ['5M Raffles', 'Other Raffles'],
            'gear'                  => ['TH Sweater', 'TH Cap', 'TH Rank Patch']
        ];

        return $items;
    }

    public function getItemID($cat, $item, $deep = '', $deeper = '') {
        // keys to find IDs are category / item / (color/gender) / size - has to match the items array from prev function
        $item_ids = [
            'brochures' => [
                'brochure'  => 'CHI009'
            ],
            'yahadus books' => [
                'during enrollment'  => [
                    'yahadus book 1'  => 'CHI201',
                    'yahadus book 2'  => 'CHI202',
                    'yahadus book 3'  => 'CHI203',
                    'yahadus book 4'  => 'CHI204',
                    'yahadus book 5'  => 'CHI205'
                ],
                'end of yr sale' => [
                    'yahadus book 1'  => 'CHI201',
                    'yahadus book 2'  => 'CHI202',
                    'yahadus book 3'  => 'CHI203',
                    'yahadus book 4'  => 'CHI204',
                    'yahadus book 5'  => 'CHI205'
                ],
            ],
            'guides'    => [
                'study guides'  => [
                    'study guide 1' => 'CHI01A',
                    'study guide 2' => 'CHI01B',
                    'study guide 3' => 'CHI01C',
                    'study guide 4' => 'CHI01D',
                    'study guide 5' => 'CHI01E'
                ],
                'khk guides'     => 'CHI012'
            ],
            'recruitment prizes'    => [
                'book light'    => 'CHI013',
                'rechargeable fan'  => 'CHI014',
                'neck pillow'  => 'CHI017',
                'mini duffle bag'  => 'CHI018',
                'watch'  => [
                    'blue'      => 'CHI015',
                    'burgundy'  => 'CHI016'
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
            'sweaters'     => [
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
                ],
                'parent/grandparent sweaters'   => [
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
            'celebration items'   => [
                'celebration boxes' => 'CHI115',
            ],
            'gifts' => [
                'yarmulka'  => [
                    '4' => 'CHI116',
                    '5' => 'CHI117',
                    '6' => 'CHI118'
                ],
//                'personalized bottle'   => [
//                    'boys'  => 'CHI120',
//                    'girls' => 'CHI121'
//                ],
                'jewelry'  => 'CHI119'
            ],
            'ID cards'  => [
                'ID card'   => 'CHI122'
            ],
            'awards'    => [
                'certificate'   => 'CHI127',
                'plaque'        => 'CHI128',
                'medal'         => 'CHI129',
                'blue trophy'   => 'CHI130',
                'khk plaque'    => 'CHI131',
                'trophy'    => [
                    'gold'      => 'CHI132',
                    'silver'    => 'CHI133',
                    'bronze'    => 'CHI134'
                ]
            ],
//            'prizes'    => [
//                'remote control helicopter' => 'CHI135',
//                'video drone'   => 'CHI136',
//                'bracelet'  => 'CHI137',
//                'necklace'  => 'CHI138',
//                'earrings'  => 'CHI139',
//                'chidon T-shirt'    => [
//                    'boys'  => [
//                        'children s'    => 'CHI140',
//                        'children m'    => 'CHI141',
//                        'children l'    => 'CHI142',
//                        'children xl'   => 'CHI143',
//                        'adult s'       => 'CHI144',
//                        'adult m'       => 'CHI145',
//                        'adult l'       => 'CHI146'
//                    ],
//                    'girls' => [
//                        'children s'    => 'CHI147',
//                        'children m'    => 'CHI148',
//                        'children l'    => 'CHI149',
//                        'children xl'   => 'CHI150',
//                        'adult s'       => 'CHI151',
//                        'adult m'       => 'CHI152',
//                        'adult l'       => 'CHI153'
//                    ]
//                ],
//                'chidon art set'    => 'CHI154',
//                'chidon juggling set'   => 'CHI155',
//                'chidon soccer ball'    => 'CHI156',
//                'chidon basket ball'    => 'CHI157',
//                'chidon football'   => 'CHI158',
//                'framed rebbe picture' => 'CHI159',
//                'chidon cap'    => [
//                    'boys'  => 'CHI160',
//                    'girls' => 'CHI161'
//                ],
//                'der rebbe ret tzu kinder'  => 'CHI162',
//                'chidon leather sefer hamitzvos'    => [
//                    'boys'  => 'CHI164',
//                    'girls' => 'CHI163'
//                ],
//                'chidon leather chitas' => [
//                    'boys'  => 'CHI165',
//                    'girls' => 'CHI166'
//                ],
//                'chidon leather siddur' => [
//                    'boys'  => 'CHI167',
//                    'girls' => 'CHI168'
//                ],
//                'chidon leather tehillim'   => [
//                    'boys'  => 'CHI169',
//                    'girls' => 'CHI170'
//                ],
//                'chidon leather machzor'    => [
//                    'boys'  => 'CHI172',
//                    'girls' => 'CHI171'
//                ],
//                'chidon baseball'   => 'CHI173',
//                'chidon carry-on'   => 'CHI174',
//                'personalized name bracelet'    => 'CHI175',
//                'chidon pogo ball'  => 'CHI176',
//                'the jewish underground vol 1'  => 'CHI177',
//                'the jewish underground vol 2'  => 'CHI178',
//                'iron curtain vol 1'    => 'CHI179',
//                'iron curtain vol 2'    => 'CHI180',
//                'escape from europe'    => 'CHI181',
//                'the rebbe and the mazkir'  => 'CHI182',
//                'chidon towel'  => [
//                    'boys'  => 'CHI183',
//                    'girls' => 'CHI184'
//                ],
//                'chocolate mold'    => 'CHI185',
//                'backpack'  => [
//                    'boys'  => 'CHI187',
//                    'girls' => 'CHI186'
//                ],
//                'waffle maker'  => 'CHI188',
//                'chidon cookie cutters' => 'CHI189',
//                'reb binyomin kletzker' => 'CHI190',
//                'reb shmuel munkes' => 'CHI191',
//                'the slavita brothers'  => 'CHI192',
//                'reb hillel paritcher'  => 'CHI193'
//            ],
            'ambassador prizes' => [
                'ambassador prize' => 'CHI194'
            ],
            'enrollment prize'  => [
                'kop cards' => [
                    'kop cards #1' => 'CHI195',
                    'kop cards #2' => 'CHI196',
                    'kop cards #3' => 'CHI197',
                    'kop cards #4' => 'CHI198',
                    'kop cards #5' => 'CHI199'
                ]
            ],
            'gear'  => [
                'th sweater'  => [
                    'boys'  => [
                        'youth xs'      => 'CHI300',
                        'youth s'       => 'CHI301',
                        'youth m'       => 'CHI302',
                        'youth l'       => 'CHI303',
                        'adult xs'      => 'CHI305',
                        'adult s'       => 'CHI306',
                        'adult m'       => 'CHI307',
                        'adult l'       => 'CHI308',
                        'adult xl'      => 'CHI309',
                        'adult xxl'     => 'CHI310',
                        'adult xxxl'    => 'CHI311'
                    ],
                    'girls' => [
                        'youth xs'      => 'CHI312',
                        'youth s'       => 'CHI313',
                        'youth m'       => 'CHI314',
                        'youth l'       => 'CHI315',
                        'adult xs'      => 'CHI317',
                        'adult s'       => 'CHI318',
                        'adult m'       => 'CHI319',
                        'adult l'       => 'CHI320',
                        'adult xl'      => 'CHI321',
                        'adult xxl'     => 'CHI322',
                        'adult xxxl'    => 'CHI323'
                    ]
                ],
                'th cap'   => [
                    'boys'  => 'CHI324',
                    'girls' => 'CHI325'
                ],
                'th rank patch'    => [
                    'private'           => 'CHI326',
                    'sergeant'          => 'CHI327',
                    'sergeant major'    => 'CHI328',
                    'second lieutenant' => 'CHI329',
                    'first lieutenant'  => 'CHI330',
                    'captain'           => 'CHI331',
                    'major'             => 'CHI332',
                    'colonel'           => 'CHI333',
                    'general'           => 'CHI334',
                    '1* general'        => 'CHI335',
                    '2* general'        => 'CHI336',
                    '3* general'        => 'CHI337',
                    '4* general'        => 'CHI338',
                    '5* general'        => 'CHI339',
                ]
            ]
        ];
//        echo "Cat: $cat, Item: $item, Deep: $deep, Deeper: $deeper<br />";
        if (! empty($deeper)) return $item_ids[$cat][$item][$deep][$deeper];
        else if (! empty($deep)) return $item_ids[$cat][$item][$deep];
        else return $item_ids[$cat][$item];
    }
}