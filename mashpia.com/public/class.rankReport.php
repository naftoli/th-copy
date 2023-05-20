<?
//if (in_array($admin_user['auths']['school'][0], array(55,66,110,112)))
//	require_once 'class.reportAustralia.php';
//else 
require_once 'class.report.php';

class RankReport extends Report {
    protected $ranks;
    protected $rankNames;
    protected $rankInfo;
    protected $userInfo;
    protected $rankOrds;
    protected $userHeNames;
    protected $schoolExceptions;
    protected $schoolLogos;
    protected $userSchool;
    protected $userPic;
    protected $picOnly;

    public function __construct($previousStart = false) {
        parent::__construct($previousStart);
        $this->rankInfo = array();
        $this->userInfo = array();
        $this->userHeNames = array();
        $this->rankOrds['Private'] = 1;
        $this->schoolExceptions = [180, 585, 588, 612, 709];
        $this->schoolLogos = [];
        $this->userSchool = [];
        $this->userPic = [];
    }

    public function setRanks($orderType = 'byGrade', $rankOrd = 0, $nameBreak = ' ', $specificGender = '', $reverseHe = false) {
        $this->ranks = array();
        $start = $this->reportDates['start'];
        $end = $this->reportDates['end'];
        $sql = "
            SELECT s.school_name, s.logo, s.logo_boys, s.logo_girls, s.school_logo_id, c.class_teacher, c.class_grade, c.class_sub, r.rank_name, u.*, rm.* 
            FROM rank_marks rm
            JOIN ranks r USING ( rank_ord )
            JOIN users u USING ( user_id )
            JOIN schools s USING ( school_id )
            JOIN classes c ON ( u.class_id = c.class_id ) 
            WHERE u.user_registered > 0 
            AND u.medals_ranks = 1  
            AND (
                (date_promoted >= $start AND date_promoted <= $end) OR (rm.date_book_shipped is null OR rm.date_card_shipped is null)
            ) ";
        if (!is_null($this->school_id)) {
            $sql .= "AND s.school_id = $this->school_id ";
        }
        $sql .= "
            AND s.school_id not in (" . implode(',', $this->schoolExceptions) . ")
        ";
        if ( $rankOrd ) {
            $sql .= "AND rm.rank_ord = " . $rankOrd . " ";
        }
        if ( !empty( $specificGender ) ) {
            $sql .= "AND gender = '" . $specificGender . "' ";
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
//        echo $sql; exit;
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

    public function getRanks() {
        return $this->ranks;
    }

    public function setRankNames() {
        //$sql = "select * from ranks where medals_required > 0 order by rank_ord";
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