<?
$steps = array(
'main' => T_('Base Registration'),
'admin' => T_('Account Setup'),
'shipping' => T_('Shipping'),
'package' => T_('Package'),
'billing' => T_('Billing'),
);

function registration_HTML_head() {
  return '<LINK href="register_styles.css" rel="stylesheet" type="text/css">';
}

function registration_banner_existing($step) {
  global $steps, $school_id;

  $ret = '
<DIV class="registration_banner">
<H1>' . T_('Tzivos Hashem') . ' - ' . $steps[$step] .'</H1>
';

  $i = 1;
  $ret .= '<TABLE class="progress" cellspacing="0" cellpadding="0"><TR>';
  foreach($steps as $step_name => $cur_step) {
    $ret .= '<TD' . ($step_name == $step ? ' class="current"' : '') . "><A HREF='register_school_{$step_name}.php?school_id=$school_id'>$cur_step</A><BR><SPAN>" . sprintf(T_('Step %d of %d'), $i++, count($steps)) . '</SPAN></TD>';
  }
  $ret .= '</TR></TABLE></DIV>';
  return $ret;
}

function registration_banner_new($step) {
  global $steps;

  $ret = '
<DIV class="registration_banner">
<H1>' . T_('Tzivos Hashem') . ' - ' . $steps[$step] .'</H1>
';

  $i = 1;
  $ret .= '<TABLE class="progress" cellspacing="0" cellpadding="0"><TR>';
  foreach($steps as $step_name => $cur_step) {
    $ret .= '<TD' . ($step_name == $step ? ' class="current"' : '') . "><INPUT type='submit' name='register_{$step_name}' value='{$cur_step}'><BR><SPAN>" . sprintf(T_('Step %d of %d'), $i++, count($steps)) . '</SPAN></TD>';
  }
  $ret .= '</TR></TABLE></DIV>';
  return $ret;
}

function registration_tail() {
  $ret = '<DIV class="registration_tail">&nbsp;</DIV>';
  return $ret;
}

?>
