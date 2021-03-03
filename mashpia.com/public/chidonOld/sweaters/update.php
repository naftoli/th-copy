<?

$admin_auth = array('school');
require('../../header.php');
require_once('./shared.php');

use Illuminate\Database\Capsule\Manager as Capsule;

$allowed_params = [ "sweater_name", "quantity", "size", "gender", "price", "our_price"];

$sweater_params = array_filter($_POST, function($k) use($allowed_params) {
    return in_array($k, $allowed_params);
}, ARRAY_FILTER_USE_KEY);

$id = isset($_POST['id']) ? $_POST['id'] : false;

$sweater_picture = Capsule::table('chidon_sweaters')->select("sweater_picture")->where('sweater_id', $id)->first()->sweater_picture;

switch($_FILES['sweater_picture']) {
    case UPLOAD_ERR_INI_SIZE:
    case UPLOAD_ERR_FORM_SIZE:
    case UPLOAD_ERR_PARTIAL:
    case UPLOAD_ERR_NO_FILE:
    break;
    default: // if an image was uploaded succesfully save it
        $sweater_params['sweater_picture'] = save_image($_FILES['sweater_picture'], "/storage/chidon_sweaters", $sweater_picture);
    break;
}

$updated = Capsule::table('chidon_sweaters')->where('sweater_id', $id)->update($sweater_params);

if ($updated) {
    http_response_code(302);
    header('Location: ./index.php');
} else {
    http_response_code(302);
    header('Location: ./edit.php?id='.$_POST['id']);
}