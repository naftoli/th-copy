<?
require_once 'class.reportBasic.php';

class Report extends ReportBasic {
	public $dates;
	    
    public function __construct($previousStart = false) {
        parent::__construct();
		// put end dates
		//$this->dates = array(2456797, 2456960, 2456992, 2457037, 2457067, 2457095, 2457147, 2457258);
		//$this->dates = array(2456797, 2456960, 2456992, 2457037, 2457067, 2457095, 2457147, 2457189, 2457258);
		//$this->dates = array(2457259, 2457312, 2457347, 2457388, 2457423, 2457451, 2457479, 2457538);
		//$this->dates = array(2457538,2457699,2457731,2457774,2457802,2457837,2457886);
		$this->dates = array(2457886,2458053,2458080,2458122,2458157,2458185,2458234); // julian days switch at 12am EST - found this out after 2458053 date has been used
		$this->setReportDates($previousStart);
    }
}
?>