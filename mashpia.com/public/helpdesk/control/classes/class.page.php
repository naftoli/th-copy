<?php

class pagination {

  public function __construct($data=array(), $query) {
    $this->total = $data[0];
    $this->start = 0;
    $this->text  = $data[1];
    $this->query = $query;
    $this->split = 10;
    $this->page  = $data[2];
    $this->flag  = (isset($data[3]) ? explode(',', $data[3]) : array());
  }

  public function perpage() {
    return PER_PAGE;
  }

  public function qstring() {
    $qstring = array();
    if (!empty($_GET)) {
      foreach ($_GET AS $k => $v) {
        if (is_array($v)) {
          foreach ($v AS $v2) {
            $qstring[] = $k . '[]=' . urlencode($v2);
          }
        } else {
          $merge = array_merge($this->flag, array('p', 'next'));
          if (!in_array($k, $merge)) {
            $qstring[] = $k . '=' . urlencode($v);
          }
        }
      }
    }
    return (!empty($qstring) ? '&amp;' . implode('&amp;', $qstring) : '');
  }

  public function display() {
    $html            = '';
    // How many pages?
    $this->num_pages = ceil($this->total / pagination::perpage());
    // If pages less than or equal to 1, display nothing..
    if ($this->num_pages <= 1) {
      return $html;
    }
    // Build pages..
    $current_page = $this->page;
    $begin        = $current_page - $this->split;
    $end          = $current_page + $this->split;
    if ($begin < 1) {
      $begin = 1;
      $end   = $this->split * 2;
    }
    if ($end > $this->num_pages) {
      $end   = $this->num_pages;
      $begin = $end - ($this->split * 2);
      $begin++;
      if ($begin < 1) {
        $begin = 1;
      }
    }
    if ($current_page != 1) {
      $html .= '<li class="hidden-xs hidden-sm"><a title="' . mswSafeDisplay($this->text[0]) . '" href="' . $this->query . '1' . $this->qstring() . '">' . $this->text[0] . '</a></li>' . mswDefineNewline();
      $html .= '<li class="hidden-xs hidden-sm"><a title="' . mswSafeDisplay($this->text[1]) . '" href="' . $this->query . ($current_page - 1) . $this->qstring() . '">' . $this->text[1] . '</a></li>' . mswDefineNewline();
    } else {
      $html .= '<li class="disabled hidden-xs hidden-sm"><a href="#">' . $this->text[0] . '</a></li>' . mswDefineNewline();
      $html .= '<li class="disabled hidden-xs hidden-sm"><a href="#">' . $this->text[1] . '</a></li>' . mswDefineNewline();
    }
    for ($i = $begin; $i <= $end; $i++) {
      if ($i != $current_page) {
        $html .= '<li><a title="' . $i . '" href="' . $this->query . $i . $this->qstring() . '">' . $i . '</a></li>' . mswDefineNewline();
      } else {
        $html .= '<li class="active"><a href="#">' . $i . '</a></li>' . mswDefineNewline();
      }
    }
    if ($current_page != $this->num_pages) {
      $html .= '<li class="hidden-xs hidden-sm"><a title="' . mswSafeDisplay($this->text[2]) . '" href="' . $this->query . ($current_page + 1) . $this->qstring() . '">' . $this->text[2] . '</a></li>' . mswDefineNewline();
      $html .= '<li class="hidden-xs hidden-sm"><a title="' . mswSafeDisplay($this->text[3]) . '" href="' . $this->query . $this->num_pages . $this->qstring() . '">' . $this->text[3] . '</a></li>' . mswDefineNewline();
    } else {
      $html .= '<li class="disabled hidden-xs hidden-sm"><a href="#">' . $this->text[2] . '</a></li>' . mswDefineNewline();
      $html .= '<li class="disabled hidden-xs hidden-sm"><a href="#">' . $this->text[3] . '</a></li>' . mswDefineNewline();
    }
    return '<div class="mswpages"><ul class="pagination pagination-sm">' . mswDefineNewline() . trim($html) . mswDefineNewline() . '</ul></div>';
  }

}

?>