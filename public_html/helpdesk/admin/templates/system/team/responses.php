<?php if (!defined('PATH') || !isset($_GET['id'])) { exit; }
$_GET['id']   = (int) $_GET['id'];
$SQL          = '';

if (isset($_GET['keys']) && $_GET['keys']) {
  $_GET['keys']  = mswSafeImportString(strtolower($_GET['keys']));
  $SQL           = 'AND (LOWER(`' . DB_PREFIX . 'replies`.`comments`) LIKE \'%' . $_GET['keys'] . '%\')';
}
if (isset($_GET['from'],$_GET['to']) && $_GET['from'] && $_GET['to']) {
  $from  = $MSDT->mswDatePickerFormat($_GET['from']);
  $to    = $MSDT->mswDatePickerFormat($_GET['to']);
  $SQL  .= " AND (DATE(FROM_UNIXTIME(`" . DB_PREFIX . "replies`.`ts`)) BETWEEN '{$from}' AND '{$to}')";
}

$q            = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SQL_CALC_FOUND_ROWS *,
                `" . DB_PREFIX . "replies`.`id` AS `repid`,
                `" . DB_PREFIX . "replies`.`ts` AS `repStamp`,
                `" . DB_PREFIX . "replies`.`comments` AS `repcomms`,
                `" . DB_PREFIX . "tickets`.`id` AS `tickID`
                FROM `" . DB_PREFIX . "replies`
                LEFT JOIN `" . DB_PREFIX . "tickets`
                ON `" . DB_PREFIX . "replies`.`ticketID` = `" . DB_PREFIX . "tickets`.`id`
                WHERE `replyType` = 'admin'
                AND `replyUser`   = '{$_GET['id']}'
                AND `spamFlag`    = 'no'
                $SQL
                GROUP BY `" . DB_PREFIX . "replies`.`id`,`" . DB_PREFIX . "replies`.`ticketID`
                ORDER BY `" . DB_PREFIX . "replies`.`id` DESC
                LIMIT $limitvalue,$limit
                ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
$c            = mysqli_fetch_object(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT FOUND_ROWS() AS `rows`"));
$countedRows  = (isset($c->rows) ? $c->rows : '0');
define('LOAD_DATE_PICKERS', 1);
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (in_array('teamman', $userAccess) || $MSTEAM->id == '1') {
      ?>
      <li><a href="index.php?p=teamman"><?php echo $msg_adheader58; ?></a></li>
      <?php
      }
      ?>
      <li><a href="index.php?p=team&amp;edit=<?php echo $_GET['id']; ?>"><?php echo mswSafeDisplay($U->name); ?></a></li>
      <li class="active"><?php echo $msg_user87; ?> (<?php echo number_format($countedRows); ?>)</li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <?php
          // Page filter..
          if ($countedRows > 0) {
          ?>
          <div class="panel-heading text-right">
            <?php
            include(PATH . 'templates/system/bootstrap/page-filter.php');
            ?>
          </div>
          <?php
          }
          ?>
          <div class="panel-body">

            <?php
            // Search..
            include(PATH . 'templates/system/bootstrap/search-responses.php');
            ?>

            <div class="table-responsive">
              <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th><?php echo $msg_response12; ?></th>
                  <th><?php echo $msg_script43; ?></th>
                </tr>
              </thead>
              <tbody>
                <?php
                if ($countedRows > 0) {
                while ($REPLY = mysqli_fetch_object($q)) {
                ?>
                <tr>
                <td>
                <?php echo (TEAM_REPLY_COMM_LIMIT > 0 ? substr(mswCleanData($MSBB->cleaner($REPLY->repcomms)),0,TEAM_REPLY_COMM_LIMIT) . '...' : mswCleanData($MSBB->cleaner($REPLY->repcomms))); ?>
                <span class="tdCellInfo">
                 <?php echo $msg_user89; ?>: <span class="highlight"><?php echo date($SETTINGS->dateformat,$REPLY->repStamp); ?></span>
                </span>
                </td>
                <td>
                  <a href="?p=edit-reply&amp;id=<?php echo $REPLY->repid; ?>" title="<?php echo mswSafeDisplay($msg_script9); ?>"><i class="fa fa-pencil fa-fw"></i></a>
                  <a href="?p=view-ticket&amp;id=<?php echo $REPLY->tickID; ?>" title="<?php echo mswSafeDisplay($msg_open7); ?>"><i class="fa fa-search fa-fw"></i></a>
                </td>
                </tr>
                <?php
                }
                } else {
                ?>
                <tr class="warning nothing_to_see">
                  <td colspan="2"><?php echo $msg_user22; ?></td>
                </tr>
                <?php
                }
                ?>
              </tbody>
              </table>
            </div>

          </div>
          <?php
          if (in_array('teamman', $userAccess)  || $MSTEAM->id == '1') {
          ?>
          <div class="panel-footer">
           <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=teamman')"><i class="fa fa-times fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels11; ?></span></button>
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