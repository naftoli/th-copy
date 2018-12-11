<?php if (!defined('PATH')) { exit; }

$slidePanelLeftMenu = array();
$footerSlideMenu    = '';

//--------------
// Tickets
//--------------

$tickMenuArr = array('assign','open','close','disputes','cdisputes','search','search-fields','add','spam');
$mR1         = array_intersect($tickMenuArr, $userAccess);

if (!empty($mR1) || $MSTEAM->id == '1') {

  $slidePanelLeftMenu['tickets']          = array($msg_adheader41, 'pencil');
  $slidePanelLeftMenu['tickets']['links'] = array();

  // Add new ticket..
  if (in_array('add', $mR1) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['tickets']['links'][] = array(
      'url' => '?p=add',
      'name' => mswCleanData($msg_open)
    );
  }

  // Assign tickets..
  if (in_array('assign', $mR1) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['tickets']['links'][] = array(
      'url' => '?p=assign',
      'name' => mswCleanData($msg_adheader32)
    );
  }

  // Open tickets..
  if (in_array('open', $mR1) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['tickets']['links'][] = array(
      'url' => '?p=open',
      'name' => mswCleanData($msg_adheader5)
    );
  }

  // Closed tickets..
  if (in_array('close', $mR1) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['tickets']['links'][] = array(
      'url' => '?p=close',
      'name' => mswCleanData($msg_adheader6)
    );
  }

  // Open disputes..
  if ($SETTINGS->disputes == 'yes') {
    if (in_array('disputes', $mR1) || $MSTEAM->id == '1') {
      $slidePanelLeftMenu['tickets']['links'][] = array(
        'url' => '?p=disputes',
        'name' => mswCleanData($msg_adheader28)
      );
    }

    // Closed disputes..
    if (in_array('cdisputes', $mR1) || $MSTEAM->id == '1') {
      $slidePanelLeftMenu['tickets']['links'][] = array(
        'url' => '?p=cdisputes',
        'name' => mswCleanData($msg_adheader29)
      );
    }
  }

  // Spam tickets..
  if (mswRowCount('imap WHERE `im_piping` = \'yes\' AND `im_spam` = \'yes\'')>0 && (in_array('spam', $mR1) || $MSTEAM->id == '1')) {
    $slidePanelLeftMenu['tickets']['links'][] = array(
      'url' => '?p=spam',
      'name' => mswCleanData($msg_adheader63)
    );
  }

  // Search tickets..
  if (in_array('search', $mR1) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['tickets']['links'][] = array(
      'url' => '?p=search',
      'name' => mswCleanData($msg_adheader7)
    );
  }

  // Search tickets by custom fields..
  if (mswRowCount('ticketfields') > 0) {
    if (in_array('search-fields', $mR1) || $MSTEAM->id == '1') {
      $slidePanelLeftMenu['tickets']['links'][] = array(
        'url' => '?p=search-fields',
        'name' => mswCleanData($msg_header18)
      );
    }
  }

}


//---------------
// Staff
//---------------

$staffMenuArr = array('team','teamman');
$mR2          = array_intersect($staffMenuArr, $userAccess);

if (!empty($mR2) || $MSTEAM->id == '1') {

  $slidePanelLeftMenu['staff']          = array($msg_adheader4, 'group');
  $slidePanelLeftMenu['staff']['links'] = array();

  // Add user..
  if (in_array('team', $mR2) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['staff']['links'][] = array(
      'url' => '?p=team',
      'name' => mswCleanData($msg_adheader57)
    );
  }

  // Manage users..
  if (in_array('teamman', $mR2) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['staff']['links'][] = array(
      'url' => '?p=teamman',
      'name' => mswCleanData($msg_adheader58)
    );
  }

}

//---------------
// Accounts
//---------------

$accMenuArr = array('accounts','accountman','accountsearch','acc-import');
$mR3        = array_intersect($accMenuArr, $userAccess);

if (!empty($mR3) || $MSTEAM->id == '1') {

  $slidePanelLeftMenu['accounts']          = array($msg_adheader38, 'user');
  $slidePanelLeftMenu['accounts']['links'] = array();

  // Add account..
  if (in_array('accounts', $mR3) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['accounts']['links'][] = array(
      'url' => '?p=accounts',
      'name' => mswCleanData($msg_adheader39)
    );
  }

  // Manage accounts..
  if (in_array('accountman', $mR3) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['accounts']['links'][] = array(
      'url' => '?p=accountman',
      'name' => mswCleanData($msg_adheader40)
    );
  }

  // Import accounts..
  if (in_array('acc-import', $mR3) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['accounts']['links'][] = array(
      'url' => '?p=acc-import',
      'name' => mswCleanData($msg_adheader59)
    );
  }

}

//--------------------
// Departments
//--------------------

$deptMenuArr = array('dept','deptman');
$mR4         = array_intersect($deptMenuArr, $userAccess);

if (!empty($mR4) || $MSTEAM->id == '1') {

  $slidePanelLeftMenu['dept']          = array($msg_adheader3, 'building');
  $slidePanelLeftMenu['dept']['links'] = array();

  // Add department..
  if (in_array('dept', $mR4) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['dept']['links'][] = array(
      'url' => '?p=dept',
      'name' => mswCleanData($msg_dept2)
    );
  }

  // Manage departments..
  if (in_array('deptman', $mR4) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['dept']['links'][] = array(
      'url' => '?p=deptman',
      'name' => mswCleanData($msg_dept9)
    );
  }

}

//-----------------
// Custom Fields
//-----------------

$fieldMenuArr = array('fieldsman','fields');
$mR5          = array_intersect($fieldMenuArr, $userAccess);

if (!empty($mR5) || $MSTEAM->id == '1') {

  $slidePanelLeftMenu['fields']          = array($msg_adheader26, 'th-list');
  $slidePanelLeftMenu['fields']['links'] = array();

  // Add custom field..
  if (in_array('fields', $mR5) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['fields']['links'][] = array(
      'url' => '?p=fields',
      'name' => mswCleanData($msg_customfields2)
    );
  }

  // Manage custom fields..
  if (in_array('fieldsman', $mR5) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['fields']['links'][] = array(
      'url' => '?p=fieldsman',
      'name' => mswCleanData($msg_adheader43)
    );
  }

}

//-------------------------
// Standard Responses
//-------------------------

$srMenuArr = array('responseman','standard-responses','standard-responses-import');
$mR6       = array_intersect($srMenuArr, $userAccess);

if (!empty($mR6) || $MSTEAM->id == '1') {

  $slidePanelLeftMenu['stanresp']          = array($msg_adheader13, 'comments-o');
  $slidePanelLeftMenu['stanresp']['links'] = array();

  // Standard responses..
  if (in_array('standard-responses', $mR6) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['stanresp']['links'][] = array(
      'url' => '?p=standard-responses',
      'name' => mswCleanData($msg_adheader53)
    );
  }

  // Manage responses..
  if (in_array('responseman', $mR6) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['stanresp']['links'][] = array(
      'url' => '?p=responseman',
      'name' => mswCleanData($msg_adheader54)
    );
  }

  // Import responses..
  if (in_array('standard-responses-import', $mR6) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['stanresp']['links'][] = array(
      'url' => '?p=standard-responses-import',
      'name' => mswCleanData($msg_adheader60)
    );
  }

}

//-------------------
// Custom Pages
//-------------------

$cPagesMenuArr = array('pages','pageman');
$mR11          = array_intersect($cPagesMenuArr, $userAccess);

if (!empty($mR11) || $MSTEAM->id == '1') {

  $slidePanelLeftMenu['pages']          = array($msadminlang3_1cspages[0], 'file-text-o');
  $slidePanelLeftMenu['pages']['links'] = array();

  // Add custom pages..
  if (in_array('pages', $mR11) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['pages']['links'][] = array(
      'url' => '?p=pages',
      'name' => mswCleanData($msadminlang3_1cspages[1])
    );
  }

  // Manage custom pages..
  if (in_array('pageman', $mR11) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['pages']['links'][] = array(
      'url' => '?p=pageman',
      'name' => mswCleanData($msadminlang3_1cspages[2])
    );
  }

}

//-------------------
// Priority Levels
//-------------------

$levelMenuArr = array('levels','levelsman');
$mR7          = array_intersect($levelMenuArr, $userAccess);

if (!empty($mR7) || $MSTEAM->id == '1') {

  $slidePanelLeftMenu['levels']          = array($msg_adheader52, 'flag-checkered');
  $slidePanelLeftMenu['levels']['links'] = array();

  // Add priority level..
  if (in_array('levels', $mR7) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['levels']['links'][] = array(
      'url' => '?p=levels',
      'name' => mswCleanData($msg_adheader50)
    );
  }

  // Manage priority levels..
  if (in_array('levelsman', $mR7) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['levels']['links'][] = array(
      'url' => '?p=levelsman',
      'name' => mswCleanData($msg_adheader51)
    );
  }

}

//----------------------
// Imap Accounts
//----------------------

$imapMenuArr = array('imap','imapman','imapfilter');
$mR8         = array_intersect($imapMenuArr, $userAccess);

if (!empty($mR8) || $MSTEAM->id == '1') {

  $slidePanelLeftMenu['imap']          = array($msg_adheader24, 'envelope-o');
  $slidePanelLeftMenu['imap']['links'] = array();

  // Add imap account..
  if (in_array('imap', $mR8) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['imap']['links'][] = array(
      'url' => '?p=imap',
      'name' => mswCleanData($msg_adheader39)
    );
  }

  // Manage imap accounts..
  if (in_array('imapman', $mR8) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['imap']['links'][] = array(
      'url' => '?p=imapman',
      'name' => mswCleanData($msg_adheader40)
    );
  }

  // Imap spam filter..
  if (in_array('imapfilter', $mR8) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['imap']['links'][] = array(
      'url' => '?p=imapfilter',
      'name' => mswCleanData($msg_adheader62)
    );
  }

}

//-----------
// FAQ
//-----------

$faqMenuArr = array('faq-cat','faq','attach','attachman','faqman','faq-catman','faq-import');
$mR10       = array_intersect($faqMenuArr, $userAccess);

if (!empty($mR10) || $MSTEAM->id == '1') {

  $slidePanelLeftMenu['faq']          = array($msg_adheader17, 'book');
  $slidePanelLeftMenu['faq']['links'] = array();

  // Add FAQ category..
  if (in_array('faq-cat', $mR10) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['faq']['links'][] = array(
      'url' => '?p=faq-cat',
      'name' => mswCleanData($msg_adheader44)
    );
  }

  // Manage FAQ categories..
  if (in_array('faq-catman', $mR10) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['faq']['links'][] = array(
      'url' => '?p=faq-catman',
      'name' => mswCleanData($msg_adheader45)
    );
  }

  // Add FAQ question..
  if (in_array('faq', $mR10) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['faq']['links'][] = array(
      'url' => '?p=faq',
      'name' => mswCleanData($msg_adheader46)
    );
  }

  // Manage FAQ questions..
  if (in_array('faqman', $mR10) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['faq']['links'][] = array(
      'url' => '?p=faqman',
      'name' => mswCleanData($msg_adheader47)
    );
  }

  // Import FAQ questions..
  if (in_array('faq-import', $mR10) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['faq']['links'][] = array(
      'url' => '?p=faq-import',
      'name' => mswCleanData($msg_adheader55)
    );
  }

  // Add attachments..
  if (in_array('attach', $mR10) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['faq']['links'][] = array(
      'url' => '?p=attachments',
      'name' => mswCleanData($msg_adheader48)
    );
  }

  // Manage attachments..
  if (in_array('attachman', $mR10) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['faq']['links'][] = array(
      'url' => '?p=attachman',
      'name' => mswCleanData($msg_adheader49)
    );
  }

}

//---------------------
// Settings
//---------------------

$setMenuArr = array('tools','portal','log','reports','settings');
$mR9        = array_intersect($setMenuArr, $userAccess);

if (!empty($mR9) || $MSTEAM->id == '1') {

  $slidePanelLeftMenu['settings']          = array($msg_adheader37, 'cog');
  $slidePanelLeftMenu['settings']['links'] = array();

  // Settings..
  if (in_array('settings', $mR9) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['settings']['links'][] = array(
      'url' => '?p=settings',
      'name' => mswCleanData($msg_adheader2)
    );
  }

  // Tools..
  if (in_array('tools', $mR9) || $MSTEAM->id == '1') {
    if (USER_DEL_PRIV == 'yes') {
      $slidePanelLeftMenu['settings']['links'][] = array(
        'url' => '?p=tools',
        'name' => mswCleanData($msg_adheader15)
      );
    }
  }

  // Reports..
  if (in_array('reports', $mR9) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['settings']['links'][] = array(
      'url' => '?p=reports',
      'name' => mswCleanData($msg_adheader34)
    );
  }

  // Entry log..
  if (in_array('log', $mR9) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['settings']['links'][] = array(
      'url' => '?p=log',
      'name' => mswCleanData($msg_adheader20)
    );
  }

  // Database backup..
  if (in_array('backup', $mR9) || $MSTEAM->id == '1') {
    $slidePanelLeftMenu['settings']['links'][] = array(
      'url' => '?p=backup',
      'name' => mswCleanData($msg_adheader30)
    );
  }

}

// Build the footer menu for the slidepanel..
if (!empty($slidePanelLeftMenu)) {
  $footerSlideMenu = '<ul>';
  foreach (array_keys($slidePanelLeftMenu) AS $smk) {
    if (!empty($slidePanelLeftMenu[$smk]['links'])) {
      $footerSlideMenu .= '<li><a href="#"><i class="fa fa-' . $slidePanelLeftMenu[$smk][1] . ' fa-fw"></i> ' . $slidePanelLeftMenu[$smk][0] . '</a><ul>';
      for ($i=0; $i<count($slidePanelLeftMenu[$smk]['links']); $i++) {
        $footerSlideMenu .= '<li><a href="' . $slidePanelLeftMenu[$smk]['links'][$i]['url'] . '"><i class="fa fa-angle-right fa-fw"></i> ' . $slidePanelLeftMenu[$smk]['links'][$i]['name'] . '</a></li>';
      }
      $footerSlideMenu .= '</ul></li>';
    }
  }
  $footerSlideMenu .= '</ul>';
}

?>