<?php if (!defined('PATH')) { exit; } ?>
        <div class="panel panel-default">
          <div class="panel-heading left-align">
            <span class="pull-right"><a href="#" onclick="mswShowHideDateRange();return false"><i class="fa fa-calendar fa-fw"></i></a></span>
            <?php
            if ($SETTINGS->disputes == 'yes') {
            ?>
            <i class="fa fa-bar-chart fa-fw"></i> <?php echo $msg_home54; ?>
            <?php
            } else {
            ?>
            <i class="fa fa-bar-chart fa-fw"></i> <?php echo $msg_home63; ?>
            <?php
            }
            ?>
          </div>
          <div class="panel-body hdates" style="display:none">
            <div class="form-group">
              <label><?php echo $msg_home55; ?></label>
              <input type="text" class="form-control" name="from" id="from" value="<?php echo mswSafeDisplay($from); ?>">
              <input type="text" class="form-control margin_top_10" name="to" id="to" value="<?php echo mswSafeDisplay($to); ?>">
            </div>
            <div class="form-group">
              <label><?php echo $msg_home59; ?> (<?php echo $msg_home60; ?>)</label>
              <input type="text" class="form-control" name="def" value="<?php echo (int) $g_config['default']; ?>" maxlength="3">
            </div>
            <button class="btn btn-primary" type="button" onclick="mswChangeDateRange()"><i class="fa fa-refresh fa-fw"></i> <?php echo $msg_home57; ?></button>
            <button class="btn btn-link" type="button" onclick="mswShowHideDateRange()"><i class="fa fa-times fa-fw"></i> <?php echo $msg_levels11; ?></button>
          </div>
          <div class="panel-body hgraph">
            <div class="homeChartWrapper">
              <?php
              if ($g_tick || $g_disp) {
              define('HOME_GRAPH_LOAD', 1);
              ?>
              <div class="graphLoader"></div>
              <div id="chart"></div>
              <div>
              <hr>
              <i class="fa fa-circle fa-fw" style="color:<?php echo $g_config['color1']; ?>"></i> <?php echo $msg_home61;
              if ($SETTINGS->disputes == 'yes') {
              ?>
              <i class="fa fa-circle fa-fw" style="color:<?php echo $g_config['color2']; ?>"></i> <?php echo $msg_home62;
              }
              ?>
              </div>
              <?php
              } else {
              ?>
              <div class="nothing_to_see"><?php echo $msg_home58; ?></div>
              <?php
              }
              ?>
	          </div>
          </div>
        </div>