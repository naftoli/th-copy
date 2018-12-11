<?php if (!defined('PATH')) { exit; }
if (SHOW_ADMIN_DASHBOARD_GRAPH) {
  $g_config  =  array(
   'default' => $MSTEAM->defDays,
   'color1'  => '#c8c8cb',
   'color2'  => '#5f6d88',
   'bg'      => '#fdfdfd',
   'gline'   => '#dddddd',
   'border'  => '#dddddd'
  );
  include(PATH . 'control/classes/class.graphs.php');
  $tz               = ($MSTEAM->timezone ? $MSTEAM->timezone : $SETTINGS->timezone);
  $from             = (isset($_GET['f']) && $_GET['f'] && $MSDT->mswDatePickerFormat($_GET['f'])!='0000-00-00' ? $_GET['f'] : $MSDT->mswConvertMySQLDate(date('Y-m-d',strtotime('-'.$g_config['default'].' days',$MSDT->mswTimeStamp()))));
  $to               = (isset($_GET['t']) && $_GET['t'] && $MSDT->mswDatePickerFormat($_GET['t'])!='0000-00-00' ? $_GET['t'] : $MSDT->mswConvertMySQLDate(date('Y-m-d',$MSDT->mswTimeStamp())));
  $graph            = new graphs();
  $graph->settings  = $SETTINGS;
  $graph->datetime  = $MSDT;
  $graph->team      = $MSTEAM;
  $data             = $graph->home($from,$to,$ticketFilterAccess);
  $g_tick           = (isset($data[0]) && $data[0]!='none' ? implode(',',$data[0]) : '');
  $g_disp           = (isset($data[1]) && $data[1]!='none' ? implode(',',$data[1]) : '');
  define('JS_LOADER', 'home-graph.php');
}
define('LOADED_HOME', 1);
define('LOAD_DATE_PICKERS', 1);
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <div class="row">
      <div class="col-lg-8">
        <?php
        // Load home screen based on user logged in..
        switch ($MSTEAM->id) {
          case 1:
            include(PATH . 'templates/system/home/admin.php');
            break;
          default:
            include(PATH . 'templates/system/home/users.php');
            break;
        }
        ?>
      </div>
      <div class="col-lg-4">
        <?php
        // Load right panel based on user logged in..
        switch ($MSTEAM->id) {
          case 1:
            include(PATH . 'templates/system/home/panel.php');
            break;
          default:
            include(PATH . 'templates/system/home/panel-user.php');
            break;
        }
        ?>
      </div>
    </div>

  </div>