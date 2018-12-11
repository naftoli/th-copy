<?php if (!defined('PATH')) { exit; }

        if (SHOW_ADMIN_DASHBOARD_GRAPH) {
          include(PATH . 'templates/system/home/graph.php');
        }

        $qTA = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT *,
               `" . DB_PREFIX . "tickets`.`id` AS `ticketID`,
	             `" . DB_PREFIX . "portal`.`name` AS `ticketName`,
	             `" . DB_PREFIX . "tickets`.`ts` AS `ticketStamp`,
	             `" . DB_PREFIX . "departments`.`name` AS `deptName`,
	             `" . DB_PREFIX . "levels`.`name` AS `levelName`
	             FROM `" . DB_PREFIX . "tickets`
               LEFT JOIN `" . DB_PREFIX . "departments`
	             ON `" . DB_PREFIX . "tickets`.`department` = `" . DB_PREFIX . "departments`.`id`
	             LEFT JOIN `" . DB_PREFIX . "portal`
	             ON `" . DB_PREFIX . "tickets`.`visitorID` = `" . DB_PREFIX . "portal`.`id`
	             LEFT JOIN `" . DB_PREFIX . "levels`
	             ON (`" . DB_PREFIX . "tickets`.`priority`   = `" . DB_PREFIX . "levels`.`id`
	              OR `" . DB_PREFIX . "tickets`.`priority`  = `" . DB_PREFIX . "levels`.`marker`)
               WHERE `ticketStatus` = 'open'
	             AND `replyStatus`   IN('start')
               AND `isDisputed`     = 'no'
               AND `assignedto`     = 'waiting'
	             AND `spamFlag`       = 'no'
               " . mswSQLDepartmentFilter($ticketFilterAccess) . "
               ORDER BY `" . DB_PREFIX . "tickets`.`id` DESC
               ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
        $TARows = mysqli_num_rows($qTA);
        if ($TARows > 0) {
        ?>
        <div class="panel panel-default">
          <div class="panel-heading left-align">
            <span class="pull-right hideminiscreen"><a href="index.php?p=assign"><i class="fa fa-link fa-fw"></i></a></span>
            <i class="fa fa-caret-right fa-fw"></i><a href="index.php?p=assign"><?php echo $msg_home52; ?></a>
          </div>
          <div class="panel-body max_height_home">
            <?php
            if ($TARows > 0) {
              while ($TICKETS = mysqli_fetch_object($qTA)) {
              ?>
              <div class="hometicketarea">
                <a href="?p=view-ticket&amp;id=<?php echo $TICKETS->ticketID; ?>"><?php echo mswSafeDisplay($TICKETS->subject); ?></a>
                <span>
                <?php
                echo str_replace(
                  array('{name}','{priority}','{date}','{ticket}'),
                  array(
                    mswSafeDisplay($TICKETS->ticketName),
                    mswCleanData($TICKETS->levelName),
                    $MSDT->mswDateTimeDisplay($TICKETS->ticketStamp, $SETTINGS->dateformat),
                    mswTicketNumber($TICKETS->ticketID)
                  ),
                  $msg_home44
                );
                ?>
                </span>
              </div>
              <hr>
              <?php
              }
            } else {
            ?>
            <div class="nothing_to_see"><?php echo $msg_home41; ?></div>
            <?php
            }
            ?>
          </div>
        </div>
        <?php
        }

        $qT1 = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT *,
               `" . DB_PREFIX . "tickets`.`id` AS `ticketID`,
	             `" . DB_PREFIX . "portal`.`name` AS `ticketName`,
	             `" . DB_PREFIX . "tickets`.`ts` AS `ticketStamp`,
	             `" . DB_PREFIX . "departments`.`name` AS `deptName`,
	             `" . DB_PREFIX . "levels`.`name` AS `levelName`
	             FROM `" . DB_PREFIX . "tickets`
               LEFT JOIN `" . DB_PREFIX . "departments`
	             ON `" . DB_PREFIX . "tickets`.`department` = `" . DB_PREFIX . "departments`.`id`
	             LEFT JOIN `" . DB_PREFIX . "portal`
	             ON `" . DB_PREFIX . "tickets`.`visitorID` = `" . DB_PREFIX . "portal`.`id`
	             LEFT JOIN `" . DB_PREFIX . "levels`
	             ON (`" . DB_PREFIX . "tickets`.`priority`   = `" . DB_PREFIX . "levels`.`id`
	              OR `" . DB_PREFIX . "tickets`.`priority`  = `" . DB_PREFIX . "levels`.`marker`)
               WHERE `ticketStatus` = 'open'
	             AND `replyStatus`   IN('start','admin')
               AND `isDisputed`     = 'no'
               AND `assignedto`    != 'waiting'
	             AND `spamFlag`       = 'no'
               " . mswSQLDepartmentFilter($ticketFilterAccess) . "
               ORDER BY `" . DB_PREFIX . "tickets`.`id` DESC
               ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
		    $T1Rows = mysqli_num_rows($qT1);
        ?>
        <div class="panel panel-default">
          <div class="panel-heading left-align">
            <span class="pull-right hidden-xs"><a href="index.php?p=open&amp;status=admin"><i class="fa fa-link fa-fw"></i></a></span>
            <i class="fa fa-caret-right fa-fw"></i><a href="index.php?p=open&amp;status=admin"><?php echo $msg_home31; ?></a>
          </div>
          <div class="panel-body max_height_home">
            <?php
            if ($T1Rows > 0) {
              while ($TICKETS = mysqli_fetch_object($qT1)) {
              ?>
              <div class="hometicketarea">
                <a href="?p=view-ticket&amp;id=<?php echo $TICKETS->ticketID; ?>"><?php echo mswSafeDisplay($TICKETS->subject); ?></a>
                <span>
                <?php
                echo str_replace(
                  array('{name}','{priority}','{date}','{ticket}'),
                  array(
                    mswSafeDisplay($TICKETS->ticketName),
                    mswCleanData($TICKETS->levelName),
                    $MSDT->mswDateTimeDisplay($TICKETS->ticketStamp, $SETTINGS->dateformat),
                    mswTicketNumber($TICKETS->ticketID)
                  ),
                  $msg_home44
                );
                ?>
                </span>
              </div>
              <hr>
              <?php
              }
            } else {
            ?>
            <div class="nothing_to_see"><?php echo $msg_home41; ?></div>
            <?php
            }
            ?>
          </div>
        </div>
        <?php

        $qT2 = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT *,
               `" . DB_PREFIX . "tickets`.`id` AS `ticketID`,
	             `" . DB_PREFIX . "portal`.`name` AS `ticketName`,
	             `" . DB_PREFIX . "tickets`.`ts` AS `ticketStamp`,
	             `" . DB_PREFIX . "departments`.`name` AS `deptName`,
	             `" . DB_PREFIX . "levels`.`name` AS `levelName`
	             FROM `" . DB_PREFIX . "tickets`
               LEFT JOIN `" . DB_PREFIX . "departments`
	             ON `" . DB_PREFIX . "tickets`.`department` = `" . DB_PREFIX . "departments`.`id`
	             LEFT JOIN `" . DB_PREFIX . "portal`
	             ON `" . DB_PREFIX . "tickets`.`visitorID` = `" . DB_PREFIX . "portal`.`id`
	             LEFT JOIN `" . DB_PREFIX . "levels`
	             ON (`" . DB_PREFIX . "tickets`.`priority`   = `" . DB_PREFIX . "levels`.`id`
	              OR `" . DB_PREFIX . "tickets`.`priority`  = `" . DB_PREFIX . "levels`.`marker`)
               WHERE `ticketStatus` = 'open'
	             AND `replyStatus`   IN('visitor')
               AND `isDisputed`     = 'no'
               AND `assignedto`    != 'waiting'
	             AND `spamFlag`       = 'no'
               " . mswSQLDepartmentFilter($ticketFilterAccess) . "
               ORDER BY `" . DB_PREFIX . "tickets`.`id` DESC
               ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
		    $T2Rows = mysqli_num_rows($qT2);
        ?>
        <div class="panel panel-default">
          <div class="panel-heading left-align">
            <span class="pull-right hidden-xs"><a href="index.php?p=open&amp;status=visitor"><i class="fa fa-link fa-fw"></i></a></span>
            <i class="fa fa-caret-right fa-fw"></i><a href="index.php?p=open&amp;status=visitor"><?php echo $msg_home39; ?></a>
          </div>
          <div class="panel-body max_height_home">
            <?php
            if ($T2Rows > 0) {
              while ($TICKETS = mysqli_fetch_object($qT2)) {
              ?>
              <div class="hometicketarea">
                <a href="?p=view-ticket&amp;id=<?php echo $TICKETS->ticketID; ?>"><?php echo mswSafeDisplay($TICKETS->subject); ?></a>
                <span>
                <?php
                echo str_replace(
                  array('{name}','{priority}','{date}','{ticket}'),
                  array(
                    mswSafeDisplay($TICKETS->ticketName),
                    mswCleanData($TICKETS->levelName),
                    $MSDT->mswDateTimeDisplay($TICKETS->ticketStamp, $SETTINGS->dateformat),
                    mswTicketNumber($TICKETS->ticketID)
                  ),
                  $msg_home44
                );
                ?>
                </span>
              </div>
              <hr>
              <?php
              }
            } else {
            ?>
            <div class="nothing_to_see"><?php echo $msg_home41; ?></div>
            <?php
            }
            ?>
          </div>
        </div>

        <?php
	      if ($SETTINGS->disputes == 'yes') {
          $qT3 = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT *,
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
                 WHERE `ticketStatus` = 'open'
	               AND `replyStatus`   IN('start','admin')
                 AND `isDisputed`     = 'yes'
                 AND `assignedto`    != 'waiting'
	               AND `spamFlag`       = 'no'
                 " . mswSQLDepartmentFilter($ticketFilterAccess) . "
                 ORDER BY `" . DB_PREFIX . "tickets`.`id` DESC
                 ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
		    $T3Rows = mysqli_num_rows($qT3);
	      ?>
        <div class="panel panel-default">
          <div class="panel-heading left-align">
            <span class="pull-right hidden-xs"><a href="index.php?p=disputes&amp;status=admin"><i class="fa fa-link fa-fw"></i></a></span>
            <i class="fa fa-caret-right fa-fw"></i><a href="index.php?p=disputes&amp;status=admin"><?php echo $msg_home32; ?></a>
          </div>
          <div class="panel-body max_height_home">
            <?php
            if ($T3Rows > 0) {
              while ($TICKETS = mysqli_fetch_object($qT3)) {
              ?>
              <div class="hometicketarea">
                <a href="?p=view-ticket&amp;id=<?php echo $TICKETS->ticketID; ?>"><?php echo mswSafeDisplay($TICKETS->subject); ?></a>
                <span>
                <?php
                echo str_replace(
                  array('{name}','{priority}','{date}','{ticket}'),
                  array(
                    mswSafeDisplay($TICKETS->ticketName),
                    mswCleanData($TICKETS->levelName),
                    $MSDT->mswDateTimeDisplay($TICKETS->ticketStamp, $SETTINGS->dateformat),
                    mswTicketNumber($TICKETS->ticketID)
                  ),
                  $msg_home44
                );
                ?>
                </span>
              </div>
              <hr>
              <?php
              }
            } else {
            ?>
            <div class="nothing_to_see"><?php echo $msg_home41; ?></div>
            <?php
            }
            ?>
          </div>
        </div>
        <?php

        $qT4 = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT *,
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
               WHERE `ticketStatus` = 'open'
	             AND `replyStatus`   IN('visitor')
               AND `isDisputed`     = 'yes'
               AND `assignedto`    != 'waiting'
	             AND `spamFlag`       = 'no'
               " . mswSQLDepartmentFilter($ticketFilterAccess) . "
               ORDER BY `" . DB_PREFIX . "tickets`.`id` DESC
               ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
		    $T4Rows = mysqli_num_rows($qT4);
        ?>
        <div class="panel panel-default">
          <div class="panel-heading left-align">
            <span class="pull-right hidden-xs"><a href="index.php?p=disputes&amp;status=visitor"><i class="fa fa-link fa-fw"></i></a></span>
            <i class="fa fa-caret-right fa-fw"></i><a href="index.php?p=disputes&amp;status=visitor"><?php echo $msg_home40; ?></a>
          </div>
          <div class="panel-body max_height_home">
            <?php
            if ($T4Rows > 0) {
              while ($TICKETS = mysqli_fetch_object($qT4)) {
              ?>
              <div class="hometicketarea">
                <a href="?p=view-ticket&amp;id=<?php echo $TICKETS->ticketID; ?>"><?php echo mswSafeDisplay($TICKETS->subject); ?></a>
                <span>
                <?php
                echo str_replace(
                  array('{name}','{priority}','{date}','{ticket}'),
                  array(
                    mswSafeDisplay($TICKETS->ticketName),
                    mswCleanData($TICKETS->levelName),
                    $MSDT->mswDateTimeDisplay($TICKETS->ticketStamp, $SETTINGS->dateformat),
                    mswTicketNumber($TICKETS->ticketID)
                  ),
                  $msg_home44
                );
                ?>
                </span>
              </div>
              <hr>
              <?php
              }
            } else {
            ?>
            <div class="nothing_to_see"><?php echo $msg_home41; ?></div>
            <?php
            }
            ?>
          </div>
        </div>
        <?php
        }

        $qSM = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT *,
               `" . DB_PREFIX . "tickets`.`id` AS `ticketID`,
	             `" . DB_PREFIX . "portal`.`name` AS `ticketName`,
	             `" . DB_PREFIX . "tickets`.`ts` AS `ticketStamp`,
	             `" . DB_PREFIX . "departments`.`name` AS `deptName`,
	             `" . DB_PREFIX . "levels`.`name` AS `levelName`
	             FROM `" . DB_PREFIX . "tickets`
               LEFT JOIN `" . DB_PREFIX . "departments`
	             ON `" . DB_PREFIX . "tickets`.`department` = `" . DB_PREFIX . "departments`.`id`
	             LEFT JOIN `" . DB_PREFIX . "portal`
	             ON `" . DB_PREFIX . "tickets`.`visitorID` = `" . DB_PREFIX . "portal`.`id`
	             LEFT JOIN `" . DB_PREFIX . "levels`
	             ON (`" . DB_PREFIX . "tickets`.`priority`   = `" . DB_PREFIX . "levels`.`id`
	              OR `" . DB_PREFIX . "tickets`.`priority`  = `" . DB_PREFIX . "levels`.`marker`)
               WHERE `spamFlag` = 'yes'
	             " . mswSQLDepartmentFilter($ticketFilterAccess) . "
               ORDER BY `" . DB_PREFIX . "tickets`.`id` DESC
               ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
        $TASRows = mysqli_num_rows($qSM);
        if ($TASRows > 0) {
        ?>
        <div class="panel panel-default">
          <div class="panel-heading left-align">
            <span class="pull-right hideminiscreen"><a href="index.php?p=spam"><i class="fa fa-link fa-fw"></i></a></span>
            <i class="fa fa-caret-right fa-fw"></i><a href="index.php?p=spam"><?php echo $msadminlang3_1[22]; ?></a>
          </div>
          <div class="panel-body max_height_home">
            <?php
            if ($TASRows > 0) {
              while ($TICKETS = mysqli_fetch_object($qSM)) {
              ?>
              <div class="hometicketarea">
                <a href="?p=view-ticket&amp;id=<?php echo $TICKETS->ticketID; ?>"><?php echo mswSafeDisplay($TICKETS->subject); ?></a>
                <span>
                <?php
                echo str_replace(
                  array('{name}','{priority}','{date}','{ticket}'),
                  array(
                    mswSafeDisplay($TICKETS->ticketName),
                    mswCleanData($TICKETS->levelName),
                    $MSDT->mswDateTimeDisplay($TICKETS->ticketStamp, $SETTINGS->dateformat),
                    mswTicketNumber($TICKETS->ticketID)
                  ),
                  $msg_home44
                );
                ?>
                </span>
              </div>
              <hr>
              <?php
              }
            } else {
            ?>
            <div class="nothing_to_see"><?php echo $msg_home41; ?></div>
            <?php
            }
            ?>
          </div>
        </div>
        <?php
        }
        ?>