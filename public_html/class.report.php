<?
require_once 'class.reportBasic.php';

class Report extends ReportBasic {
	public $dates;
	    
    public function __construct($previousStart = false) {
        parent::__construct();
		$this->dates = array(2458185,2458234,2458402,2458437,2458479,2458514,2458542,2458570,2458619); // each year take last 2 dates from previous yr and add current dates
		$this->setReportDates($previousStart);
    }
}
?>