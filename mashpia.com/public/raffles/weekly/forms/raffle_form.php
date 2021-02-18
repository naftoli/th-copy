<?
/***************** DEBUGGING SETTINGS **********************/
if ($_GET['debug']) {
    //error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
}

require_once(dirname(__FILE__).'/../../shared/functions.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/db.php');

$parshos = get_parshos( $raffle->year );

?>

<input name="type" value="weekly" type="hidden"/>
<div class="input_group input_full">
    <label>Parsha:
        <select id="week" name="week" class="week" required>
            <?foreach($parshos as $parsha){ // fill the list with parshos
                $selected = $parsha['start'] == $raffle->start_date ? "selected" : "";
                echo "<option value="
                .formatJdToDate($parsha['start']).",".formatJdToDate($parsha['start'] + 6)
                ." $selected >".$parsha["year"] ." - ".$parsha["name"]."</option>"; 
            }?>
        </select>
    </label>
</div>

<div class="input_group input_half">
    Or From Date: <input id="start_date" type="date" name="start_date" value="<?=$raffle->start_date ? formatJdToDate($raffle->start_date) : ""; ?>">
</div>
<div class="input_group input_half">
    To Date: <input id="end_date" type="date" name="end_date" value="<?=$raffle->end_date ? formatJdToDate($raffle->end_date) : ""; ?>">
</div>
<div class="input_group input_half">
    Run Date* <input type="date" name="run_date" value="<?=$raffle->run_date ? $raffle->run_date->format("Y-m-d") : ""; ?>">
</div>

<div class="action-links">
    <input type="submit" value="<?=$action == "add" ? "Create" : "Save"?>"/>
    <a href="/raffles/shared/forms/raffle_form.php<?=$debug ? "?debug=true" : "";?>" class="button">Cancel</a>
</div>