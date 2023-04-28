<?
require_once 'class.reportBasic.php';

class Report extends ReportBasic {
    public $dates;
    public $date_selection;
	    
    public function __construct($previousStart = false) {
        parent::__construct();
//        $this->dates = array(2459340,2459361,2459471,2459501,2459529,2459557,2459585,2459620,2459648,2459697,2459718); // each year take last 2 dates from previous yr and add current dates
        $this->dates = array( 2459697, 2459718, 2459823, 2459851, 2459879, 2459914, 2459942, 2459970, 2459998, 2460026, 2460061, 2460089 );
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
}
?>