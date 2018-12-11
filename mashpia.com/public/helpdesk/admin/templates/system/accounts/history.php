<?php if (!defined('PATH') || !isset($ACC->id)) { exit; }
include(PATH . 'templates/system/tickets/global/order-by.php');
include(PATH . 'templates/system/tickets/global/filter-by.php');
$dis       = array();
$SQL       = '';
$ticketIDs = array();
if (isset($_GET['keys']) && $_GET['keys']) {
  $_GET['keys']  = strtolower($_GET['keys']);
  $SQL           = 'AND (LOWER(`' . DB_PREFIX . 'tickets`.`subject`) LIKE \'%' . mswSafeImportString($_GET['keys']) . '%\' OR LOWER(`' . DB_PREFIX . 'tickets`.`comments`) LIKE \'%' . mswSafeImportString($_GET['keys']) . '%\')';
}
if (isset($_GET['from'],$_GET['to']) && $_GET['from'] && $_GET['to']) {
  $from  = $MSDT->mswDatePickerFormat($_GET['from']);
  $to    = $MSDT->mswDatePickerFormat($_GET['to']);
  $SQL  .= " AND (DATE(FROM_UNIXTIME(`" . DB_PREFIX . "tickets`.`ts`)) BETWEEN '{$from}' AND '{$to}')";
}
if (isset($_GET['keys']) && $_GET['keys'] && isset($_GET['replies'])) {
  $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `ticketID` FROM `" . DB_PREFIX . "replies`
       WHERE LOWER(`comments`) LIKE '%" . mswSafeImportString($_GET['keys']) . "%'
	     GROUP BY `ticketID`
	     ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
  while ($RP = mysqli_fetch_object($q)) {
	  $ticketIDs[] = $RP->ticketID;
	}
}
// Disputes..
if ($SETTINGS->disputes == 'yes' && (isset($_GET['disputes']) || isset($_GET['all']))) {
  // Disputes in other tickets..
  $qD = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `ticketID` FROM `" . DB_PREFIX . "disputes`
        WHERE `visitorID` = '{$ACC->id}'
        " . (!empty($ticketIDs) ? 'AND `id` NOT IN(' . mswSafeImportString(implode(',', $ticketIDs)) . ')' : '') . "
	      GROUP BY `ticketID`
	      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
  while ($DP = mysqli_fetch_object($qD)) {
    $ticketIDs[] = $DP->ticketID;
  }
  // Disputes from started tickets..
  $qD2 = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id` FROM `" . DB_PREFIX . "tickets`
         WHERE `visitorID` = '{$ACC->id}'
		     AND `isDisputed`  = 'yes'
		     AND `spamFlag`    = 'no'
	       " . (!empty($ticketIDs) ? 'AND `id` NOT IN(' . mswSafeImportString(implode(',', $ticketIDs)) . ')' : '') . "
	      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
  while ($DP2 = mysqli_fetch_object($qD2)) {
    $ticketIDs[] = $DP2->id;
  }
}
// What to show?
$whatToShow = '';
if (isset($_GET['all'])) {
   $whatToShow  = 'WHERE `' . DB_PREFIX . 'portal`.`email` = \'' . $ACC->email . '\'';
   if (!empty($ticketIDs)) {
     $whatToShow .= ' OR `' . DB_PREFIX . 'tickets`.`id` IN(' . mswSafeImportString(implode(',', $ticketIDs)) . ')';
   }
} else {
  if (empty($ticketIDs)) {
    $whatToShow  = 'WHERE `' . DB_PREFIX . 'portal`.`email` = \'' . $ACC->email . '\'';
  } else {
    $whatToShow .= 'WHERE `' . DB_PREFIX . 'tickets`.`id` IN(' . mswSafeImportString(implode(',', $ticketIDs)) . ')';
  }
}
$q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SQL_CALC_FOUND_ROWS *,
     `" . DB_PREFIX . "tickets`.`id` AS `ticketID`,
     `" . DB_PREFIX . "portal`.`name` AS `ticketName`,
     `" . DB_PREFIX . "tickets`.`ts` AS `ticketStamp`,
     `" . DB_PREFIX . "departments`.`name` AS `deptName`,
     `" . DB_PREFIX . "levels`.`name` AS `levelName`,
     (SELECT count(*) FROM `" . DB_PREFIX . "disputes`
      WHERE `" . DB_PREFIX . "disputes`.`ticketID` = `" . DB_PREFIX . "tickets`.`id`
     ) AS `disputeCount`
     FROM `" . DB_PREFIX . "tickets`
       LEFT JOIN `" . DB_PREFIX . "departments`
     ON `" . DB_PREFIX . "tickets`.`department` = `" . DB_PREFIX . "departments`.`id`
     LEFT JOIN `" . DB_PREFIX . "portal`
     ON `" . DB_PREFIX . "tickets`.`visitorID`  = `" . DB_PREFIX . "portal`.`id`
     LEFT JOIN `" . DB_PREFIX . "levels`
     ON (`" . DB_PREFIX . "tickets`.`priority`   = `" . DB_PREFIX . "levels`.`id`
	    OR `" . DB_PREFIX . "tickets`.`priority`  = `" . DB_PREFIX . "levels`.`marker`)
     $whatToShow
     $SQL
	   AND (`spamFlag` = 'no')
     " . $filterBy . mswSQLDepartmentFilter($ticketFilterAccess) . "
     " . $orderBy . "
     LIMIT $limitvalue,$limit
     ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
$c            = mysqli_fetch_object(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT FOUND_ROWS() AS `rows`"));
$countedRows  =  (isset($c->rows) ? $c->rows : '0');
define('LOAD_DATE_PICKERS', 1);
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (in_array('accountman', $userAccess) || $MSTEAM->id == '1') {
      ?>
      <li><a href="index.php?p=accountman"><?php echo $msg_adheader40; ?></a></li>
      <?php
      }
      ?>
      <li><a href="index.php?p=accounts&amp;edit=<?php echo $ACC->id; ?>"><?php echo mswSafeDisplay($ACC->name); ?></a></li>
      <li class="active"><?php echo $msg_accounts13; ?> (<?php echo number_format($countedRows); ?>)</li>
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
            define('SKIP_SEARCH_BOX', 1);
            include(PATH . 'templates/system/tickets/global/order-filter.php');
            include(PATH . 'templates/system/tickets/global/status-filter.php');
            ?>
            <div class="mobilebreakpoint">
            <?php
            include(PATH . 'templates/system/tickets/global/dept-filter.php');
            include(PATH . 'templates/system/bootstrap/page-filter.php');
            ?>
            <button class="btn btn-info btn-sm" type="button" onclick="mswToggleButton('search')"><i class="fa fa-search fa-fw"></i></button>
            </div>
          </div>
          <div class="panel-body">
            <?php
            // Search..
            include(PATH . 'templates/system/bootstrap/search-tickets.php');
            ?>

            <div class="table-responsive">
              <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th style="width:5%">
                    <input type="checkbox" onclick="mswCheckBoxes(this.checked,'.panel-body');mswCheckCount('panel-body','delButton','mswCVal');mswCheckCount('panel-body','delButton2','mswCVal2')">
                  </th>
                  <th>ID / <?php echo $msg_showticket16; ?></th>
                  <th><?php echo $msg_viewticket25; ?></th>
                  <th><?php echo $msg_open36; ?></th>
                  <th><?php echo $msg_open37; ?></th>
                  <th><?php echo $msg_script43; ?></th>
               </tr>
              </thead>
              <tbody>
              <?php
              if ($countedRows > 0) {
              while ($TICKETS = mysqli_fetch_object($q)) {
              $last = $MSPTICKETS->getLastReply($TICKETS->ticketID);
              ?>
              <tr id="datatr_<?php echo $TICKETS->ticketID; ?>">
                <td><input type="checkbox" onclick="mswCheckCount('panel-body','delButton','mswCVal');mswCheckCount('panel-body','delButton2','mswCVal2')" name="del[]" value="<?php echo $TICKETS->ticketID; ?>"></td>
                <td><a href="?p=view-ticket&amp;id=<?php echo $TICKETS->ticketID; ?>" title="<?php echo mswSafeDisplay($msg_viewticket11); ?>"><?php echo mswTicketNumber($TICKETS->ticketID); ?></a>
                <span class="ticketPriority"><?php echo mswSafeDisplay($TICKETS->levelName); ?></span>
                </td>
                <td><?php echo mswSafeDisplay($TICKETS->subject); ?>
                <?php if ($TICKETS->isDisputed=='yes') {
                ?>
                <span class="tdCellInfoDispute">
                 <i class="fa fa-angle-right fa-fw"></i> <?php echo $MSYS->department($TICKETS->department,$msg_script30); ?>
                </span>
                <span class="tdCellInfoDispute">
                 <i class="fa fa-bullhorn fa-fw"></i> <?php echo str_replace('{count}',($TICKETS->disputeCount + 1),$msg_showticket30); ?>
                </span>
                <?php
                } else {
                ?>
                <span class="tdCellInfo"><i class="fa fa-angle-right fa-fw"></i><?php echo $MSYS->department($TICKETS->department,$msg_script30); ?></span>
                <?php
                }
                ?>
                </td>
                <td><?php echo mswSafeDisplay($TICKETS->ticketName); ?>
                <span class="ticketDate"><?php echo $MSDT->mswDateTimeDisplay($TICKETS->ticketStamp,$SETTINGS->dateformat); ?> @ <?php echo $MSDT->mswDateTimeDisplay($TICKETS->ticketStamp,$SETTINGS->timeformat); ?></span>
                </td>
                <td>
                <?php
                if (isset($last[0]) && $last[0]!='0') {
                echo mswCleanData($last[0]);
                ?>
                <span class="ticketDate"><?php echo $MSDT->mswDateTimeDisplay($last[1],$SETTINGS->dateformat); ?> @ <?php echo $MSDT->mswDateTimeDisplay($last[1],$SETTINGS->timeformat); ?></span>
                <?php
                } else {
                echo '- - - -';
                }
                ?>
                </td>
                <td>
                 <?php
                 if (USER_EDIT_T_PRIV == 'yes') {
                 ?>
                 <a href="?p=edit-ticket&amp;id=<?php echo $TICKETS->ticketID; ?>" title="<?php echo mswSafeDisplay($msg_viewticket120); ?>"><i class="fa fa-pencil fa-fw"></i></a>
                 <?php
                 }
                 if ($MSTEAM->notePadEnable == 'yes' || $MSTEAM->id == '1') {
                 ?>
                 <a href="?p=view-ticket&amp;id=<?php echo $TICKETS->ticketID; ?>&amp;editNotes=yes" onclick="iBox.showURL(this.href,'',{width:<?php echo IBOX_NOTES_WIDTH; ?>,height:<?php echo IBOX_NOTES_HEIGHT; ?>});return false" title="<?php echo mswSafeDisplay($msg_viewticket72); ?>"><i class="fa fa-file-text fa-fw"></i></a>
                 <?php
                 }
                 ?>
                </td>
              </tr>
              <?php
              }
              } else {
              ?>
              <tr class="warning nothing_to_see">
                <td colspan="6"><?php echo $msg_open10; ?></td>
              </tr>
              <?php
              }
              ?>
              </tbody>
              </table>
            </div>
          </div>
          <div class="panel-footer">
           <?php
           if (USER_DEL_PRIV == 'yes' && $countedRows > 0) {
	         ?>
           <button onclick="mswConfirmButtonAction('<?php echo mswSafeDisplay($msg_script_action); ?>','tickdel');return false;" class="btn btn-danger button_margin_right20" disabled="disabled" type="button" id="delButton"><i class="fa fa-trash fa-fw" title="<?php echo mswCleanData($msg_open15); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_open15); ?></span> <span id="mswCVal">(0)</span></button>
	         <?php
	         }
           if ($countedRows > 0) {
           ?>
           <button class="btn btn-primary button_margin_right20" onclick="mswProcess('tickexp')" type="button" id="delButton2" disabled="disabled"><i class="fa fa-save fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_search25); ?></span> <span id="mswCVal2">(0)</span></button>
           <?php
           }
           if (in_array('accountman', $userAccess)  || $MSTEAM->id == '1') {
           ?>
           <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=accountman')"><i class="fa fa-times fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels11); ?></span></button>
           <?php
           }
           ?>
          </div>
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