<?php

/* @method null chap_card($student, $year)
 *
 *  relies on a mysql_connect connection.
 *
 *  @param array $students => a row from the DBS to render....
 */
function custom_card($info, $year = 5778) { ?>
    <h2 class="no-print">FRONT</h2>
    <div class="card">
        <div class="topSec grade5">
            <?= $info['title'] ?>
        </div>
        
        <img src="chidon_<?=$info['gender'];?>.png?v=2" class="chidon_img" />
        
        <div class="personal">
            <div class="name">
                <?= $info['name'] . ' #' . $info['id_number']; ?>
            </div>
            <?=$info['school_name']?><br/>
            
            <?= $info['school_location'] ?>
        </div>
        
        <div class="team">
            Team:<br />
            <div class="teamName"><?=$info['team']?></div>
        </div>
        
        <div class="bunk">
            Bunk:<br />
            <?=$info['bunk']?>
        </div>
        
        <div class="grade">
            Grade: <?=$info['grade']?>
        </div>
        
        <div class="bottomSec grade5 bottomText">
            <?php // TODO: show the xth year of the chidon that the child is in. ?>
        </div>
    </div>
    <div style="page-break-after: always"></div>
    <h2 class="no-print">BACK</h2>
    <div class="card back">
        <div class="topSec grade5">
            Contacts
        </div>
        
        <div class="middle">
            <div class="host">
                <div class="title">Headquarters</div>
                718-907-8884<br/>
            </div>
            <div class="emerg">
                <div class="title">Emergency</div>
                Hatzola: 718-387-1750<br />
                Police / Fire: 911<br />
                Shmirah: 718-221-0303<br />
                <br />
            </div>
        </div>
        <div style="clear: both"></div>

        <img src="award-ceremony.png" class="force_bottom"/>
        <div style="page-break-after: always"></div>
    </div>
    <hr class="no-print"/>
<?php } // endfunciton student_card ?>