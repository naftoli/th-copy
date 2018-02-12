<?php if (!defined('PATH')) { exit; }
$SQL           = '';
$searchParams  = '';
if (!isset($_GET['orderby'])) {
  $_GET['orderby'] = 'order_asc';
}
$orderBy = 'ORDER BY `name`';

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
	  // Email Address (ascending)..
    case 'email_asc':
	    $orderBy = 'ORDER BY `email`';
	    break;
	  // Email Address (descending)..
    case 'email_desc':
	    $orderBy = 'ORDER BY `email` desc';
	    break;
	  // Most tickets..
    case 'tickets_asc':
	    $orderBy = 'ORDER BY `tickCount` desc';
	    break;
	  // Least tickets..
    case 'tickets_desc':
	    $orderBy = 'ORDER BY `tickCount`';
	    break;
  }
}

if (isset($_GET['filter'])) {
  switch ($_GET['filter']) {
    case 'disabled':
      $SQL = 'WHERE `enabled` = \'no\'';
      break;
  }
}

if (isset($_GET['keys'])) {
  // Filters..
  $filters = array();
  if ($_GET['keys']) {
    $_GET['keys']  = mswSafeImportString(strtolower($_GET['keys']));
    $filters[]     = "LOWER(`" . DB_PREFIX . "portal`.`name`) LIKE '%" . $_GET['keys'] . "%' OR LOWER(`" . DB_PREFIX . "portal`.`email`) LIKE '%" . $_GET['keys'] . "%' OR LOWER(`" . DB_PREFIX . "portal`.`ip`) LIKE '%" . $_GET['keys'] . "%'  OR LOWER(`" . DB_PREFIX . "portal`.`notes`) LIKE '%" . $_GET['keys'] . "%'";
  }
  if (isset($_GET['from'],$_GET['to']) && $_GET['from'] && $_GET['to']) {
    $from  = $MSDT->mswDatePickerFormat($_GET['from']);
    $to    = $MSDT->mswDatePickerFormat($_GET['to']);
    $filters[]     = "DATE(FROM_UNIXTIME(`ts`)) BETWEEN '{$from}' AND '{$to}'";
  }
  // Build search string..
  if (!empty($filters)) {
    for ($i=0; $i<count($filters); $i++) {
      $searchParams .= ($i ? ' AND (' : 'WHERE (') . $filters[$i] . ')';
    }
  }
}

// Are we querying for disputes..
$sqlDisputes = '';
if ($SETTINGS->disputes == 'yes') {
  $sqlDisputes = ',
   (SELECT count(*) FROM `' . DB_PREFIX . 'disputes`
    WHERE `' . DB_PREFIX . 'portal`.`id` = `' . DB_PREFIX . 'disputes`.`visitorID`
   ) AS `dispCount`';
}

$q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SQL_CALC_FOUND_ROWS *,
     (SELECT count(*) FROM `" . DB_PREFIX . "tickets`
      WHERE `" . DB_PREFIX . "portal`.`id` = `" . DB_PREFIX . "tickets`.`visitorID`
      AND `spamFlag`   = 'no'
      AND `isDisputed` = 'no'
      ) AS `tickCount`
      $sqlDisputes
      FROM `" . DB_PREFIX . "portal`
      $SQL
      $searchParams
      $orderBy
      LIMIT $limitvalue,$limit
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
$c            = mysqli_fetch_object(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT FOUND_ROWS() AS `rows`"));
$countedRows  = (isset($c->rows) ? $c->rows : '0');
define('LOAD_DATE_PICKERS', 1);
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <li class="active"><?php echo $msg_adheader40; ?> (<?php echo $countedRows; ?>)</li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-heading text-right">
            <?php
            if ($MSTEAM->id == '1' || in_array('accounts', $userAccess)) {
            ?>
            <button class="btn btn-success btn-sm" type="button" onclick="mswWindowLoc('index.php?p=accounts')"><i class="fa fa-plus fa-fw"></i></button>
            <?php
            }
            if ($countedRows > 0) {
            // Order By..
            $links = array(
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=name_asc' . mswQueryParams(array('p','orderby')),    'name' => $msg_levels21),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=name_desc' . mswQueryParams(array('p','orderby')),   'name' => $msg_levels22),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=email_asc' . mswQueryParams(array('p','orderby')),   'name' => $msg_accounts9),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=email_desc' . mswQueryParams(array('p','orderby')),  'name' => $msg_accounts10),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=tickets_asc' . mswQueryParams(array('p','orderby')), 'name' => $msg_accounts11),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=tickets_desc' . mswQueryParams(array('p','orderby')),'name' => $msg_accounts12)
            );
            echo $MSBOOTSTRAP->button($msg_script45,$links, ' dropdown-menu-right', 'yes', 'admin', 'sort');
            // Filters..
            $links = array(
             array('link' => '?p='.$_GET['p'].mswQueryParams(array('p','orderby')),                       'name' => $msg_accounts14),
             array('link' => '?p=' . $_GET['p'] . '&amp;filter=disabled' . mswQueryParams(array('p','filter')), 'name' => $msg_response27)
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
            include(PATH . 'templates/system/bootstrap/search-accounts.php');
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
                 <th><?php echo $msg_accounts; ?></th>
                 <th><?php echo $msg_accounts2; ?></th>
                 <th><?php echo ($SETTINGS->disputes == 'yes' ? $msg_accounts38 : $msg_accounts3); ?></th>
                 <th><?php echo $msg_script43; ?></th>
               </tr>
              </thead>
              <tbody>
              <?php
              if ($countedRows > 0) {
                while ($ACC = mysqli_fetch_object($q)) {
                if (isset($ACC->dispCount)) {
                  $dCStart        = mswRowCount('tickets WHERE `visitorID` = \''.$ACC->id.'\' AND `isDisputed` = \'yes\' AND `spamFlag` = \'no\'');
                  $ACC->dispCount = ($ACC->dispCount+$dCStart);
                }
                ?>
                <tr id="datatr_<?php echo $ACC->id; ?>">
                <?php
                if (USER_DEL_PRIV == 'yes') {
                ?>
                <td><input type="checkbox" onclick="mswCheckCount('panel-body','delButton','mswCVal')" name="del[]" value="<?php echo $ACC->id; ?>"></td>
                <?php
                }
                ?>
                <td><?php echo ($ACC->name ? mswSafeDisplay($ACC->name) : 'N/A'); ?></td>
                <td><?php echo mswCleanData($ACC->email); ?></td>
                <?php
                if ($SETTINGS->disputes == 'yes') {
                ?>
                <td><a href="?p=acchistory&amp;id=<?php echo $ACC->id; ?>" title="<?php echo @number_format($ACC->tickCount); ?>"><?php echo @number_format($ACC->tickCount); ?></a> / <a href="?p=acchistory&amp;id=<?php echo $ACC->id; ?>&amp;disputes=yes" title="<?php echo @number_format($ACC->dispCount); ?>"><?php echo @number_format($ACC->dispCount); ?></a></td>
                <?php
                } else {
                ?>
                <td><a href="?p=acchistory&amp;id=<?php echo $ACC->id; ?>" title="<?php echo @number_format($ACC->tickCount); ?>"><?php echo @number_format($ACC->tickCount); ?></a></td>
                <?php
                }
                $appendDisUrl = '';
                if ($SETTINGS->disputes == 'yes' && isset($ACC->dispCount) && $ACC->dispCount>0) {
                  $appendDisUrl = '&amp;disputes=yes';
                }
                ?>
                <td>
                  <i class="fa fa-<?php echo ($ACC->enabled=='yes' ? 'flag' : 'flag-o'); ?> fa-fw<?php echo ($ACC->enabled=='yes' ? ' msw-green' : ''); ?> cursor_pointer" onclick="mswEnableDisable(this,'accstate','<?php echo $ACC->id; ?>')" title="<?php echo mswSafeDisplay($msg_response28); ?>"></i>
                  <a href="?p=accounts&amp;edit=<?php echo $ACC->id; ?>" title="<?php echo mswSafeDisplay($msg_script9); ?>"><i class="fa fa-pencil fa-fw"></i></a>
                  <a href="?p=acchistory&amp;id=<?php echo $ACC->id.$appendDisUrl; ?>&amp;all=yes" title="<?php echo mswSafeDisplay($msg_accounts13); ?>"><i class="fa fa-calendar fa-fw"></i></a>
                </td>
                </tr>
                <?php
                }
                } else {
                ?>
                <tr class="warning nothing_to_see">
                  <td colspan="<?php echo (USER_DEL_PRIV == 'yes' ? '5' : '4'); ?>"><?php echo $msg_accounts5; ?></td>
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
          foreach ($_GET AS $k => $v) {
          if (!in_array($k, array('p','next'))) {
          ?>
          <input type="hidden" name="<?php echo mswSafeDisplay($k); ?>" value="<?php echo mswSafeDisplay($v); ?>">
          <?php
          }
          }
	        if (USER_DEL_PRIV == 'yes') {
	        ?>
          <button onclick="mswConfirmButtonAction('<?php echo mswSafeDisplay($msg_script_action); ?>','accdel');return false;" class="btn btn-danger button_margin_right20" disabled="disabled" type="button" id="delButton"><i class="fa fa-trash fa-fw" title="<?php echo mswSafeDisplay($msg_levels9); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels9); ?></span> <span id="mswCVal">(0)</span></button>
	        <?php
	        }
	        ?>
	        <button class="btn btn-primary" type="button" onclick="mswProcess('accexp')"><i class="fa fa-download fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_accounts36; ?></span></button>
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