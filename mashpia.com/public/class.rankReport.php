<?php
require_once 'class.report.php';
require_once 'class.globalSettings.php';

class RankReport extends Report {
    protected $ranks;
    protected $rankNames;
    protected $rankInfo;
    protected $userInfo;
    protected $rankOrds;
    protected $userHeNames;
    protected $schoolLogos;
    protected $userSchool;
    protected $userPic;
    protected $picOnly;
    protected $books;
    protected $year;
    protected $shipped;
    protected $medalsShipped;
    protected $rank_medals_for_shipping;
    protected $rank_books_for_shipping;

    public function __construct($previousStart = false) {
        parent::__construct($previousStart);
        $this->rankInfo = array();
        $this->userInfo = array();
        $this->userHeNames = array();
        $this->rankOrds['Private'] = 1;
        $this->schoolLogos = [];
        $this->userSchool = [];
        $this->userPic = [];
        $this->picOnly = [];
        $this->books = [];
        $this->shipped = [];
        $this->medalsShipped = [];
        $this->year = GlobalSettings::getCurrentYear();
    }

    public function setRanks($orderType = 'byGrade', $rankOrd = 0, $nameBreak = ' ', $specificGender = '', $forShipping = false) {
        // if there's a school ID, update year
        // if ($this->school_id) {
        //     $this->year = GlobalSettings::getRegistrationYear($this->school_id);
        // }
        $this->ranks = [];
        $start = $this->reportDates['start'];
        $end = $this->reportDates['end'];
//        if ($forShipping) {
//            $filter = "
//                AND (
//                (date_promoted >= $start AND date_promoted <= $end AND date_printed is null) OR ((rm.date_book_shipped is null OR rm.date_card_shipped is null) AND rm.date_printed is not null)
//            )";
//        }
//        else $filter = " AND (date_promoted >= $start AND date_promoted <= $end)";
        $filter = " AND (date_promoted >= $start AND date_promoted <= $end)";
        $sql = "
            SELECT s.school_name, s.logo, s.logo_boys, s.logo_girls, s.school_logo_id, c.class_teacher, c.class_grade, c.class_sub, r.rank_name, u.*, rm.*  
            FROM rank_marks rm
            JOIN ranks r USING ( rank_ord )
            JOIN users u USING ( user_id )
            JOIN schools s USING ( school_id )
            JOIN classes c ON ( u.class_id = c.class_id ) 
            JOIN user_registration ur ON u.user_id = ur.user_id 
            WHERE u.medals_ranks = 1 
            AND u.user_registered > 0 
            AND ur.year = $this->year 
            $filter ";
        if (!is_null($this->school_id) && $this->school_id > 0) {
            $sql .= "AND s.school_id = $this->school_id ";
        }
        if (! $forShipping ) {
            $exceptions = $this->schoolExceptions;
            if ( in_array($this->school_id, [61, 269]) ) {
                $exceptions = array_diff($this->schoolExceptions, [61, 269]);
            }
            $sql .= "
                AND s.school_id not in (" . implode(',', $exceptions) . ")
            ";
        }
        if ( $rankOrd ) {
            $sql .= "AND rm.rank_ord = " . $rankOrd . " ";
        }
        if ( !empty( $specificGender ) ) {
            $sql .= "AND gender = '" . strtoupper($specificGender) . "' ";
        }

        if ($orderType == 'byGrade') {
            $sql .= "ORDER BY s.school_name, c.class_grade, c.class_sub, u.last, u.first, r.rank_ord";
        } else if ( $orderType == 'byRankFirst' || $orderType == 'byRankFirstMixedGender' ) {
            $sql .= "ORDER BY r.rank_ord, s.school_name, c.class_grade, c.class_sub, u.last, u.first";
        } else if ( $orderType == 'byGenerals' ) {
            $sql .= "ORDER BY r.rank_ord, u.last, u.first";
        } else {
            $sql .= "ORDER BY s.school_name, r.rank_ord, c.class_grade, c.class_sub, u.last, u.first";
        }
    //    echo $sql; exit;
//        echo "<input type='hidden' name='SQL' value='" . $sql . "' />";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $user_id = $row['user_id'];
            $school = $row['school_name'];
            $teacher = $row['class_teacher'];
            $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
            $first = $row['first'];
            $last = $row['last'];
            if ($this->isHebrew($first) || $this->isHebrew($last)) {
                $first = $this->reverseHebrew($first);
                $last = $this->reverseHebrew($last);
            }
            $user = $first . $nameBreak . $last;
            $this->userInfo[$user_id] = $user;
            $this->userHeNames[$row['user_id']] = $row['first_he'] . ' ' . $row['last_he'];

            $rank = $row['rank_name'];
            if ( $orderType == 'byGrade' )
                $this->ranks[$school][$teacher][$grade][][$user_id] = $rank;
            else if ( $orderType == 'byGradeRank')
                $this->ranks[$school][$teacher][$grade][$rank][] = $user_id;
            else if ( $orderType == 'byGradeOnlyRank')
                $this->ranks[$school][$row['class_grade']][$rank][] = $user_id;
            else if ( $orderType == 'byRank' )
                $this->ranks[$school][$rank][$teacher][$grade][] = $row;
            else if ( $orderType == 'byRankFirst' )
                $this->ranks[$row['gender']][$rank][$school][] = $user_id;
            else if ( $orderType == 'byRankFirstMixedGender' )
                $this->ranks[$rank][$row['gender']][$school][] = $user_id;
            else if ( $orderType == 'byGenerals' ) {
                if ( $row['rank_ord'] < 9 ) continue;
                $this->ranks[$rank][$row['gender']][] = $user_id;
            } else if ( $orderType == 'byGender') {
                $this->ranks[$row['gender']][$school][$rank][$teacher][$grade][] = $user_id;
            } else if ( $orderType == 'byUser' ) {
                if ($row['rank_ord'] == 1) continue; // skip private
                $this->ranks[$school][$teacher][$grade][$user_id][] = $row['rank_ord'];
                $this->rank_medals_for_shipping[$user_id][] = $row;
            }

            $this->rankInfo[$user_id]['card_printed'] = $row['date_printed'];
            $this->rankInfo[$user_id]['card_shipped'] = $row['date_card_shipped'];
            $this->rankInfo[$user_id]['card_received'] = $row['date_card_received'];
            $this->rankInfo[$user_id]['book_shipped'] = $row['date_book_shipped'];
            $this->rankInfo[$user_id]['book_received'] = $row['date_book_received'];

            $this->userSchool[$user_id] = $school;
            $this->schoolLogos[$school] = [
                'logo_boys'     =>  $row['logo_boys'],
                'logo_girls'    =>  $row['logo_girls'],
                'logo_id'       =>  $row['school_logo_id'],
                'logo'          =>  $row['logo']
            ];

            // set user pic
            $pic = '/mobile/reg/images/profile-photo-default.jpg';
            if ( $row['mobile_pic'] ) {
                $pic = '/mobile/reg/' . $row['mobile_pic'];
            } else if ( $row['user_photo_id'] ) {
                $pic = '/file_view.php?id=' . $row['user_photo_id'];
            }
            $this->userPic[$user_id] = $pic;
            $pos = strpos($row['mobile_pic'], 'img/');
            if ($pos !== false) {
                $img = substr($row['mobile_pic'], $pos + 4);
                $this->picOnly[$user_id] = $img;
            }
        }
    }

    public function setOtherChildren($nameBreak, $users) {
        // if there's a school ID, update year
        if ($this->school_id) {
            $this->year = GlobalSettings::getRegistrationYear($this->school_id);
        }

        $this->ranks = [];
        $this->setRankNames();

        $exceptions = $this->schoolExceptions;
        if ( in_array($this->school_id, [61, 269]) ) {
            $exceptions = array_diff($this->schoolExceptions, [61, 269]);
        }

        $sql = "
            SELECT s.school_name, s.logo, s.logo_boys, s.logo_girls, s.school_logo_id, c.class_teacher, c.class_grade, c.class_sub, u.*, MAX(rm.rank_ord) as rnk 
            FROM rank_marks rm
            JOIN users u USING ( user_id )
            JOIN schools s USING ( school_id )
            JOIN classes c ON ( u.class_id = c.class_id ) 
            JOIN user_registration ur ON u.user_id = ur.user_id 
            WHERE u.user_registered > 0 
            AND ur.year = $this->year
            AND u.user_id NOT IN (" . implode(',', $users) . ") ";
        if (!is_null($this->school_id)) {
            $sql .= "AND s.school_id = $this->school_id ";
        }
        $sql .= "
            AND s.school_id not in (" . implode(',', $exceptions) . ")
        ";
        $sql .= "GROUP BY u.user_id ";
        $sql .= "ORDER BY s.school_name, rnk, c.class_grade, c.class_sub, u.last, u.first";
//        echo $sql; exit;
//        echo "<input type='hidden' name='SQL' value='" . $sql . "' />";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $user_id = $row['user_id'];
            $school = $row['school_name'];
            $first = $row['first'];
            $last = $row['last'];
            if ($this->isHebrew($first) || $this->isHebrew($last)) {
                $first = $this->reverseHebrew($first);
                $last = $this->reverseHebrew($last);
            }
            $user = $first . $nameBreak . $last;
            $this->userInfo[$user_id] = $user;
            $this->userHeNames[$row['user_id']] = $row['first_he'] . ' ' . $row['last_he'];

            $rank_ord = $row['rnk'];
            $rank = array_search($rank_ord, $this->rankOrds);
            $this->ranks[$school][$row['class_grade']][$rank][] = $user_id;
            $this->schoolLogos[$school] = [
                'logo_boys'     =>  $row['logo_boys'],
                'logo_girls'    =>  $row['logo_girls'],
                'logo_id'       =>  $row['school_logo_id'],
                'logo'          =>  $row['logo']
            ];

            // set user pic
            $pic = '/mobile/reg/images/profile-photo-default.jpg';
            if ( $row['mobile_pic'] ) {
                $pic = '/mobile/reg/' . $row['mobile_pic'];
            } else if ( $row['user_photo_id'] ) {
                $pic = '/file_view.php?id=' . $row['user_photo_id'];
            }
            $this->userPic[$user_id] = $pic;
            $pos = strpos($row['mobile_pic'], 'img/');
            if ($pos !== false) {
                $img = substr($row['mobile_pic'], $pos + 4);
                $this->picOnly[$user_id] = $img;
            }
        }
    }

    public function setHighestRanks($gender = '') {
        $this->ranks = [];
        $start = $this->reportDates['start'];
        $end = $this->reportDates['end'];
        $filter = " AND (date_promoted >= $start AND date_promoted <= $end)";
        $exceptions = $this->schoolExceptions;
        if ( in_array($this->school_id, [61, 269]) ) {
            $exceptions = array_diff($this->schoolExceptions, [61, 269]);
        }
        $sql = "
            SELECT s.school_name, s.logo, s.logo_boys, s.logo_girls, s.school_logo_id, c.class_teacher, c.class_grade, c.class_sub, 
                   u.*, rm.*, MAX(rm.rank_ord) as `rank` 
            FROM rank_marks rm
            JOIN users u USING ( user_id )
            JOIN schools s USING ( school_id )
            JOIN classes c ON ( u.class_id = c.class_id ) 
            JOIN user_registration ur ON u.user_id = ur.user_id 
            WHERE u.medals_ranks = 1 
            AND u.user_registered > 0 
            AND ur.year = $this->year  
            $filter ";
        if (!is_null($this->school_id) && $this->school_id > 0) {
            $sql .= "AND s.school_id = $this->school_id ";
        } else {
            $sql .= "
                AND s.school_id not in (" . implode(',', $exceptions) . ")
            ";
        }
        if ($gender == 'm' || $gender == 'f') {
            $sql .= "AND u.gender = '" . strtoupper($gender) . "' ";
        }
        $sql .= "GROUP BY u.user_id ";
        $sql .= "ORDER BY s.school_name, c.class_grade, c.class_sub, u.last, u.first";
        // echo $sql; exit;

        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $user_id = $row['user_id'];
            $school = $row['school_name'];
            $teacher = $row['class_teacher'];
            $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
            $this->userInfo[$user_id] = $row;
            $this->userHeNames[$row['user_id']] = $row['first_he'] . ' ' . $row['last_he'];
            $this->ranks[$school][$teacher][$grade][$user_id] = $row['rank'];
        }
    }

    public function getBooksToSend(string $gender = '', bool $forShipping = false) {
        $this->setHighestRanks($gender);
        $this->books = [];
        $this->rank_books_for_shipping = [];
        foreach ($this->ranks as $school => $teachers) {
            foreach ($teachers as $teacher => $grades) {
                foreach ($grades as $grade => $users) {
                    foreach ($users as $user_id => $rank_ord) {
                        $book = 0;
                        if ($rank_ord >= 1 && $rank_ord < 9) {
                            $book = 1;
                        } else if ($rank_ord >= 9 && $rank_ord < 12) {
                            $book = 2;
                        } else if ($rank_ord >= 12) {
                            $book = 3;
                        }
                        if ($book) {
                            $this->books[$school][$book][$teacher][$grade][] = $user_id;
                            if ($forShipping) {
                                $this->rank_books_for_shipping[$user_id][] = $book;
                            }
                        }
                    }
                }
            }
        }
        if ($forShipping) {
            return $this->rank_books_for_shipping;
        } else {
            return $this->books;
        }
    }

    public function setRankBooksShipped() {
        $this->shipped = [];
        $sql = "select * from rank_books_shipped";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $this->shipped[$row['user_id']][] = $row['book'];
        }
    }

    public function getRankBooksShipped() {
        if (empty($this->shipped)) {
            $this->setRankBooksShipped();
        }
        return $this->shipped;
    }

    public function setRankMedalsShipped() {
        $this->medalsShipped = [];
        $sql = "select * from rank_medals_shipped";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $this->medalsShipped[$row['user_id']][] = $row['rank_ord'];
        }
    }

    public function getRankMedalsShipped() {
        if (empty($this->medalsShipped)) {
            $this->setRankMedalsShipped();
        }
        return $this->medalsShipped;
    }

    public function getRanks() {
        return $this->ranks;
    }

    public function setRankNames() {
        $sql = "select * from ranks order by rank_ord";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $rank = $row['rank_name'];
            $needed = $row['medals_required'];
            $this->rankNames[$rank] = $needed;
            $this->rankOrds[$rank] = $row['rank_ord'];
        }
    }

    public function getRankNames() {
        return $this->rankNames;
    }

    public function getRankInfo() {
        return $this->rankInfo;
    }

    public function getUserInfo() {
        return $this->userInfo;
    }

    public function getUserHeNames() {
        return $this->userHeNames;
    }

    public function getRankOrds() {
        return $this->rankOrds;
    }

    public function getSchoolLogos() {
        return $this->schoolLogos;
    }

    public function getUserSchool() {
        return $this->userSchool;
    }

    public function getUserPic() {
        return $this->userPic;
    }

    public function getPicOnly() {
        return $this->picOnly;
    }

    public function getRankMedalsForShipping() {
        return $this->rank_medals_for_shipping;
    }

    private function reverseHebrew($text)
    {
        $words = array_reverse(explode(' ', $text));
        foreach ($words as $index => $word) {
            if ($this->isHebrew($word)) {
                $words[$index] = $this->mbStrRev($word);
            }
        }
        return join(' ', $words);
    }

    private function isHebrew($text)
    {
        for ($i = 0, $cnt = strlen($text); $i < $cnt; ++$i) {
            if (ord($text[$i]) > 127) {
                return true;
            }
        }
        return false;
    }

    private function mbStrRev($string, $encoding = null)
    {
        if ($encoding === null) {
            $encoding = mb_detect_encoding($string);
        }

        $length   = mb_strlen($string, $encoding);
        $reversed = '';
        while ($length-- > 0) {
            $reversed .= mb_substr($string, $length, 1, $encoding);
        }

        return $reversed;
    }
}
?>