<?php

/* @method null bunk_card($student, $year)
 *
 *  relies on a mysql_connect connection.
 *
 *  @param array $bunk => a row from the DBS to render....
 */
function bunk_card($bunk, $year = 5778) { ?>
    <h2 class="no-print">FRONT</h2>
    <div class="card">
        <div class="topSec grade8">
            Chidon<br/>Counselor
        </div>
        
        <img src="chidon_<?=$bunk['chidon_type'] == "boys" ? "M" : "F";?>.png?v=2" class="chidon_img" />
        
        <div class="personal">
            <div class="name">
                <?= $bunk['counselor'] ?>
            </div>
            Bunk #<?=$bunk['bunk_name']?><br/>
            
        </div>
        
        <div class="team">
            Walking Zone:<br />
            <?= $bunk['walking_zone'] ? $bunk['walking_zone'] : "N/A"; ?>
        </div>
        
        <div class="bunk">
            Chidon Type: <?=$bunk['chidon_type']?>
        </div>
        
        <div class="grade">
            Grade:
            <?= $bunk['grade']; ?>
        </div>
        
        <div class="bottomSec grade8 bottomText">
            <?php // TODO: show the xth year of the chidon that the child is in. ?>
        </div>
    </div>
    <div style="page-break-after: always"></div>
    <h2 class="no-print">BACK</h2>
    <div class="card back">
        <div class="topSec grade8">
            Contacts
        </div>
        
        <div class="middle">
            <div class="host">
                <?php // Host ?>
                <div class="title">Host</div>
                <?=$bunk['host_name']?> Family,<br/>
                <?=$bunk['host_address1'] . " " . $bunk['host_address2']?><br/>
                <small><?= $bunk['host_between_streets'] ?></small><br />
                
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
                
                <div class="title">Walking Chaperones</div>
                <?php
                $walking_bunk_query = mysql_query(
                    "SELECT * FROM th_chidon_chaps WHERE walking_zone = '" . $bunk['walking_zone'] . "' AND year = '$year'"
                );
                while ($walking_bunk = mysql_fetch_assoc($walking_bunk_query)) {
                    echo $walking_bunk['name']   ? $walking_bunk['name']   . "<br />" : "";
                    echo $walking_bunk['phone']  ? $walking_bunk['phone']  . "<br />" : "";
                    echo "<br />";
                } ?>
            </div>
        </div>
        
        <div class="info">
            <div class="bus">
                <div class="title">Bus Numbers</div>
                <div class="desc">
                    Thursday Bus    <b>#<?=$bunk['c_coach_bus']?></b><br />
                    Friday Bus      <b>#<?=$bunk['c_school_bus']?></b><br />
                    Sunday Bus      <b>#<?=$bunk['c_double_decker']?></b><br />
                </div>
            </div>
        </div>
        <div style="clear: both"></div>

        <img src="award-ceremony.png" class="force_bottom"/>
        <div style="page-break-after: always"></div>
    </div>
    <hr class="no-print"/>
<?php } // endfunciton student_card ?>