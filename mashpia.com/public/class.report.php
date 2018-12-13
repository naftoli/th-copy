<?
require_once 'class.reportBasic.php';

class Report extends ReportBasic {
    public $dates;
    public $date_selection;
	    
    public function __construct($previousStart = false) {
        parent::__construct();
		$this->dates = array(2458185,2458234,2458402,2458437,2458479,2458514,2458542,2458570,2458619); // each year take last 2 dates from previous yr and add current dates
		$this->setReportDates($previousStart);
    }

    public function setDateSelection() {
        foreach ( $this->dates as $index => $jd ) {
            // skip first one b/c there's no from date
            if ( $index == 0 ) continue;
            $from = $this->getDateForSelection( $this->dates[$index-1] );
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


}
?>