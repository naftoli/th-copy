<?
abstract class BaseParent {
	protected $fields;
	protected $data;
	
	public function __construct() {
		$this->fields = array(
			'first', 
			'last',
			'admin_address1',
			'admin_address2',
			'admin_city',
			'admin_state',
			'admin_postal',
			'admin_country', 
			'admin_phone_home',
			'admin_phone_mobile',
			'admin_email', 
			'username', 
			'password', 
			'photo', 
			'father', 
			'mother', 
			'father_pic', 
			'mother_pic'
		);
	}
	
	public function getAdminID() {
		return $this->data['admin_id'];
	}
	
	abstract public function action($data);	
	abstract public function sendConfEmail();
}
?>