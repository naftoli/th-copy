<?php if (!defined('PATH')) { exit; }
$_GET['id'] = (int)$_GET['id'];
$USER       = mswGetTableData('users','id',$_GET['id']);
checkIsValid($USER);
// For graphs..
$dateRange           = '-6 months';
$colors              = array('#c8c8cb','#65718a');
$from                = (isset($_GET['from']) && $MSDT->mswDatePickerFormat($_GET['from'])!='0000-00-00' ? $_GET['from'] : $MSDT->mswConvertMySQLDate(date('Y-m-d',strtotime($dateRange,$MSDT->mswTimeStamp()))));
$to                  = (isset($_GET['to']) && $MSDT->mswDatePickerFormat($_GET['to'])!='0000-00-00' ? $_GET['to'] : $MSDT->mswConvertMySQLDate(date('Y-m-d',$MSDT->mswTimeStamp())));
include(PATH . 'control/classes/class.graphs.php');
$MSGRAPH             = new graphs();
$MSGRAPH->settings   = $SETTINGS;
$MSGRAPH->datetime   = $MSDT;
$MSGRAPH->range      = array($from,$to);
$buildGraph          = $MSGRAPH->graph('responses');
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
      <li><a href="index.php?p=team&amp;edit=<?php echo $_GET['id']; ?>"><?php echo mswSafeDisplay($USER->name); ?></a></li>
      <li class="active"><?php echo $msg_user86; ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-heading text-right">
            <button class="btn btn-info btn-sm" type="button" onclick="mswToggleButton('dates')"><i class="fa fa-calendar fa-fw"></i></button>
          </div>
          <div class="panel-body">

            <?php
            // Search..
            include(PATH . 'templates/system/bootstrap/search-date.php');
            ?>
            <div class="row graphbar">

              <div class="col-lg-6 col-md-6 col-md-6 col-sm-5 hidden-xs">
                <i class="fa fa-calendar fa-fw" onclick="mswToggleButton('dates')" style="cursor:pointer"></i> <?php echo $from; ?> - <?php echo $to; ?>
              </div>
              <div class="col-lg-6 col-md-6 col-md-6 col-sm-7 text-right">
                <span><i class="fa fa-circle fa-fw" style="color:<?php echo $colors[0]; ?>"></i> <?php echo $msg_user92; ?></span> <span><i class="fa fa-circle fa-fw" style="color:<?php echo $colors[1]; ?>"></i> <?php echo $msg_user93; ?></span>
              </div>

            </div>
            <?php
            if (!empty($buildGraph[0]) || !empty($buildGraph[1])) {
            define('JS_LOADER', 'graph.php');
            ?>
            <div class="row">
              <div class="chartWrapper">
                <div class="graphLoader"></div>
                <div id="chart"></div>
              </div>
            </div>
            <?php
            } else {
            ?>
            <table class="table table-striped table-hover">
              <tr class="warning nothing_to_see">
                <td><?php echo $msg_user94; ?></td>
              </tr>
            </table>
            <?php
            }
            ?>

          </div>
          <div class="panel-footer">
             <?php
             if (in_array('teamman', $userAccess)  || $MSTEAM->id == '1') {
             ?>
             <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=teamman')"><i class="fa fa-times fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels11); ?></span></button>
             <?php
             }
             ?>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>