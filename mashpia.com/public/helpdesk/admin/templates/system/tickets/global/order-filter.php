    <?php
	  $_GET['p'] = (isset($_GET['p']) ? $_GET['p'] : 'x');
    if (!defined('PARENT') || !in_array($_GET['p'],array('open','close','disputes','cdisputes','search','search-fields','assign','acchistory','spam'))) { exit; }

	  //=============================
	  // ORDER BY OPTIONS
	  //=============================

	  $links = array();
	  foreach ($msg_script44 AS $k => $v) {
	    $links[] = array('link' => '?p=' . $_GET['p'] . '&amp;orderby=' . $k . mswUrlApp('dept').mswQueryParams(array('p','orderby','dept')),  'name' => $v);
	  }
	  echo $MSBOOTSTRAP->button($msg_script45, $links, '', 'no', 'admin', 'sort');
	  ?>