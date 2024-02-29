<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

class ParentShipping
{
    private $db, $year, $parents;

    public function __construct() {
        global $MASHPIA_DB;
        $this->db = $MASHPIA_DB;
        $this->year = GlobalSettings::getChidonYear();
        $this->parents = [];
    }

    public function getCategories() {
        $categories = [
            'chidon'   => 'Chidon',
        ];
        return $categories;
    }

    public function getItems() {
        $items = [
            'Chidon'   => ['Extra Purchases']
        ];
        return $items;
    }

    public function getStatus() {
        $info = [];
        $sql = "select * from parent_shipping where year = :year";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $info[$row['admin_id']][$row['item_id']] = $row;
        }
        return $info;
    }

    public function getChidon($items) {
        $info = [];
        if (in_array('extra purchases', $items)) {
            $info += $this->getExtraPurchases();
        }
        return $info;
    }

    private function getExtraPurchases() {
        $ep = [];
        $sql = "select * from extra_purchases ep 
                join purchase_addresses pa using (purchase_id) 
                join admins a using (admin_id) 
                where ep.year = :year";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            // add to parents array
            if (! isset($this->parents[$row['admin_id']])) {
                $this->parents[$row['admin_id']] = $row;
            }
            $id = $this->getItemID($row['item'], $row['type_of_sweater'], $row['size']);
            $ep[$row['admin_id']][] = [
                'id'    => $id,
                'item'  => $row['item'],
                'size'  => $row['size'] ?? '',
                'type'  => $row['type_of_sweater'] ?? '',
                'cat'   => 'extra purchases',
                'qty'   => $row['amount'],
            ];
        }
        return $ep;
    }

    public function getParents() {
        return $this->parents;
    }

    private function getItemID($cat, $item, $deep = '') {
        $item_ids = [
            'sweater'   => [
                'bubby' => [
                    'xs'            => 'CHI095',
                    'small'         => 'CHI091',
                    'medium'        => 'CHI092',
                    'large'         => 'CHI093',
                    'xl'            => 'CHI094'
                ],
                'zaidy' => [
                    'xs'        => 'CHI096',
                    'small'     => 'CHI097',
                    'medium'    => 'CHI098',
                    'large'     => 'CHI099',
                    'xl'        => 'CHI100'
                ],
                'mother' => [
                    'xs'        => 'CHI102',
                    'small'     => 'CHI103',
                    'medium'    => 'CHI104',
                    'large'     => 'CHI105',
                    'xl'        => 'CHI106'
                ],
                'father' => [
                    'xs'        => 'CHI108',
                    'small'     => 'CHI109',
                    'medium'    => 'CHI110',
                    'large'     => 'CHI111',
                    'xl'        => 'CHI112'
                ]
            ],
            'celeb_box' => 'CHI115'
        ];

        if (! empty($deep)) return $item_ids[$cat][$item][$deep];
        else if (! empty($item)) return $item_ids[$cat][$item];
        else return $item_ids[$cat];
    }
}