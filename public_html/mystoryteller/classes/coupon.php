<?
class Coupon {
	private $code;
	private $amount;
	private $type;
	private $applyTo;
	private $start;
	private $expires;
	
	public function __construct($amount, $type = 'percent', $applyTo = 'total', $start = 0, $end = 0) {
		$this->amount = $amount;
		$this->type = $type;
		$this->applyTo = $applyTo;
		$this->start = $start;
		$this->end = $end;
		$this->code = rand(111111, 999999);
	}
	
}
?>