<?php if (!defined('PATH')) { exit; }
// Vars..
$from  = (isset($_GET['from']) && $MSDT->mswDatePickerFormat($_GET['from'])!='0000-00-00' ? $_GET['from'] : $MSDT->mswConvertMySQLDate(date('Y-m-d',strtotime(REP_DEF_RANGE_OLD,$MSDT->mswTimeStamp()))));
$to    = (isset($_GET['to']) && $MSDT->mswDatePickerFormat($_GET['to'])!='0000-00-00' ? $_GET['to'] : $MSDT->mswConvertMySQLDate(date('Y-m-d',$MSDT->mswTimeStamp())));
$view  = (isset($_GET['view']) && in_array($_GET['view'],array('month','day')) ? $_GET['view'] : 'month');
$dept  = (isset($_GET['dept']) ? $_GET['dept'] : '0');
$cns   = array(0,0,0,0);
$where = 'WHERE DATE(FROM_UNIXTIME(`ts`)) BETWEEN \'' . $MSDT->mswDatePickerFormat($from) . '\' AND \'' . $MSDT->mswDatePickerFormat($to) . '\'';
if (substr($dept,0,1)=='u') {
  $where .= mswDefineNewline() . 'AND FIND_IN_SET(\'' . substr($dept,1) . '\', `assignedto`) > 0';
} else {
  if ($dept > 0) {
    $where .= mswDefineNewline() . 'AND `department` = \'' . (int) $dept . '\'';
  }
}
$where .= mswDefineNewline().'AND `assignedto` != \'waiting\'';
switch ($view) {
  case 'month':
    $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT *,MONTH(FROM_UNIXTIME(`ts`)) AS `m`,YEAR(FROM_UNIXTIME(`ts`)) AS `y` FROM `" . DB_PREFIX . "tickets`
         $where
	       AND `spamFlag` = 'no'
         GROUP BY MONTH(FROM_UNIXTIME(`ts`)),YEAR(FROM_UNIXTIME(`ts`))
         ORDER BY 2
         ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
    $ticketNumRows = mysqli_num_rows($q);
    break;
  case 'day':
    $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT *,DATE(FROM_UNIXTIME(`ts`)) AS `d` FROM `" . DB_PREFIX . "tickets`
         $where
	       AND `spamFlag` = 'no'
         GROUP BY DATE(FROM_UNIXTIME(`ts`))
         ORDER BY 2
         ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
    $ticketNumRows = mysqli_num_rows($q);
    break;
}
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
      <li class="active"><?php echo $msg_adheader34; ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-heading text-right">
            <button class="btn btn-info btn-sm" type="button" onclick="mswToggleButton('search')"><i class="fa fa-search fa-fw"></i></button>
          </div>
          <div class="panel-body">
            <?php
            // Search..
            include(PATH . 'templates/system/bootstrap/search-reports.php');
            ?>

            <div class="table-responsive">
            <table class="table table-striped table-hover">
             <thead>
               <tr>
               <th><?php echo $msg_reports7; ?></th>
               <th><?php echo $msg_reports8; ?></th>
               <th><?php echo $msg_reports9; ?></th>
               <?php
               if ($SETTINGS->disputes == 'yes') {
               ?>
               <th><?php echo $msg_reports10; ?></th>
               <th><?php echo $msg_reports11; ?></th>
               <?php
               }
               ?>
             </tr>
            </thead>
            <tbody>
            <?php
            if ($ticketNumRows>0) {
            while ($REP = mysqli_fetch_object($q)) {
            switch ($view) {
               case 'month':
                 // Open tickets..
                 $C1 = mysqli_fetch_object(
                        mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) AS `c` FROM `" . DB_PREFIX . "tickets`
                        $where
                        AND `ticketStatus`             = 'open'
                        AND `isDisputed`               = 'no'
                        AND MONTH(FROM_UNIXTIME(`ts`)) = '{$REP->m}'
                        AND YEAR(FROM_UNIXTIME(`ts`))  = '{$REP->y}'
                        ")
                       );
                 // Closed tickets..
                 $C2 = mysqli_fetch_object(
                        mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) AS `c` FROM `" . DB_PREFIX . "tickets`
                        $where
                        AND `ticketStatus`             = 'close'
                        AND `isDisputed`               = 'no'
                        AND MONTH(FROM_UNIXTIME(`ts`)) = '{$REP->m}'
                        AND YEAR(FROM_UNIXTIME(`ts`))  = '{$REP->y}'
                        ")
                       );
                 if ($SETTINGS->disputes == 'yes') {
                 // Open disputes..
                 $C3 = mysqli_fetch_object(
                        mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) AS `c` FROM `" . DB_PREFIX . "tickets`
                        $where
                        AND `ticketStatus`             = 'open'
                        AND `isDisputed`               = 'yes'
                        AND MONTH(FROM_UNIXTIME(`ts`)) = '{$REP->m}'
                        AND YEAR(FROM_UNIXTIME(`ts`))  = '{$REP->y}'
                        ")
                       );
                 // Closed disputes..
                 $C4 = mysqli_fetch_object(
                        mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) AS `c` FROM `" . DB_PREFIX . "tickets`
                        $where
                        AND `ticketStatus`             = 'close'
                        AND `isDisputed`               = 'yes'
                        AND MONTH(FROM_UNIXTIME(`ts`)) = '{$REP->m}'
                        AND YEAR(FROM_UNIXTIME(`ts`))  = '{$REP->y}'
                        ")
                       );
                 }
                 break;
                 case 'day':
                   // Open tickets..
                   $C1 = mysqli_fetch_object(
                      mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) AS `c` FROM `" . DB_PREFIX . "tickets`
                      $where
                      AND `ticketStatus`             = 'open'
                      AND `isDisputed`               = 'no'
                      AND DATE(FROM_UNIXTIME(`ts`))  = '{$REP->d}'
                      ")
                     );
                   // Closed tickets..
                   $C2 = mysqli_fetch_object(
                      mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) AS `c` FROM `" . DB_PREFIX . "tickets`
                      $where
                      AND `ticketStatus`             = 'close'
                      AND `isDisputed`               = 'no'
                      AND DATE(FROM_UNIXTIME(`ts`))  = '{$REP->d}'
                      ")
                     );
                   if ($SETTINGS->disputes == 'yes') {
                   // Open disputes..
                   $C3 = mysqli_fetch_object(
                      mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) AS `c` FROM `" . DB_PREFIX . "tickets`
                      $where
                      AND `ticketStatus`             = 'open'
                      AND `isDisputed`               = 'yes'
                      AND DATE(FROM_UNIXTIME(`ts`))  = '{$REP->d}'
                      ")
                     );
                   // Closed disputes..
                   $C4 = mysqli_fetch_object(
                      mysqli_query($GLOBALS["___mysqli_ston"], "SELECT COUNT(*) AS `c` FROM `" . DB_PREFIX . "tickets`
                      $where
                      AND `ticketStatus`             = 'close'
                      AND `isDisputed`               = 'yes'
                      AND DATE(FROM_UNIXTIME(`ts`))  = '{$REP->d}'
                      ")
                     );
                   }
                 break;
               }
               $cnt1 = (isset($C1->c) ? $C1->c : '0');
               $cnt2 = (isset($C2->c) ? $C2->c : '0');
               $cnt3 = (isset($C3->c) ? $C3->c : '0');
               $cnt4 = (isset($C4->c) ? $C4->c : '0');
               ?>
             <tr>
               <td><?php echo ($view=='day' ? date($SETTINGS->dateformat,strtotime($REP->d)) : $msg_script21[($REP->m-1)] . ' ' . $REP->y); ?></td>
               <td><?php echo number_format($cnt1); ?></td>
               <td><?php echo number_format($cnt2); ?></td>
               <?php
               if ($SETTINGS->disputes == 'yes') {
               ?>
               <td><?php echo number_format($cnt3); ?></td>
               <td><?php echo number_format($cnt4); ?></td>
               <?php
               }
               ?>
             </tr>
             <?php
             // Totals..
             $cns[0] = ($cns[0]+$cnt1);
                 $cns[1] = ($cns[1]+$cnt2);
                 $cns[2] = ($cns[2]+$cnt3);
                 $cns[3] = ($cns[3]+$cnt4);
             }
             if ($ticketNumRows>0) {
             ?>
             <tr class="reporttotals">
               <td><?php echo $msg_reports12; ?></td>
               <td><?php echo number_format($cns[0]); ?></td>
               <td><?php echo number_format($cns[1]); ?></td>
               <?php
               if ($SETTINGS->disputes == 'yes') {
               ?>
               <td><?php echo number_format($cns[2]); ?></td>
               <td><?php echo number_format($cns[3]); ?></td>
               <?php
               }
               ?>
             </tr>
             <?php
             }
             } else {
             ?>
             <tr class="warning nothing_to_see">
              <td colspan="<?php echo ($SETTINGS->disputes == 'yes' ? '5' : '3'); ?>"><?php echo $msg_reports13; ?></td>
             </tr>
             <?php
             }
             ?>
            </tbody>
           </table>
           </div>
          </div>

          <?php
          if ($ticketNumRows>0) {
          ?>
          <div class="panel-footer">
            <input type="hidden" name="from" value="<?php echo mswSafeDisplay($from); ?>">
            <input type="hidden" name="to" value="<?php echo mswSafeDisplay($to); ?>">
            <input type="hidden" name="view" value="<?php echo mswSafeDisplay($view); ?>">
            <input type="hidden" name="dept" value="<?php echo mswSafeDisplay($dept); ?>">
            <button class="btn btn-primary" type="button" onclick="mswProcess('report')"><i class="fa fa-save fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_reports14); ?></span></button>
          </div>
          <?php
          }
          ?>
        </div>
      </div>
    </div>
    </form>

  </div>