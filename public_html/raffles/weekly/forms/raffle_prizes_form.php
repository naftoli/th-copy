<?require_once(dirname(__FILE__).'/../../shared/classes/Prize.php');
use raffles\weekly\Prize as Prize;
?>
<? if ($raffle->date_ran) { // if the raffle already ran
    $raffle->get_prizes(); // get the prizes that this raffle has'
    ?>
    <h3>Raffle has already run. <br/><br/>Prizes awarded:<br/><br/></h3>
    
    <table>
        <thead>
            <th>Name</th><th>Created On</th><th>Thunmbnail</th><th>Quantity</th><th>Actions</th>
        </thead>
        <tbody>
            <? foreach ($raffle->prizes as $prize) { ?>
                <tr>
                    <td><?=$prize->name;?></td>
                    <td><?=$prize->date_created->format('m/d/Y');?></td>
                    <td><img src="<?=$prize->thumbnail;?>"/></td>
                    <td><?=$prize->qty?></td>
                    <td>
                        <a href="/raffles/weekly/forms/prize_form.php?action=edit&prize_id=<?=$prize->prize_id?>">
                            View/Edit
                        </a>
                    </td>
                </tr>
            <? } ?>
        </tbody>
    </table>
    
<?} else { // the raffle did not run... ?>
    <table>
        <thead>
            <th>Name</th><th>Created On</th><th>Thunmbnail</th><th>Included</th><th>Quantity</th>
        </thead>
        
        <tbody>
        <?
        $prizes = Prize::loadAll("WHERE type_of_prize='".$raffle->type."'"); // get all the prizes for the list
        $raffle->get_prizes(); // get the prizes that this raffle has
        // now render the rows
        foreach($prizes as $prize){ // iterate through each prize?>
            <tr>
                <td><?=$prize->name;?></td>
                <td><?=$prize->date_created->format('m/d/Y');?></td>
                <td><img src="<?=$prize->thumbnail;?>"/></td>
                <? if ($raffle->type == "weekly"){ // only show the checkbox for weekly raffles ?>
                    <td>
                        <input type="checkbox" <?=$prize->type_of_prize == "weekly" ? "" : "disabled";?>
                            id="prize_<?=$prize->prize_id?>" <?= $raffle->prizes[$prize->prize_id] ? "checked ": ""; ?>
                        />
                    </td> 
                <?}?>
                <td>
                    <input type="number" disabled id="qty-prize_<?=$prize->prize_id?>"
                        value="<?=$raffle->prizes[$prize->prize_id]->qty ? $raffle->prizes[$prize->prize_id]->qty : 0;?>"
                    />
                </td>
            </tr>
        <?}?>
    </tbody>
</table>
<?}?>