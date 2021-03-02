<?
$admin_auth = array('school');
require('../../header.php');
require_once('./shared.php');
use Illuminate\Database\Capsule\Manager as Capsule;

$allowed_params = [ "prize_name", "quantity", "made_possible_by", "personalization", "color", "size", "note", "price", "our_price"];

$prize_params = array_filter($_POST, function($k) use($allowed_params) {
    return in_array($k, $allowed_params);
}, ARRAY_FILTER_USE_KEY);

switch($_FILES['prize_picture']) {
    case UPLOAD_ERR_INI_SIZE:
    case UPLOAD_ERR_FORM_SIZE:
    case UPLOAD_ERR_PARTIAL:
    case UPLOAD_ERR_NO_FILE:
        break;
    default: // if an image was uploaded succesfully save it
        $prize_params['prize_picture'] = save_image($_FILES['prize_picture'], "/storage/chidon_prizes");
    break;
}

$inserted = Capsule::table('chidon_prizes')->insert($prize_params);

if ($inserted) {
    http_response_code(302);
    header('Location: ./index.php');
} else {
    http_response_code(302);
    header('Location: ./new.php');
}