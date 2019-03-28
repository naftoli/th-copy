<?php if (!defined('PATH')) { exit; }
$orderBy   = 'ORDER BY `' . DB_PREFIX . 'mailbox`.`ts` DESC';
$keys      = (isset($_GET['keys']) && $_GET['keys'] ? $_GET['keys'] : '');
$searchSQL = '';
// Are we searching?
if ($keys) {
  $searchSQL = 'AND (`' . DB_PREFIX . 'mailbox`.`subject` LIKE \'%' . mswSafeImportString($keys) . '%\' OR `' . DB_PREFIX . 'mailbox`.`message` LIKE \'%' . mswSafeImportString($keys) . '%\')';
}
$q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SQL_CALC_FOUND_ROWS *,
     `" . DB_PREFIX . "mailbox`.`staffID` AS `starter`,
     `" . DB_PREFIX . "mailbox`.`ts` AS `mailStamp`,
     `" . DB_PREFIX . "mailassoc`.`mailID` AS `messageID`
     FROM `" . DB_PREFIX . "mailassoc`
     LEFT JOIN `" . DB_PREFIX . "mailbox`
     ON `" . DB_PREFIX . "mailassoc`.`mailID` = `" . DB_PREFIX . "mailbox`.`id`
     LEFT JOIN `" . DB_PREFIX . "users`
     ON `" . DB_PREFIX . "users`.`id`         = `" . DB_PREFIX . "mailbox`.`staffID`
     WHERE `folder`                           = '{$toLoad}'
     AND `" . DB_PREFIX . "mailassoc`.`staffID` = '{$MSTEAM->id}'
     " . ($searchSQL ? $searchSQL . mswDefineNewline() . 'GROUP BY `' . DB_PREFIX . 'mailassoc`.`mailID`' : '') . "
     " . $orderBy . "
     LIMIT $limitvalue,$limit
     ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
$c            = mysqli_fetch_object(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT FOUND_ROWS() AS `rows`"));
$countedRows  =  (isset($c->rows) ? $c->rows : '0');
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <li class="active"><?php echo $msg_adheader61; ?> (<?php echo $boxName; ?>)</li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <?php
      include(PATH . 'templates/system/mailbox/mailbox-nav.php');
	    ?>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-heading text-right">
            <button class="btn btn-success btn-sm" type="button" onclick="mswWindowLoc('?p=mailbox&amp;new=1')"><i class="fa fa-pencil fa-fw"></i></button>
            <?php
            if ($countedRows > 0) {
            ?>
            <button class="btn btn-info btn-sm" type="button" onclick="mswToggleButton('search')"><i class="fa fa-search fa-fw"></i></button>
            <?php
            }
            ?>
          </div>
          <div class="panel-body">

            <?php
            // Search..
            if ($countedRows > 0) {
            include(PATH . 'templates/system/bootstrap/search-box.php');
            }
            ?>

            <div class="table-responsive">
              <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th style="width:5%">
                    <input type="checkbox" onclick="mswCheckBoxes(this.checked,'.panel-body');mswCheckCount('panel-body','delButton','mswCVal')">
                  </th>
                  <th><?php echo $msg_viewticket25; ?></th>
                  <th><?php echo $msg_mailbox17; ?></th>
                  <th><?php echo $msg_open37; ?></th>
                  <th><?php echo $msgadminlang3_1mailbox[1]; ?></th>
               </tr>
              </thead>
              <tbody>
                <?php
                if ($countedRows > 0) {
                while ($MSG = mysqli_fetch_object($q)) {
                $last = $MSMB->getLastReply($MSG->messageID);
                $rec  = $MSMB->getRecipient($MSG->messageID,$MSTEAM->id);
                ?>
                <tr id="datatr_<?php echo $MSG->messageID; ?>">
                 <td><input type="checkbox" onclick="mswCheckCount('panel-body','delButton','mswCVal')" name="del[]" value="<?php echo $MSG->messageID; ?>"></td>
                 <td><a class="mail<?php echo $MSG->status; ?>" href="?p=mailbox&amp;msg=<?php echo $MSG->messageID; ?>" title="<?php echo mswSafeDisplay($msg_mailbox18); ?>"><?php echo mswCleanData($MSG->subject); ?></a>
                 <span class="ticketDate">
                 <?php
                 // If person who sent message is logged in, its to, else its from..
                 if ($MSG->staffID == $MSTEAM->id) {
                   echo $msg_mailbox34.': '.$rec;
                 } else {
                   //echo $msg_mailbox33.': '.$rec;
                 }
                 ?>
                 </span>
                 </td>
                 <td><?php echo mswCleanData($MSG->name); ?>
                 <span class="ticketDate"><?php echo $MSDT->mswDateTimeDisplay($MSG->mailStamp,$SETTINGS->dateformat); ?> @ <?php echo $MSDT->mswDateTimeDisplay($MSG->mailStamp,$SETTINGS->timeformat); ?></span>
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
                 switch($MSG->status) {
                   case 'read':
                     ?>
                     <i class="fa fa-flag-o fa-fw" title="<?php echo mswSafeDisplay($msg_mailbox19); ?>"></i>
                     <?php
                     break;
                   case 'unread':
                     ?>
                     <i class="fa fa-flag fa-fw" title="<?php echo mswSafeDisplay($msg_mailbox20); ?>"></i>
                     <?php
                     break;
                 }
                 ?>
                 </td>
                </tr>
                <?php
                }
                } else {
                ?>
                <tr class="warning nothing_to_see">
                 <td colspan="5"><?php echo $msg_mailbox16; ?></td>
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
            <div class="btn-group dropup button_margin_right20">
            <a class="btn tn-primary dropdown-toggle" data-toggle="dropdown" href="#">
             <i class="fa fa-share-square-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_mailbox24; ?></span>
             <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
            <?php
            foreach ($moveToFolders AS $k => $v) {
            // Don`t show current folder..
            if ($k != $toLoad) {
            // Spacer..
            if ($k == '-') {
            ?>
            <li role="separator" class="divider"></li>
            <?php
            } else {
            ?>
            <li><a href="#" onclick="mswConfirmButtonAction('<?php echo mswSafeDisplay($msg_script_action); ?>','mbmove','<?php echo $k; ?>');return false;"><?php echo $msgadminlang3_1mailbox[2] . ': ' .$v; ?></a></li>
            <?php
            }
            }
            }
            ?>
            <li role="separator" class="divider"></li>
            <li><a href="#" onclick="mswConfirmButtonAction('<?php echo mswSafeDisplay($msg_script_action); ?>','mbread');return false;"><?php echo mswSafeDisplay($msgadminlang3_1mailbox[3]); ?></a></li>
            <li><a href="#" onclick="mswConfirmButtonAction('<?php echo mswSafeDisplay($msg_script_action); ?>','mbunread');return false;"><?php echo mswSafeDisplay($msgadminlang3_1mailbox[4]); ?></a></li>
            </ul>
           </div>
           <?php
           if ($toLoad == 'bin' && ($MSTEAM->mailDeletion == 'yes' || $MSTEAM->id == '1')) {
           ?>
           <button onclick="mswConfirmButtonAction('<?php echo mswSafeDisplay($msg_script_action); ?>','mbdel');return false;" class="btn btn-danger button_margin_right20" disabled="disabled" type="button" id="delButton" title="<?php echo mswCleanData($msg_levels9); ?>"><i class="fa fa-trash fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo mswCleanData($msg_levels9); ?></span> <span id="mswCVal">(0)</span></button>
           <div class="pull-right"><button onclick="mswConfirmButtonAction('<?php echo mswSafeDisplay($msg_script_action); ?>','mbclear');return false;" type="button" class="btn btn-warning"><i class="fa fa-trash-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo mswSafeDisplay($msg_mailbox23); ?></span></button></div>
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