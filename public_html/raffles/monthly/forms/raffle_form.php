<?
/***************** DEBUGGING SETTINGS **********************/
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
}

require_once(dirname(__FILE__).'/../../shared/functions.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/db.php');

$parshos = get_parshos( $raffle->year );

?>
<input name="type" value="monthly" type="hidden"/>

<div class="input_group input_half">
    <label>From Parsha:
        <select name="start_date" required>
            <?foreach($parshos as $parsha){ // fill the list with parshos
                $selected = $parsha['start'] == $raffle->start_date ? "selected" : "";
                echo "<option value=".$parsha['start']." $selected >".$parsha["year"] ." - ".$parsha["name"]."</option>"; 
            }?>
        </select>
    </label>
</div>

<div class="input_group input_half">
    <label>To Parsha:
        <select name="end_date" required>
            <?foreach($parshos as $parsha){ // fill the list with parshos
                $selected = $parsha['end'] == $raffle->end_date ? "selected" : "";
                echo "<option value=".$parsha['end']." $selected >".$parsha["year"] ." - ".$parsha["name"]."</option>"; 
            }?>
        </select>
    </label>
</div>

<div class="action-links">
    <input type="submit" value="<?=$action == "add" ? "Create" : "Save"?>"/>
    <a href="/raffles/shared/forms/raffle_form.php<?=$debug ? "?debug=true" : "";?>" class="button">Cancel</a>
</div>