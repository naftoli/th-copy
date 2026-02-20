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

    public function __construct($yr = 0) {
        global $MASHPIA_DB;
        $this->db = $MASHPIA_DB;
        $this->year = $yr ?? GlobalSettings::getRegistrationYear();
    }

    public function getCategories() {
        $categories = [
            'raffles' => 'Raffles',
            'chidon' => 'Chidon', 
            'hachayols' => 'Hachayols'
        ];
        return $categories;
    }

    public function getItems() {
        $items = [
            'Raffles' => ['5M Raffle', '60M Raffle', 'Auction'],
            'Chidon' => ['Trip Celebration Items', 'Sweaters'],
            'Hachayols' => ['Teacher Hachayols']
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
                        $prize_info = $this->prizes[$prize] ?? null;
                        if (!$prize_info) {
                            echo "Prize $prize was deleted. It's supposed to be given to School $school_id for Raffle $raffle_id<br /><br />";
                            continue;
                        }
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
                                'id' => $prize_info['code'],
                                'item' => $prize_name,
                                'cat' => $raffle_name,
                                'qty' => 1
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
            case 'auction':
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
            case 'auction':
                $qry = "select prize_id, prize_name, shipping_code from prizes_auction";
                break;
        }
        $stmt = $this->db->query($qry);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $prizes[$row['prize_id']] = [
                'name' => $row['prize_name'],
                'code' => $row['shipping_code']
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
            case 'auction':
                $this->setAuction();
                break;
        }
    }

    private function setWeeklyRaffles() {
        $raffles = [];
        // first get raffles from prev year
        $start_yr = GlobalSettings::getCurYearDates()['start'];
        $sql = "select * from raffles where type = 'weekly' and year = :year and end_date >= :start";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'year' => intval($this->year) - 1,
            'start' => $start_yr
        ]);
        $row = $stmt->fetch();
        if ($row) {
            $raffles[$row['raffle_id']] = $row['name'];
        }
        // then get all raffles from this year
        $sql = "select * from raffles where type = 'weekly' and year = :year";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'year' => $this->year,
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
        // $sql1 = "select * from raffles where type = 'monthly' and year = :year order by run_date desc limit 1";
        // $stmt1 = $this->db->prepare($sql1);
        // $stmt1->execute(['year' => intval($this->year) - 1]);
        // $row1 = $stmt1->fetch();
        // $raffles[$row1['raffle_id']] = $row1['name'] . ' (' . $row1['year'] . ')';
        $sql2 = "select * from raffles where type = 'monthly' and year = :year order by run_date";
        $stmt2 = $this->db->prepare($sql2);
        $stmt2->execute(['year' => $this->year]);
        $rows = $stmt2->fetchAll();
        foreach ($rows as $row) {
            $raffles[$row['raffle_id']] = $row['name'] . ' (' . $row['year'] . ')';
        }

        $this->raffles = $raffles;
    }

    private function setAuction() {
        $sql = "select * from auctions where year = :year order by auction_id desc limit 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => intval($this->year) - 1]); // auction is for end of last yr
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
            'trip celebration items' => $this->getDescForCelebBoxItems(),
            'sweaters' => $this->getDescForSweaters()
        ];

        $school_items = $this->getItemsForSchools($schools);
        foreach ($school_items as $school_id => $more) {
            foreach ($more as $itemID => $qty) {
                // find out what type of item it is
                $numID = intval(substr($itemID, 3));
                if ($numID >= 600) $cat = 'trip celebration items';
                else $cat = 'sweaters';
                if (in_array($cat, $items)) {
                    $info[$school_id][] = [
                        'id' => $itemID,
                        'item' => $desc[$cat][$itemID],
                        'cat' => $cat,
                        'qty' => $qty
                    ];
                }
            }
        }
        return $info;
    }

    public function getHachayols(array $schools, array $items) {
        global $MASHPIA_DB;

        $hachayols = [];
        $sql = "SELECT 
                    school_id, COUNT(*) AS total
                FROM
                    classes
                WHERE
                    school_id IN (" . implode(',', $schools) . ")
                        AND class_era = 0
                GROUP BY school_id";
        $stmt = $MASHPIA_DB->query($sql);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $hachayols[$row['school_id']][] = [
                'id' => 'HAC001',
                'item' => 'Teacher Hachayols',
                'cat' => 'Hachayols',
                'qty' => $row['total']
            ];
        }
        return $hachayols;
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
            'CHI610' => 'Banner W/ Frame',
            'CHI611' => 'Bentcher', 
            'CHI612' => 'Mincha Cards'
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
        $sql = "select * from school_chidon_items where school_id in (" . implode(',', $schools) . ")";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $data[$row['school_id']][$row['item_id']] = $row['qty'];
        }
        return $data;
        /*
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
        */
    }

    function createCSV($items, $year, $school_id, $shipTo = 'all') {
        global $MASHPIA_DB, $shipping_paid;
    
        // create sql to get all needed fields
        $sql = "SELECT 
                    a.*,
                    u.user_id,
                    u.first AS u_first,
                    u.last AS u_last,
                    u.user_serial,
                    u.school_id
                FROM
                    admins a
                        JOIN
                    admin_auths aa USING (admin_id)
                        JOIN
                    users u ON u.user_id = aa.id
                        JOIN
                    th_chidon tc ON tc.user_id = u.user_id
                WHERE
                    aa.auth = 'user' AND u.school_id = :id 
                        AND tc.year = :year ";
        if ($shipTo == 'domestic') $sql .= " AND a.admin_country IN ('USA', 'US', 'United States', 'U.S.A', 'Unites States of America')";
        else if ($shipTo == 'intl') $sql .= " AND a.admin_country NOT IN ('USA', 'US', 'United States', 'U.S.A', 'Unites States of America')";
        $sql .= " GROUP BY u.user_id";
        $stmt = $MASHPIA_DB->prepare($sql);
        $stmt->execute([
            'year'  => $year,
            'id' => $school_id
        ]);
        $rows = $stmt->fetchAll();
    
        $users = [];
        $admins = [];
        $children = [];
        $shipping_status = [];
        foreach ($rows as $row) {
            $user_id = $row['user_id'];
            $admin_id = $row['admin_id'];
            if (! isset($admins[$admin_id])) $admins[$admin_id] = $row;
            $children[$user_id] = $admin_id;
            $users[$user_id] = $row;
            $ship_status = in_array($admin_id, $shipping_paid) ? 'ship' : 'pickup';
            $country = $row['admin_country'];
            $usa = ['USA', 'US', 'United States', 'U.S.A', 'Unites States of America'];
            if ($ship_status == 'ship') {
                if (in_array($country, $usa)) $ship_status .= ' USA';
                else $ship_status .= ' INTL';
            }
            $shipping_status[$user_id] = $ship_status;
        }
    
        $info = [];
        foreach ($items as $cat => $more) {
            foreach ($more as $user_id => $list) {
                foreach ($list as $item) {
                    $info[$cat][$user_id][] = $item;
                }
            }
        }
    
        $i = 0;
        $csv[$i++] = ['Order #', 'Recipient Full Name', 'Recipient First Name', 'Recipient Last Name', 'Recipient Phone',
            'Recipient Company', 'Address Line 1', 'Address Line 2', 'Address Line 3', 'City', 'State', 'Postal Code',
            'Country Code', 'Item SKU', 'Item Name / Title', 'Item Warehouse Location', 'Item Quantity', 'Item Options', 'Buyer Email', 
            'Custom Field 1', 'Internal Notes', 'Custom Field 2'];
        $csv[$i++] = ['Family ID', 'Parent Full Name', 'Parent First Name', 'Parent Last Name', 'Recipient Phone', 'School - Shipping Type',
            'Address Line 1', 'Address Line 2', 'Address Line 3', 'City', 'State', 'Postal Code', 'Country Code', 'CHI Number',
            'Full Item Name', 'Spanish Item Name', 'Quantity', 'Child Name - Serial #', 'Recipient Email', 
            'Custom Field 1', 'Internal Notes', 'Custom Field 2'];
        foreach ($more as $user_id => $list) {
            foreach ($list as $item) {
                if (! isset($children[$user_id])) continue;
                $admin = $admins[$children[$user_id]];
                $phone = $admin['admin_phone_mobile'] ?? $admin['admin_phone_work'] ?? $admin['admin_phone_home'] ?? '';
                $phone = makeTextForExcel($phone);
                $first = empty($admin['father']) ? $admin['first'] : ($admin['father'] . ' ' . $admin['mother']);

                $user = $users[$user_id];
                $qty = $item['qty'] ?? 1;
                $school = $user['school_id'] == 61 ? 'MyShliach' : ($user['school_id'] == 269 ? 'Anash Kinder' : '');
                $shipping = $shipping_status[$user_id];

                $itemDesc = '';
                if ($item['name']) $itemDesc .= "Personalized ";
                $itemDesc .= $item['item'];
                if ($item['color']) $itemDesc .= ", " . $item['color'];
                if ($item['size']) $itemDesc .= ", size: " . $item['size'];

                $csv[$i++] = [$admin['admin_id'], ($first . ' ' . $admin['last']), $admin['first'], $admin['last'],
                    $phone, ($school . ' - ' . ucwords($shipping)), $admin['admin_address1'], $admin['admin_address2'], '', $admin['admin_city'],
                    $admin['admin_state'], $admin['admin_postal'], $admin['admin_country'], $item['id'], $itemDesc, '',
                    $qty, ($user['u_first'] . ' ' . $user['u_last'] . ' - ' . $user['user_serial']), $admin['admin_email'], '', '',
                    ($admin['admin_city'] . ', ' . $admin['admin_state'] . ', ' . $admin['admin_country'])];
            }
        }
    
        return $csv;
    }
    
    function createFile($name, $info) {
        $fp = fopen($name, "w");
        fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) )); // utf8
        if (is_array($info)) {
            foreach ($info as $fields) {
                fputcsv($fp, $fields);
            }
        } else {
            fputs($fp, $info);
        }
        fclose($fp);
    }
    
    function createZip($files, $filename) {
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
    
    function downloadFile($filename) {
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
}