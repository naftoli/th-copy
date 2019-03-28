    <?php
	  $_GET['p'] = (isset($_GET['p']) ? $_GET['p'] : 'x');

	  //=============================
	  // LIMIT OPTIONS
	  //=============================

	  $links = array();
	  foreach (array(10,20,30,40,50,75,100,150,200,250,300,500) AS $k) {
	    $links[] = array('link' => '?p=' . $_GET['p'] . '&amp;limit=' . $k . mswQueryParams(array('p','limit','next')), 'name' => $k . ' ' . $msg_script50);
	  }
	  echo $MSBOOTSTRAP->button($msg_script51, $links, ' dropdown-menu-right', 'yes');
    if (!defined('SKIP_SEARCH_BOX')) {
	  ?>
    <button class="btn btn-info btn-sm" type="button" onclick="mswToggleButton('search')"><i class="fa fa-search fa-fw"></i></button>
    <?php
    }
    ?>