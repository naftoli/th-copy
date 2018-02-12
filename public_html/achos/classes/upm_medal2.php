<?php
class upm_medal {
	public $medal_ord;
	public $medal_name;
	
	public $missions_required;
		
	function __construct($row){
		$this->medal_ord = $row['medal_ord'];
		$this->medal_name = $row['medal_name'];
	}

	function set_missions_required($missions_required)
	{
		$this->missions_required = $missions_required;
	}				
}
?>