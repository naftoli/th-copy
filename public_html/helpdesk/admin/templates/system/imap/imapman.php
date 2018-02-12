<?php if (!defined('PATH')) { exit; }
$SQL           = '';

if (!isset($_GET['orderby'])) {
  $_GET['orderby'] = 'user_asc';
}

if (isset($_GET['orderby'])) {
  switch ($_GET['orderby']) {
    // Protocol (ascending)..
    case 'host_asc':
	    $orderBy = 'ORDER BY `im_host`';
	    break;
	  // Protocol (descending)..
    case 'host_desc':
	    $orderBy = 'ORDER BY `im_host` desc';
	    break;
	  // Mailbox User (ascending)..
    case 'user_asc':
	    $orderBy = 'ORDER BY `im_user`';
	    break;
	  // Mailbox User (descending)..
    case 'user_desc':
	    $orderBy = 'ORDER BY `im_user` desc';
	    break;
  }
}

if (isset($_GET['filter'])) {
  $SQL  = 'WHERE `im_piping` = \'no\'';
}

if (isset($_GET['keys']) && $_GET['keys']) {
  $_GET['keys']  = mswSafeImportString(strtolower($_GET['keys']));
  $SQL           = 'WHERE LOWER(`im_host`) LIKE \'%' . $_GET['keys'] . '%\' OR LOWER(`im_user`) LIKE \'%' . $_GET['keys'] . '%\'';
}

$q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SQL_CALC_FOUND_ROWS * FROM `" . DB_PREFIX . "imap`
     $SQL
		 $orderBy
		 LIMIT $limitvalue,$limit
		 ")
                 or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
$c             = mysqli_fetch_object(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT FOUND_ROWS() AS `rows`"));
$countedRows   = (isset($c->rows) ? $c->rows : '0');
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <li class="active"><?php echo $msadminlang3_1[4]; ?> (<?php echo @number_format($countedRows); ?>)</li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-heading text-right">
            <?php
            if ($MSTEAM->id == '1' || in_array('imap', $userAccess)) {
            ?>
            <button class="btn btn-success btn-sm" type="button" onclick="mswWindowLoc('index.php?p=imap')"><i class="fa fa-plus fa-fw"></i></button>
            <?php
            }
            // Order By..
            if ($countedRows > 0) {
            $links = array(
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=host_asc' . mswQueryParams(array('p','orderby')),  'name' => $msg_imap35),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=host_desc' . mswQueryParams(array('p','orderby')), 'name' => $msg_imap36),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=user_asc' . mswQueryParams(array('p','orderby')),      'name' => $msg_imap37),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=user_desc' . mswQueryParams(array('p','orderby')),     'name' => $msg_imap38)
            );
            echo $MSBOOTSTRAP->button($msg_script45,$links, ' dropdown-menu-right', 'yes', 'admin', 'sort');
            // Filter By..
            $links = array(
             array('link' => '?p='.$_GET['p'].mswQueryParams(array('p','filter','next')),  'name' => $msg_imap39),
             array('link' => '?p=' . $_GET['p'] . '&amp;filter=disabled' . mswQueryParams(array('p','filter','next')), 'name' => $msg_response27)
            );
            echo $MSBOOTSTRAP->button($msg_search20,$links, ' dropdown-menu-right','no','admin','cogs');
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
                   <th>ID</th>
                   <th><?php echo $msg_imap7; ?></th>
                   <th><?php echo $msg_imap8; ?></th>
                   <th><?php echo $msg_script43; ?></th>
                 </tr>
               </thead>
               <tbody>
               <?php
               if ($countedRows > 0) {
               while ($IMAP = mysqli_fetch_object($q)) {
               ?>
               <tr id="datatr_<?php echo $IMAP->id; ?>">
               <?php
               if (USER_DEL_PRIV == 'yes') {
               ?>
               <td><input type="checkbox" onclick="mswCheckCount('panel-body','delButton','mswCVal')" name="del[]" value="<?php echo $IMAP->id; ?>"></td>
               <?php
               }
               ?>
               <td><?php echo $IMAP->id; ?></td>
               <td><?php echo mswSafeDisplay($IMAP->im_host); ?></td>
               <td><?php echo mswSafeDisplay($IMAP->im_user); ?></td>
               <td>
                  <i class="fa fa-<?php echo ($IMAP->im_piping=='yes' ? 'flag' : 'flag-o'); ?> fa-fw<?php echo ($IMAP->im_piping=='yes' ? ' msw-green' : ''); ?> cursor_pointer" onclick="mswEnableDisable(this,'imstate','<?php echo $IMAP->id; ?>')" title="<?php echo mswSafeDisplay($msg_response28); ?>"></i>
                  <a href="?p=imap&amp;edit=<?php echo $IMAP->id; ?>" title="<?php echo mswSafeDisplay($msg_script9); ?>"><i class="fa fa-pencil fa-fw"></i></a>
                 <a href="../?<?php echo $SETTINGS->imap_param.'='.$IMAP->id; ?>" title="<?php echo mswSafeDisplay($msg_imap29); ?>" onclick="window.open(this);return false"><i class="fa fa-envelope-o fa-fw"></i></a>
               </td>
               </tr>
               <?php
               }
               } else {
               ?>
               <tr class="warning nothing_to_see">
                 <td colspan="<?php echo (USER_DEL_PRIV == 'yes' ? '5' : '4'); ?>"><?php echo $msg_imap21; ?></td>
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
          <button onclick="mswConfirmButtonAction('<?php echo mswSafeDisplay($msg_script_action); ?>','imdel');return false;" class="btn btn-danger button_margin_right20" disabled="disabled" type="button" id="delButton"><i class="fa fa-trash fa-fw" title="<?php echo mswSafeDisplay($msg_levels9); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels9); ?></span> <span id="mswCVal">(0)</span></button>
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