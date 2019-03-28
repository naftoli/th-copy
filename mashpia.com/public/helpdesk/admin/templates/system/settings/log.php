<?php if (!defined('PATH')) { exit; }
$from        = (isset($_GET['from']) && $MSDT->mswDatePickerFormat($_GET['from'])!='0000-00-00' ? $_GET['from'] : '');
$to          = (isset($_GET['to']) && $MSDT->mswDatePickerFormat($_GET['to'])!='0000-00-00' ? $_GET['to'] : '');
$keys        = '';
$where       = array();
if (isset($_GET['keys']) && $_GET['keys']) {
  $chop  = explode(' ',$_GET['keys']);
  $words = '';
  for ($i=0; $i<count($chop); $i++) {
    $words .= ($i ? 'OR ' : 'WHERE (') . "`" . DB_PREFIX . "portal`.`name` LIKE '%" . mswSafeImportString($chop[$i]) . "%' OR `" . DB_PREFIX . "users`.`name` LIKE '%" . mswSafeImportString($chop[$i]) . "%' ";
  }
  if ($words) {
    $where[] = $words.')';
  }
}
if ($from && $to) {
  $where[]  = (!empty($where) ? 'AND ' : 'WHERE ').'DATE(FROM_UNIXTIME(`' . DB_PREFIX . 'log`.`ts`)) BETWEEN \''.$MSDT->mswDatePickerFormat($from).'\' AND \''.$MSDT->mswDatePickerFormat($to).'\'';
}
$q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SQL_CALC_FOUND_ROWS *,
     `" . DB_PREFIX . "log`.`ts` AS `lts`,
     `" . DB_PREFIX . "log`.`id` AS `logID`,
     `" . DB_PREFIX . "log`.`userID` AS `personID`,
     `" . DB_PREFIX . "log`.`ip` AS `entryLogIP`,
     `" . DB_PREFIX . "portal`.`name` AS `portalName`,
     `" . DB_PREFIX . "users`.`name` AS `userName`
     FROM `" . DB_PREFIX . "log`
     LEFT JOIN `" . DB_PREFIX . "users`
     ON `" . DB_PREFIX . "log`.`userID` = `" . DB_PREFIX . "users`.`id`
     LEFT JOIN `" . DB_PREFIX . "portal`
     ON `" . DB_PREFIX . "log`.`userID` = `" . DB_PREFIX . "portal`.`id`
     " . (!empty($where) ? mswSafeImportString(implode(mswDefineNewline(),$where)) : '') . "
     ORDER BY `" . DB_PREFIX . "log`.`id` DESC
     LIMIT $limitvalue,$limit
     ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
$c            = mysqli_fetch_object(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT FOUND_ROWS() AS `rows`"));
$countedRows  =  (isset($c->rows) ? $c->rows : '0');
$actualRows   = mswRowCount('log');
define('LOAD_DATE_PICKERS', 1);
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (in_array('settings', $userAccess) || $MSTEAM->id == '1') {
      ?>
      <li><a href="index.php?p=settings"><?php echo $msg_adheader2; ?></a></li>
      <?php
      }
      ?>
      <li class="active"><?php echo $msg_adheader20; ?> (<?php echo @number_format($countedRows); ?>)</li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <?php
          if ($actualRows>0) {
          ?>
          <div class="panel-heading text-right">
            <?php
            // Filters..
            $links   = array(array('link' => '?p='.$_GET['p'].mswQueryParams(array('p','type','from','to','q')),  'name' => $msg_log11));
            $links[] = array('link' => '?p=' . $_GET['p'] . '&amp;type=user' . mswQueryParams(array('p','type','next')),'name' => $msg_log13);
            $links[] = array('link' => '?p=' . $_GET['p'] . '&amp;type=acc' . mswQueryParams(array('p','type','next')),'name' => $msg_log12);
            echo $MSBOOTSTRAP->button($msg_search20,$links);
            include(PATH . 'templates/system/bootstrap/page-filter.php');
            ?>
          </div>
          <?php
          }
          ?>
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
                  <th><?php echo $msg_log; ?></th>
                  <th><?php echo $msg_log16; ?></th>
                  <th><?php echo $msg_log8; ?></th>
                  <th><?php echo $msg_log7; ?></th>
               </tr>
              </thead>
              <tbody>
               <?php
               if (mysqli_num_rows($q)>0) {
               while ($LOG = mysqli_fetch_object($q)) {
               // IP entry..
               $ips_html = '';
               if (strpos($LOG->entryLogIP,',')!==false) {
                 $ips = array_map('trim',explode(',',mswCleanData($LOG->entryLogIP)));
                 foreach ($ips AS $ipA) {
                   $ips_html .= mswCleanData($ipA).' <a href="'.str_replace('{ip}',mswCleanData($ipA),IP_LOOKUP).'" onclick="window.open(this);return false"><i class="fa fa-external-link fa-fw"></i></a><br>';
                 }
               } else {
                 $ips_html = mswCleanData($LOG->entryLogIP).' <a href="'.str_replace('{ip}',mswCleanData($LOG->entryLogIP),IP_LOOKUP).'" onclick="window.open(this);return false"><i class="fa fa-external-link fa-fw"></i></a>';
               }
               ?>
               <tr id="datatr_<?php echo $LOG->logID; ?>">
               <?php
               if (USER_DEL_PRIV == 'yes') {
               ?>
               <td><input type="checkbox" onclick="mswCheckCount('panel-body','delButton','mswCVal')" name="del[]" value="<?php echo $LOG->logID; ?>"></td>
               <?php
               }
               ?>
               <td><?php echo mswSafeDisplay(($LOG->type=='acc' ? $LOG->portalName : $LOG->userName)); ?> <a href="?p=<?php echo ($LOG->type=='acc' ? 'accounts&amp;edit='.$LOG->personID : 'team&amp;edit='.$LOG->personID); ?>" title="<?php echo mswSafeDisplay($msg_script9); ?>"><i class="fa fa-edit fa-fw"></i></a></td>
               <td><?php echo ($LOG->type=='user' ? $msg_log15 : $msg_log14); ?></td>
               <td><?php echo $ips_html; ?></td>
               <td><?php echo $MSDT->mswDateTimeDisplay($LOG->lts,$SETTINGS->dateformat).' / '.$MSDT->mswDateTimeDisplay($LOG->lts,$SETTINGS->timeformat); ?></td>
               </tr>
               <?php
               }
               } else {
               ?>
               <tr class="warning nothing_to_see">
                <td colspan="<?php echo (USER_DEL_PRIV == 'yes' ? '5' : '4'); ?>"><?php echo $msg_log4; ?></td>
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
            <input type="hidden" name="from" value="<?php echo mswSafeDisplay($from); ?>">
            <input type="hidden" name="to" value="<?php echo mswSafeDisplay($to); ?>">
            <input type="hidden" name="keys" value="<?php echo mswSafeDisplay((isset($_GET['keys']) ? $_GET['keys'] : '')); ?>">
            <?php
            if (USER_DEL_PRIV == 'yes') {
            ?>
            <button onclick="mswConfirmButtonAction('<?php echo mswSafeDisplay($msg_script_action); ?>','logdel');return false;" class="btn btn-danger button_margin_right20" disabled="disabled" type="button" id="delButton"><i class="fa fa-trash fa-fw" title="<?php echo mswSafeDisplay($msg_levels9); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels9); ?></span> <span id="mswCVal">(0)</span></button>
            <?php
            }
            ?>
            <button class="btn btn-primary button_margin_right20" type="button" onclick="mswProcess('log')"><i class="fa fa-save fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_log3; ?></span></button>
            <?php
            if (USER_DEL_PRIV == 'yes') {
            ?>
            <button onclick="mswConfirmButtonAction('<?php echo mswSafeDisplay($msg_script_action); ?>','logclr');return false;" class="btn btn-warning" type="button"><i class="fa fa-times fa-fw" title="<?php echo mswSafeDisplay($msg_levels9); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo mswSafeDisplay($msg_log2); ?></span></button>
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