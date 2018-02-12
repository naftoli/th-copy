    <?php
		$_GET['p'] = (isset($_GET['p']) ? $_GET['p'] : 'x');
    if (!defined('PARENT') || !in_array($_GET['p'],array('open','close','disputes','cdisputes','search','search-fields','assign','acchistory','spam'))) { exit; }

		//===========================================
		// DEPARTMENT FILTERS
		//===========================================

		$links   = array();
    $links[] = array('link' => '?p='.$_GET['p'].mswQueryParams(array('p','dept','next')),'name' => $msg_open2);
    $q_dept  = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id`,`name` FROM `" . DB_PREFIX . "departments` " . mswSQLDepartmentFilter($mswDeptFilterAccess,'WHERE') . " ORDER BY `orderBy`")
               or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
    while ($DEPT = mysqli_fetch_object($q_dept)) {
		  $links[] = array('link' => '?p=' . $_GET['p'] . '&amp;dept=' . $DEPT->id . mswQueryParams(array('p','dept','next')),'name' => mswCleanData($DEPT->name));
    }

		//=========================================================
		// SHOW ALL ASSIGNED USERS IN FILTER IF PERMISSIONS ALLOW
		//=========================================================

    if (!defined('HIDE_ASSIGN_FILTERS') && ($MSTEAM->id == '1' || in_array('assign', $userAccess))) {
		  $q_users  = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id`,`name` FROM `" . DB_PREFIX . "users` ORDER BY `name`")
                  or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
      while ($U = mysqli_fetch_object($q_users)) {
		    $links[] = array('link' => '?p=' . $_GET['p'] . '&amp;dept=u' . $U->id . mswQueryParams(array('p','dept','next')),'name' => $msg_open31 . ' ' . mswSafeDisplay($U->name));
      }
		}
		echo $MSBOOTSTRAP->button($msg_viewticket107, $links,' dropdown-menu-right', 'yes', 'admin', 'briefcase');
    ?>