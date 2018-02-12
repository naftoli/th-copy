<?php if (!defined('PATH')) { exit; }
$SQL = '';

if (!isset($_GET['orderby'])) {
  $_GET['orderby'] = 'order_asc';
}
$orderBy  = 'ORDER BY `orderBy`';

if (isset($_GET['orderby'])) {
  switch ($_GET['orderby']) {
    // Name (ascending)..
    case 'name_asc':
	    $orderBy = 'ORDER BY `name`';
	    break;
	  // Name (descending)..
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
	  // Most tickets..
    case 'tickets_desc':
	    $orderBy = 'ORDER BY `tickCount` desc';
	    break;
	  // Least tickets..
    case 'tickets_asc':
	    $orderBy = 'ORDER BY `tickCount`';
	    break;
  }
}

if (isset($_GET['keys']) && $_GET['keys']) {
  $_GET['keys']  = mswSafeImportString(strtolower($_GET['keys']));
  $SQL           = 'WHERE LOWER(`name`) LIKE \'%' . $_GET['keys'] . '%\'';
}

$q  = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SQL_CALC_FOUND_ROWS *,
      (SELECT count(*) FROM `" . DB_PREFIX . "tickets`
			WHERE (`" . DB_PREFIX . "levels`.`id` = `" . DB_PREFIX . "tickets`.`priority` AND `spamFlag` = 'no')
			OR (`" . DB_PREFIX . "levels`.`marker` = `" . DB_PREFIX . "tickets`.`priority` AND `spamFlag` = 'no')
			) AS `tickCount`
      FROM `" . DB_PREFIX . "levels`
      $SQL
			$orderBy
      LIMIT $limitvalue,$limit
			") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
$c            = mysqli_fetch_object(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT FOUND_ROWS() AS `rows`"));
$countedRows  = (isset($c->rows) ? $c->rows : '0');
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <li class="active"><?php echo $msg_adheader51; ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-heading text-right">
            <?php
            if ($MSTEAM->id == '1' || in_array('levels', $userAccess)) {
            ?>
            <button class="btn btn-success btn-sm" type="button" onclick="mswWindowLoc('index.php?p=levels')"><i class="fa fa-plus fa-fw"></i></button>
            <?php
            }
            // Order By..
            $links = array(
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=name_asc' . mswQueryParams(array('p','orderby','next')),     'name' => $msg_levels21),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=name_desc' . mswQueryParams(array('p','orderby','next')),    'name' => $msg_levels22),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=order_asc' . mswQueryParams(array('p','orderby','next')),    'name' => $msg_levels23),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=order_desc' . mswQueryParams(array('p','orderby','next')),   'name' => $msg_levels24),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=tickets_desc' . mswQueryParams(array('p','orderby','next')), 'name' => $msg_accounts11),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=tickets_asc' . mswQueryParams(array('p','orderby','next')),  'name' => $msg_accounts12)
            );
            echo $MSBOOTSTRAP->button($msg_script45,$links);
            // Page filter..
            include(PATH . 'templates/system/bootstrap/page-filter.php');
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
                 <th>ID</th>
                 <th><?php echo $msg_customfields; ?></th>
                 <th><?php echo $msg_levels18; ?></th>
                 <th><?php echo $msg_accounts3; ?></th>
                 <th><?php echo $msg_script43; ?></th>
               </tr>
              </thead>
              <tbody>
                <?php
                if ($countedRows > 0) {
                while ($LEVELS = mysqli_fetch_object($q)) {
                ?>
                <tr id="datatr_<?php echo $LEVELS->id; ?>">
                <?php
                if (USER_DEL_PRIV == 'yes') {
                if ($LEVELS->id>3) {
                ?>
                <td><input type="checkbox" onclick="mswCheckCount('panel-body','delButton','mswCVal')" name="del[]" value="<?php echo $LEVELS->id; ?>"></td>
                <?php
                } else {
                ?>
                <td>&nbsp;</td>
                <?php
                }
                }
                ?>
                <td><?php echo ($LEVELS->marker ? $LEVELS->marker : $LEVELS->id); ?></td>
                <td>
                <select name="order[<?php echo $LEVELS->id; ?>]" class="form-control">
                  <?php
                  for ($i=1; $i<($countedRows+1); $i++) {
                  ?>
                  <option value="<?php echo $i; ?>"<?php echo mswSelectedItem($LEVELS->orderBy,$i,false); ?>><?php echo $i; ?></option>
                  <?php
                  }
                  $whatsOn = array($msg_script5);
                  if ($LEVELS->display=='yes') {
                    $whatsOn[0] = $msg_script4;
                  }
                  ?>
                  </select>
                </td>
                <td>
                <?php echo mswSafeDisplay($LEVELS->name); ?>
                <span class="tdCellInfo">
                 <?php echo $msg_levels15; ?>: <span class="highlight"><?php echo ms_YesNo($LEVELS->display); ?></span>
                </span>
                </td>
                <td><a href="?p=search&amp;keys=&amp;priority=<?php echo ($LEVELS->marker ? $LEVELS->marker : $LEVELS->id); ?>" title="<?php echo @number_format($LEVELS->tickCount); ?>"><?php echo @number_format($LEVELS->tickCount); ?></a></td>
                <td>
                  <a href="?p=levels&amp;edit=<?php echo $LEVELS->id; ?>" title="<?php echo mswSafeDisplay($msg_script9); ?>"><i class="fa fa-pencil fa-fw"></i></a>
                </td>
                </tr>
                <?php
                }
                } else {
                ?>
                <tr class="warning nothing_to_see">
                 <td colspan="<?php echo (USER_DEL_PRIV == 'yes' ? '6' : '5'); ?>"><?php echo $msg_levels16; ?></td>
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
          <button onclick="mswConfirmButtonAction('<?php echo mswSafeDisplay($msg_script_action); ?>','levdel');return false;" class="btn btn-danger button_margin_right20" disabled="disabled" type="button" id="delButton"><i class="fa fa-trash fa-fw" title="<?php echo mswSafeDisplay($msg_levels9); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels9); ?></span> <span id="mswCVal">(0)</span></button>
	        <?php
	        }
	        ?>
	        <button class="btn btn-primary" type="button" onclick="mswProcess('levseq')"><i class="fa fa-sort-numeric-asc fa-fw" title="<?php echo mswSafeDisplay($msg_levels8); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels8); ?></span></button>
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