<?
$admin_auth = array('school');
require('../../header.php');
require_once('./shared.php');
use Illuminate\Database\Capsule\Manager as Capsule;

$allowed_params = [ "sweater_name", "quantity", "size", "gender", "price", "our_price"];

$sweater_params = array_filter($_POST, function($k) use($allowed_params) {
    return in_array($k, $allowed_params);
}, ARRAY_FILTER_USE_KEY);

switch($_FILES['sweater_picture']) {
    case UPLOAD_ERR_INI_SIZE:
    case UPLOAD_ERR_FORM_SIZE:
    case UPLOAD_ERR_PARTIAL:
    case UPLOAD_ERR_NO_FILE:
        break;
    default: // if an image was uploaded succesfully save it
        $sweater_params['sweater_picture'] = save_image($_FILES['sweater_picture'], "/storage/chidon_sweaters");
        break;
}

$id = Capsule::table('chidon_sweaters')->insertGetId($sweater_params);

if ($id) {
    http_response_code(302);
    header("Location: ./edit.php?id=$id");
} else {
    http_response_code(302);
    header('Location: ./new.php');
}