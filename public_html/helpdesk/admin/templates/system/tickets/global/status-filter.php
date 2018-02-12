    <?php
	  $_GET['p'] = (isset($_GET['p']) ? $_GET['p'] : 'x');
    if (!defined('PARENT') || !in_array($_GET['p'],array('open','close','disputes','cdisputes','search','search-fields','assign','acchistory','spam'))) { exit; }

	  //=============================
	  // ORDER BY OPTIONS
	  //=============================

	  $links = array(array('link' => '?p='.$_GET['p'].mswUrlApp('dept').mswQueryParams(array('p','dept','priority','status')),  'name' => $msg_open3));
	  foreach ($ticketLevelSel AS $k => $v) {
	    $links[] = array('link' => '?p=' . $_GET['p'] . '&amp;priority=' . $k . mswUrlApp('dept').mswQueryParams(array('p','dept','priority','status')),  'name' => $v);
    }

	  //=============================
	  // SHOW STATUS OPTIONS
	  //=============================

	  if (in_array($_GET['p'],array('open','search','acchistory'))) {
	    $links[] = array('link' => '?p=' . $_GET['p'] . '&amp;status=visitor' . mswQueryParams(array('p','status')), 'name' => $msg_open12);
		  $links[] = array('link' => '?p=' . $_GET['p'] . '&amp;status=admin' . mswQueryParams(array('p','status')),   'name' => $msg_open11);
		  $links[] = array('link' => '?p=' . $_GET['p'] . '&amp;status=start' . mswQueryParams(array('p','status')),  'name' => $msg_open27);
    }
	  echo $MSBOOTSTRAP->button($msg_search20,$links,' dropdown-menu-right', 'no', 'admin', 'hourglass');
	  ?>