<?php

/* @method null student_card($student, $year)
 *
 *  relies on a mysql_connect connection.
 *
 *  @param array $students => a row from the DBS to render....
 *  @param number $year => the current chidon year.
 */
function student_card($student, $year = 5778) { // default to 5778 for now.... ?>
    <h2 class="no-print">FRONT</h2>
    <div class="card">
        <div class="topSec grade<?=$student['grade']?>">
            <?= strtoupper( $student['school_rep'] ? "School Representative" : "Chidon <br/>Contestant" ); // get the correct title and make it uppercase....?>
        </div>
        
        <img src="chidon_<?=$student['gender']?>.png?v=2" class="chidon_img" />
        
        <div class="personal">
            <div class="name">
                <?= $student['first'] . ' ' . $student['last'] . ' #' . $student['th_chidon_id']; ?>
            </div>
            <?=$student['school_name']?><br/>
            
            <?php // Anash Kinder and MyShliach need the parents location information...
            if( in_array( $student['school_id'], [61, 269] ) ) {
                $address_query = mysql_query(
                    "  SELECT a.admin_city, a.admin_state FROM admins a"
                    ." JOIN admin_auths aa USING (admin_id) "
                    ." WHERE aa.id = ".$student['user_id']." AND aa.auth = 'user' "
                    ." LIMIT 1 "
                );
                
                $parent = mysql_fetch_assoc($address_query);
                echo ucwords(strtolower($parent['admin_city'])) . ", " . strtoupper($parent['admin_state']);
            } else { // render the school location....
                echo ucwords(strtolower($student['school_city'])) . ", " . strtoupper($student['school_state']);
            } // end address if statement....
            ?>
        </div>
        
        <div class="team">
            Team:<br />
            <div class="teamName"><?=$student['team']?></div>
        </div>
        
        <div class="bunk">
            Bunk:<br />
            <?=$student['bunk_name']?>
        </div>
        
        <div class="grade">
            Grade: <?=$student['grade']?>
        </div>
        
        <div class="bottomSec grade<?=$student['grade']?> bottomText">
            <?php // TODO: show the xth year of the chidon that the child is in. ?>
        </div>
    </div>
    <div style="page-break-after: always"></div>
    <h2 class="no-print">BACK</h2>
    <div class="card back">
        <div class="topSec grade<?=$student['grade']?>">
            Contacts
        </div>
        
        <div class="middle">
            <div class="host">
                <?php // Host ?>
                <div class="title">Host</div>
                <?=$student['host']?> Family,<br/>
                <?=$student['host_address1'] . " " . $student['host_address2']?><br/>
                <small><?= $student['between_streets'] ?></small><br/>
                <?= $student['host_number'] ?><br />
                
                <?php // Chaperone ?>
                <?= $student['c_number'] ? "" : "<br/>"; // add some spacing if there is no number for the chaperone to make it look ok ?>
                <div class="title">Chaperone</div>
                
                <?php // load the first chaperone from the school and show him here....
                $chaperone_query = mysql_query(
                    "SELECT * FROM th_chidon_chaps WHERE school_id = " . $student['school_id'] . " AND year = '$year' LIMIT 1"
                );
                $chap = mysql_fetch_assoc($chaperone_query); ?>
                <?= $chap['name'];  ?><br />
                <?= $chap['phone']; ?><br />
                
                <div class="title">Counselor</div>
                <?=$student['counselor'];?><br />
                <?=$student['c_number'];?><br />
                
                <div class="title">Headquarters</div>
                718-907-8884<br/>
            </div>
            <div class="emerg">
                <div class="title">Emergency</div>
                Hatzola: 718-387-1750<br />
                Police / Fire: 911<br />
                Shmirah: 718-221-0303<br />
                <br />
                
                <div class="title inline">Walk Alone:</div>
                <?php if ( $student['walk_day'] && $student['walk_night']) { // if both are marked....
                    echo "Yes";
                } else if ( $student['walk_day'] ) { // only by day...
                    echo "Day Only";
                } else if ($student['walk_night']) { // only by night....
                    echo "Night Only";
                } else { // just plain old no...
                    echo "No";
                }?>
                <br />
                
                <div class="title inline">Walking Zone</div> #<?= $student['walking_zone'] ?><br />
                <?= $student['c_number'] ? "<br/>" : ""; // if there is a chap number then add some space to make it nicer... ?>
                
                <?php if($student['walking_zone'] != "65") { ?>
                
                    <div class="title">Walking Counselor</div>
                    <?php
                    $walking_bunk_query = mysql_query(
                        "SELECT * FROM th_chidon_bunks WHERE walking_zone = '" . $student['walking_zone'] . "' AND year = '$year' LIMIT 1"
                    );
                    $walking_bunk = mysql_fetch_assoc($walking_bunk_query);?>
                    <?=$walking_bunk['counselor'];  ?><br />
                    <?=$walking_bunk['c_number'];   ?><br />
                
                    <div class="title">Walking Chaperone</div>
                    <?php
                    $walking_chap_query = mysql_query(
                        "SELECT * FROM th_chidon_chaps WHERE walking_zone = '" . $student['walking_zone'] . "' AND year = '$year' LIMIT 1"
                    );
                    $walking_chap = mysql_fetch_assoc($walking_chap_query);?>
                    <?=$walking_chap['name'];?><br />
                    <?=$walking_chap['phone'];?><br />
                <?php } ?>
            </div>
        </div>
        <div style="clear: both"></div>
        <div class="info">
            <div class="bus">
                <div class="title">Bus Numbers</div>
                <div class="desc">
                    Thursday Bus    <b>#<?=$student['coach_bus']?></b><br />
                    Friday Bus      <b>#<?=$student['school_bus']?></b><br />
                    Sunday Bus      <b>#<?=$student['double_decker']?></b><br />
                </div>
            </div>
            
            <div class="other">
                <div class="title">Test Table</div>
                #<?= $student['test_table'] ?><br />
                
                <div class="title inline">Bowling Lane</div>
                <?php if ($student['grade'] < 6) echo "<br/>"; // grade 4 and 5 do not have a workshop, so add some space to be nice. ?>
                #<?= $student['bowling_lane'] ?>
                
                <?php
                if ($student['grade'] >= 6) { // 6 - 8 have workshops.... ?>
                    <div class="title inline">Workshop</div>
                    #<?= $student['workshop_number'] ?>
                <?php } ?>
            </div>
        </div>
        <div style="clear: both"></div>
        <img src="award-ceremony.png" />
    </div>
    <div style="page-break-after: always"></div>
    <hr class="no-print"/>
<?php } // endfunciton student_card ?>