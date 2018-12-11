<?php if (!defined('PATH')) { exit; }
$SQL = '';
if ($MSTEAM->id != '1') {
  $SQL = 'WHERE `id` > 1';
}

if (!isset($_GET['orderby'])) {
  $_GET['orderby'] = 'name_asc';
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
	  // Most responses..
    case 'resp_asc':
	    $orderBy = 'ORDER BY `respCount` desc';
	    break;
	  // Least tickets..
    case 'resp_desc':
	    $orderBy = 'ORDER BY `respCount`';
	    break;
  }
}

if (isset($_GET['filter'])) {
  switch ($_GET['filter']) {
    case 'disabled':
      $SQL = 'WHERE `enabled` = \'no\'';
      break;
	  case 'notify':
      $SQL = 'WHERE `notify` = \'no\'';
      break;
	  case 'delpriv':
      $SQL = 'WHERE `delPriv` = \'yes\'';
      break;
	  case 'notepad':
      $SQL = 'WHERE `notePadEnable` = \'yes\'';
      break;
	  case 'assigned':
      $SQL = 'WHERE `assigned` = \'yes\'';
      break;
  }
}

if (isset($_GET['keys']) && $_GET['keys']) {
  $_GET['keys']  = mswSafeImportString(strtolower($_GET['keys']));
  $SQL           = 'WHERE LOWER(`name`) LIKE \'%' . $_GET['keys'] . '%\' OR LOWER(`email`) LIKE \'%' . $_GET['keys'] . '%\'';
}

$q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SQL_CALC_FOUND_ROWS *,
     (SELECT count(*) FROM `" . DB_PREFIX . "replies`
		 WHERE `" . DB_PREFIX . "replies`.`replyUser` = `" . DB_PREFIX . "users`.`id`
		 AND `" . DB_PREFIX . "replies`.`replyType` = 'admin'
		 ) AS `respCount`
		 FROM `" . DB_PREFIX . "users`
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
      <li class="active"><?php echo $msg_adheader58; ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-heading text-right">
            <?php
            if ($MSTEAM->id == '1' || in_array('team', $userAccess)) {
            ?>
            <button class="btn btn-success btn-sm" type="button" onclick="mswWindowLoc('index.php?p=team')"><i class="fa fa-plus fa-fw"></i></button>
            <?php
            }
            // Order By..
            $links = array(
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=name_asc' . mswQueryParams(array('p','orderby')),  'name' => $msg_levels21),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=name_desc' . mswQueryParams(array('p','orderby')), 'name' => $msg_levels22),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=email_asc' . mswQueryParams(array('p','orderby')), 'name' => $msg_accounts9),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=email_desc' . mswQueryParams(array('p','orderby')),'name' => $msg_accounts10),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=resp_asc' . mswQueryParams(array('p','orderby')),  'name' => $msg_user78),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=resp_desc' . mswQueryParams(array('p','orderby')), 'name' => $msg_user79)
            );
            echo $MSBOOTSTRAP->button($msg_script45,$links, ' dropdown-menu-right', 'yes', 'admin', 'sort');
            // Filters..
            $links = array(
             array('link' => '?p='.$_GET['p'].mswQueryParams(array('p','filter')),                               'name' => $msg_accounts14),
             array('link' => '?p=' . $_GET['p'] . '&amp;filter=disabled' . mswQueryParams(array('p','filter')),  'name' => $msg_response27),
             array('link' => '?p=' . $_GET['p'] . '&amp;filter=notify' . mswQueryParams(array('p','filter')),    'name' => $msg_user80),
             array('link' => '?p=' . $_GET['p'] . '&amp;filter=delpriv' . mswQueryParams(array('p','filter')),   'name' => $msg_user81),
             array('link' => '?p=' . $_GET['p'] . '&amp;filter=notepad' . mswQueryParams(array('p','filter')),   'name' => $msg_user82),
             array('link' => '?p=' . $_GET['p'] . '&amp;filter=assigned' . mswQueryParams(array('p','filter')),  'name' => $msg_user83)
            );
            echo $MSBOOTSTRAP->button($msg_search20,$links, ' dropdown-menu-right', 'no', 'admin', 'cogs');
            ?>
            <div class="mobilebreakpoint">
            <?php
            // Page filter..
            include(PATH . 'templates/system/bootstrap/page-filter.php');
            ?>
            </div>
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
                <th style="width:5%">ID</th>
                <th><?php echo $msg_user; ?></th>
                <th><?php echo $msg_user4; ?></th>
                <th><?php echo $msg_user77; ?></th>
                <th><?php echo $msg_script43; ?></th>
               </tr>
              </thead>
              <tbody>
                <?php
                if ($countedRows > 0) {
                while ($USER = mysqli_fetch_object($q)) {
                ?>
                <tr id="datatr_<?php echo $USER->id; ?>">
                <?php
                if (USER_DEL_PRIV == 'yes') {
                if ($USER->id>1) {
                ?>
                <td><input type="checkbox" onclick="mswCheckCount('panel-body','delButton','mswCVal')" name="del[]" value="<?php echo $USER->id; ?>"></td>
                <?php
                } else {
                ?>
                <td>&nbsp;</td>
                <?php
                }
                }
                ?>
                <td><?php echo $USER->id; ?></td>
                <td><?php echo mswSafeDisplay($USER->name); ?></td>
                <td><?php echo mswSafeDisplay($USER->email); ?></td>
                <td><a href="?p=responses&amp;id=<?php echo $USER->id; ?>" title=""><?php echo @number_format($USER->respCount); ?></a></td>
                <td>
                <i class="fa fa-<?php echo ($USER->enabled=='yes' ? 'flag' : 'flag-o'); ?> fa-fw<?php echo ($USER->enabled=='yes' ? ' msw-green' : ''); ?> cursor_pointer" onclick="mswEnableDisable(this,'tmstate','<?php echo $USER->id; ?>')" title="<?php echo mswSafeDisplay($msg_response28); ?>"></i>
                <a href="?p=team&amp;edit=<?php echo $USER->id; ?>" title="<?php echo mswSafeDisplay($msg_script9); ?>"><i class="fa fa-pencil fa-fw"></i></a>
                <a href="?p=responses&amp;id=<?php echo $USER->id; ?>" title="<?php echo mswSafeDisplay($msg_user25); ?>"><i class="fa fa-comments-o fa-fw"></i></a>
                <a href="?p=graph&amp;id=<?php echo $USER->id; ?>" title="<?php echo mswSafeDisplay($msg_user31); ?>"><i class="fa fa-bar-chart fa-fw"></i></a>
                </td>
                </tr>
                <?php
                }
                } else {
                ?>
                <tr class="warning nothing_to_see">
                 <td colspan="<?php echo (USER_DEL_PRIV == 'yes' ? '6' : '5'); ?>"><?php echo $msg_user11; ?></td>
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
          <button onclick="mswConfirmButtonAction('<?php echo mswSafeDisplay($msg_script_action); ?>','tmdel');return false;" class="btn btn-danger" disabled="disabled" type="button" id="delButton"><i class="fa fa-trash fa-fw" title="<?php echo mswSafeDisplay($msg_levels9); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels9); ?></span> <span id="mswCVal">(0)</span></button>
	        <?php
	        }
	        ?>
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