<pre>
<?
$raffle->get_prizes();
$raffle->get_winner_info(false, false);

$prize_winners = [];
foreach($raffle->winner_info as $winner){
    $prize_winners[$winner['prize_id']][$winner['school_id']] = $winner;
}

?>
</pre>
<table>
    <thead>
        <th>Prize #</th><th>School</th><th>Prize ID</th><th>Prize Name</th>
        <? if($raffle->date_ran) {?><th>Winner</th><?} // only show the winner if the raffle ran...?>
    </thead>
    
    <tbody>
        <? foreach ($raffle->prizes as $index => $prize) {
            if($raffle->date_ran) { // if the raffle ran...
                $winner = $prize_winners[$prize['prize_id']][$prize['school_id']]['name'];
                if(!$winner){ // if there is no winner create an input field to set one...
                    $winner = "<strong>No Eligible Winners.<br/>Please Set Manually:".
                    "</strong><br/><input class='manual_winner_serial' type='text' placeholder='Serial Number' />".
                    "<button class='manual_winner' data-prize_id='".$prize['prize_id']."' ".
                        "data-raffle_id='".$raffle->raffle_id."' ".
                        "data-school_id='".$prize['school_id']."' ".
                        "'>Save</button>";
                };
            }; // end if raffle ran?>
        <tr>
            <td><?=$index + 1?></td>
            <td><?=$prize['school_name']?></td>
            <td><?=$prize['prize_id']?></td>
            <td><?=$prize['prize_name']?></td>
            <? if($raffle->date_ran) {?><td><?=$winner?></td><?} // only show the winner if the raffle ran...?>
        </tr>
        <? } ?>
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
    });
</script>

