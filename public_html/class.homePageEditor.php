<?
/*
 * class to edit / add / delete home page announcements
 */
class HomePageEditor {
	private $items;
	
	public function __construct() {
		$this->getItems();
	}
	
	private function getItems() {
		$sql = "select * from items";
	}
}
?>
