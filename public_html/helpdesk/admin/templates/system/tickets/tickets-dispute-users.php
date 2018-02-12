<?php if (!defined('PATH') || !isset($_GET['disputeUsers'])) { exit; }
$dispID = (int) $_GET['disputeUsers'];
$q      = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT *,
          `" . DB_PREFIX . "disputes`.`id` AS `disputeID`,
					`" . DB_PREFIX . "portal`.`id` AS `portalID`,
          (SELECT count(*) FROM `" . DB_PREFIX . "replies`
           WHERE `" . DB_PREFIX . "replies`.`disputeUser` = `" . DB_PREFIX . "disputes`.`id`
					 AND `" . DB_PREFIX . "replies`.`ticketID` = '{$dispID}'
          ) AS `tickRepCount`
					FROM `" . DB_PREFIX . "disputes`
					LEFT JOIN `" . DB_PREFIX . "portal`
					ON `" . DB_PREFIX . "disputes`.`visitorID`  = `" . DB_PREFIX . "portal`.`id`
					WHERE `ticketID`                            = '{$dispID}'
					ORDER BY `name`
					") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
// Load ticket data..
$SUPTICK = mswGetTableData('tickets', 'id', $dispID);
if (isset($SUPTICK->visitorID)) {
  $PORTAL = mswGetTableData('portal', 'id', $SUPTICK->visitorID);
}
// Check we have all data..
if (!isset($PORTAL->name)) {
  die('An error has occurred. Portal data not found for visitor ID:  '.$SUPTICK->visitorID);
}
define('JS_LOADER', 'dispute-users.php');
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      // Status of ticket for link
      $link = getTicketLink($SUPTICK);
      if ($link[0]) {
      ?>
      <li><a href="index.php?p=<?php echo $link[0]; ?>"><?php echo $link[1]; ?></a></li>
      <?php
      }
      ?>
      <li><a href="?p=view-<?php echo ($SUPTICK->isDisputed == 'yes' ? 'dispute' : 'ticket'); ?>&amp;id=<?php echo $SUPTICK->id; ?>"><?php echo ($SUPTICK->isDisputed == 'yes' ? $msg_portal35 : $msg_portal8); ?></a></li>
      <li class="active"><?php echo $msg_disputes8; ?>: #<?php echo mswTicketNumber($_GET['disputeUsers']); ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
         <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-user fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_script_action4; ?></span></a></li>
         <li><a href="#two" data-toggle="tab"><i class="fa fa-user-plus fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_viewticket58; ?></span></a></li>
        </ul>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="tab-content">
              <div class="tab-pane active in" id="one">

                <div class="table-responsive">
                  <table class="table table-striped table-hover">
                  <thead>
                    <tr>
                      <th><?php echo $msg_accounts . '/' . $msg_accounts2; ?></th>
                      <th><?php echo $msg_viewticket106; ?></th>
                      <th><?php echo $msg_viewticket105; ?></th>
                      <th><?php echo $msg_viewticket115; ?></th>
                      <th><?php echo $msg_script47; ?></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>
                      <input type="hidden" name="userID[]" value="t_<?php echo $PORTAL->id; ?>">
                      <?php
                      if (in_array('accounts', $userAccess) || $MSTEAM->id == '1') {
                      ?>
                      <a href="?p=accounts&amp;edit=<?php echo $PORTAL->id; ?>"><?php echo mswSafeDisplay($PORTAL->name); ?></a>
                      <?php
                      } else {
                      echo mswSafeDisplay($PORTAL->name);
                      }
                      ?>
                      <span class="tdCellInfo">
                      <?php echo mswCleanData($PORTAL->email); ?>
                      </span>
                      </td>
                      <td><?php echo (mswRowCount('replies WHERE `ticketID` = \'' . $dispID . '\' AND `replyType` = \'visitor\' AND `disputeUser` = \'0\'') + 1); ?></td>
                      <td><input type="checkbox" name="priv[]" value="t_<?php echo $PORTAL->id; ?>"<?php echo ($SUPTICK->disPostPriv == 'yes' ? ' checked="checked"' : ''); ?>></td>
                      <td><input type="checkbox" name="notify[]" value="t_<?php echo $PORTAL->id; ?>"></td>
                      <td>&nbsp;</td>
                    </tr>
                    <?php
                    while ($ACC = mysqli_fetch_object($q)) {
                    ?>
                    <tr id="duser_<?php echo $ACC->disputeID; ?>">
                      <td>
                      <input type="hidden" name="userID[]" value="<?php echo $ACC->portalID; ?>">
                      <?php
                      if (in_array('accounts', $userAccess) || $MSTEAM->id == '1') {
                      ?>
                      <a href="?p=accounts&amp;edit=<?php echo $ACC->portalID; ?>"><?php echo mswSafeDisplay($ACC->name); ?></a>
                      <?php
                      } else {
                      echo mswSafeDisplay($ACC->name);
                      }
                      ?>
                      <span class="tdCellInfo">
                      <?php echo mswCleanData($ACC->email); ?>
                      </span>
                      </td>
                      <td><?php echo number_format($ACC->tickRepCount); ?></td>
                      <td><input type="checkbox" name="priv[]" value="<?php echo $ACC->portalID; ?>"<?php echo ($ACC->postPrivileges == 'yes' ? ' checked="checked"' : ''); ?>></td>
                      <td><input type="checkbox" name="notify[]" value="<?php echo $ACC->portalID; ?>"></td>
                      <td><a href="#" onclick="mswRowForDel('duser_<?php echo $ACC->disputeID; ?>','duser');return false"><i class="fa fa-times fa-fw ms_red"></i></a></td>
                    </tr>
                    <?php
                    }
                    ?>
                  </tbody>
                  </table>
                </div>

              </div>
              <div class="tab-pane fade" id="two">

                <div class="form-group">
                  <label><?php echo $msadminlang3_1adminviewticket[9]; ?></label>
                  <input type="text" class="form-control" name="search" value="">
                </div>

                <div class="form-group">
                  <label><?php echo $msadminlang3_1adminviewticket[11]; ?></label>
                  <div class="form-group">
                    <div class="form-group input-group">
                     <input type="text" class="form-control" name="newacc" value="">
                     <span class="input-group-addon"><a href="#" onclick="mswLoadDisputeNewUser();return false"><i class="fa fa-plus fa-fw"></i></a></span>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>
          <div class="panel-footer">
           <input type="hidden" name="disputeID" value="<?php echo $dispID; ?>">
           <button onclick="mswConfirmButtonAction('<?php echo mswSafeDisplay($msg_script_action); ?>','tickdispusers');return false;" class="btn btn-primary" type="button"><i class="fa fa-check fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_viewticket65; ?></span></button>
		       <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=view-dispute&amp;id=<?php echo $dispID; ?>')"><i class="fa fa-times fa-fw"></i> <?php echo mswSafeDisplay($msg_levels11); ?></button>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>