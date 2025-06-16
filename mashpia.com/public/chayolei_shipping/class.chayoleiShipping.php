<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.medalReport.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.rankReport.php';

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
        $categories = ['medals', 'ranks', 'name plates','chanuka', 'hei teves'];
        return $categories;
    }

    public function getItems() {
        $items['name plates'] = ['Name Plates'];
        $items['medals'] = ['Medals'];
        $items['ranks'] = ['Rank Medals', 'Rank Books'];
        $items['chanuka'] = $this->getYomTovItems('Chanuka');
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

    private function getYomTovItems($yom_tov) {
        $items = [];
        $sql = "
            SELECT 
                *
            FROM    
                mashpia_purchases.mivtzoim_items 
            WHERE
                yom_tov = :yom_tov 
            ORDER BY ord";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['yom_tov' => $yom_tov]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $items[] = $row['item'];
        }
        return $items;
    }

    private function getPurchases($gender, $school, $items, $yom_tov) {
        $purchases = [];
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
                mi.yom_tov = :yom_tov
                    AND p.year = :year";
        if ($gender == 'm') $sql .= " AND u.gender = 'M'";
        else if ($gender == 'f') $sql .= " AND u.gender = 'F'";
        if ($school > 0) $sql .= " AND u.school_id = " . $school;
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'year'      => $this->year,
            'yom_tov'   => $yom_tov
        ]);
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
                'cat'   => 'hei teves',
                'size'  => $info['size'],
                'qty'   => $row['qty']
            ];
        }

        return $purchases;
    }

    public function getHeiTeves($gender, $school, $items) {
        return $this->getPurchases($gender, $school, $items, 'Hei Teves');
    }

    public function getChanuka($gender, $school, $items) {
        return $this->getPurchases($gender, $school, $items, 'Chanuka');
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

    public function getMedals($gender, $school) {
        $medals = [];
        $medal_ids = $this->getMedalIDs();
        $subject_names = $this->getSubjectNames();
        $medal_names = $this->getMedalNames();
        $m = new MedalReport;
        $m->setDateToAll();
        //set up medals array
        $m->setSchoolId($school);
        $m->setMedalDetails(true, true, $gender);
        $medals_for_shipping = $m->getMedalsForShipping();
        foreach ($medals_for_shipping as $user_id => $subjects) {
            foreach ($subjects as $subject_id => $medal_ords) {
                foreach ($medal_ords as $row) {
                    $medal_ord = $row['medal_ord'];
                    $medal_id = $medal_ids[$subject_id][$medal_ord];
                    $medals[$user_id][] = [
                        'item'  => $subject_names[$subject_id] . ' ' . $medal_names[$medal_ord] . ' Medal',
                        'size'  => '',
                        'name'  => $row['first'] . ' ' . $row['last'],
                        'id'    => $medal_id,
                        'cat'   => 'medals',
                        'size'  => '',
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

    public function getRanks($gender, $school, $items) {
        $medals = [];
        $books = [];
        if (in_array('rank medals', $items)) {
            $medals = $this->getRankMedals($gender, $school);
        }
        if (in_array('rank books', $items)) {
            $books = $this->getRankBooks($gender, $school);
        }
        $ranks = $medals + $books;
        return $ranks;
    }

    private function getRankMedals($gender, $school) {
        $ranks = [];
        $rank_info = $this->getRankInfo();
        $rr = new RankReport;
        $rr->setDateToAll();
        $rr->setSchoolId($school);
        $rr->setRanks('byUser', 0, ' ', $gender);
        $rank_medals_for_shipping = $rr->getRankMedalsForShipping();
        if (empty($rank_medals_for_shipping)) return $ranks;
        $rank_medals_shipped = $this->getRankMedalsShipped();
        foreach ($rank_medals_for_shipping as $user_id => $rows) {
            foreach ($rows as $row) {
                // check if this user has already received this rank medal
                if (isset($rank_medals_shipped[$user_id]) && in_array($row['rank_ord'], $rank_medals_shipped[$user_id])) continue;
                $ranks[$user_id][] = [
                    'item'  => $rank_info[$row['rank_ord']]['rank_name'] . ' Rank Medal',
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

    private function getRankBooks($gender, $school) {
        $ranks = [];
        $rr = new RankReport;
        $rr->setDateToAll();
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
}