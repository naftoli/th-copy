<pre>
<?
$raffle->get_prizes();
$raffle->get_winner_info(false, false);

$prize_winners = [];
foreach($raffle->winner_info as $winner){
    $prize_winners[$winner['prize_id']][$winner['school_id']][] = $winner;
}

require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

$prizes = [];
$prizes_query = mysql_query( 'SELECT prize_id, prize_name FROM prizes_auction WHERE archived = 0 ORDER BY prize_name' );
while( $prize = mysql_fetch_assoc( $prizes_query ) ) $prizes[] = $prize; 

?>
</pre>
<table>
    <thead>
        <th>Prize #</th><th>School</th><th>Prize ID</th><th>Prize Name</th>
        <? if($raffle->date_ran) {?><th>Winner</th><?} // only show the winner if the raffle ran...?>
    </thead>
    
    <tbody>
        <? foreach ($raffle->prizes as $index => $prize) { ?>
            <?php
            $j = $prize['num_prizes'];
            for ($k = 0; $k < $j; $k++) { ?>
              <tr>
                  <td><?=$index + 1?></td>
                  <td><?=$prize['school_name']?></td>
                  <td><?=$prize['prize_id']?></td>
                  <td><?=$prize['prize_name']?></td>
                  <? if( $raffle->date_ran ) {
                      if (isset($prize_winners[$prize['prize_id']][$prize['school_id']][$k]['name'])) {
                          $winner = $prize_winners[$prize['prize_id']][$prize['school_id']][$k]['name'];
                          ?>
                          <td> <?=$winner?> </td>
                      <? } else { ?>
                          <td>
                              <strong>No Eligible Winners.<br/>Please Set Manually:</strong><br/>
                              <input class='manual_winner_serial' type='text' placeholder='Serial Number' />
                              <button class='manual_winner' data-prize_id='<?=$prize['prize_id']?>'
                                  data-raffle_id='<?=$raffle->raffle_id?>'
                                  data-school_id='<?=$prize['school_id']?>'>
                                  Save
                              </button>
                          </td>
                      <? } ?>
                  <? } // only show the winner if the raffle ran...?>
              </tr>
        <? } } ?>
        <?php if ( !$raffle->date_ran ) { ?>
            <tr>
            <td colspan='4' style="text-align: center; font-size: 1.5em; padding-top: 15px;"><h3>Add Prize</h3></td>
            </tr>
            <tr>
                <td></td>
                <td style='max-width: 200px'>
                    School: 
                    <select id="school_id" name="school_id" style='width: 100%'>
                        <? foreach($schools as $school_id => $school_name){?>
                            <option value="<?=$school_id?>"><?=$school_name?></option>
                        <?}?>
                    </select>
                </td>
                <td style='width: 150px'>
                    Prize: 
                    <select id="prize_id" name="prize_id" style='width: 100%'>
                        <? foreach($prizes as $prize){?>
                            <option value="<?=$prize['prize_id']?>"><?=$prize['prize_name']?></option>
                        <?}?>
                    </select>
                </td>
                <td>
                    <a class='button' id='add-prize'>Add Prize</a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<script>
    $(document).ready(function(){
        $("button.manual_winner").click(function(event){
            var data= Object.assign({}, event.target.dataset);
            data.serial_number = $(event.target).parent().find("input.manual_winner_serial").val();
            
            if (!data.serial_number) {
                alert("Please enter a serial number"); return false;
            }
            
            $.post("/raffles/monthly/ajax/set_winner.php", data, function(data){
                data = JSON.parse(data);
                if (!data.success) {
                    alert("Error: " + data.error);
                } else {
                    $(event.target).parent().html(data.name)
                }
            });
        });
        $('#add-prize').click( function( event ){
            var postData = {
                school_id: $('#school_id').val(),
                prize_id: $('#prize_id').val(),
                raffle_id: <?= $raffle->raffle_id ?>,
            }
            if ( confirm('Are you sure you want to add this prize?') ) {
                $.post( "/raffles/monthly/ajax/add_prize.php", postData, function( response ){
                    location.reload();
                });
            }
        });
    });
</script>

