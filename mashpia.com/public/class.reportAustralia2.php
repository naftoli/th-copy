<?
require_once 'class.reportBasic.php';

class Report extends ReportBasic {
	protected $date;
	    
    public function __construct($previousStart = false) {
        parent::__construct();
		//$this->dates = array(2456441, 2456576, 2456715, 2456744, 2456797);
		$this->dates = array(2456797, 2456960, 2457067, 2457095, 2457149);
		$this->setReportDates($previousStart);
    }
}
?>