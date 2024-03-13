<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/raffles/shared/classes/Constants.php';
use raffles\shared\Constants as Constants;

class SchoolShipping
{
    private $db, $year, $schools, $prizes, $raffles;

    public function __construct() {
        global $MASHPIA_DB;
        $this->db = $MASHPIA_DB;
        $this->year = GlobalSettings::getChidonYear();
    }

    public function getCategories() {
        $categories = [
            'raffles'   => 'Raffles',
            'chidon'    => 'Chidon'
        ];
        return $categories;
    }

    public function getItems() {
        $items = [
            'Raffles'   => ['5M Raffle', '60M Raffle', 'Auction 5783'],
            'Chidon'    => ['Celeb Box Items', 'Sweaters']
        ];
        return $items;
    }

    public function getStatus() {
        $info = [];
        $sql = "select * from school_shipping where year = :year";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $info[$row['school_id']][$row['item_id']] = $row;
        }
        return $info;
    }

    public function getRaffles(array $schools, array $items) {
        $this->schools = $schools;
        $info = [];
        foreach ($items as $type) {
            $this->setPrizes($type);
            $this->setRafflesInfo($type);
            $school_prizes = $this->getSchoolPrizes($type);
            foreach ($school_prizes as $school_id => $raffles) {
                foreach ($raffles as $raffle_id => $prizes) {
                    foreach ($prizes as $prize) {
                        $prize_info = $this->prizes[$prize];
                        $prize_name = $prize_info['name'];
                        $raffle_name = $this->raffles[$raffle_id];
                        // check if this prize is already in the list
                        $found = false;
                        if (isset($info[$school_id])) {
                            foreach ($info[$school_id] as &$item) {
                                if ($item['id'] == $prize_info['code'] && $item['cat'] == $raffle_name) {
                                    $found = true;
                                    $item['qty']++;
                                    break;
                                }
                            }
                        }
                        if (!$found) {
                            $info[$school_id][] = [
                                'id'    => $prize_info['code'],
                                'item'  => $prize_name,
                                'cat'   => $raffle_name,
                                'qty'   => 1
                            ];
                        }
                    }
                }
            }
        }
        return $info;
    }

    private function getSchoolPrizes(string $type) {
        $prizes = [];
        switch ($type) {
            case '5m raffle':
                $prizes = $this->getSchoolWeeklyPrizes();
                break;
            case '60m raffle':
                $prizes = $this->getSchoolMonthlyPrizes();
                break;
            case 'auction 5783':
                $prizes = $this->getSchoolAuctionPrizes();
                break;
        }
        return $prizes;
    }

    private function setPrizes(string $type) {
        $prizes = [];
        switch ($type) {
            case '5m raffle':
                $qry = "select prize_id, name as prize_name, shipping_code from prizes";
                break;
            case '60m raffle':
            case 'auction 5783':
                $qry = "select prize_id, prize_name, shipping_code from prizes_auction";
                break;
        }
        $stmt = $this->db->query($qry);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $prizes[$row['prize_id']] = [
                'name'  =>  $row['prize_name'],
                'code'  =>  $row['shipping_code']
            ];
        }
        $this->prizes = $prizes;
    }

    private function setRafflesInfo(string $type) {
        switch ($type) {
            case '5m raffle':
                $this->setWeeklyRaffles();
                break;
            case '60m raffle':
                $this->setMonthlyRaffles();
                break;
            case 'auction 5783':
                $this->setAuction();
                break;
        }
    }

    private function setWeeklyRaffles() {
        $raffles = [];
        // first get last raffle from prev year
        $sql = "select * from raffles where type = 'weekly' and year = :year order by raffle_id desc limit 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'year'  => $this->year - 1,
        ]);
        $row = $stmt->fetch();
        $raffles[$row['raffle_id']] = $row['name'];
        // then get all raffles from this year
        $sql = "select * from raffles where type = 'weekly' and year = :year";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'year'  => $this->year,
        ]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $raffles[$row['raffle_id']] = $row['name'] . ' (' . $row['year'] . ')';
        }
        $this->raffles = $raffles;
    }

    private function setMonthlyRaffles() {
        $raffles = [];
        // get last year's 4th 60m and the first three of this year's 60m
        $lastYr = $this->year - 1;
        $curYr = $this->year;
        $sql = "select * from raffles where type = 'monthly' and year in ($lastYr, $curYr) order by raffle_id";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            // filter out last year's 4th 60m and this year's first three 60m for monthly raffles
            if ($row['year'] == ($this->year - 1) && strpos($row['name'], '4') === false) continue;
            else if ($row['year'] == $this->year && strpos($row['name'], '4') !== false) continue;
            $raffles[$row['raffle_id']] = $row['name'] . ' (' . $row['year'] . ')';
        }
        $this->raffles = $raffles;
    }

    private function setAuction() {
        $sql = "select * from auctions where year = :year order by auction_id desc limit 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => ($this->year - 1)]); // auction is for end of last yr
        $row = $stmt->fetch();
        $auction[$row['auction_id']] = $row['auction_name'];
        $this->raffles = $auction;
    }

    private function getSchoolWeeklyPrizes() {
        $info = [];

        // get ratio of winners per school
        $ratio = Constants::get_raffle_school_max_winners();
        $school_ids = array_keys($ratio);

        // get the prize for each raffle
        $rafflePrizes = [];
        $sql = "SELECT 
                    raffle_id, prize_id  
                FROM
                    raffle_prizes
                WHERE
                    raffle_id IN (" . implode(',', array_keys($this->raffles)) . ")";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $rafflePrizes[$row['raffle_id']] = $row['prize_id'];
        }

        foreach ($school_ids as $school_id) {
            $max = $ratio[$school_id];
            foreach ($rafflePrizes as $raffle_id => $prize_id) {
                for ($i = 0; $i < $max; $i++) {
                    $info[$school_id][$raffle_id][] = $prize_id;
                }
            }
        }
        return $info;
    }

    private function getSchoolMonthlyPrizes() {
        $info = [];
        $sql = "select * from raffles_monthly where raffle_id in (" .
            implode(',', array_keys($this->raffles)) . ") and school_id in (" .
            implode(',', $this->schools) . ")";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $info[$row['school_id']][$row['raffle_id']][] = $row['prize_id'];
        }
        return $info;
    }

    private function getSchoolAuctionPrizes() {
        $info = [];
        $auction_id = key($this->raffles);
//        $sql = "select * from school_auction_prizes where auction_id = :auction and school_id in (" .
//            implode(',', $this->schools) . ")";
        // use existing winners to determine how many prizes each school gets
        $sql = "select u.school_id, aw.user_id, aw.prize_id  
                from auction_winners aw 
                join users u using (user_id) 
                where auction_id = :auction";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['auction' => $auction_id]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $info[$row['school_id']][$auction_id][] = $row['prize_id'];
        }
        return $info;
    }

    public function getChidon(array $schools, array $items) {
        $info = [];
        $desc = [
            'celeb box items'  => $this->getDescForCelebBoxItems(),
            'sweaters'         => $this->getDescForSweaters()
        ];

        $school_items = $this->getItemsForSchools($schools);
        foreach ($school_items as $school_id => $more) {
            foreach ($more as $itemID => $qty) {
                // find out what type of item it is
                $numID = intval(substr($itemID, 3));
                if ($numID >= 600) $cat = 'celeb box items';
                else $cat = 'sweaters';
                if (in_array($cat, $items)) {
                    $info[$school_id][] = [
                        'id'    => $itemID,
                        'item'  => $desc[$cat][$itemID],
                        'cat'   => $cat,
                        'qty'   => $qty
                    ];
                }
            }
        }
        return $info;
    }

    private function getDescForCelebBoxItems() {
        $info = array(
            'CHI601' => 'Plates',
            'CHI602' => 'Cups',
            'CHI603' => 'Napkins',
            'CHI604' => 'Tablecloths',
            'CHI605' => 'Balloon (Navy)',
            'CHI606' => 'Balloon (Blue)',
            'CHI607' => 'Bunting',
            'CHI608' => 'Carpet',
            'CHI609' => 'Podium Sign',
            'CHI610' => 'Banner W/ Frame'
        );
        return $info;
    }

    private function getDescForSweaters() {
        // got description from chidon shipping
        $info = [
            'CHI031' => 'Children Sweater XS',
            'CHI032' => 'Children Sweater S',
            'CHI033' => 'Children Sweater M',
            'CHI034' => 'Children Sweater L',
            'CHI035' => 'Children Sweater XL',
            'CHI036' => 'Adult Sweater XS',
            'CHI037' => 'Adult Sweater S',
            'CHI038' => 'Adult Sweater M',
            'CHI039' => 'Adult Sweater L',
            'CHI040' => 'Adult Sweater XL',
            'CHI041' => 'Adult Sweater XXL',
            'CHI042' => 'Adult Sweater XXXL',
            'CHI043' => 'Children Sweater XS',
            'CHI044' => 'Children Sweater S',
            'CHI045' => 'Children Sweater M',
            'CHI046' => 'Children Sweater L',
            'CHI047' => 'Children Sweater XL',
            'CHI048' => 'Adult Sweater XS',
            'CHI049' => 'Adult Sweater S',
            'CHI050' => 'Adult Sweater M',
            'CHI051' => 'Adult Sweater L',
            'CHI052' => 'Adult Sweater XL',
            'CHI053' => 'Adult Sweater XXL'
        ];
        return $info;
    }

    private function getItemsForSchools(array $schools) {
        // array of schools and the items they have including qty
        $data = [
            2 => [
                'CHI050' => 14,
                'CHI051' => 3,
                'CHI601' => 5,
                'CHI602' => 5,
                'CHI603' => 5,
                'CHI604' => 14,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 5,
                'CHI608' => 1
            ],
            3 => [
                'CHI039' => 2,
                'CHI040' => 1,
                'CHI601' => 3,
                'CHI602' => 3,
                'CHI603' => 3,
                'CHI604' => 8,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 2
            ],
            4 => [
                'CHI601' => 7,
                'CHI602' => 7,
                'CHI603' => 7,
                'CHI604' => 21,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 7,
                'CHI608' => 1
            ],
            5 => [
                'CHI037' => 2,
                'CHI038' => 2,
                'CHI039' => 2,
                'CHI040' => 1,
                'CHI601' => 4,
                'CHI602' => 4,
                'CHI603' => 4,
                'CHI604' => 10,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 3,
                'CHI608' => 1
            ],
            11 => [
                'CHI038' => 2,
                'CHI039' => 2,
                'CHI040' => 2,
                'CHI601' => 2,
                'CHI602' => 2,
                'CHI603' => 2,
                'CHI604' => 6,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 2,
                'CHI608' => 1
            ],
            19 => [
                'CHI601' => 5,
                'CHI602' => 5,
                'CHI603' => 5,
                'CHI604' => 13,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 4,
                'CHI608' => 1
            ],
            21 => [
                'CHI039' => 2,
                'CHI040' => 1,
                'CHI041' => 2
            ],
            33 => [
                'CHI039' => 3
            ],
            40 => [
                'CHI601' => 2,
                'CHI602' => 2,
                'CHI603' => 2,
                'CHI604' => 6,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 2,
                'CHI608' => 1
            ],
            42 => [
                'CHI601' => 5,
                'CHI602' => 5,
                'CHI603' => 5,
                'CHI604' => 14,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 5,
                'CHI608' => 1
            ],
            45 => [
                'CHI601' => 4,
                'CHI602' => 4,
                'CHI603' => 4,
                'CHI604' => 11,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 3,
                'CHI608' => 1
            ],
            50 => [
                'CHI049' => 3,
                'CHI050' => 2,
                'CHI051' => 1,
                'CHI601' => 4,
                'CHI602' => 4,
                'CHI603' => 4,
                'CHI604' => 10,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 3,
                'CHI608' => 1
            ],
            58 => [
                'CHI601' => 5,
                'CHI602' => 5,
                'CHI603' => 5,
                'CHI604' => 13,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 4,
                'CHI608' => 1
            ],
            61 => [
                'CHI601' => 20,
                'CHI602' => 20,
                'CHI603' => 20,
                'CHI604' => 50,
                'CHI605' => 4,
                'CHI606' => 4,
                'CHI607' => 20,
                'CHI608' => 2
            ],
            63 => [
                'CHI038' => 2,
                'CHI039' => 1
            ],
            80 => [
                'CHI601' => 2,
                'CHI602' => 2,
                'CHI603' => 2,
                'CHI604' => 6,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 2,
                'CHI608' => 1
            ],
            81 => [
                'CHI050' => 2
            ],
            84 => [
                'CHI601' => 2,
                'CHI602' => 2,
                'CHI603' => 2,
                'CHI604' => 4,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 2,
                'CHI608' => 1
            ],
            87 => [
                'CHI601' => 2,
                'CHI602' => 2,
                'CHI603' => 2,
                'CHI604' => 3,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 2,
                'CHI608' => 1
            ],
            89 => [
                'CHI038' => 3,
                'CHI039' => 3,
                'CHI040' => 1,
                'CHI050' => 3,
                'CHI051' => 3,
                'CHI052' => 1
            ],
            105 => [
                'CHI038' => 1,
                'CHI039' => 1,
                'CHI049' => 2
            ],
            106 => [
                'CHI038' => 2,
                'CHI039' => 2,
                'CHI601' => 4,
                'CHI602' => 4,
                'CHI603' => 4,
                'CHI604' => 11,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 4,
                'CHI608' => 1
            ],
            110 => [
                'CHI601' => 5,
                'CHI602' => 5,
                'CHI603' => 5,
                'CHI604' => 14,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 4,
                'CHI608' => 1
            ],
            162 => [
                'CHI050' => 2,
                'CHI051' => 4,
                'CHI601' => 6,
                'CHI602' => 6,
                'CHI603' => 6,
                'CHI604' => 17,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 5,
                'CHI608' => 1
            ],
            176 => [
                'CHI601' => 1,
                'CHI602' => 1,
                'CHI603' => 1,
                'CHI604' => 2,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 2,
                'CHI608' => 1
            ],
            180 => [
                'CHI601' => 3,
                'CHI602' => 3,
                'CHI603' => 3,
                'CHI604' => 7,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 2
            ],
            185 => [
                'CHI601' => 3,
                'CHI602' => 3,
                'CHI603' => 3,
                'CHI604' => 7,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 2,
                'CHI608' => 1
            ],
            263 => [
                'CHI039' => 1,
                'CHI601' => 2,
                'CHI602' => 2,
                'CHI603' => 2,
                'CHI604' => 4,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 2,
                'CHI608' => 1
            ],
            265 => [
                'CHI601' => 1,
                'CHI602' => 1,
                'CHI603' => 1,
                'CHI604' => 3,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 2
            ],
            427 => [
                'CHI049' => 2,
                'CHI050' => 1,
                'CHI601' => 1,
                'CHI602' => 1,
                'CHI603' => 1,
                'CHI604' => 1,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 2,
                'CHI608' => 1
            ],
            432 => [
                'CHI049' => 1,
                'CHI601' => 2,
                'CHI602' => 2,
                'CHI603' => 2,
                'CHI604' => 4,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 2
            ],
            434 => [
                'CHI601' => 4,
                'CHI602' => 4,
                'CHI603' => 4,
                'CHI604' => 12,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 4,
                'CHI608' => 1
            ],
            470 => [
                'CHI601' => 3,
                'CHI602' => 3,
                'CHI603' => 3,
                'CHI604' => 8,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 2,
                'CHI608' => 1
            ],
            472 => [
                'CHI601' => 1,
                'CHI602' => 1,
                'CHI603' => 1,
                'CHI604' => 3,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 2,
                'CHI608' => 1
            ],
            542 => [
                'CHI038' => 1,
                'CHI049' => 1
            ],
            614 => [
                'CHI038' => 2,
                'CHI039' => 2,
                'CHI050' => 1,
                'CHI050' => 2
            ],
            659 => [
                'CHI601' => 2,
                'CHI602' => 2,
                'CHI603' => 2,
                'CHI604' => 5,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 2,
                'CHI608' => 1
            ],
            690 => [
                'CHI601' => 1,
                'CHI602' => 1,
                'CHI603' => 1,
                'CHI604' => 1,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 2,
                'CHI609' => 1,
                'CHI610' => 1
            ],
            692 => [
                'CHI601' => 1,
                'CHI602' => 1,
                'CHI603' => 1,
                'CHI604' => 2,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 2,
                'CHI608' => 1
            ],
            693 => [
                'CHI038' => 1,
                'CHI039' => 1,
                'CHI040' => 1
            ],
            694 => [
                'CHI601' => 1,
                'CHI602' => 1,
                'CHI603' => 1,
                'CHI604' => 3,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 2,
                'CHI608' => 1
            ],
            726 => [
                'CHI601' => 2,
                'CHI602' => 2,
                'CHI603' => 2,
                'CHI604' => 6,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 2,
                'CHI608' => 1
            ],
            739 => [
                'CHI050' => 1
            ],
            796 => [
                'CHI601' => 4,
                'CHI602' => 4,
                'CHI603' => 4,
                'CHI604' => 10,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 3,
                'CHI608' => 1
            ],
            805 => [
                'CHI053' => 2
            ],
            806 => [
                'CHI601' => 1,
                'CHI602' => 1,
                'CHI603' => 1,
                'CHI604' => 1,
                'CHI605' => 2,
                'CHI606' => 2,
                'CHI607' => 2,
                'CHI608' => 1,
                'CHI609' => 1,
                'CHI610' => 1
            ]
        ];

        $info = [];
        foreach ($schools as $school_id) {
            $info[$school_id] = [];
            if (isset($data[$school_id])) {
                $info[$school_id] += $data[$school_id];
            }
        }
        return $info;
    }
}