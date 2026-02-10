<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

class ParentShipping
{
    private $db, $year, $parents;

    public function __construct($yr = 0) {
        global $MASHPIA_DB;
        $this->db = $MASHPIA_DB;
        $this->year = $yr ?? GlobalSettings::getChidonRegYear();
        $this->parents = [];
    }

    public function setYear($yr) {
        $this->year = $yr;
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
        global $ship_to;

        $ep = [];
        $sql = "select * from extra_purchases ep 
                join purchase_addresses pa using (purchase_id) 
                join admins a using (admin_id) 
                where ep.year = :year";
        if ($ship_to == 'domestic') {
            $sql .= " and pa.country = 'USA'";
        } else if ($ship_to == 'intl') {
            $sql .= " and pa.country != 'USA'";
        }
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
                'info'  => $row
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

    public function createCSV($info) {
        global $translations;
        $i = 0;
        $csv[$i++] = ['Order #', 'Recipient Full Name', 'Recipient First Name', 'Recipient Last Name', 'Recipient Phone',
            'Recipient Company', 'Address Line 1', 'Address Line 2', 'Address Line 3', 'City', 'State', 'Postal Code',
            'Country Code', 'Item SKU', 'Item Name / Title', 'Item Name 2', 'Item Quantity', 'Item Options', 'Recipient Email', 'Custom Field 1', 'Internal Notes', 'Custom Field 2'];
        $csv[$i++] = ['Family ID', 'Parent Full Name', 'Parent First Name', 'Parent Last Name', 'Recipient Phone', 'Shipping Type',
            'Address Line 1', 'Address Line 2', 'Address Line 3', 'City', 'State', 'Postal Code', 'Country Code', 'CHI Number',
            'Full Item Name', 'Item Warehouse Location', 'Quantity', 'Recipient Email', 'Comments', 'City, State, Country'];
        foreach ($info as $cat => $details) {
            // Skip status category - it has a different structure (metadata, not items)
            if ($cat == 'status') continue;
            foreach ($details as $admin_id => $items) {
                // $child_count = count($items);
                foreach ($items as $idx => $item) {
                    $admin = $item['info'];
                    $phone = $admin['admin_phone_mobile'] ?? $admin['admin_phone_work'] ?? $admin['admin_phone_home'] ?? '';
                    $phone = makeTextForExcel($phone);
                    $first = empty($admin['father']) ? $admin['first'] : ($admin['father'] . ' ' . $admin['mother']);
    
                    $qty = $item['qty'] ?? 1;
                    $country = $admin['admin_country'];
                    $usa = ['USA', 'US', 'United States', 'U.S.A', 'Unites States of America'];
                    if (in_array($country, $usa)) $shipping = ' USA';
                    else $shipping = ' INTL';
                    
                    $translation = $translations[$item['id']] ?? '';
                    $itemDesc = $item['type'] ? ($item['type'] . ' ') : '';
                    // if ($item['name']) $itemDesc .= "Personalized ";
                    $itemDesc .= $item['item'];
                    // if ($item['color']) $itemDesc .= ", " . $item['color'];
                    if ($item['size']) $itemDesc .= ", size: " . $item['size'];
                    $address = $admin['address'] ?? $admin['admin_address1'];
                    $address2 = $admin['address'] ? '' : ($admin['admin_address2'] ?? '');
                    $address3 = $admin['address'] ? '' : ($admin['admin_address3'] ?? '');
                    $city = $admin['city'] ?? $admin['admin_city'];
                    $state = $admin['state'] ?? $admin['admin_state'];
                    $zip = $admin['zip'] ?? $admin['admin_postal'];
                    $country = $admin['country'] ?? $admin['admin_country'];
    
                    $csv[$i++] = [$admin['admin_id'], ($first . ' ' . $admin['last']), $admin['first'], $admin['last'],
                        $phone, $shipping, $address, $address2, $address3, $city,
                        $state, $zip, $country, $item['id'], $itemDesc, $translation, 
                        $qty, $admin['admin_email'], '', ($city . ', ' . $state . ', ' . $country)];
                }
            }
        }
    
        return $csv;
    }
    
    public function createFile($name, $info) {
        $fp = fopen($name, "w");
        // Add the UTF-8 BOM
        fputs($fp, "\xEF\xBB\xBF"); 
        if (is_array($info)) {
            foreach ($info as $fields) {
                fputcsv($fp, $fields);
            }
        } else {
            fputs($fp, $info);
        }
        fclose($fp);
    }
    
    public function createZip($files, $filename) {
        $zip = new ZipArchive;
        $success = $zip->open($filename, ZipArchive::CREATE);
        if ($success !== true) {
            exit("cannot open <$filename>\n");
        }
        foreach($files as $file) {
            $zip->addFromString($file, file_get_contents($file));
            unlink($file);
        }
        $zip->close();
    }
    
    public function downloadFile($filename) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filename));
        flush(); // Flush system output buffer
        readfile($filename);
        unlink($filename);
    }

    public function getSpanishTranslations() {
        $sql = "select item_id, spanish from chidon_items_translations";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $translations[$row['item_id']] = $row['spanish'];
        }
        return $translations;
    }
}