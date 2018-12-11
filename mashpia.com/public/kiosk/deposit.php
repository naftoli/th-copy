<?php 
include_once ("../header.php");
require_once('../calendar.php');
require_once('../file_save.php');
require_once('../card_printer.php');

$range = gri('range', 0);
$user_row = mysql_fetch_assoc(mq("
SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
       user_city, user_state, user_postal, user_country, user_phone,
       user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id,
       team_name, school_name, school_number, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, rank_name, rank_image_id, rank_color
FROM users
     LEFT JOIN schools USING (school_id)
     LEFT JOIN institutions USING (inst_id)
     LEFT JOIN classes USING (school_id, class_id)
     LEFT JOIN teams USING (school_id, team_id)
     LEFT JOIN (SELECT user_id, MAX(rank_ord) rank_ord FROM rank_marks WHERE user_id = {$user['user_id']} GROUP BY user_id) rank USING (user_id)
     LEFT JOIN ranks USING (rank_ord)
WHERE user_id = {$user['user_id']}
ORDER BY class_grade, class_sub, last, first
"));

$title ='Deposit';
include("includes/header.php");
?>
<STYLE type="text/css">
#code_popup table.card {
  float: left;
  margin: 0px 1em;
  line-height: auto;
  z-index: 200;
}
#black_cover {
  height: 100%;
  width: 100%;
  position: absolute;
  background-color: black;
  opacity: 0.7;
  -moz-opacity: 0.7;
  filter: alpha(opacity=70);
  z-index: 100;
}

   #code_popup {
  min-height: 200px;
  width: 60%;
  margin: 5% 20%;
  position: absolute;
  overflow: auto;
  background-color: white;
  color: black;
  opacity: 1;
  -moz-opacity: 1;
  filter: alpha(opacity=100);
  z-index: 110;
  padding: 10px;
}

#code_popup div.message {
  font-size: 24px;
}

#code_popup div.message b {
  color: #8cad42;
}

#code_popup div.message div {
  font-size: 60px;
  font-weight: bold;
}
 </STYLE>

<body class="orange">
<? if($user['registered']) include('../code_processor.php'); ?>

    <div id="wrapper">
        <div id="header">
          <?php include("includes/topbar.php"); ?>
      </div>
        <div id="main">
            <div id="page_title">Deposit</div>
            <div class="three_column padding_top">
              <div class="content deposit">
                    <div id="slider">
                      <ul>
                    <? $codes = mq("SELECT first, last, code_id, code_id_prefix, grant_date FROM user_codes LEFT JOIN admins USING (admin_id) WHERE user_id = {$user['user_id']} ORDER BY grant_date"); ?>
                    <? if(!mysql_num_rows($codes)): ?>
                    <li>
                              <div class="card_single"> 
                      <P style="width:100%;vertical-align:middle;height:100%;text-align:center;"><?=T_('No achievement cards available for deposit.')?></P>
                      </div>
                      </li>
                    <? else: 
                    $li = 0;
                    while($row = mysql_fetch_assoc($codes)):?>
                        <? 
                        $li++;
                        $code_details = code_details($row['code_id_prefix'], $row['code_id'], $user['user_id']);
                        $left_circle = $code_details['left_circle'];
                        $right_circle = $code_details['right_circle'] ;
                        if($left_circle === '') 
                          $left_circle = '&nbsp;';
                        if($right_circle === '') 
                          $right_circle = '&nbsp;';
                        $id = $row['code_id_prefix'].str_pad($row['code_id'], 19, '0', STR_PAD_LEFT);  
                      $bonus = floatval($code_details['bonus']) ? ' + ' . floatval($code_details['bonus']) . ' ' . T_('Bonus') : '';
                        ?>
                            <li>
                              <div class="card_single">
								<div class="card_shadow card_front_left">
                                    <div class="card_front">
                                        <p><img alt="Chayolei Tzivos Hashem" src="images/cards/Chayolei_Tzivos_Hashem.png"/><br/>
                                        <b><img alt="Achievement Card" src="images/cards/Achievement_Card.png"/></b></p>
                                        <p>This card is only valid in:</p>
                                        <table>
                                            <tbody>
                                                <tr>
                                                    <td><?=(!is_null($code_details['school_logo_id']) ? linkImgFile($code_details['school_logo_id'], 50,50) : '')?></td>
                                                    <td>
                                                        <b><?=T_('BASE'). ' #' . $code_details['school_number']?></b><br/>
                                                        <b><?es($code_details['school_name'])?></b><br/>
                                                        <?=es($code_details['school_city']) . ', ' . es($code_details['school_state'])?>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <p><?=T_('This card expires'). ': <b>' . dateToHebrewCommaYear($code_details['expires'])?></b></p>
                                    </div>
                                </div>
                                <div class="card_shadow">
                                    <div class="card_back">
                                        <div class="border">
                                            <table style="width: 100%;">
                                                <tbody>
                                                    <tr>
                                                        <td style="width: 33%;">
                                                          <div class="circle"><?=$left_circle?></div>
                                                          <?=es($code_details['description'])?>
                                                        </td>
                                                        <th><?=(!is_null($code_details['subject_image_id']) ? linkImgFile($code_details['subject_image_id'],70,74) : '')?></th>
                                                        <td style="width: 33%;">
                                                          <div class="circle"><?=$right_circle?></div>
                                                          <?=es($code_details['subject_name']).($code_details['series'] !== '' && !is_null($code_details['series']) ? " #".$code_details['series'] : '')?>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        <div class="barcode"><IMG SRC="../barcode.php/'<?=$id?>'" width="233" height="29" alt=""><BR><?=$id?></div>
                                        <div class="points"><table><tbody><tr><td><div class="border"><?=floatval($code_details['points']).' '.T_('Miles')?></div></td></tr></tbody></table></div>
                                        </div>
                                    </div>
                                </div>                      
                               <div class="member_info" >
                                        <div><label>Granted by:</label> <span><?=es($row['first'])?> <?=es($row['last'])?></span></div>
                                        <div><label>Date:</label>  <span><?=dateToHebrew(unixtojd(strtotime($row['grant_date'])));?></span></div>
                                </div>
                                <div class="button button_icons">
                                	<FORM id="deposit<?=$li?>" name="deposit<?=$li?>" action="deposit.php" method="post" accept-charset="UTF-8" >
                                	<INPUT type="hidden" name="scan_code" value="<?=$row['code_id_prefix'].str_pad($row['code_id'], 19, '0', STR_PAD_LEFT)?>">
                                	<div class="bottom">
                                	<a class="icon_deposit" onclick="document.getElementById('deposit<?=$li?>').submit()" href="#"><?=T_('Deposit to Account')?></a>
                                	</div>
                                	</FORM>
                                    
                                </div>
                              </div>
                            </li>
                          <?endwhile;?>
                        <?endif;?>
                      </ul>
                    </div>
              </div>
            </div>
        </div>
        <div id="footer">
			<?php include("includes/bottombar.php"); ?>
      </div>
    </div>
</body>

<?php include("includes/footer.php"); ?>
