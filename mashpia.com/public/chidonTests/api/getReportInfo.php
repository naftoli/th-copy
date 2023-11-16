<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';

$ct = new ChidonTests();
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$data = [];
foreach ($schools as $school_id => $school) {
    $info = $ct->getSettingsForReport($school_id);
    $settings = $info['settings'];
    $details = $info['details'];

    $types = ['school', 'class', 'user'];
    foreach ($types as $type) {
        if (isset($settings[$type])) {
            foreach ($settings[$type] as $id => $more) {
                $test_level = '';
                $final_level = '';

                if (isset($more['test_levels'])) {
                    foreach ($more['test_levels'] as $type => $level) {
                        if ($type == 'tests') $test_level = $level;
                        else if ($type == 'finals') $final_level = $level;
                    }
                }

                $grade = '';
                if ($type == 'class') {
                    $grade = $details['class'][$id]['class_grade'] . ($details['class'][$id]['class_sub'] ?
                            '-' . $details['class'][$id]['class_sub'] : '');
                }

                $data[] = [
                    'school' => $school,
                    'grade' => $grade,
                    'serial' => $type == 'user' ? $details['user'][$id]['user_serial'] : '',
                    'first_name' => $type == 'user' ? $details['user'][$id]['first'] : '',
                    'last_name' => $type == 'user' ? $details['user'][$id]['last'] : '',
                    'level' => $test_level,
                    'yesod' => isset($more['passing_avgs']['maven']) ? $more['passing_avgs']['maven'] : '',
                    'yediah' => isset($more['passing_avgs']['pro']) ? $more['passing_avgs']['pro'] : '',
                    'havanah' => isset($more['passing_avgs']['expert']) ? $more['passing_avgs']['expert'] : '',
                    'iyun' => isset($more['passing_avgs']['genius']) ? $more['passing_avgs']['genius'] : '',
                    'final_level' => $final_level,
                    'final_yesod' => isset($more['final_passing_avgs']['maven']) ? $more['final_passing_avgs']['maven'] : '',
                    'final_yediah' => isset($more['final_passing_avgs']['pro']) ? $more['final_passing_avgs']['pro'] : '',
                    'final_havanah' => isset($more['final_passing_avgs']['expert']) ? $more['final_passing_avgs']['expert'] : '',
                    'final_iyun' => isset($more['final_passing_avgs']['genius']) ? $more['final_passing_avgs']['genius'] : '',
                ];
            }
        }
    }
}
echo "<pre>";
print_r($data);
echo "</pre>";
exit;

echo json_encode($data);