<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

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
        $categories = [];
        if (isset($_COOKIE['naftoli'])) $categories[] = 'hei teves';
        return $categories;
    }

    public function getItems() {
        $items = [];
        if (isset($_COOKIE['naftoli'])) $items['hei teves'] = $this->getHeiTevesItems();
        return $items;
    }

    public function getStatus() {
        $info = [];
        $sql = "select * from th_chidon_shipping where year = :year";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $info[$row['user_id']][$row['item_id']] = $row;
        }
        return $info;
    }

    public function getHeiTevesItems() {
        $items = [];
        $sql = "
            SELECT 
                *
            FROM    
                mashpia_purchases.mivtzoim_items 
            WHERE
                yom_tov = 'Hei Teves' 
            ORDER BY ord";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $items[] = $row['item'];
        }
        return $items;
    }

    public function getHeiTeves($gender, $school, $items) {
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
                mi.yom_tov = 'Hei Teves'
                    AND p.year = :year";
        if ($gender == 'M') $sql .= " AND u.gender = 'M'";
        else if ($gender == 'F') $sql .= " AND u.gender = 'F'";
        if ($school > 0) $sql .= " AND u.school_id = " . $school;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            if (count($items) && !in_array(strtolower($row['item']), $items)) continue;
            $purchases[$row['user_id']][] = [
                'item'  => $row['item'],
                'size'  => '',
                'name'  => '',
                'id'    => $row['shipping_code'],
                'cat'   => 'hei teves',
                'size'  => $row['size'],
            ];
        }

        return $purchases;
    }
}