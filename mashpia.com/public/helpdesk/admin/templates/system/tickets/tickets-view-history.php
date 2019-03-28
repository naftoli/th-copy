<?php if (!defined('TICKET_LOADER') || !isset($tickID)) { exit; }
    // Show ticket history..
	  if ($SETTINGS->ticketHistory == 'yes' && $MSTEAM->ticketHistory == 'yes') {
	  $qTH = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "tickethistory`
             WHERE `ticketID` = '{$tickID}'
             ORDER BY `ts` DESC
             ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
	  $historyRows = mysqli_num_rows($qTH);
    ?>
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
      <div class="panel panel-default history-panel">
        <div class="panel-heading colorchangeheader text-right right-align">
          <?php
          if ($historyRows > 0) {
            $links = array();
            $links[] = array(
              'link' => '#',
              'name' => $msg_viewticket112,
              'extra' => 'onclick="mswProcess(\'tickhisexp\',\'' . $tickID . '\');return false"'
            );
            if (USER_DEL_PRIV == 'yes') {
              $links[] = array(
                'link' => 'sep'
              );
              $links[] = array(
                'link' => '?p=view-ticket&amp;exportHistory=' . $tickID,
                'name' => $msg_viewticket118,
                'extra' => 'onclick="mswRemoveHistory(\'all\', \'' . $tickID . '\',\'' . mswSafeDisplay($msg_script_action) . '\');return false"'
              );
            }
            echo $MSBOOTSTRAP->button($msg_script43, $links, ' dropdown-menu-right', '', 'admin', 'cog');
          } else {
            echo '&nbsp;';
          }
          ?>
          <span class="pull-left<?php echo ($historyRows > 0 ? ' margin_top_7' : ''); ?>">
           <i class="fa fa-clock-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo strtoupper($msg_viewticket110) . '</span> ( #' . mswTicketNumber($tickID); ?> )
          </span>
        </div>
        <div class="panel-body historybody">

          <?php
          if ($historyRows > 0) {
          ?>
          <div class="table-responsive historyarea">
            <table class="table table-striped table-hover">
            <tbody>
              <?php
              while ($HIS = mysqli_fetch_object($qTH)) {
              ?>
              <tr id="hdata_<?php echo $HIS->id; ?>">
                <td><?php echo $MSDT->mswDateTimeDisplay($HIS->ts, $SETTINGS->dateformat) . ' @ ' . $MSDT->mswDateTimeDisplay($HIS->ts, $SETTINGS->timeformat) . ($HIS->ip ? '<span class="tdCellInfo">' . loadIPAddresses($HIS->ip) . '</span>' : ''); ?></td>
                <td><?php echo mswCleanData($HIS->action); ?></td>
                <?php
                if (USER_DEL_PRIV == 'yes') {
                ?>
                <td><i class="fa fa-times fa-fw ms_red cursor_pointer" onclick="mswRemoveHistory('<?php echo $HIS->id; ?>', '<?php echo $tickID; ?>', '<?php echo mswSafeDisplay($msg_script_action); ?>')" title="<?php echo mswSafeDisplay($msg_public_history12); ?>"></i></td>
                <?php
                }
                ?>
              </tr>
              <?php
              }
              ?>
            </tbody>
            </table>
          </div>
          <?php
          } else {
          ?>
          <div class="nothing_to_see"><?php echo $msg_viewticket111; ?></div>
          <?php
          }
          ?>

        </div>
      </div>
    </div>
    <?php
    }
    ?>


