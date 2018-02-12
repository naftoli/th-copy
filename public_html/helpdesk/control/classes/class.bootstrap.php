<?php

class msBootStrap {

  // Drop down button..
  public function button($text, $links = array(), $orientation = '', $centered = 'no', $area = 'admin', $icon = '') {
    $html = '';
    $sep  = file_get_contents(PATH . 'templates/system/bootstrap/drop-down-button-li-sep.htm');
    switch ($area) {
      case 'admin':
        $button = file_get_contents(PATH . 'templates/system/bootstrap/drop-down-button.htm');
        $link   = file_get_contents(PATH . 'templates/system/bootstrap/drop-down-button-li.htm');
        break;
      default:
        $button = file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/bootstrap/drop-down-button.htm');
        $link   = file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/bootstrap/drop-down-button-li.htm');
        break;
    }
    foreach ($links AS $l => $v) {
      $html .= str_replace(array(
        '{link}',
        '{text}',
        '{extra}'
      ), array(
        $v['link'],
        (isset($v['name']) ? $v['name'] : ''),
        (isset($v['extra']) ? ' ' . $v['extra'] : '')
      ), ($v['link'] == 'sep' ? $sep : $link));
    }
    return str_replace(array(
      '{text}',
      '{links}',
      '{orientation}',
      '{icon}',
      '{centered}'
    ), array(
      $text,
      trim($html),
      $orientation,
      ($icon ? $icon : ($orientation == ' dropdown-menu-right' ? 'filter' : 'sort')),
      ($centered == 'yes' ? ' center_dropdown' : '')
    ), $button);
  }

}

?>