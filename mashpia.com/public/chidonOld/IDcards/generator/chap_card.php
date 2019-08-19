<?php

/* @method null chap_card($student, $year)
 *
 *  relies on a mysql_connect connection.
 *
 *  @param array $students => a row from the DBS to render....
 */
function chap_card($chaperone, $year = 5778) { ?>
    <h2 class="no-print">FRONT</h2>
    <div class="card">
        <div class="topSec grade4">
            Chidon<br/>Chaperone
        </div>
        
        <img src="chidon_<?=$chaperone['chidon_type'] == "boys" ? "M" : "F";?>.png?v=2" class="chidon_img" />
        
        <div class="personal">
            <div class="name">
                <?= $chaperone['name'] . ' #' . $chaperone['th_chidon_chap_id']; ?>
            </div>
            <?=$chaperone['school_name']?><br/>
            
            <?= ucwords(strtolower($chaperone['school_city'])) . ", " . strtoupper($chaperone['school_state']); ?>
        </div>
        
        <div class="team">
            Walking Zone:<br />
            <?= $chaperone['walking_zone'] ? $chaperone['walking_zone'] : "N/A"; ?>
        </div>
        
        <div class="bunk">
            Chidon Type: <?=$chaperone['chidon_type']?>
        </div>
        
        <div class="grade">
            Vehicle:
            <?= $chaperone['vehicle'] ? "Yes" : "No"; ?>
        </div>
        
        <div class="bottomSec grade4 bottomText">
            <?php // TODO: show the xth year of the chidon that the child is in. ?>
        </div>
    </div>
    <div style="page-break-after: always"></div>
    <h2 class="no-print">BACK</h2>
    <div class="card back">
        <div class="topSec grade4">
            Contacts
        </div>
        
        <div class="middle">
            <div class="host">
                <?php // Host ?>
                <div class="title">Host</div>
                <?=$chaperone['acc_name']?> Family,<br/>
                <?=$chaperone['acc_address']?><br/>
                <small><?= $chaperone['acc_cross_st'] ?></small><br/>
                <?= $chaperone['acc_phone'] ?><br />
                
                <br />
                
                <div class="title">Headquarters</div>
                718-907-8884<br/>
            </div>
            <div class="emerg">
                <div class="title">Emergency</div>
                Hatzola: 718-387-1750<br />
                Police / Fire: 911<br />
                Shmirah: 718-221-0303<br />
                <br />
                
                <!--<div class="title">Walking Counselors</div>-->
                <?php
                //$walking_bunk_query = mysql_query(
                //    "SELECT * FROM th_chidon_bunks WHERE walking_zone = '" . $chaperone['walking_zone']
                //    . "' AND year = '$year' AND chidon_type = '".  $chaperone['chidon_type'] ."' ORDER BY counselor"
                //);
                //while ($walking_bunk = mysql_fetch_assoc($walking_bunk_query)) {
                //    echo $walking_bunk['counselor'] ? $walking_bunk['counselor'] . "<br />" : "";
                //    echo $walking_bunk['bunk_name'] ? "Bunk: " . $walking_bunk['bunk_name'] . "<br />" : "";
                //    echo $walking_bunk['c_number']  ? $walking_bunk['c_number']  . "<br />" : "";
                //    echo "<br />";
                //} ?>
            </div>
        </div>
        <div style="clear: both"></div>

        <img src="award-ceremony.png" class="force_bottom"/>
    </div>
    <div style="page-break-after: always"></div>
    <hr class="no-print"/>
<?php } // endfunciton student_card ?>