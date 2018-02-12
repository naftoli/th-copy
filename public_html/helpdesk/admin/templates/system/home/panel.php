<?php if (!defined('PATH')) { exit; }
        if (defined('LOADED_HOME')) {
        ?>
        <div class="panel panel-default">
          <div class="panel-body">
            <i class="fa fa-user fa-fw"></i> <?php echo mswCleanData($MSTEAM->name); ?><br>
            <i class="fa fa-envelope-o fa-fw"></i> <?php echo mswCleanData($MSTEAM->email); ?>
            <hr>
            <i class="fa fa-caret-right fa-fw"></i><a href="index.php?p=team&amp;edit=1"><?php echo $msg_header17; ?></a>
            <?php
						// Show version check..
            if (defined('DEV_BETA')) {
              $noShowVer = true;
            }
						if (DISPLAY_SOFTWARE_VERSION_CHECK && !isset($noShowVer)) {
						?>
            <i class="fa fa-caret-right fa-fw"></i><a href="index.php?p=vc"><?php echo $msg_adheader27; ?> (<?php echo $SETTINGS->softwareVersion; ?>)</a>
            <?php
            }
            ?>
          </div>
        </div>
        <?php
        }
        ?>

        <div class="panel panel-default">
          <div class="panel-heading left-align">
            <i class="fa fa-edit fa-fw"></i> <?php echo $msg_home3; ?>
          </div>
          <div class="panel-body text_height_25">
            <?php
            $arrTickOverview = array(
              mswRowCount('tickets WHERE `replyStatus` = \'start\' AND `ticketStatus` = \'open\' AND `assignedto` = \'waiting\' AND `spamFlag` = \'no\' AND `isDisputed` = \'no\' ' . mswSQLDepartmentFilter($ticketFilterAccess)),
              mswRowCount('tickets WHERE `replyStatus` = \'start\' AND `ticketStatus` = \'open\' AND `assignedto` != \'waiting\' AND `spamFlag` = \'no\' AND `isDisputed` = \'no\' ' . mswSQLDepartmentFilter($ticketFilterAccess)),
              mswRowCount('tickets WHERE `replyStatus` = \'admin\' AND `ticketStatus` = \'open\' AND `assignedto` != \'waiting\' AND `spamFlag` = \'no\' AND `isDisputed` = \'no\' ' . mswSQLDepartmentFilter($ticketFilterAccess)),
              mswRowCount('tickets WHERE `replyStatus` = \'visitor\' AND `ticketStatus` = \'open\' AND `assignedto` != \'waiting\' AND `spamFlag` = \'no\' AND `isDisputed` = \'no\' ' . mswSQLDepartmentFilter($ticketFilterAccess)),
              mswRowCount('tickets WHERE `ticketStatus` != \'open\' AND `assignedto` != \'waiting\' AND `spamFlag` = \'no\' AND `isDisputed` = \'no\' ' . mswSQLDepartmentFilter($ticketFilterAccess)),
              mswRowCount('tickets WHERE `spamFlag` = \'yes\' ' . mswSQLDepartmentFilter($ticketFilterAccess))
            );
            ?>
            <a class="nodec" href="?p=assign"><span class="label label-primary"><?php echo ($arrTickOverview[0] < 10 ? '&nbsp;&nbsp;' . $arrTickOverview[0] : $arrTickOverview[0]); ?></span></a> - <?php echo $msg_home46; ?><br>
            <a class="nodec" href="?p=open&amp;status=start"><span class="label label-info"><?php echo ($arrTickOverview[1] < 10 ? '&nbsp;&nbsp;' . $arrTickOverview[1] : $arrTickOverview[1]); ?></span></a> - <?php echo $msg_home4; ?><br>
            <a class="nodec" href="?p=open&amp;status=adminonly"><span class="label label-info"><?php echo ($arrTickOverview[2] < 10 ? '&nbsp;&nbsp;' . $arrTickOverview[2] : $arrTickOverview[2]); ?></span></a> - <?php echo $msg_home5; ?><br>
            <a class="nodec" href="?p=open&amp;status=visitor"><span class="label label-info"><?php echo ($arrTickOverview[3] < 10 ? '&nbsp;&nbsp;' . $arrTickOverview[3] : $arrTickOverview[3]); ?></span></a> - <?php echo $msg_home6; ?><br>
            <a class="nodec" href="?p=close"><span class="label label-info"><?php echo ($arrTickOverview[4] < 10 ? '&nbsp;&nbsp;' . $arrTickOverview[4] : $arrTickOverview[4]); ?></span></a> - <?php echo $msg_home7; ?><br>
            <?php
            if (mswRowCount('imap WHERE `im_piping` = \'yes\'') > 0) {
            ?>
            <a class="nodec" href="?p=spam"><span class="label label-warning"><?php echo ($arrTickOverview[5] < 10 ? '&nbsp;&nbsp;' . $arrTickOverview[5] : $arrTickOverview[5]); ?></span></a> - <?php echo $msg_adheader63; ?>
            <?php
            }
            ?>
          </div>
        </div>

        <?php
	      if ($SETTINGS->disputes == 'yes') {
	      ?>
        <div class="panel panel-default">
          <div class="panel-heading left-align">
            <i class="fa fa-bullhorn fa-fw"></i> <?php echo $msg_home29; ?>
          </div>
          <div class="panel-body text_height_25">
            <?php
		        $arrDispOverview = array(
		          mswRowCount('tickets WHERE `replyStatus` = \'start\' AND `ticketStatus` = \'open\' AND `assignedto` != \'waiting\' AND `spamFlag` = \'no\' AND `isDisputed` = \'yes\' ' . mswSQLDepartmentFilter($ticketFilterAccess)),
		          mswRowCount('tickets WHERE `replyStatus` IN(\'admin\',\'start\') AND `ticketStatus` = \'open\' AND `assignedto` != \'waiting\' AND `spamFlag` = \'no\' AND `isDisputed` = \'yes\' ' . mswSQLDepartmentFilter($ticketFilterAccess)),
		          mswRowCount('tickets WHERE `replyStatus` = \'visitor\' AND `ticketStatus` = \'open\' AND `assignedto` != \'waiting\' AND `spamFlag` = \'no\' AND `isDisputed` = \'yes\' ' . mswSQLDepartmentFilter($ticketFilterAccess)),
		          mswRowCount('tickets WHERE `ticketStatus` != \'open\' AND `assignedto` != \'waiting\' AND `spamFlag` = \'no\' AND `isDisputed` = \'yes\' ' . mswSQLDepartmentFilter($ticketFilterAccess))
		        );
		        ?>
		        <a class="nodec" href="?p=disputes&amp;status=start"><span class="label label-info"><?php echo ($arrDispOverview[0] < 10 ? '&nbsp;&nbsp;' . $arrDispOverview[0] : $arrDispOverview[0]); ?></span></a> - <?php echo $msg_home43; ?><br>
            <a class="nodec" href="?p=disputes&amp;status=adminonly"><span class="label label-info"><?php echo ($arrDispOverview[1] < 10 ? '&nbsp;&nbsp;' . $arrDispOverview[1] : $arrDispOverview[1]); ?></span></a> - <?php echo $msg_home26; ?><br>
            <a class="nodec" href="?p=disputes&amp;status=visitor"><span class="label label-info"><?php echo ($arrDispOverview[2] < 10 ? '&nbsp;&nbsp;' . $arrDispOverview[2] : $arrDispOverview[2]); ?></span></a> - <?php echo $msg_home27; ?><br>
            <a class="nodec" href="?p=cdisputes"><span class="label label-info"><?php echo ($arrDispOverview[3] < 10 ? '&nbsp;&nbsp;' . $arrDispOverview[3] : $arrDispOverview[3]); ?></span></a> - <?php echo $msg_home28; ?>
          </div>
        </div>
        <?php
        }
        ?>

        <div class="panel panel-default">
          <div class="panel-heading left-align">
            <i class="fa fa-gears fa-fw"></i> <?php echo $msg_home2; ?>
          </div>
          <div class="panel-body text_height_25">
            <?php
            $arrSysOverview = array(
              mswRowCount('users'),
              mswRowCount('departments'),
              mswRowCount('imap'),
              mswRowCount('cusfields'),
              mswRowCount('responses'),
              mswRowCount('faq'),
              mswRowCount('categories'),
              mswRowCount('faqattach'),
              count($ticketLevelSel),
              mswRowCount('portal WHERE `enabled` = \'yes\' AND `verified` = \'yes\'')
            );
            ?>
		        <i class="fa fa-angle-right fa-fw"></i> <?php echo str_replace(array('{visitors}'),array($arrSysOverview[9]),$msg_home50); ?><br>
            <i class="fa fa-angle-right fa-fw"></i> <?php echo str_replace(array('{users}'),array($arrSysOverview[0]),$msg_home8); ?><br>
            <i class="fa fa-angle-right fa-fw"></i> <?php echo str_replace(array('{levels}','{dept}'),array($arrSysOverview[8],$arrSysOverview[1]),$msg_home51); ?><br>
            <i class="fa fa-angle-right fa-fw"></i> <?php echo str_replace(array('{imap}'),array($arrSysOverview[2]),$msg_home48); ?><br>
            <i class="fa fa-angle-right fa-fw"></i> <?php echo str_replace(array('{fields}'),array($arrSysOverview[3]),$msg_home49); ?><br>
            <i class="fa fa-angle-right fa-fw"></i> <?php echo str_replace(array('{responses}'),array($arrSysOverview[4]),$msg_home9); ?><br>
            <i class="fa fa-angle-right fa-fw"></i> <?php echo str_replace(array('{questions}','{cats}','{attachments}'),array($arrSysOverview[5],$arrSysOverview[6],$arrSysOverview[7]),$msg_home10); ?>
          </div>
        </div>

        <div class="panel panel-default">
          <div class="panel-heading left-align">
            <i class="fa fa-link fa-fw"></i> <?php echo $msg_home42; ?>
          </div>
          <div class="panel-body text_height_25">
            <?php
            // Quick links..
            if (file_exists(PATH . 'templates/system/home/quick-links-1.php')) {
              include(PATH . 'templates/system/home/quick-links-1.php');
            } else {
              include(PATH . 'templates/system/home/quick-links.php');
            }
            ?>
          </div>
        </div>