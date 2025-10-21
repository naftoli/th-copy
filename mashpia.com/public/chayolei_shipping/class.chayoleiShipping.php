<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.medalReport.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.rankReport.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.hachayol.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.birthdayEn.php';

/**
 * Class ChayoleiShipping
 *
 * list of functions needed for figuring out what to ship
 * all functions need to return an array or items
 * each item needs to have item/qty/size/color/name keys
 */

class ChayoleiShipping
{
    private $db, $year, $schools, $grades, $users;

    public function __construct() {
        global $MASHPIA_DB;
        $this->db = $MASHPIA_DB;
        $this->year = GlobalSettings::getChidonRegYear();
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

    public function getCategories() {
        $categories = ['birthdays', 'hachayols', 'medals', 'ranks', 'name plates','mivtzoim', 'hei teves'];
        return $categories;
    }

    public function getItems() {
        $items['birthdays'] = ['Birthday Envelope', 'Boys Birthday Card', 'Girls Birthday Card', 'Kapital Card Age 6', 'Kapital Card Age 7', 'Kapital Card Age 8', 'Kapital Card Age 9', 'Kapital Card Age 10', 'Kapital Card Age 11', 'Kapital Card Age 12'];
        $items['hachayols'] = ['Hachayols'];
        $items['name plates'] = ['Name Plates'];
        $items['medals'] = ['Medals'];
        $items['ranks'] = ['Rank Medals', 'Rank Books'];
        $items['mivtzoim'] = $this->getYomTovItems(['Mivtza Lulav', 'Chanuka']);
        $items['hei teves'] = $this->getYomTovItems('Hei Teves');
        return $items;
    }

    public function getStatus() {
        $info = [];
        $sql = "select * from th_chidon_shipping where year = :year";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $info[$row['user_id']][$row['item_id']][$row['item_num']] = $row;
        }
        return $info;
    }

    public function getHachayols($gender, $school, $items) {
        $hachayols = [];
        $h = new Hachayol();
        $h->setSchools($school);
        $rows = $h->runSql($gender, $school, $this->year);
        foreach ($rows as $row) {
            $hachayols[$row['user_id']][] = [
                'item'  => 'Hachayol',
                'size'  => '',
                'name'  => '',
                'id'    => 'HACH01',
                'cat'   => 'hachayols',
                'size'  => '',
                'qty'   => 1
            ];
        }
        return $hachayols;
    }

    private function getYomTovItems($yom_tov) {
        $items = [];
        $sql = "
            SELECT 
                *
            FROM    
                mashpia_purchases.mivtzoim_items 
            WHERE
                yom_tov IN (:yom_tov) 
            ORDER BY ord";
        $stmt = $this->db->prepare($sql);
        if (! is_array($yom_tov)) $yom_tov = [$yom_tov];
        foreach ($yom_tov as $yom_tov) {
            $stmt->execute(['yom_tov' => $yom_tov]);
            // $stmt->debugDumpParams();
            $rows = $stmt->fetchAll();
            foreach ($rows as $row) {
                $items[] = $row['item'];
            }
        }
        return $items;
    }

    private function getPurchases($gender, $school, $items) {
        $purchases = [];
        $itemsList = [];
        foreach ($items as $item) {
            $itemsList[] = ucwords($item);
        }
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
                users u USING (user_id)
            WHERE
                mi.item in ('" . implode("','", $itemsList) . "')
                    AND p.year = :year";
        if ($gender == 'm') $sql .= " AND u.gender = 'M'";
        else if ($gender == 'f') $sql .= " AND u.gender = 'F'";
        if ($school > 0) $sql .= " AND u.school_id = " . $school;
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'year'      => $this->year
        ]);
        // $stmt->debugDumpParams();
        $rows = $stmt->fetchAll();
        // find all items related to same individual and add up all the qtys of that item into one entity
        $userItems = [];
        foreach ($rows as $row) {
            $userKey = $row['user_id'] . '-' . $row['item'];
            if (!isset($userItems[$userKey])) {
                $userItems[$userKey] = [
                    'item'  => $row['item'],
                    'qty'   => $row['qty'],
                    'info'  => $row
                ];
            } else {
                $userItems[$userKey]['qty'] += $row['qty'];
            }
        }
        $rows = [];
        foreach ($userItems as $key => $details) {
            $user_id = explode('-', $key)[0];
            $item = $details['item'];
            $qty = $details['qty'];
            $info = $details['info'];
            $rows[] = [
                'user_id'   => $user_id,
                'item'      => $item,
                'qty'       => $qty,
                'info'      => $info
            ];
        }
        foreach ($rows as $row) {
            if (count($items) && !in_array(strtolower($row['item']), $items)) continue;
            $info = $row['info'];
            $purchases[$row['user_id']][] = [
                'item'  => $row['item'],
                'size'  => '',
                'name'  => '',
                'id'    => $info['shipping_code'],
                'cat'   => 'mivtzoim',
                'size'  => $info['size'],
                'qty'   => $row['qty']
            ];
        }

        return $purchases;
    }

    public function getHeiTeves($gender, $school, $items) {
        return $this->getPurchases($gender, $school, $items);
    }

    public function getMivtzoim($gender, $school, $items) {
        return $this->getPurchases($gender, $school, $items);
    }

    public function getNamePlates($gender, $school, $items) {
        $purchases = [];
        $sql = "
            SELECT 
                np.*,
                u.first_he,
                u.last_he
            FROM
                name_plates np 
                JOIN users u ON np.user_id = u.user_id 
            WHERE
                np.year = :year ";
        if ($gender == 'm') $sql .= " AND u.gender = 'M'";
        else if ($gender == 'f') $sql .= " AND u.gender = 'F'";
        if ($school > 0) $sql .= " AND u.school_id = " . $school;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $purchases[$row['user_id']][] = [
                'item'  => 'Name Plate',
                'size'  => '',
                'name'  => $row['first_he'] . ' ' . $row['last_he'],
                'id'    => 'NP101',
                'cat'   => 'name plates',
                'size'  => '',
                'qty'   => $row['qty']
            ];
        }
        return $purchases;
    }

    public function getMedals(string $gender, int $school, array $items, string $date_limit = '') {
        $medals = [];
        $medal_ids = $this->getMedalIDs();
        $subject_names = $this->getSubjectNames();
        $medal_names = $this->getMedalNames();
        $m = new MedalReport;
        if (!empty($date_limit)) {
            $dates = explode(':', $date_limit);
        } else {
            $dates = explode(':', $_POST['medals_dates']);
        } 
        $m->overrideDates($dates[0], $dates[1]);
        //set up medals array
        if ($school > 0) {
            $m->setSchoolId($school);
        }
        $m->setMedalDetails(true, $gender);
        $medals_for_shipping = $m->getMedalsForShipping();
        foreach ($medals_for_shipping as $user_id => $subjects) {
            foreach ($subjects as $subject_id => $medal_ords) {
                foreach ($medal_ords as $row) {
                    $medal_ord = $row['medal_ord'];
                    $medal_id = $medal_ids[$subject_id][$medal_ord];                    
                    $medals[$user_id][] = [
                        'item'  => $subject_names[$subject_id],
                        'size'  => '',
                        'name'  => $row['first'] . ' ' . $row['last'],
                        'id'    => $medal_id,
                        'cat'   => 'medals',
                        'color' => $medal_names[$medal_ord],
                        'qty'   => 1
                    ];
                }
            }
        }
        return $medals;
    }

    private function getMedalIDs() {
        $medal_ids = [];
        $sql = "select * from medals_subjects";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $medal_ids[$row['subject_id']][$row['medal_ord']] = $row['shipping_code'];
        }
        return $medal_ids;
    }

    private function getSubjectNames() {
        $subjects = [];
        $sql = "select * from subjects";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            if ($row['subject_name'] == 'שבת מברכים תהילים') $row['subject_name'] = 'WWTC';
            $subjects[$row['subject_id']] = $row['subject_name'];
        }
        return $subjects;
    }

    private function getMedalNames() {
        $medal_names = [];
        $sql = "select * from medals";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $medal_names[$row['medal_ord']] = $row['medal_name'];
        }
        return $medal_names;
    }

    public function getRanks(string $gender, int $school, array $items) {
        $medals = [];
        $books = [];
        $dates = explode(':', $_POST['ranks_dates']);
        if (in_array('rank medals', $items)) {
            $medals = $this->getRankMedals($gender, $school, $dates);
        }
        if (in_array('rank books', $items)) {
            $books = $this->getRankBooks($gender, $school, $dates);
        }
        $ranks = $medals + $books;
        return $ranks;
    }

    public function getRankMedals(string $gender, int $school, array $dates) {
        $ranks = [];
        $rank_info = $this->getRankInfo();
        $rr = new RankReport;
        $rr->overrideDates($dates[0], $dates[1]);
        $rr->setSchoolId($school);
        $rr->setRanks('byUser', 0, ' ', $gender, true);
        $rank_medals_for_shipping = $rr->getRankMedalsForShipping();
        if (empty($rank_medals_for_shipping)) return $ranks;
        $rank_medals_shipped = $this->getRankMedalsShipped();
        foreach ($rank_medals_for_shipping as $user_id => $rows) {
            foreach ($rows as $row) {
                // check if this user has already received this rank medal from before the time we included this in the shipping report
                if (isset($rank_medals_shipped[$user_id]) && in_array($row['rank_ord'], $rank_medals_shipped[$user_id])) continue;
                $ranks[$user_id][] = [
                    'item'  => $rank_info[$row['rank_ord']]['rank_name'],
                    'size'  => '',
                    'name'  => $row['first'] . ' ' . $row['last'],
                    'id'    => $rank_info[$row['rank_ord']]['shipping_code'],
                    'cat'   => 'ranks',
                    'size'  => '',
                    'qty'   => 1
                ];
            }
        }
        return $ranks;
    }

    private function getRankInfo() {
        $rank_info = [];
        $sql = "select * from ranks";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $rank_info[$row['rank_ord']] = $row;
        }
        return $rank_info;
    }

    private function getRankMedalsShipped() {
        $rank_medals_shipped = [];
        $sql = "select * from rank_medals_shipped";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $rank_medals_shipped[$row['user_id']][] = $row['rank_ord'];
        }
        return $rank_medals_shipped;
    }

    public function getRankBooks(string $gender, int $school, array $dates) {
        $ranks = [];
        $rr = new RankReport;
        $rr->overrideDates($dates[0], $dates[1]);
        $rr->setSchoolId($school);
        $books = $rr->getBooksToSend($gender, true);
        if (empty($books)) return $ranks;
        $books_shipped = $rr->getRankBooksShipped();
        $user_info = $rr->getUserInfo();
        foreach ($books as $user_id => $rows) {
            $info = $user_info[$user_id];
            foreach ($rows as $book) {
                // check if this user has already received this rank book
                if (isset($books_shipped[$user_id]) && in_array($book, $books_shipped[$user_id])) continue;
                $ranks[$user_id][] = [
                    'item'  => 'Rank Book #' . $book,
                    'size'  => '',
                    'name'  => $info['first'] . ' ' . $info['last'],
                    'id'    => 'RB' . $book,
                    'cat'   => 'ranks',
                    'size'  => '',
                    'qty'   => 1
                ];
            }
        }
        return $ranks;
    }

    public function getBirthdays($gender, $school, $items) {
        $birthdays = [];
        // all registered users in school
        $users = $this->getUsers($school, $gender);
        if (empty($users)) return [];

        foreach ($items as $item) {
            switch ($item) {
                case 'birthday envelope':
                    $b = $this->getBirthdayEnvelope($users);
                    break;
                case 'boys birthday card':
                    $b = $this->getBoysBirthdayCard($users);
                    break;
                case 'girls birthday card':
                    $b = $this->getGirlsBirthdayCard($users);
                    break;
                case 'kapital card age 6':
                case 'kapital card age 7':
                case 'kapital card age 8':
                case 'kapital card age 9':
                case 'kapital card age 10':
                case 'kapital card age 11':
                case 'kapital card age 12':
                    $b = $this->getKapitalCard($users, $item);
                    break;
                default:
                    $b = [];
                    break;
            }
            foreach ($users as $user) {
                if (isset($b[$user['user_id']])) {
                    foreach ($b[$user['user_id']] as $item) {
                        $birthdays[$user['user_id']][] = $item;
                    }
                }
            }
        }
        // echo "<pre>"; print_r($birthdays); echo "</pre>"; 
        return $birthdays;
    }

    private function getBirthdayEnvelope($users) {
        foreach ($users as $user) {
            $birthdays[$user['user_id']][] = [
                'item'  => 'Birthday Envelope',
                'size'  => '',
                'name'  => $user['first'] . ' ' . $user['last'],
                'id'    => 'BE01',
                'cat'   => 'birthdays',
                'size'  => '',
                'qty'   => 1
            ];
        }
        return $birthdays;
    }

    private function getBoysBirthdayCard($users) {
        foreach ($users as $user) {
            if (strtolower($user['gender']) != 'm') continue;
            $birthdays[$user['user_id']][] = [
                'item'  => 'Boys Birthday Card',
                'size'  => '',
                'name'  => $user['first'] . ' ' . $user['last'],
                'id'    => 'BBC01',
                'cat'   => 'birthdays',
                'size'  => '',
                'qty'   => 1
            ];
        }
        return $birthdays;
    }

    private function getGirlsBirthdayCard($users) {
        foreach ($users as $user) {
            if (strtolower($user['gender']) != 'f') continue;
            $birthdays[$user['user_id']][] = [
                'item'  => 'Girls Birthday Card',
                'size'  => '',
                'name'  => $user['first'] . ' ' . $user['last'],
                'id'    => 'GBC01',
                'cat'   => 'birthdays',
                'size'  => '',
                'qty'   => 1
            ];
        }
        return $birthdays;
    }

    private function getKapitalCard($users, $item) {
        $birthdays = [];
        $itemInfo = explode(' ', $item);
        $age_card = intval($itemInfo[3]);
        foreach ($users as $user) {
            $age = intval($user['age']);
            if ($age == $age_card) {
                $birthdays[$user['user_id']][] = [
                    'item'  => 'Kapital Card Age ' . $age_card,
                    'size'  => '',
                    'name'  => $user['first'] . ' ' . $user['last'],
                    'id'    => 'KC' . $age_card,
                    'cat'   => 'birthdays',
                    'size'  => '',
                    'qty'   => 1
                ];
            }
        }
        return $birthdays;
    }

    private function getUsers($school, $gender) {
        $users = [];
        $sql = "SELECT 
                    u.*, b.date_tasks_mission_id 
                FROM
                    users u
                        JOIN
                    birthdays b USING (user_id)
                WHERE
                    user_registered IS NOT NULL
                        AND user_registered != ''
                        AND user_registered != '0000-00-00'
                        AND school_id = :school";
        if ($gender == 'm') $sql .= " AND gender = 'M'";
        else if ($gender == 'f') $sql .= " AND gender = 'F'";
        $sql .= " GROUP BY user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['school' => $school]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $user) {
            // check if child has a birthday between ages 6 and 12
            $b = new BirthdayEn($user['user_id']);
            $age = intval($b->calculateAge($user)[0]);
            if ($age >= 6 && $age <= 12) {
                $user['age'] = $age;
                $users[] = $user;
            }
        }
        return $users;
    }
}