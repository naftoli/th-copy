<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

class SchoolShipping
{
    private $db, $year, $schools, $prizes, $raffles, $auction;

    public function __construct() {
        global $MASHPIA_DB;
        $this->db = $MASHPIA_DB;
        $this->year = GlobalSettings::getChidonYear();
    }

    public function getCategories() {
        $categories = [
            'raffles'   => 'Raffles',
        ];
        return $categories;
    }

    public function getItems() {
        $items = [
            'Raffles'   => ['5M Raffle', '60M Raffle', 'Auction 5783']
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
        $raffles = [];
        foreach ($items as $type) {
            switch ($type) {
                case '5m raffle':
                    $this->prizes = $this->getWeeklyPrizes();
                    $weekly_prizes = $this->getWeeklyRafflePrizes();
                    foreach ($weekly_prizes as $school_id => $prizes) {
                        foreach ($prizes as $prize) {
                            $raffles[$school_id][] = $prize;
                        }
                    }
                    break;
                case '60m raffle':
                    $this->prizes = $this->getPrizes();
                    $this->raffles = $this->getRaffleInfo('monthly');
                    $monthly_prizes = $this->getMonthlyRafflePrizes();
                    foreach ($monthly_prizes as $school_id => $prizes) {
                        foreach ($prizes as $prize) {
                            $raffles[$school_id][] = $prize;
                        }
                    }
                    break;
                case 'auction 5783':
                    $this->prizes = $this->getPrizes();
                    $this->auction = $this->getAuction();
                    $auction_prizes = $this->getAuctionPrizes();
                    foreach ($auction_prizes as $school_id => $prizes) {
                        foreach ($prizes as $prize) {
                            $raffles[$school_id][] = $prize;
                        }
                    }
                    break;
            }
        }
        return $raffles;
    }

    private function getWeeklyRafflePrizes() {
        return [];
    }

    private function getMonthlyRafflePrizes() {
        $info = [];
        $school_prizes = $this->getSchoolPrizes();
        foreach ($school_prizes as $school_id => $raffles) {
            foreach ($raffles as $raffle_id => $prizes) {
                $raffle_name = $this->raffles[$raffle_id];
                foreach ($prizes as $prize_id) {
                    $prize_info = $this->prizes[$prize_id];
                    $prize_name = $prize_info['name'];
                    $info[$school_id][] = [
                        'id' => $prize_info['code'],
                        'item' => $prize_name,
                        'cat' => $raffle_name,
                        'qty' => 1
                    ];
                }
            }
        }
        return $info;
    }

    private function getAuctionPrizes() {
        $info = [];
        $auction_id = key($this->auction);
        $school_prizes = $this->getSchoolAuctionPrizes();
        foreach ($school_prizes as $school_id => $prizes) {
            foreach ($prizes as $prize_id) {
                $prize_info = $this->prizes[$prize_id];
                $prize_name = $prize_info['name'];
                $info[$school_id][] = [
                    'id' => $prize_info['code'],
                    'item' => $prize_name,
                    'cat' => $this->auction[$auction_id],
                    'qty' => 1
                ];
            }
        }
        return $info;
    }

    private function getPrizes() {
        $stmt = $this->db->query("select prize_id, prize_name, shipping_code from prizes_auction");
        $rows = $stmt->fetchAll();
        $prizes = [];
        foreach ($rows as $row) {
            $prizes[$row['prize_id']] = [
                'name'  =>  $row['prize_name'],
                'code'  =>  $row['shipping_code']
            ];
        }
        return $prizes;
    }

    private function getWeeklyPrizes() {
        $stmt = $this->db->query("select prize_id, name from prizes");
        $rows = $stmt->fetchAll();
        $prizes = [];
        foreach ($rows as $row) {
            $prizes[$row['prize_id']] = $row['name'];
        }
        return $prizes;
    }

    private function getRaffleInfo($type) {
        $raffles = [];
        switch ($type) {
            case 'monthly':
                $sql = "select * from raffles where year = :year and type = :type";
                break;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => $this->year, 'type' => $type]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $raffles[$row['raffle_id']] = $row['name'];
        }
        return $raffles;
    }

    private function getSchoolPrizes() {
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
        $sql = "select * from school_auction_prizes where auction_id = :auction and school_id in (" .
            implode(',', $this->schools) . ")";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['auction' => key($this->auction)]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $info[$row['school_id']][] = $row['prize_id'];
        }
        return $info;
    }

    private function getAuction() {
        $sql = "select * from auctions where year = :year order by auction_id desc limit 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['year' => ($this->year - 1)]); // auction is for end of last yr
        $row = $stmt->fetch();
        $auction[$row['auction_id']] = $row['auction_name'];
        return $auction;
    }
}