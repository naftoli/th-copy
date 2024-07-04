<?
/***************** DEBUGGING SETTINGS **********************/
if (!isset($debug)) { $debug = false; }
if ($_GET['debug']) {
    //error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
}

require_once(dirname(__FILE__).'/../../shared/functions.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/db.php');

require_once( __DIR__ . '/../../shared/classes/Constants.php' );
use raffles\shared\Constants as Constants;

$parshos = get_parshos( $raffle->year );

?>

<input name="type" value="weekly" type="hidden"/>
<div class="input_group input_full">
    <label>Parsha:
        <select id="week" name="week" class="week" required>
            <?foreach($parshos as $parsha){ // fill the list with parshos
                $selected = $parsha['start'] == $raffle->start_date ? "selected" : "";
                if (! $selected) {
                    // decide by current date
                    $current_jd = unixtojd(time());
                    if ($current_jd >= $parsha['start'] && $current_jd <= $parsha['end']) {
                        $selected = "selected";
                    }
                }
                echo "<option value="
                .formatJdToDate($parsha['start']).",".formatJdToDate($parsha['start'] + 6)
                ." $selected >".$parsha["year"] ." - ".$parsha["name"]."</option>"; 
            }?>
        </select>
    </label>
</div>

<div class="input_group input_half">
    or from date: <input id="start_date" type="date" name="start_date" value="<?=$raffle->start_date ? formatJdToDate($raffle->start_date) : ""; ?>">
</div>
<div class="input_group input_half">
    to date: <input id="end_date" type="date" name="end_date" value="<?=$raffle->end_date ? formatJdToDate($raffle->end_date) : ""; ?>">
</div>
<div class="input_group input_half">
    Run date* <input type="date" name="run_date" id="run_date" value="<?=$raffle->run_date ? $raffle->run_date->format("Y-m-d") : ""; ?>">
</div>

<div class="input_group input_half">
    Required days of tasks <input type="number" name="days_of_tasks" value="<?=$raffle->days_of_tasks ?? Constants::get_weekly_task_requirment(); ?>">
</div>

<div class="input_group input_half">
  Date Ran: <?=$raffle->date_ran ? $raffle->date_ran->format("Y-m-d") : "Not Yet";?>
</div>

<div class="input_group input_half">
  <input type="checkbox" name="hq" id="show_hq" value="1" <?=$raffle->show_for_hq ? "checked" : "";?>> Show for HQ Reports<br />
  <input type="checkbox" name="bc" id="show_bc" value="1" <?=$raffle->show_for_bc ? "checked" : "";?>> Show for BC<br />
  <input type="checkbox" name="kids" id="show_kids" value="1" <?=$raffle->show_for_kids ? "checked" : "";?>> Show on app/mobile site<br />
</div>

<div class="action-links">
    <input type="submit" value="<?=$action == "add" ? "Create" : "Save"?>" style="padding: 5px;"/>
    <a href="/raffles/shared/forms/raffle_form.php<?=$debug ? "?debug=true" : "";?>" class="button">Cancel</a>
</div>

<script>
  if ($("#raffle_type").val() == 'weekly') {
    $("#show_bc").attr("checked", true)
    $("#show_kids").attr("checked", true)
  }
</script>