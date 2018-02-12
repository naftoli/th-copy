<?php

class faqCentre {

  public $settings;
  public $internal = array('chmod' => 0777, 'chmod-after' => 0644);

  // Rebuild attachment order sequence..
  public function rebuildAttSequence() {
    $seq = 0;
    $q   = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id` FROM `" . DB_PREFIX . "faqattach` ORDER BY IF(`orderBy`>0,`orderBy`,9999)");
    while ($AT = mysqli_fetch_object($q)) {
      $n = (++$seq);
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "faqattach` SET
      `orderBy`  = '{$n}'
      WHERE `id` = '{$AT->id}'
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
  }

  // Order sequence for attachments..
  public function orderAttSequence() {
    foreach ($_POST['order'] AS $k => $v) {
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "faqattach` SET
      `orderBy`  = '{$v}'
      WHERE `id` = '{$k}'
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
  }

  // Enable/disable attachment..
  public function enableDisableAtt() {
    $_GET['id'] = (int) $_GET['id'];
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "faqattach` SET
    `ts`       = UNIX_TIMESTAMP(),
    `enAtt`    = '" . ($_GET['changeState'] == 'fa fa-flag fa-fw msw-green cursor_pointer' ? 'no' : 'yes') . "'
    WHERE `id` = '{$_GET['id']}'
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
  }

  // Delete attachment..
  public function deleteAttachments() {
    if (!empty($_POST['del'])) {
      // Remove attachment files..
      $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `path` FROM `" . DB_PREFIX . "faqattach`
           WHERE `id` IN(" . mswSafeImportString(implode(',', $_POST['del'])) . ")
		       AND `path` != ''
           ORDER BY `id`
		  ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      while ($AT = mysqli_fetch_object($q)) {
        if (file_exists($this->settings->attachpathfaq . '/' . $AT->path)) {
          @unlink($this->settings->attachpathfaq . '/' . $AT->path);
        }
      }
      // Delete data..
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "faqattach`
      WHERE `id` IN(" . mswSafeImportString(implode(',', $_POST['del'])) . ")
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      $rows = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
      if (mswRowCount('faqattach') == 0) {
        @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "faqattach`");
      }
    }
    // Rebuild sequence..
    faqCentre::rebuildAttSequence();
    return $rows;
  }

  // Update attachment..
  public function updateAttachment($upl) {
    $ID     = (isset($_POST['update']) ? (int) $_POST['update'] : '0');
    $reload = 'no';
    if ($ID > 0) {
      $display  = $_POST['name'];
      $remote   = (isset($_POST['remote']) ? $_POST['remote'] : '');
      $f_name   = (isset($_FILES['file']['name']) ? $_FILES['file']['name'] : '');
      $f_temp   = (isset($_FILES['file']['tmp_name']) ? $_FILES['file']['tmp_name'] : '');
      $f_mime   = (isset($_FILES['file']['type']) ? $_FILES['file']['type'] : $_POST['mimeType']);
      $f_size   = ($f_name && $f_temp ? $_FILES['file']['size'] : $_POST['size']);
      $path     = $_POST['opath'];
      $ext      = substr(strrchr(strtolower($f_name), '.'), 1);
      // Update file..
      if ($remote == '' && $f_size > 0 && $f_name && $f_temp && $upl->isUploaded($f_temp)) {
        // Delete original..
        if (file_exists($this->settings->attachpathfaq . '/' . $_POST['opath'])) {
          @unlink($this->settings->attachpathfaq . '/' . $_POST['opath']);
        }
        // Does file exist?
        if (file_exists($this->settings->attachpathfaq . '/' . $f_name)) {
          // Are we renaming attachments..
          if ($this->settings->renamefaq == 'yes') {
            $path = $ID . '-' . time() . '.' . $ext;
          } else {
            $path = $ID . '_' . mswCleanFile($f_name);
          }
          $upl->moveFile($f_temp, $this->settings->attachpathfaq . '/' . $path);
          // Required by some servers to make image viewable and accessible via FTP..
          $upl->chmodFile($this->settings->attachpathfaq . '/' . $path, $this->internal['chmod-after']);
        } else {
          // Are we renaming attachments..
          if ($this->settings->renamefaq == 'yes') {
            $path = $ID . '.' . $ext;
          } else {
            $path = mswCleanFile($f_name);
          }
          $upl->moveFile($f_temp, $this->settings->attachpathfaq . '/' . $path);
          // Required by some servers to make image viewable and accessible via FTP..
          $upl->chmodFile($this->settings->attachpathfaq . '/' . $path, $this->internal['chmod-after']);
        }
        // Remove temp file if it still exists..
        if (file_exists($f_temp)) {
          @unlink($f_temp);
        }
        $reload = 'yes';
      }
      // Add to database..
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "faqattach` SET
      `ts`       = UNIX_TIMESTAMP(),
      `name`     = '" . mswSafeImportString($display) . "',
      `remote`   = '" . mswSafeImportString($remote) . "',
      `path`     = '" . mswSafeImportString($path) . "',
      `size`     = '{$f_size}',
      `mimeType` = '{$f_mime}'
      WHERE `id` = '{$ID}'
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
    return $reload;
  }

  // Remote file size
  public function remoteSize($file) {
    $headers = @get_headers($file, true);
    if (isset($headers['Content-Length'])) {
      return $headers['Content-Length'];
    }
    return '0';
  }

  // Add attachments..
  public function addAttachments($dl, $upl) {
    $count = 0;
    if (!empty($_FILES['file']['tmp_name'])) {
      for ($i = 0; $i < count($_FILES['file']['tmp_name']); $i++) {
        if ($upl->isUploaded($_FILES['file']['tmp_name'][$i])) {
          $display = $_FILES['file']['name'][$i];
          $f_name  = $_FILES['file']['name'][$i];
          $f_temp  = $_FILES['file']['tmp_name'][$i];
          $f_mime  = $_FILES['file']['type'][$i];
          $f_size  = ($f_name && $f_temp ? $_FILES['file']['size'][$i] : '0');
          $ext     = substr(strrchr(strtolower($f_name), '.'), 1);
          // Add to database..
          mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "faqattach` (
          `ts`,
          `name`,
          `path`,
          `size`,
          `mimeType`
          ) VALUES (
          UNIX_TIMESTAMP(),
          '" . mswSafeImportString($display) . "',
          '" . mswSafeImportString($f_name) . "',
          '{$f_size}',
          '{$f_mime}'
          )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
          $ID = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
          // Now upload file if applicable..
          if ($ID > 0) {
            if ($f_size > 0) {
              // Does file exist?
              if (file_exists($this->settings->attachpathfaq . '/' . $f_name)) {
                // Are we renaming attachments..
                if ($this->settings->renamefaq == 'yes') {
                  $new = $ID . '-' . time() . '.' . $ext;
                } else {
                  $new = $ID . '_' . mswCleanFile($f_name);
                }
                $upl->moveFile($f_temp, $this->settings->attachpathfaq . '/' . $new);
                // Required by some servers to make file viewable and accessible via FTP..
                $upl->chmodFile($this->settings->attachpathfaq . '/' . $new, $this->internal['chmod-after']);
              } else {
                // Are we renaming attachments..
                if ($this->settings->renamefaq == 'yes') {
                  $new = $ID . '.' . $ext;
                } else {
                  $new = mswCleanFile($f_name);
                }
                $upl->moveFile($f_temp, $this->settings->attachpathfaq . '/' . $new);
                // Required by some servers to make file viewable and accessible via FTP..
                $upl->chmodFile($this->settings->attachpathfaq . '/' . $new, $this->internal['chmod-after']);
              }
              // Was file renamed?
              mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "faqattach` SET `path` = '{$new}' WHERE `id` = '{$ID}'");
            }
            ++$count;
          }
          // Remove temp file if it still exists..
          if (file_exists($f_temp)) {
            @unlink($f_temp);
          }
        }
      }
    }
    // Remote files..
    if (!empty($_POST['remote'])) {
      $mime = $dl->mime_types();
      foreach ($_POST['remote'] AS $rm) {
        if ($rm) {
          // Add to database..
          $display = substr(basename($rm), -250);
          $ext     = substr(strrchr(strtolower($display), '.'), 1);
          $size    = faqCentre::remoteSize($rm);
          $mime    = (isset($mime[$ext]) ? $mime[$ext] : '');
          mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "faqattach` (
          `ts`,
          `name`,
          `remote`,
          `size`,
          `mimeType`
          ) VALUES (
          UNIX_TIMESTAMP(),
          '" . mswSafeImportString($display) . "',
          '" . mswSafeImportString($rm) . "',
          '{$size}',
          '{$mime}'
          )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
          ++$count;
        }
      }
    }
    // Rebuild sequence..
    faqCentre::rebuildAttSequence();
    return $count;
  }

  // Enable/disable cats..
  public function enableDisableCats() {
    $_GET['id'] = (int) $_GET['id'];
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "categories` SET
    `enCat`    = '" . ($_GET['changeState'] == 'fa fa-flag fa-fw msw-green cursor_pointer' ? 'no' : 'yes') . "'
    WHERE `id` = '{$_GET['id']}'
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
  }

  // Re-order categories..
  public function orderCatSequence() {
    // Parents..
    foreach ($_POST['order'] AS $k => $v) {
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "categories` SET
	    `orderBy`  = '{$v}'
      WHERE `id` = '{$k}'
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
    // Children..
    if (!empty($_POST['orderSub'])) {
      foreach ($_POST['orderSub'] AS $k => $v) {
        mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "categories` SET
	      `orderBy`  = '{$v}'
        WHERE `id` = '{$k}'
        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      }
    }
  }

  // Rebuild category sequence..
  public function rebuildCatSequence() {
    $seq = 0;
    $q   = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id` FROM `" . DB_PREFIX . "categories` WHERE `subcat` = '0' ORDER BY IF(`orderBy`>0,`orderBy`,9999)");
    while ($CT = mysqli_fetch_object($q)) {
      $n = (++$seq);
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "categories` SET
	    `orderBy`  = '{$n}'
	    WHERE `id` = '{$CT->id}'
	    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      // Subs..
      $seqs = 0;
      $q2   = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id` FROM `" . DB_PREFIX . "categories` WHERE `subcat` = '{$CT->id}' ORDER BY IF(`orderBy`>0,`orderBy`,9999)");
      while ($SB = mysqli_fetch_object($q2)) {
        $ns = (++$seqs);
        mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "categories` SET
	      `orderBy`  = '{$ns}'
	      WHERE `id` = '{$SB->id}'
	      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      }
    }
  }

  // Add category..
  public function addCategory() {
    $_POST['subcat'] = (int) $_POST['subcat'];
    mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "categories` (
    `name`,
    `summary`,
    `enCat`,
    `subcat`,
    `private`
    ) VALUES (
    '" . mswSafeImportString($_POST['name']) . "',
    '" . mswSafeImportString($_POST['summary']) . "',
    '" . (isset($_POST['enCat']) ? 'yes' : 'no') . "',
    '{$_POST['subcat']}',
    '" . (isset($_POST['private']) && $_POST['subcat'] == '0' ? 'yes' : 'no') . "'
    )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    $last = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
    // Rebuild order sequence..
    faqCentre::rebuildCatSequence();
  }

  // Update category..
  public function updateCategory() {
    $_GET['edit']    = (int) $_POST['update'];
    $_POST['subcat'] = (int) $_POST['subcat'];
    $private         = (isset($_POST['private']) && $_POST['subcat'] == '0' ? 'yes' : 'no');
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "categories` SET
    `name`      = '" . mswSafeImportString($_POST['name']) . "',
    `summary`   = '" . mswSafeImportString($_POST['summary']) . "',
    `enCat`     = '" . (isset($_POST['enCat']) && in_array($_POST['enCat'], array(
      'yes',
      'no'
     )) ? $_POST['enCat'] : 'no') . "',
    `subcat`    = '{$_POST['subcat']}',
    `private`   = '{$private}'
    WHERE `id`  = '{$_GET['edit']}'
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    // Update privacy status for questions in cat and sub cats..
    $catIDs = array($_GET['edit']);
    $q      = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id` FROM `" . DB_PREFIX . "categories`
              WHERE `subcat` = '{$_GET['edit']}'");
    while ($SB = mysqli_fetch_object($q)) {
      $catIDs[] = $SB->id;
    }
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "faq` SET
    `private`   = '{$private}'
    WHERE `id` IN(" . mswSafeImportString(implode(',', $catIDs)) . ")
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    // Update all subcats..
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "categories` SET
    `private`   = '{$private}'
    WHERE `id` IN(" . mswSafeImportString(implode(',', $catIDs)) . ")
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
  }

  // Delete categories..
  public function deleteCategories() {
    $que = array();
    if (!empty($_POST['del'])) {
      // Clear cats..
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "categories`
      WHERE `id` IN(" . mswSafeImportString(implode(',', $_POST['del'])) . ")
	    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      $rows = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
      // Clear assigned data..
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "faqassign`
      WHERE `itemID` IN(" . mswSafeImportString(implode(',', $_POST['del'])) . ")
	    AND `desc`      = 'category'
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      // Table cleanup..
      if (mswRowCount('categories') == 0) {
        @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "categories`");
        @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "faq`");
        @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "faqassign`");
      } else {
        if (mswRowCount('faq') == 0) {
          @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "faq`");
          @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "faqassign`");
        } else {
          if (mswRowCount('faqassign') == 0) {
            @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "faqassign`");
          }
        }
      }
      // Rebuild sequence..
      faqCentre::rebuildCatSequence();
      return $rows;
    }
  }

  // Enable/disable questions..
  public function enableDisableQuestions() {
    $_GET['id'] = (int) $_GET['id'];
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "faq` SET
    `enFaq`    = '" . ($_GET['changeState'] == 'fa fa-flag fa-fw msw-green cursor_pointer' ? 'no' : 'yes') . "'
    WHERE `id` = '{$_GET['id']}'
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
  }

  // Add question..
  public function addQuestion() {
    $assign = (empty($_POST['cat']) ? (!empty($_POST['catall']) ? $_POST['catall'] : array()) : $_POST['cat']);
    mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "faq` (
    `ts`,
    `question`,
    `answer`,
    `featured`,
    `enFaq`
    ) VALUES (
    UNIX_TIMESTAMP(),
    '" . mswSafeImportString($_POST['question']) . "',
    '" . mswSafeImportString($_POST['answer']) . "',
    '" . (isset($_POST['featured']) ? 'yes' : 'no') . "',
    '" . (isset($_POST['enFaq']) ? 'yes' : 'no') . "'
    )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    $ID = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
    // Assign attachments..
    if (!empty($_POST['att']) && $ID > 0) {
      foreach ($_POST['att'] AS $aID) {
        mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "faqassign` (
        `question`,`itemID`,`desc`
        ) VALUES (
        '{$ID}','{$aID}','attachment'
        )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      }
    }
    // Assign categories..
    if (!empty($assign) && $ID > 0) {
      foreach ($assign AS $aID) {
        mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "faqassign` (
        `question`,`itemID`,`desc`
        ) VALUES (
        '{$ID}','{$aID}','category'
        )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      }
    }
    // Are any categories private? If so, question is private..
    if (!empty($assign)) {
      $qP = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id` FROM `" . DB_PREFIX . "categories`
            WHERE `id`    IN(" . mswSafeImportString(implode(',', $assign)) . ")
            AND `private` = 'yes'
            AND `enCat`   = 'yes'
            ");
      if (mysqli_num_rows($qP) > 0) {
        mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "faq` SET
        `private`   = 'yes'
        WHERE `id`  = '{$ID}'
        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      }
    }
    // Rebuild sequence..
    faqCentre::rebuildQueSequence();
  }

  // Update question..
  public function updateQuestion() {
    $_GET['edit'] = (int) $_POST['update'];
    $assign       = (empty($_POST['cat']) ? $_POST['catall'] : $_POST['cat']);
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "faq` SET
    `ts`        = UNIX_TIMESTAMP(),
    `question`  = '" . mswSafeImportString($_POST['question']) . "',
    `answer`    = '" . mswSafeImportString($_POST['answer']) . "',
    `featured`  = '" . (isset($_POST['featured']) ? 'yes' : 'no') . "',
    `enFaq`     = '" . (isset($_POST['enFaq']) ? 'yes' : 'no') . "'
    WHERE `id`  = '{$_GET['edit']}'
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    // Update attachments..
    if (!empty($_POST['att'])) {
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "faqassign` WHERE `question` = '{$_GET['edit']}' AND `desc` = 'attachment'");
      if (mswRowCount('faqassign') == 0) {
        @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "faqassign`");
      }
      foreach ($_POST['att'] AS $aID) {
        mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "faqassign` (
        `question`,`itemID`,`desc`
        ) VALUES (
        '{$_GET['edit']}','{$aID}','attachment'
        )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      }
    }
    // Update categories..
    if (!empty($assign)) {
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "faqassign` WHERE `question` = '{$_GET['edit']}' AND `desc` = 'category'");
      if (mswRowCount('faqassign') == 0) {
        @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "faqassign`");
      }
      foreach ($assign AS $aID) {
        mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "faqassign` (
        `question`,`itemID`,`desc`
        ) VALUES (
        '{$_GET['edit']}','{$aID}','category'
        )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      }
    }
    // Are any categories private? If so, question is private..
    $qP = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id` FROM `" . DB_PREFIX . "categories`
          WHERE `id`    IN(" . mswSafeImportString(implode(',', $assign)) . ")
          AND `private` = 'yes'
          AND `enCat`   = 'yes'
          ");
    if (mysqli_num_rows($qP) > 0) {
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "faq` SET
      `private`   = 'yes'
      WHERE `id`  = '{$_GET['edit']}'
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
  }

  // Delete question..
  public function deleteQuestions() {
    if (!empty($_POST['del'])) {
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "faq`
      WHERE `id` IN(" . mswSafeImportString(implode(',', $_POST['del'])) . ")
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      $rows = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "faqassign`
      WHERE `question` IN(" . mswSafeImportString(implode(',', $_POST['del'])) . ")
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      if (mswRowCount('faq') == 0) {
        @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "faq`");
        @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "faqassign`");
      } else {
        if (mswRowCount('faqassign') == 0) {
          @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "faqassign`");
        }
      }
      // Rebuild sequence..
      faqCentre::rebuildQueSequence();
      return $rows;
    }
  }

  // Rebuild question order sequence..
  public function rebuildQueSequence() {
    $seq = 0;
    $q   = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id` FROM `" . DB_PREFIX . "faq` ORDER BY IF(`orderBy`>0,`orderBy`,9999)");
    while ($RB = mysqli_fetch_object($q)) {
      $n = (++$seq);
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "faq` SET
	    `orderBy`  = '{$n}'
	    WHERE `id` = '{$RB->id}'
	    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
  }

  // Order sequence..
  public function orderQueSequence() {
    foreach ($_POST['order'] AS $k => $v) {
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "faq` SET
	    `ts`       = UNIX_TIMESTAMP(),
      `orderBy`  = '{$v}'
      WHERE `id` = '{$k}'
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
  }

  // Reset counts..
  public function resetCounts() {
    if (!empty($_POST['del'])) {
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "faq` SET
      `ts`          = UNIX_TIMESTAMP(),
      `kviews`      = '0',
      `kuseful`     = '0',
      `knotuseful`  = '0'
      WHERE `id`   IN(" . mswSafeImportString(implode(',', $_POST['del'])) . ")
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
  }

  // Batch import..
  public function batchImportQuestions() {
    $count = 0;
    // Clear current questions..
    if (isset($_POST['clear'])) {
      $que  = array();
      $chop = (empty($_POST['cat']) ? $_POST['catall'] : $_POST['cat']);
      if (!empty($chop)) {
        $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `question` FROM `" . DB_PREFIX . "faqassign`
	           WHERE `itemID` IN(" . mswSafeImportString(implode(',', $chop)) . ")
             AND `desc`      = 'category'
             GROUP BY `question`
             ORDER BY `itemID`
             ");
        while ($QUE = mysqli_fetch_object($q)) {
          $que[] = $QUE->question;
        }
        if (!empty($que)) {
          mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "faq` WHERE `id` IN(" . mswSafeImportString(implode(',', $que)) . ")") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
          if (mswRowCount('faq') == 0) {
            @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "faq`");
            @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "faqassign`");
          }
        }
      }
    }
    // Upload CSV file..
    if (isset($_SESSION['upload']['file']) && file_exists($_SESSION['upload']['file'])) {
      // If uploaded file exists, read CSV data...
      $handle = fopen($_SESSION['upload']['file'], 'r');
      if ($handle) {
        while (($CSV = fgetcsv($handle, CSV_MAX_LINES_TO_READ, CSV_IMPORT_DELIMITER, CSV_IMPORT_ENCLOSURE)) !== false) {
          // Clean array..
          $CSV = array_map('trim', $CSV);
          mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "faq` (
          `ts`,
          `question`,
          `answer`
          ) VALUES (
          UNIX_TIMESTAMP(),
          '" . mswSafeImportString($CSV[0]) . "',
          '" . mswSafeImportString($CSV[1]) . "'
          )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
          $ID     = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
          // Assign categories..
          $assign = (empty($_POST['cat']) ? $_POST['catall'] : $_POST['cat']);
          if (!empty($assign) && $ID > 0) {
            foreach ($assign AS $aID) {
              mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "faqassign` (
              `question`,`itemID`,`desc`
              ) VALUES (
              '{$ID}','{$aID}','category'
              )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
            }
          }
          ++$count;
        }
        fclose($handle);
      }
    }
    // Clear session file..
    if (file_exists($_SESSION['upload']['file'])) {
      @unlink($_SESSION['upload']['file']);
    }
    // Rebuild sequence..
    faqCentre::rebuildQueSequence();
    return $count;
  }

}

?>