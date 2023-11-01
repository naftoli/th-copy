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
        $info = [];
        foreach ($items as $type) {
            $this->setPrizes($type);
            $this->setRafflesInfo($type);
            $school_prizes = $this->getSchoolPrizes($type);
            foreach ($school_prizes as $school_id => $raffles) {
                foreach ($raffles as $raffle_id => $prizes) {
                    $raffle_name = $this->raffles[$raffle_id];
                    foreach ($prizes as $prize_id) {
                        $prize_info = $this->prizes[$prize_id];
                        $prize_name = $prize_info['name'];
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
            $raffles[$row['raffle_id']] = $row['name'];
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
            $raffles[$row['raffle_id']] = $row['name'];
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
}