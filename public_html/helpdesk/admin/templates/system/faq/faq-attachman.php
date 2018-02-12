<?php if (!defined('PATH')) { exit; }
$SQL = '';

if (!isset($_GET['orderby'])) {
  $_GET['orderby'] = 'order_asc';
}
$orderBy = 'ORDER BY `orderBy`';

if (isset($_GET['orderby'])) {
  switch ($_GET['orderby']) {
    // Cat Name (ascending)..
    case 'name_asc':
	    $orderBy = 'ORDER BY `name`';
	    break;
	  // Cat Name (descending)..
    case 'name_desc':
	    $orderBy = 'ORDER BY `name` desc';
	    break;
	  // Order Sequence (ascending)..
    case 'order_asc':
	    $orderBy = 'ORDER BY `orderBy`';
	    break;
	  // Order Sequence (descending)..
    case 'order_desc':
	    $orderBy = 'ORDER BY `orderBy` desc';
	    break;
	  // Most questions..
    case 'questions_desc':
	    $orderBy = 'ORDER BY `queCount` desc';
	    break;
	  // Least questions..
    case 'questions_asc':
	    $orderBy = 'ORDER BY `queCount`';
	    break;
  }
}

if (isset($_GET['opt'])) {
  switch ($_GET['opt']) {
    case 'disabled':
	    $SQL = 'WHERE `enAtt` = \'no\'';
	    break;
	  case 'remote':
      $SQL = 'WHERE `path` = \'\'';
	    break;
  }
}

if (isset($_GET['keys']) && $_GET['keys']) {
  $_GET['keys']  = mswSafeImportString(strtolower($_GET['keys']));
  $SQL           = 'WHERE LOWER(`name`) LIKE \'%' . $_GET['keys'] . '%\' OR LOWER(`remote`) LIKE \'%' . $_GET['keys'] . '%\' OR LOWER(`path`) LIKE \'%' . $_GET['keys'] . '%\'';
} else {
  // Are we showing attachments only allocated to a question?
  if (isset($_GET['question'])) {
    $_GET['question'] = (int)$_GET['question'];
	  $attachIDs        = array();
	  $qA               = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `itemID` FROM `" . DB_PREFIX . "faqassign`
                        WHERE `question` = '{$_GET['question']}'
                        AND `desc`       = 'attachment'
                        GROUP BY `itemID`
                        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
    while ($AA = mysqli_fetch_object($qA)) {
	    $attachIDs[] = $AA->itemID;
	  }
	  if (!empty($attachIDs)) {
	    $SQL = 'WHERE `id` IN(' . mswSafeImportString(implode(',',$attachIDs)) . ')';
	  } else {
	    $SQL = 'WHERE `id` IN(0)';
	  }
  }
}

$q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SQL_CALC_FOUND_ROWS *,
     (SELECT count(*) FROM `" . DB_PREFIX . "faqassign`
		  WHERE (`" . DB_PREFIX . "faqassign`.`itemID` = `" . DB_PREFIX . "faqattach`.`id`)
			AND `" . DB_PREFIX . "faqassign`.`desc`     = 'attachment'
		 ) AS `queCount`
		 FROM `" . DB_PREFIX . "faqattach`
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
      <li class="active"><?php echo $msg_adheader49; ?> (<?php echo @number_format($countedRows); ?>)</li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-heading text-right">
            <?php
            if ($MSTEAM->id == '1' || in_array('attachments', $userAccess)) {
            ?>
            <button class="btn btn-success btn-sm" type="button" onclick="mswWindowLoc('index.php?p=attachments')"><i class="fa fa-plus fa-fw"></i></button>
            <?php
            }
            // Order By..
            if (mswRowCount('faqattach')>0) {
            $links = array(
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=name_asc' . mswQueryParams(array('p','orderby','next')),       'name' => $msg_attachments17),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=name_desc' . mswQueryParams(array('p','orderby','next')),      'name' => $msg_attachments18),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=order_asc' . mswQueryParams(array('p','orderby','next')),      'name' => $msg_levels23),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=order_desc' . mswQueryParams(array('p','orderby','next')),     'name' => $msg_levels24),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=questions_desc' . mswQueryParams(array('p','orderby','next')), 'name' => $msg_kbase58),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=questions_asc' . mswQueryParams(array('p','orderby','next')),  'name' => $msg_kbase57)
            );
            echo $MSBOOTSTRAP->button($msg_script45,$links, ' dropdown-menu-right', 'yes', 'admin', 'sort');
            // Filters..
            $links = array(
             array('link' => '?p='.$_GET['p'].mswQueryParams(array('p','opt','next')),                     'name' => $msg_attachments20),
             array('link' => '?p=' . $_GET['p'] . '&amp;opt=disabled' . mswQueryParams(array('p','opt','next')), 'name' => $msg_response27),
             array('link' => '?p=' . $_GET['p'] . '&amp;opt=remote' . mswQueryParams(array('p','opt','next')),   'name' => $msg_attachments21)
            );
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
                <th style="width:6%">
                  <input type="checkbox" onclick="mswCheckBoxes(this.checked,'.panel-body');mswCheckCount('panel-body','delButton','mswCVal')">
                </th>
                <?php
                }
                ?>
                <th><?php echo $msg_customfields; ?></th>
                <th><?php echo $msg_attachments16; ?></th>
                <th><?php echo $msg_kbase56; ?></th>
                <th><?php echo $msg_script43; ?></th>
               </tr>
              </thead>
              <tbody>
                <?php
                if ($countedRows > 0) {
                while ($ATT = mysqli_fetch_object($q)) {
                ?>
                <tr id="datatr_<?php echo $ATT->id; ?>">
                <?php
                if (USER_DEL_PRIV == 'yes') {
                ?>
                <td><input type="checkbox" onclick="mswCheckCount('panel-body','delButton','mswCVal');" name="del[]" value="<?php echo $ATT->id; ?>"></td>
                <?php
                }
                ?>
                <td><select name="order[<?php echo $ATT->id; ?>]" class="form-control">
                <?php
                for ($i=1; $i<($countedRows+1); $i++) {
                ?>
                <option value="<?php echo $i; ?>"<?php echo mswSelectedItem($ATT->orderBy,$i); ?>><?php echo $i; ?></option>
                <?php
                }
                ?>
                </select></td>
                <td>
                <?php echo ($ATT->name ? mswSafeDisplay($ATT->name) : ($ATT->remote ? $ATT->remote : $ATT->path)); ?>
                <span class="tdCellInfo">
                <?php echo str_replace(
                 array(
                  '{type}',
                  '{size}'
                 ),
                 array(
                  strtoupper(substr(strrchr(strtolower(($ATT->remote ? $ATT->remote : $ATT->path)),'.'),1)),
                  ($ATT->size>0 ? mswFileSizeConversion($ATT->size) : 'N/A')
                 ),
                 $msg_attachments11);
                ?>
                </span>
                </td>
                <td><a href="?p=faqman&amp;attached=<?php echo $ATT->id; ?>"><?php echo @number_format($ATT->queCount); ?></a></td>
                <td>
                  <i class="fa fa-<?php echo ($ATT->enAtt=='yes' ? 'flag' : 'flag-o'); ?> fa-fw<?php echo ($ATT->enAtt=='yes' ? ' msw-green' : ''); ?> cursor_pointer" onclick="mswEnableDisable(this,'faqattachstate','<?php echo $ATT->id; ?>')" title="<?php echo mswSafeDisplay($msg_response28); ?>"></i>
                  <a href="?p=attachments&amp;edit=<?php echo $ATT->id; ?>" title="<?php echo mswSafeDisplay($msg_script9); ?>"><i class="fa fa-pencil fa-fw"></i></a>
                  <a href="?fattachment=<?php echo $ATT->id; ?>" title="<?php echo mswSafeDisplay($msg_viewticket50); ?>"><i class="fa fa-download fa-fw"></i></a>
                </td>
                </tr>
                <?php
                }
                } else {
                ?>
                <tr class="warning nothing_to_see">
                  <td colspan="<?php echo (USER_DEL_PRIV == 'yes' ? '5' : '4'); ?>"><?php echo $msg_attachments9; ?></td>
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
          <button onclick="mswConfirmButtonAction('<?php echo mswSafeDisplay($msg_script_action); ?>','faqattachdel');return false;" class="btn btn-danger button_margin_right20" disabled="disabled" type="button" id="delButton"><i class="fa fa-trash fa-fw" title="<?php echo mswSafeDisplay($msg_levels9); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels9); ?></span> <span id="mswCVal">(0)</span></button>
	        <?php
	        }
	        ?>
	        <button class="btn btn-primary" type="button" onclick="mswProcess('faqattachseq')"><i class="fa fa-sort-numeric-asc fa-fw" title="<?php echo mswSafeDisplay($msg_levels8); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels8); ?></span></button>
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