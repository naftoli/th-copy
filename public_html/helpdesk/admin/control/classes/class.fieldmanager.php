<?php

class fieldManager {

  // Create select/drop down menu..
  public function buildSelect($text, $id, $options, $tabIndex, $value = '') {
    $html   = '<option value="nothing-selected">- - - - - - -</option>';
    $select = explode(mswDefineNewline(), $options);
    foreach ($select AS $o) {
      $html .= '<option value="' . mswSafeDisplay($o) . '"' . mswSelectedItem($value, $o) . '>' . mswCleanData($o) . '</option>' . mswDefineNewline();
    }
    return mswDefineNewline() . '<div class="form-group"><label>' . $text . '</label>' . mswDefineNewline() . '<select name="customField[' . $id . ']" tabindex="' . $tabIndex . '" class="form-control">' . $html . '</select></div>' . mswDefineNewline();
  }

  // Create checkbox..
  public function buildCheckBox($text, $id, $options, $values = '') {
    $html  = '';
    $v     = array();
    $boxes = explode(mswDefineNewline(), $options);
    if ($values) {
      $v = explode('#####', $values);
    }
    foreach ($boxes AS $cb) {
      $html .= '<div class="checkbox"><label><input type="checkbox" name="customField[' . $id . '][]" value="' . mswSafeDisplay($cb) . '"' . (in_array($cb, $v) ? ' checked="checked"' : '') . '> ' . $cb . '</label></div>' . mswDefineNewline();
    }
    return ($html ? mswDefineNewline() . '<div class="form-group"><input type="hidden" name="hiddenBoxes[]" value="' . $id . '"><label>' . $text . '</label>' . $html . '</div>' : '');
  }

  // Create input box..
  public function buildInputBox($text, $id, $tabIndex, $value = '') {
    return mswDefineNewline() . '<div class="form-group"><label>' . $text . '</label>' . mswDefineNewline() . '<input tabindex="' . $tabIndex . '" class="form-control" type="text" name="customField[' . $id . ']" value="' . mswSafeDisplay($value) . '"></div>' . mswDefineNewline();
  }

  // Create textarea..
  public function buildTextArea($text, $id, $tabIndex, $value = '') {
    return mswDefineNewline() . '<div class="form-group"><label>' . $text . '</label>' . mswDefineNewline() . '<textarea tabindex="' . $tabIndex . '" rows="5" cols="40" name="customField[' . $id . ']" class="form-control">' . mswSafeDisplay($value) . '</textarea></div>' . mswDefineNewline();
  }

}

?>