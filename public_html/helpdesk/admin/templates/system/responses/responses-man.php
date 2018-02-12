<?php if (!defined('PATH')) { exit; }
$SQL         = '';

if (!isset($_GET['orderby'])) {
  $_GET['orderby'] = 'order_asc';
}

if (isset($_GET['orderby'])) {
  switch ($_GET['orderby']) {
    // Title (ascending)..
    case 'title_asc':
	    $orderBy = 'ORDER BY `title`';
	    break;
	  // Title (descending)..
    case 'title_desc':
	    $orderBy = 'ORDER BY `title` desc';
	    break;
	  // Order Sequence (ascending)..
    case 'order_asc':
	    $orderBy = 'ORDER BY `orderBy`';
	    break;
	  // Order Sequence (descending)..
    case 'order_desc':
	    $orderBy = 'ORDER BY `orderBy` desc';
	    break;
  }
}

if (isset($_GET['dept'])) {
  if ($_GET['dept']=='disabled') {
    $SQL          = 'WHERE `enResponse` = \'no\'';
  } else {
    $_GET['dept'] = (int) $_GET['dept'];
    $SQL          = 'WHERE FIND_IN_SET(\'' . $_GET['dept'] . '\',`departments`) > 0';
  }
}

if (isset($_GET['keys']) && $_GET['keys']) {
  $_GET['keys']  = mswSafeImportString(strtolower($_GET['keys']));
  $SQL           = 'WHERE LOWER(`title`) LIKE \'%' . $_GET['keys'] . '%\' OR LOWER(`answer`) LIKE \'%' . $_GET['keys'] . '%\'';
}

$q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SQL_CALC_FOUND_ROWS * FROM `" . DB_PREFIX . "responses`
     $SQL
		 $orderBy
		 LIMIT $limitvalue,$limit
		 ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
$c            = mysqli_fetch_object(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT FOUND_ROWS() AS `rows`"));
$countedRows  =  (isset($c->rows) ? $c->rows : '0');
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <li class="active"><?php echo $msg_adheader54; ?> (<?php echo @number_format($countedRows); ?>)</li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-heading text-right">
            <?php
            if ($MSTEAM->id == '1' || in_array('standard-responses', $userAccess)) {
            ?>
            <button class="btn btn-success btn-sm" type="button" onclick="mswWindowLoc('index.php?p=standard-responses')"><i class="fa fa-plus fa-fw"></i></button>
            <?php
            }
            // Order By..
            if ($countedRows > 0) {
            $links = array(
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=title_asc' . mswQueryParams(array('p','orderby')),  'name' => $msg_response23),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=title_desc' . mswQueryParams(array('p','orderby')), 'name' => $msg_response24),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=order_asc' . mswQueryParams(array('p','orderby')),  'name' => $msg_levels23),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=order_desc' . mswQueryParams(array('p','orderby')), 'name' => $msg_levels24)
            );
            echo $MSBOOTSTRAP->button($msg_script45,$links, ' dropdown-menu-right', 'yes', 'admin', 'sort');
            // Filters..
            $links   = array(array('link' => '?p='.$_GET['p'].mswQueryParams(array('p','dept','next')), 'name' => $msg_response25));
            $q_dept  = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "departments` " . mswSQLDepartmentFilter($mswDeptFilterAccess,'WHERE') . " ORDER BY `orderBy`")
                       or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
              while ($DEPT = mysqli_fetch_object($q_dept)) {
              $links[] = array('link' => '?p=' . $_GET['p'] . '&amp;dept='.$DEPT->id.mswQueryParams(array('p','dept','next')), 'name' => $msg_response26 . ' ' . mswCleanData($DEPT->name));
            }
            $links[] = array('link' => '?p=' . $_GET['p'] . '&amp;dept=disabled' . mswQueryParams(array('p','dept','next')), 'name' => $msg_response27);
            echo $MSBOOTSTRAP->button($msg_search20,$links, ' dropdown-menu-right', 'no', 'admin', 'cogs');
            // Page filter..
            ?>
            <div class="mobilebreakpoint">
            <?php
            include(PATH . 'templates/system/bootstrap/page-filter.php');
            ?>
            </div>
            <?php
            }
            ?>
          </div>
          <div class="panel-body">
            <?php
            // Search..
            include(PATH . 'templates/system/bootstrap/search-box.php');
            ?>

            <div class="table-responsive">
              <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <?php
                  if (USER_DEL_PRIV == 'yes') {
                  ?>
                  <th style="width:5%">
                    <input type="checkbox" onclick="mswCheckBoxes(this.checked,'.panel-body');mswCheckCount('panel-body','delButton','mswCVal')">
                  </th>
                  <?php
                  }
                  ?>
                  <th><?php echo $msg_customfields; ?></th>
                  <th><?php echo $msg_response; ?></th>
                  <th><?php echo $msg_script43; ?></th>
                </tr>
              </thead>
              <tbody>
                <?php
                if ($countedRows > 0) {
                $totalR = mswRowCount('responses');
                while ($SR = mysqli_fetch_object($q)) {
                ?>
                <tr id="datatr_<?php echo $SR->id; ?>">
                <?php
                if (USER_DEL_PRIV == 'yes') {
                ?>
                <td>
                <input type="checkbox" onclick="mswCheckCount('panel-body','delButton','mswCVal')" name="del[]" value="<?php echo $SR->id; ?>">
                </td>
                <?php
                }
                ?>
                <td>
                <select name="order[<?php echo $SR->id; ?>]" class="form-control">
                <?php
                for ($i=1; $i<($totalR+1); $i++) {
                ?>
                <option value="<?php echo $i; ?>" <?php echo mswSelectedItem($SR->orderBy,$i,false); ?>>
                <?php echo $i; ?>
                </option>
                <?php
                }
                ?>
                </select>
                </td>
                <td>
                <?php echo mswCleanData($SR->title); ?>
                <span class="tdCellInfo">
                <?php echo mswSrCat($SR->departments); ?>
                </span>
                </td>
                <td>
                  <i class="fa fa-<?php echo ($SR->enResponse=='yes' ? 'flag' : 'flag-o'); ?> fa-fw<?php echo ($SR->enResponse=='yes' ? ' msw-green' : ''); ?> cursor_pointer" onclick="mswEnableDisable(this,'srstate','<?php echo $SR->id; ?>')" title="<?php echo mswSafeDisplay($msg_response28); ?>"></i>
                  <a href="?p=standard-responses&amp;edit=<?php echo $SR->id; ?>" title="<?php echo mswSafeDisplay($msg_script9); ?>"><i class="fa fa-pencil fa-fw"></i></a>
                  <a href="?p=<?php echo $_GET['p']; ?>&amp;view=<?php echo $SR->id; ?>" title="<?php echo mswSafeDisplay($msg_response12); ?>" onclick="iBox.showURL(this.href,'',{width:<?php echo IBOX_RESPONSE_WIDTH; ?>,height:<?php echo IBOX_RESPONSE_HEIGHT; ?>});return false"><i class="fa fa-search fa-fw"></i></a>
                  </td>
                </tr>
                <?php
                }
                } else {
                ?>
                <tr class="warning nothing_to_see">
                <td colspan="<?php echo (USER_DEL_PRIV == 'yes' ? '5' : '4'); ?>">
                <?php echo $msg_response9; ?>
                </td>
                </tr>
                <?php
                }
                ?>
                </tbody>
              </table>
            </div>
          </div>

          <?php
	        if ($countedRows > 0) {
          ?>
          <div class="panel-footer">
          <?php
	        if (USER_DEL_PRIV == 'yes') {
	        ?>
          <button onclick="mswConfirmButtonAction('<?php echo mswSafeDisplay($msg_script_action); ?>','srdel');return false;" class="btn btn-danger button_margin_right20" disabled="disabled" type="button" id="delButton"><i class="fa fa-trash fa-fw" title="<?php echo mswSafeDisplay($msg_levels9); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels9); ?></span> <span id="mswCVal">(0)</span></button>
	        <?php
	        }
	        ?>
	        <button class="btn btn-primary" type="button" onclick="mswProcess('srseq')"><i class="fa fa-sort-numeric-asc fa-fw" title="<?php echo mswSafeDisplay($msg_levels8); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels8); ?></span></button>
          </div>
	        <?php
	        }
          ?>
        </div>

        <?php
        if ($countedRows > 0 && $countedRows > $limit) {
          define('PER_PAGE', $limit);
          $PGS = new pagination(array($countedRows, $msg_script42, $page),'?p=' . $_GET['p'] . '&amp;next=');
          echo $PGS->display();
        }
        ?>

      </div>
    </div>
    </form>

  </div>