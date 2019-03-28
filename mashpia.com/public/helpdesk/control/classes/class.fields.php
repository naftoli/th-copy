<?php

class customFieldManager {

  public $parser;

  // Mysql..
  public function insert($ticketID, $fieldID, $replyID, $data) {
    mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "ticketfields` (
    `ticketID`,`fieldID`,`replyID`,`fieldData`
    ) VALUES (
    '{$ticketID}','{$fieldID}','{$replyID}','" . mswSafeImportString($data) . "'
    )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
  }

  // Display..
  public function display($ticketID, $replyID = 0, $count = 0, $label = 'panel panel-default') {
    $html = '';
    $wrap = file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/ticket-custom-fields-wrapper.htm');
    $qT = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "ticketfields`
          LEFT JOIN `" . DB_PREFIX . "cusfields`
          ON `" . DB_PREFIX . "cusfields`.`id` = `" . DB_PREFIX . "ticketfields`.`fieldID`
          WHERE `ticketID`  = '{$ticketID}'
          AND `replyID`     = '{$replyID}'
          AND `enField`     = 'yes'
          AND `fieldData`  != 'nothing-selected'
          AND `fieldData`  != ''
          ORDER BY `" . DB_PREFIX . "cusfields`.`id`
          ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    if ($count) {
      return mysqli_num_rows($qT);
    }
    while ($TS = mysqli_fetch_object($qT)) {
      if ($TS->repeatPref == 'no' && strpos($TS->fieldLoc, 'admin') !== false) {
      } else {
        switch ($TS->fieldType) {
          case 'textarea':
          case 'input':
          case 'select':
            $html .= str_replace(array(
              '{head}',
              '{data}',
              '{label}'
            ), array(
              mswCleanData($TS->fieldInstructions),
              $this->parser->mswTxtParsingEngine($TS->fieldData),
              $label
            ), file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/ticket-custom-fields.htm'));
            break;
          case 'checkbox':
            $html .= str_replace(array(
              '{head}',
              '{data}',
              '{label}'
            ), array(
              mswCleanData($TS->fieldInstructions),
              str_replace('#####', '<br>', mswCleanData($TS->fieldData)),
              $label
            ), file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/ticket-custom-fields.htm'));
            break;
        }
      }
    }
    return ($html ? str_replace('{fields}', trim($html), $wrap) : '');
  }

  // Return data for emails..
  public function email($ticketID, $replyID = 0) {
    $text = '';
    $qF = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "cusfields`
          LEFT JOIN `" . DB_PREFIX . "ticketfields`
          ON `" . DB_PREFIX . "cusfields`.`id` = `" . DB_PREFIX . "ticketfields`.`fieldID`
          WHERE `ticketID`  = '{$ticketID}'
          AND `replyID`     = '{$replyID}'
          AND `enField`     = 'yes'
          ORDER BY `" . DB_PREFIX . "cusfields`.`orderBy`
          ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    if (mysqli_num_rows($qF) > 0) {
      while ($FIELDS = mysqli_fetch_object($qF)) {
        switch ($FIELDS->fieldType) {
          case 'checkbox':
            $text .= mswCleanData($FIELDS->fieldInstructions) . mswDefineNewline();
            $text .= str_replace('#####', mswDefineNewline(), mswCleanData($FIELDS->fieldData)) . mswDefineNewline() . mswDefineNewline();
            break;
          default:
            $text .= mswCleanData($FIELDS->fieldInstructions) . mswDefineNewline();
            $text .= mswCleanData($FIELDS->fieldData) . mswDefineNewline() . mswDefineNewline();
            break;
        }
      }
    }
    return ($text ? trim($text) : 'N/A');
  }

  // Insert and return data..
  public function data($area, $ticketID, $replyID = 0, $dept) {
    $text = '';
    $qF  = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "cusfields`
           WHERE FIND_IN_SET('{$area}',`fieldLoc`) > 0
           AND `enField`  = 'yes'
           AND FIND_IN_SET('{$dept}',`departments`) > 0
           ORDER BY `orderBy`
           ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    if (mysqli_num_rows($qF) > 0) {
      while ($FIELDS = mysqli_fetch_object($qF)) {
        switch ($FIELDS->fieldType) {
          case 'textarea':
          case 'input':
            if ($_POST['customField'][$FIELDS->id] != '') {
              $text .= mswCleanData($FIELDS->fieldInstructions) . mswDefineNewline();
              $text .= $_POST['customField'][$FIELDS->id] . mswDefineNewline() . mswDefineNewline();
            }
            break;
          case 'select':
            if ($_POST['customField'][$FIELDS->id] != 'nothing-selected') {
              $text .= mswCleanData($FIELDS->fieldInstructions) . mswDefineNewline();
              $text .= $_POST['customField'][$FIELDS->id] . mswDefineNewline() . mswDefineNewline();
            }
            break;
          case 'checkbox':
            if (!empty($_POST['customField'][$FIELDS->id])) {
              $text .= mswCleanData($FIELDS->fieldInstructions) . mswDefineNewline();
              foreach ($_POST['customField'][$FIELDS->id] AS $k => $v) {
                $text .= $v . mswDefineNewline();
              }
              $text .= mswDefineNewline();
            }
            break;
        }
      }
    }
    return ($text ? trim($text) : 'N/A');
  }

  // Check required fields..
  public function check($area, $dept) {
    $e = array();
    $qF = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "cusfields`
          WHERE FIND_IN_SET('{$area}',`fieldLoc`) > 0
          AND `fieldReq`  = 'yes'
          AND `enField`   = 'yes'
          AND FIND_IN_SET('{$dept}',`departments`) > 0
          ORDER BY `orderBy`
          ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    if (mysqli_num_rows($qF) > 0) {
      while ($FIELDS = mysqli_fetch_object($qF)) {
        switch ($FIELDS->fieldType) {
          case 'textarea':
          case 'input':
            if (isset($_POST['customField'][$FIELDS->id]) && $_POST['customField'][$FIELDS->id] == '') {
              $e[] = $FIELDS->id;
            }
            break;
          case 'select':
            if (isset($_POST['customField'][$FIELDS->id]) && $_POST['customField'][$FIELDS->id] == 'nothing-selected') {
              $e[] = $FIELDS->id;
            }
            break;
          case 'checkbox':
            if (empty($_POST['customField'][$FIELDS->id])) {
              $e[] = $FIELDS->id;
            }
            break;
        }
      }
    }
    return $e;
  }

  // Render new fields..
  public function build($area, $dept) {
    $html = '';
    $tab  = 6;
    $qF = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "cusfields`
          WHERE FIND_IN_SET('{$area}',`fieldLoc`) > 0
          AND `enField`  = 'yes'
          AND FIND_IN_SET('{$dept}',`departments`) > 0
          ORDER BY `orderBy`
          ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    if (mysqli_num_rows($qF) > 0) {
      while ($F = mysqli_fetch_object($qF)) {
        switch ($F->fieldType) {
          case 'textarea':
            $html .= customFieldManager::textarea(mswCleanData($F->fieldInstructions), $F->id, ++$tab, $F->fieldReq);
            break;
          case 'input':
            $html .= customFieldManager::box(mswCleanData($F->fieldInstructions), $F->id, ++$tab, $F->fieldReq);
            break;
          case 'select':
            $html .= customFieldManager::select(mswCleanData($F->fieldInstructions), $F->id, $F->fieldOptions, ++$tab, $F->fieldReq);
            break;
          case 'checkbox':
            $html .= customFieldManager::checkbox(mswCleanData($F->fieldInstructions), $F->id, $F->fieldOptions, $F->fieldReq);
            break;
        }
      }
    }
    
    // Custom code added by Menachem Hornbacher on 1/22/2017 to avoid infinite loop issues
    if($dept == 3){
      $html .= "<script src='/helpdesk/content/_default_set/js/mashipa/technical_support_autofill.js?v=2.0'></script>";
    }
    
    return ($html ? trim($html) : '');
  }

  // Create select/drop down menu..
  public function select($text, $id, $options, $tab, $req) {
    global $msadminlang3_1createticket;
    $html    = '';
    $wrapper = file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/custom-fields/select.htm');
    $rqfld   = file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/custom-fields/required-field.htm');
    $select  = explode(mswDefineNewline(), $options);
    foreach ($select AS $o) {
      $html .= str_replace(array(
        '{value}',
        '{selected}',
        '{text}'
      ), array(
        mswCleanData($o),
        (isset($_POST['customField'][$id]) ? mswSelectedItem($_POST['customField'][$id], $o) : ''),
        mswCleanData($o)
      ), file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/custom-fields/select-option.htm'));
    }
    return str_replace(array(
      '{id}',
      '{options}',
      '{label}',
      '{tab}',
      '{req}'
    ), array(
      $id,
      trim($html),
      mswCleanData($text),
      $tab,
      ($req == 'yes' ? str_replace('{text}', $msadminlang3_1createticket[9], $rqfld) : '')
    ), $wrapper);
  }

  // Create checkbox..
  public function checkbox($text, $id, $options, $req) {
    global $msg_viewticket71, $msadminlang3_1createticket;
    $wrapper = file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/custom-fields/checkbox-wrapper.htm');
    $rqfld   = file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/custom-fields/required-field.htm');
    $html    = '';
    $v       = array();
    $boxes   = explode(mswDefineNewline(), $options);
    if (isset($_POST['customField'][$id]) && !empty($_POST['customField'][$id])) {
      $v = $_POST['customField'][$id];
    }
    foreach ($boxes AS $cb) {
      $html .= str_replace(array(
        '{value}',
        '{checked}',
        '{id}'
      ), array(
        mswCleanData($cb),
        (in_array($cb, $v) ? ' checked="checked"' : ''),
        $id
      ), file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/custom-fields/checkbox.htm'));
    }
    return str_replace(array(
      '{label}',
      '{text}',
      '{checkboxes}',
      '{id}',
      '{req}'
    ), array(
      mswCleanData($text),
      $msg_viewticket71,
      trim($html),
      $id,
      ($req == 'yes' ? str_replace('{text}', $msadminlang3_1createticket[9], $rqfld) : '')
    ), $wrapper);
  }

  // Create input box..
  public function box($text, $id, $tab, $req) {
    global $msadminlang3_1createticket;
    $rqfld   = file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/custom-fields/required-field.htm');
    return str_replace(array(
      '{label}',
      '{value}',
      '{id}',
      '{tab}',
      '{req}'
    ), array(
      mswCleanData($text),
      (isset($_POST['customField'][$id]) ? mswSafeDisplay($_POST['customField'][$id]) : ''),
      $id,
      $tab,
      ($req == 'yes' ? str_replace('{text}', $msadminlang3_1createticket[9], $rqfld) : '')
    ), file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/custom-fields/input-box.htm'));
  }

  // Create textarea..
  public function textarea($text, $id, $tab, $req) {
    global $msadminlang3_1createticket;
    $rqfld   = file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/custom-fields/required-field.htm');
    return str_replace(array(
      '{label}',
      '{value}',
      '{id}',
      '{tab}',
      '{req}'
    ), array(
      mswCleanData($text),
      (isset($_POST['customField'][$id]) ? mswSafeDisplay($_POST['customField'][$id]) : ''),
      $id,
      $tab,
      ($req == 'yes' ? str_replace('{text}', $msadminlang3_1createticket[9], $rqfld) : '')
    ), file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/custom-fields/textarea.htm'));
  }

}

?>