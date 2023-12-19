<?
require_once 'class.reportBasic.php';

class Report extends ReportBasic {
    public $dates;
    public $date_selection;
	    
    public function __construct($previousStart = false) {
        parent::__construct();
        // each year take last 2 dates from previous yr and add current dates
//        $this->dates = array( 2459697, 2459718, 2459823, 2459851, 2459879, 2459914, 2459942, 2459970, 2459998, 2460026, 2460061, 2460092 ); // each year take last 2 dates from previous yr and add current dates
        $this->dates = array( 2460061, 2460092, 2460226, 2460258, 2460292, 2460321, 2460349, 2460374, 2460405, 2460450 );
        $this->setReportDates($previousStart);
    }

    public function setDateSelection() {
        foreach ( $this->dates as $index => $jd ) {
            // skip first one b/c there's no from date
            if ( $index == 0 ) continue;
            $from = $this->getDateForSelection( $this->dates[$index-1]+1 );
            $to = $this->getDateForSelection( $this->dates[$index] );
            $this->date_selection[$jd] = $from . ' - ' . $to; // show from / to date in selection and keeps end julian date as key
            if ( unixtojd() < $jd ) break; // only show up to next date from today    
        }
    }

    private function getDateForSelection( $jd ) {
        $arrDate = explode('/', jdtogregorian( $jd )); 
        $mm = jdmonthname( $jd, CAL_MONTH_GREGORIAN_SHORT );
        $date = $mm . ' ' . $arrDate[1] . ', ' . $arrDate[2];
        return $date;
    }

    public function getDates() {
        return $this->dates;
    }

    public function getHtmlSelect() {
        $reportDates = $this->getReportDates();

        $str = "<select name='date_selection' id='date_selection'>";
        $num = count($this->dates)-1;
        for ( $i = 1; $i < $num; $i++ ) {
            $start = $this->dates[$i]+1;
            $end = $this->dates[$i+1];

            $str1 = jdtojewish($start, true, CAL_JEWISH_ADD_GERESHAYIM);
            $start_he = iconv('WINDOWS-1255', 'UTF-8', $str1);
            $str2 = jdtojewish($end, true, CAL_JEWISH_ADD_GERESHAYIM);
            $end_he = iconv('WINDOWS-1255', 'UTF-8', $str2);

            $str .= "<option value='" . ($start . ':' . $end) . "'";
            if ( $start == $reportDates['start'] )
                $str .= " selected='selected'";
            $str .= ">" . ($start_he . ' - ' . $end_he) . "</option>";
        }
        $str .= "</select>";
        return $str;
    }
}
?>