      <div class="form-group searchbox" style="height:210px;display:none">
        <div class="form-group">
          <select name="dept" class="form-control">
            <option value="0"><?php echo $msg_tools10; ?></option>
            <?php
            $q_dept = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "departments` " . mswSQLDepartmentFilter($mswDeptFilterAccess,'WHERE') . " ORDER BY `orderBy`")
                      or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
            while ($DEPT = mysqli_fetch_object($q_dept)) {
            ?>
            <option value="<?php echo $DEPT->id; ?>"<?php echo mswSelectedItem('dept',$DEPT->id,true); ?>><?php echo mswSafeDisplay($DEPT->name); ?></option>
            <?php
            }
            // For administrator, show all assigned users in filter..
            if ($MSTEAM->id == '1') {
            $q_users     = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "users` ORDER BY `name`")
                          or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
            if (mysqli_num_rows($q_users)>0) {
            ?>
            <option value="0" disabled="disabled">- - - - - -</option>
            <?php
            while ($U = mysqli_fetch_object($q_users)) {
            ?>
            <option value="u<?php echo $U->id; ?>"<?php echo mswSelectedItem('dept','u'.$U->id,true); ?>><?php echo $msg_open31 . ' ' . mswSafeDisplay($U->name); ?></option>
            <?php
            }
            }
            }
            ?>
          </select>
        </div>

        <div class="form-group">
          <select name="view" class="form-control">
            <option value="day"<?php echo mswSelectedItem('view','day',true); ?>><?php echo $msg_reports4; ?></option>
            <option value="month"<?php echo mswSelectedItem('view','month',true); ?>><?php echo $msg_reports5; ?></option>
          </select>
	      </div>

        <div class="form-group">
          <input type="text" placeholder="<?php echo mswSafeDisplay($msg_reports2); ?>" class="form-control" id="from" name="from" value="<?php echo mswSafeDisplay($from); ?>">
        </div>

        <div class="form-group">
          <div class="form-group input-group">
           <input type="text" placeholder="<?php echo mswSafeDisplay($msg_reports3); ?>" class="form-control" id="to" name="to" value="<?php echo mswSafeDisplay($to); ?>">
           <span class="input-group-addon"><a href="#" onclick="mswDoSearch('<?php echo (isset($searchBoxUrl) ? $searchBoxUrl : $_GET['p']); ?>')"><i class="fa fa-arrow-right fa-fw"></i></a></span>
          </div>
        </div>

      </div>