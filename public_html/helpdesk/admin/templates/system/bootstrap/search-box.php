      <div class="form-group searchbox" style="height:60px;display:none">
       <div class="form-group input-group">
        <input class="form-control" type="text" placeholder="<?php echo mswSafeDisplay($msg_pkbase2); ?>" name="keys" value="<?php echo (isset($_GET['keys']) ? urlencode(mswSafeDisplay($_GET['keys'])) : ''); ?>">
        <span class="input-group-addon"><a href="#" onclick="mswDoSearch('<?php echo (isset($searchBoxUrl) ? $searchBoxUrl : $_GET['p']); ?>')"><i class="fa fa-arrow-right fa-fw"></i></a></span>
       </div>
      </div>