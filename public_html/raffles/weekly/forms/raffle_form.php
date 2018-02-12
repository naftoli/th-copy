<?
/***************** DEBUGGING SETTINGS **********************/
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
}

require_once(dirname(__FILE__).'/../../shared/functions.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/db.php');

$parshos = get_parshos();

?>
<input name="type" value="weekly" type="hidden"/>
<div class="input_group input_full">
    <label>Parsha:
        <select name="week_start" class="week_start" required>
            <?foreach($parshos as $parsha){ // fill the list with parshos
                $selected = $parsha['start'] == $raffle->start_date ? "selected" : "";
                echo "<option value=".$parsha['start']." $selected >".$parsha["year"] ." - ".$parsha["name"]."</option>"; 
            }?>
        </select>
    </label>
</div>

<div class="action-links">
    <input type="submit" value="<?=$action == "add" ? "Create" : "Save"?>"/>
    <a href="/raffles/shared/forms/raffle_form.php<?=$debug ? "?debug=true" : "";?>" class="button">Cancel</a>
</div>