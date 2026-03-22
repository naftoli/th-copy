<?php
require_once __DIR__ . '/../class.globalSettings.php';
require_once __DIR__ . '/../api/header/db.php';

class ComprehensiveShipmentReport {
    private $year;
    private $school_id;
    private $check_for_payment;

    public function __construct($school_id, $check_for_payment = true) {
        $this->year = GlobalSettings::getChidonYear();
        $this->school_id = intval($school_id);
        $this->check_for_payment = $check_for_payment;
    }

    public function getFamilyShipmentDetails($max_shipment_number) {
        global $MASHPIA_DB;

        // 1. Get all eligible children and their family info
        $sql_children = "
            SELECT 
                a.admin_id, a.last AS family_name, a.admin_address1, a.admin_address2, 
                a.admin_city, a.admin_state, a.admin_postal, a.admin_country,
                u.user_id, u.first AS child_first_name, u.last AS child_last_name, u.user_registered 
            FROM admins a
            JOIN admin_auths aa USING (admin_id)
            JOIN users u ON aa.id = u.user_id 
            JOIN user_registration ur USING (user_id) 
            JOIN hachayols_to_give htg ON htg.user_id = u.user_id AND htg.year = ur.year 
            WHERE aa.auth = 'user' AND u.school_id = :school_id 
              AND u.user_registered > 0 AND ur.year = :year;
        ";
        $stmt_children = $MASHPIA_DB->prepare($sql_children);
        $stmt_children->execute(['school_id' => $this->school_id, 'year' => $this->year]);
        // $stmt_children->debugDumpParams();
        $all_children = $stmt_children->fetchAll(PDO::FETCH_ASSOC);

        if (empty($all_children)) {
            return [];
        }

        // 2. Get all shipment data for these children up to the max shipment number        
        $user_ids = array_map(function($child) { return $child['user_id']; }, $all_children);
        $sql_shipped = "
            SELECT user_id, shipment_number
            FROM th_chidon_shipping
            WHERE year = :year AND item_id = 'HACH01' AND shipment_number <= :max_shipment_number
              AND user_id IN (" . implode(',', $user_ids) . ");
        ";
        $stmt_shipped = $MASHPIA_DB->prepare($sql_shipped);
        $stmt_shipped->execute(['year' => $this->year, 'max_shipment_number' => $max_shipment_number]);
        
        $shipments = [];
        while ($row = $stmt_shipped->fetch(PDO::FETCH_ASSOC)) {
            $shipments[$row['user_id']][$row['shipment_number']] = true;
        }

        // 3. Aggregate data by family
        $removed = 0;
        $families = [];
        foreach ($all_children as $index => $child) {
            $admin_id = $child['admin_id'];

            if ($this->check_for_payment && !$this->paidForShipping($admin_id)) {
                $removed++;
                continue;
            }

            if (!isset($families[$admin_id])) {
                $families[$admin_id] = [
                    'family_id' => $admin_id,
                    'family_name' => $child['family_name'],
                    'address1' => $child['admin_address1'],
                    'address2' => $child['admin_address2'],
                    'city' => $child['admin_city'],
                    'state' => $child['admin_state'],
                    'zip' => $child['admin_postal'],
                    'country' => $child['admin_country'],
                    'children' => []
                ];
            }

            $child_shipments = [];
            for ($i = 1; $i <= $max_shipment_number; $i++) {
                $child_shipments[$i] = isset($shipments[$child['user_id']][$i]);
            }

            $families[$admin_id]['children'][] = [
                'name' => $child['child_first_name'] . ' ' . $child['child_last_name'],
                'registered' => $child['user_registered'],
                'shipments' => $child_shipments
            ];
        }

        foreach ($families as $admin_id => $more) {
            foreach ($more['children'] as $index => $child) {
                foreach ($child['shipments'] as $shipment_number => $is_shipped) {
                    if ($index == 0 && $shipment_number == 1 && !$is_shipped) {
                        $families[$admin_id]['children'][$index]['shipments'][$shipment_number] = $this->checkShipmentOne($admin_id);
                    }
                }
            }
        }
        // echo $removed . " families removed<br />";
        // echo count($families) . " families found<br />";

        // Sort by family name
        usort($families, function($a, $b) {
            return strcmp($a['family_name'], $b['family_name']);
        });

        return $families;
    }

    private function checkShipmentOne($admin_id) {
        $family_ids = [
            172247, 195746, 71578, 168952, 175042, 189749, 194010, 197148, 198983, 199754,
            200132, 197476, 193120, 150479, 5794, 182987, 193229, 686, 1145, 3574,
            71046, 71487, 119027, 140768, 150580, 168402, 172411, 194120, 196727, 197335,
            198225, 199255, 199491, 199533, 200381, 200847, 200973, 200998, 118, 436,
            1264, 6205, 6356, 6459, 6561, 6646, 6725, 6804, 6823, 6848,
            7118, 8224, 71227, 71314, 71580, 128799, 129140, 129268, 129283, 129574,
            140230, 140771, 141151, 150317, 150658, 150844, 167609, 167705, 167857, 167963,
            168078, 168294, 169109, 169281, 169520, 170626, 170928, 171631, 172009, 172192,
            172237, 172269, 172307, 172627, 173897, 174022, 174986, 175122, 175218, 175276,
            175309, 175427, 175559, 175665, 175845, 175952, 177864, 178463, 178830, 178946,
            179238, 179460, 179692, 179729, 179730, 179731, 179732, 179749, 179754, 180026,
            180166, 180359, 180419, 180669, 180825, 180949, 181042, 181144, 181265, 181385,
            181398, 181498, 181565, 181579, 181626, 181676, 181716, 181719, 181742, 181808,
            181892, 181974, 182023, 182103, 182189, 182283, 182332, 182360, 182451, 182497,
            182542, 182579, 182633, 182646, 182768, 182769, 182822, 182880, 182998, 183085,
            183096, 183162, 183184, 183250, 183331, 183397, 183420, 183422, 183515, 183613,
            183721, 183829, 183948, 184027, 184167, 184233, 184365, 184403, 184459, 184517,
            184583, 184594, 184677, 184797, 184823, 184877, 184960, 185023, 185121, 185199,
            185320, 185379, 185466, 185584, 185631, 185779, 185831, 185906, 185999, 186099,
            186170, 186288, 186318, 186429, 186467, 186573, 186674, 186761, 186818, 186913,
            187003, 187071, 187154, 187253, 187357, 187401, 187523, 187612, 187752, 187828,
            187936, 188067, 188159, 188294, 188395, 188506, 188601, 188742, 188823, 188935,
            189027, 189138, 189261, 189371, 189489, 189600, 189701, 189810, 189925, 190074,
            190161, 190299, 190400, 190532, 190659, 190726, 190865, 190988, 191115, 191211,
            191344, 191472, 191590, 191725, 191871, 191999, 192115, 192270, 192392, 192523,
            192684, 192819, 192957, 193099, 193286, 193385, 193534, 193689, 193802, 193940,
            194088, 194245, 194389, 194533, 194695, 194834, 194985, 195153, 195302, 195457,
            195627, 195800, 195966, 196139, 196317, 196502, 196679, 196868, 197048, 197238,
            197421, 197618, 197820, 198031, 198234, 198444, 198654, 198863, 199084, 199314,
            199536, 199774, 200003, 200244, 200328, 200330, 200349, 200356, 200362, 200544,
            200570, 200799, 200904, 200914
        ];

        return in_array($admin_id, $family_ids);
    }

    private function paidForShipping($admin_id) {
        global $MASHPIA_DB;

        if ($this->school_id == 61) {
            $type = 'THMS%';
        } else if ($this->school_id == 269) {
            $type = 'THAK%';
        } else {
            return false;
        }
        $sql = "SELECT * FROM registration_charges WHERE type like :type and user_id in (
            SELECT id FROM admin_auths WHERE admin_id = :admin_id) and year = :year";
        $result = $MASHPIA_DB->prepare($sql);
        $result->execute(['admin_id' => $admin_id, 'year' => $this->year, 'type' => $type]);
        $rows = $result->fetchAll(PDO::FETCH_ASSOC);
        return count($rows) > 0;
    }
}
