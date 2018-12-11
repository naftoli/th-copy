<?php if (!defined('PATH')) { exit; }
$filters       = array();
$searchParams  = '';
$s             = '';
$countedRows   = 0;
$area          = (empty($_GET['area']) ? array('tickets', 'disputes') : $_GET['area']);
include(PATH . 'templates/system/tickets/global/order-by.php');
if (isset($_GET['keys'])) {
  // Filters..
  if ($_GET['keys']) {
    $_GET['keys']  = mswSafeImportString(strtolower($_GET['keys']));
    // Search custom field data for ticket IDs
    $csFieldSearch = 'WHERE LOWER(`fieldData`) LIKE \'%' . $_GET['keys'] . '%\' ';
    if (isset($_GET['field'])) {
      if ($_GET['field'] > 0) {
        $_GET['field'] = (int) $_GET['field'];
        $csFieldSearch .= 'AND `fieldID` = \'' . $_GET['field'] . '\'';
      }
    }
    $ticketIDs = array();
	  $q         = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `ticketID` FROM `" . DB_PREFIX . "ticketfields`
	               $csFieldSearch
		             GROUP BY `ticketID`
		             ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
    while ($CFD = mysqli_fetch_object($q)) {
	    $ticketIDs[] = $CFD->ticketID;
	  }
    $ticketIDs = (!empty($ticketIDs) ? $ticketIDs : array(0));
	  $filters[] = '`' . DB_PREFIX . 'tickets`.`id` IN(' . mswSafeImportString(implode(',', $ticketIDs)) . ')';
	}
  if ($_GET['keys']) {
    if (isset($_GET['priority']) && in_array($_GET['priority'], $levelPrKeys)) {
      $filters[]  = "`priority` = '{$_GET['priority']}'";
    }
    if (isset($_GET['dept']) && $_GET['dept'] > 0) {
      $_GET['dept'] = (int) $_GET['dept'];
      $filters[]    = "`department` = '{$_GET['dept']}'";
    }
    if (isset($_GET['assign'])) {
      if ($_GET['assign'] > 0) {
        $_GET['assign'] = (int) $_GET['assign'];
        $filters[]      = "FIND_IN_SET('{$_GET['assign']}',`assignedto`) > 0";
      }
    }
    if (isset($_GET['status']) && in_array($_GET['status'], array('close','open','closed'))) {
      $filters[] = "`ticketStatus` = '{$_GET['status']}'";
    }
    if (isset($_GET['from'],$_GET['to']) && $_GET['from'] && $_GET['to']) {
      $from      = $MSDT->mswDatePickerFormat($_GET['from']);
      $to        = $MSDT->mswDatePickerFormat($_GET['to']);
      $filters[] = "DATE(FROM_UNIXTIME(`ts`)) BETWEEN '{$from}' AND '{$to}'";
    }
    if (count($area) > 1) {
      $filters[] = "`isDisputed` IN('yes','no')";
    } else {
      if (in_array('tickets', $area)) {
        $filters[] = "`isDisputed` = 'no'";
      } else {
        $filters[] = "`isDisputed` = 'yes'";
      }
    }
    // Build search string..
    if (!empty($filters)) {
      for ($i=0; $i<count($filters); $i++) {
        $searchParams .= ($i ? ' AND (' : 'WHERE (') . $filters[$i] . ')';
      }
    }
    // Count for pages..
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
         ON `" . DB_PREFIX . "tickets`.`visitorID` = `" . DB_PREFIX . "portal`.`id`
         LEFT JOIN `" . DB_PREFIX . "levels`
         ON (`" . DB_PREFIX . "tickets`.`priority`   = `" . DB_PREFIX . "levels`.`id`
          OR `" . DB_PREFIX . "tickets`.`priority`  = `" . DB_PREFIX . "levels`.`marker`)
         " . ($searchParams ? $searchParams . ' AND `spamFlag` = \'no\' ' . mswSQLDepartmentFilter($ticketFilterAccess) : 'WHERE `spamFlag` = \'no\'') . "
         $orderBy
         LIMIT $limitvalue,$limit
         ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
    $c            = mysqli_fetch_object(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT FOUND_ROWS() AS `rows`"));
    $countedRows  =  (isset($c->rows) ? $c->rows : '0');
  }
}
define('LOAD_DATE_PICKERS', 1);
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (isset($_GET['keys']) && isset($q)) {
      ?>
      <li><a href="index.php?p=search-fields"><?php echo $msg_header18; ?></a></li>
      <li class="active"><?php echo $msg_search6.' ('.@number_format($countedRows).')'; ?></li>
      <?php
      } else {
      ?>
      <li class="active"><?php echo $msg_header18; ?></li>
      <?php
      }
      ?>
    </ol>

    <form method="get" action="#">
    <div class="row searcharea"<?php echo (isset($_GET['keys']) && $_GET['keys'] ? ' style="display:none"' : ''); ?>>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-search fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_search; ?></span></a></li>
          <li><a href="#two" data-toggle="tab"><i class="fa fa-calendar fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_search19; ?></span></a></li>
          <li><a href="#three" data-toggle="tab"><i class="fa fa-filter fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_search20; ?></span></a></li>
        </ul>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="tab-content">
              <div class="tab-pane active in" id="one">

                <div class="form-group">
                  <label><?php echo $msadminlang3_1[30]; ?></label>
                  <input type="text" class="form-control" name="keys" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($_GET['keys']) ? mswSafeDisplay($_GET['keys']) : ''); ?>">
                </div>
                <?php
                if ($SETTINGS->disputes == 'yes') {
                  if (in_array('open', $userAccess) || in_array('close', $userAccess) || $MSTEAM->id == '1') {
                  ?>
                  <div class="form-group">
                    <div class="checkbox">
                      <label><input type="checkbox" name="area[]" value="tickets"<?php echo (!empty($_GET['area']) && in_array('tickets',$_GET['area']) ? ' checked="checked"' : (empty($_GET['area']) && SEARCH_AUTO_CHECK_TICKETS == 'yes' ? ' checked="checked"' : '')); ?>> <?php echo $msg_search12; ?></label>
                    </div>
                  </div>
                  <?php
                  }
                  if (in_array('disputes', $userAccess) || in_array('cdisputes', $userAccess) || $MSTEAM->id == '1') {
                  ?>
                  <div class="form-group">
                    <div class="checkbox">
                      <label><input type="checkbox" name="area[]" value="disputes"<?php echo (!empty($_GET['area']) && in_array('disputes',$_GET['area']) ? ' checked="checked"' : (empty($_GET['area']) && SEARCH_AUTO_CHECK_DISPUTES == 'yes' ? ' checked="checked"' : '')); ?>> <?php echo $msg_search13; ?></label>
                    </div>
                  </div>
                  <?php
                  }
		            }
                ?>

              </div>
              <div class="tab-pane fade" id="two">

                <div class="form-group">
                  <label><?php echo $msg_search7; ?></label>
                  <input type="text" class="form-control" id="from" tabindex="<?php echo (++$tabIndex); ?>" name="from" value="<?php echo (isset($_GET['from']) ? mswSafeDisplay($_GET['from']) : ''); ?>">
                  <input type="text" class="form-control" id="to" tabindex="<?php echo (++$tabIndex); ?>" name="to" value="<?php echo (isset($_GET['to']) ? mswSafeDisplay($_GET['to']) : ''); ?>" style="margin-top:10px">
                </div>

              </div>
              <div class="tab-pane fade" id="three">

                <div class="form-group">
                  <label><?php echo $msg_search4; ?></label>
                  <select name="dept" tabindex="<?php echo (++$tabIndex); ?>" class="form-control">
                  <option value="0">- - - - -</option>
                  <?php
                  $q_dept = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id`,`name` FROM `" . DB_PREFIX . "departments` " . mswSQLDepartmentFilter($mswDeptFilterAccess,'WHERE') . " ORDER BY `orderBy`")
                            or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
                  while ($DEPT = mysqli_fetch_object($q_dept)) {
                  ?>
                  <option value="<?php echo $DEPT->id; ?>"<?php echo mswSelectedItem('dept',$DEPT->id,true); ?>><?php echo mswSafeDisplay($DEPT->name); ?></option>
                  <?php
                  }
                  ?>
                  </select>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_search5; ?></label>
                  <select name="priority" tabindex="<?php echo (++$tabIndex); ?>" class="form-control">
                  <option value="0">- - - - -</option>
                  <?php
                  foreach ($ticketLevelSel AS $k => $v) {
                  ?>
                  <option value="<?php echo $k; ?>"<?php echo mswSelectedItem('priority',$k,true); ?>><?php echo $v; ?></option>
                  <?php
                  }
                  ?>
                  </select>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_search8; ?></label>
                  <select name="status" tabindex="<?php echo (++$tabIndex); ?>" class="form-control">
                  <option value="0">- - - - -</option>
                  <option value="open"<?php echo mswSelectedItem('status','open',true); ?>><?php echo $msg_viewticket14; ?></option>
                  <option value="close"<?php echo mswSelectedItem('status','close',true); ?>><?php echo $msg_viewticket15; ?></option>
                  <option value="closed"<?php echo mswSelectedItem('status','closed',true); ?>><?php echo $msg_viewticket16; ?></option>
                  </select>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_open31; ?></label>
                  <select name="assign" tabindex="<?php echo (++$tabIndex); ?>" class="form-control">
                  <option value="0">- - - - -</option>
                  <?php
                  $q_users  = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id`,`name` FROM `" . DB_PREFIX . "users` ORDER BY `name`")
                              or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
                  while ($U = mysqli_fetch_object($q_users)) {
                  ?>
                  <option value="<?php echo $U->id; ?>"<?php echo mswSelectedItem('assign',$U->id,true); ?>><?php echo mswCleanData($U->name); ?></option>
                  <?php
                  }
                  ?>
                  </select>
                </div>

                <div class="form-group">
                  <label><?php echo $msadminlang3_1[29]; ?></label>
                  <select name="field" tabindex="<?php echo (++$tabIndex); ?>" class="form-control">
                  <option value="0">- - - - -</option>
                  <?php
                  $q_fld  = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id`,`fieldInstructions` FROM `" . DB_PREFIX . "cusfields` ORDER BY `fieldInstructions`")
                            or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
                  while ($F = mysqli_fetch_object($q_fld)) {
                  ?>
                  <option value="<?php echo $F->id; ?>"<?php echo mswSelectedItem('field',$F->id,true); ?>><?php echo mswCleanData($F->fieldInstructions); ?></option>
                  <?php
                  }
                  ?>
                  </select>
                </div>

              </div>
            </div>
          </div>
          <div class="panel-footer">
           <input type="hidden" name="p" value="search-fields">
           <?php
           if ($SETTINGS->disputes == 'no') {
           ?>
           <input type="hidden" name="area[]" value="tickets">
           <?php
           }
           ?>
            <button class="btn btn-primary" type="submit"><i class="fa fa-search fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo mswCleanData($msg_search2); ?></span></button>
          </div>
        </div>

      </div>
    </div>
    </form>

    <?php
    // Search results.
    if (isset($_GET['keys']) && $_GET['keys'] && isset($q)) {
    ?>
    <form method="post" action="#">
    <div class="row resultsarea">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-heading text-right">
            <?php
            define('SKIP_SEARCH_BOX', 1);
            include(PATH . 'templates/system/tickets/global/order-filter.php');
            include(PATH . 'templates/system/tickets/global/status-filter.php');
            ?>
            <div class="mobilebreakpoint">
            <?php
            include(PATH . 'templates/system/tickets/global/dept-filter.php');
            include(PATH . 'templates/system/bootstrap/page-filter.php');
            ?>
            <button class="btn btn-info btn-sm" type="button" onclick="mswSearchReload('search-fields')"><i class="fa fa-search fa-fw"></i></button>
            </div>
          </div>
          <div class="panel-body">

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
                <td><a href="?p=view-<?php echo ($TICKETS->isDisputed == 'yes' ? 'dispute' : 'ticket'); ?>&amp;id=<?php echo $TICKETS->ticketID; ?>" title="<?php echo mswSafeDisplay($msg_viewticket11); ?>"><?php echo mswTicketNumber($TICKETS->ticketID); ?></a>
                <span class="ticketPriority"><?php echo mswSafeDisplay($TICKETS->levelName); ?></span>
                </td>
                <?php
                if ($TICKETS->isDisputed == 'yes') {
                ?>
                <td><?php echo mswSafeDisplay($TICKETS->subject); ?>
                <span class="tdCellInfoDispute">
                 <i class="fa fa-angle-right fa-fw"></i> <?php echo $MSYS->department($TICKETS->department,$msg_script30); ?>
                </span>
                <span class="tdCellInfoDispute">
                 <i class="fa fa-bullhorn fa-fw"></i> <?php echo str_replace('{count}',($TICKETS->disputeCount + 1),$msg_showticket30); ?>
                </span>
                </td>
                <?php
                } else {
                ?>
                <td><?php echo mswSafeDisplay($TICKETS->subject); ?>
                <span class="tdCellInfo"><i class="fa fa-angle-right fa-fw"></i><?php echo $MSYS->department($TICKETS->department,$msg_script30); ?></span>
                </td>
                <?php
                }
                ?>
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
                 <a href="?p=view-<?php echo ($TICKETS->isDisputed == 'yes' ? 'dispute' : 'ticket'); ?>&amp;id=<?php echo $TICKETS->ticketID; ?>&amp;editNotes=yes" onclick="iBox.showURL(this.href,'',{width:<?php echo IBOX_NOTES_WIDTH; ?>,height:<?php echo IBOX_NOTES_HEIGHT; ?>});return false" title="<?php echo mswSafeDisplay($msg_viewticket72); ?>"><i class="fa fa-file-text fa-fw"></i></a>
                 <?php
                 }
                 ?>
                 <a href="?p=view-ticket&amp;id=<?php echo $TICKETS->ticketID; ?>&amp;quickView=yes" onclick="iBox.showURL(this.href,'',{width:<?php echo IBOX_QVIEW_WIDTH; ?>,height:<?php echo IBOX_QVIEW_HEIGHT; ?>});return false" title="<?php echo mswSafeDisplay($msadminlang3_1[31]); ?>"><i class="fa fa-search fa-fw"></i></a>
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

          <?php
          if ($countedRows > 0) {
          ?>
          <div class="panel-footer">
           <input type="hidden" name="orderbyexp" value="<?php echo mswSafeDisplay($orderBy); ?>">
           <?php
           if (USER_DEL_PRIV == 'yes') {
	         ?>
           <button onclick="mswConfirmButtonAction('<?php echo mswSafeDisplay($msg_script_action); ?>','tickdel');return false;" class="btn btn-danger button_margin_right20" disabled="disabled" type="button" id="delButton"><i class="fa fa-trash fa-fw" title="<?php echo mswCleanData($msg_open15); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_open15); ?></span> <span id="mswCVal">(0)</span></button>
	         <?php
	         }
           ?>
           <button class="btn btn-primary" onclick="mswProcess('tickexp')" type="button" id="delButton2" disabled="disabled"><i class="fa fa-save fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_search25); ?></span> <span id="mswCVal2">(0)</span></button>
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
    <?php
    }
    ?>

  </div>