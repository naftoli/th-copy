<?php
/*
Plugin Name: Chidon Calendar
Description: Calendar settings page for Chidon website
Author: Esty Rosenberg
Author URI: http://www.estyrosenberg.com
*/

add_action( 'init', 'chidon_calendar' );

function chidoncalendar_init() {
    wp_enqueue_style( 'calendar', plugin_dir_url( __FILE__ ).'styles/stylesheet.css');   
}
add_action('wp_enqueue_scripts','chidoncalendar_init');

function chidoncalendar_register_settings() {
    add_option('chidoncalendar_registration-en', 'June 13 - September 17, 2019');
    add_option('chidoncalendar_registration-hb', ' י”ג סיון - י”ז אלול תשע”ט ');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_registration-en');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_registration-hb');


    add_option('chidoncalendar_test1-en', 'June 13 - September 17, 2019');
    add_option('chidoncalendar_test1-hb', ' י”ג סיון - י”ז אלול תשע”ט ');
    add_option('chidoncalendar_test1-grade4', 'Units 1-16');
    add_option('chidoncalendar_test1-grade5', 'Units 46-62');
    add_option('chidoncalendar_test1-grade6', 'Units 100-117');
    add_option('chidoncalendar_test1-grade7', 'Units 157-173');
    add_option('chidoncalendar_test1-grade8', 'Units 209-225');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_test1-en');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_test1-hb');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_test1-grade4');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_test1-grade5');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_test1-grade6');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_test1-grade7');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_test1-grade8');

    add_option('chidoncalendar_test2-en', 'June 13 - September 17, 2019');
    add_option('chidoncalendar_test2-hb', ' י”ג סיון - י”ז אלול תשע”ט ');
    add_option('chidoncalendar_test2-grade4', 'Units 17-30');
    add_option('chidoncalendar_test2-grade5', 'Units 63-80');
    add_option('chidoncalendar_test2-grade6', 'Units 118-135');
    add_option('chidoncalendar_test2-grade7', 'Units 174-192');
    add_option('chidoncalendar_test2-grade8', 'Units 226-239');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_test2-en');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_test2-hb');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_test2-grade4');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_test2-grade5');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_test2-grade6');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_test2-grade7');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_test2-grade8');

    add_option('chidoncalendar_test3-en', 'June 13 - September 17, 2019');
    add_option('chidoncalendar_test3-hb', ' י”ג סיון - י”ז אלול תשע”ט ');
    add_option('chidoncalendar_test3-grade4', 'Units 31-45');
    add_option('chidoncalendar_test3-grade5', 'Units 81-99');
    add_option('chidoncalendar_test3-grade6', 'Units 136-156');
    add_option('chidoncalendar_test3-grade7', 'Units 193-208');
    add_option('chidoncalendar_test3-grade8', 'Units 240-257');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_test3-en');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_test3-hb');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_test3-grade4');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_test3-grade5');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_test3-grade6');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_test3-grade7');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_test3-grade8');

    add_option('chidoncalendar_shabbaton-girls-en', 'June 13 - September 17, 2019');
    add_option('chidoncalendar_shabbaton-girls-hb', ' י”ג סיון - י”ז אלול תשע”ט ');
    add_option('chidoncalendar_shabbaton-boys-en', 'June 13 - September 17, 2019');
    add_option('chidoncalendar_shabbaton-boys-hb', ' י”ג סיון - י”ז אלול תשע”ט ');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_shabbaton-girls-en');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_shabbaton-girls-hb');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_shabbaton-boys-en');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_shabbaton-boys-hb');

    add_option('chidoncalendar_ceremony-girls-en', 'June 13 - September 17, 2019');
    add_option('chidoncalendar_ceremony-girls-hb', ' י”ג סיון - י”ז אלול תשע”ט ');
    add_option('chidoncalendar_ceremony-boys-en', 'June 13 - September 17, 2019');
    add_option('chidoncalendar_ceremony-boys-hb', ' י”ג סיון - י”ז אלול תשע”ט ');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_ceremony-girls-en');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_ceremony-girls-hb');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_ceremony-boys-en');
    register_setting('chidoncalendar_options_group', 'chidoncalendar_ceremony-boys-hb');
}
add_action( 'admin_init', 'chidoncalendar_register_settings' );

function chidoncalendar_register_options_page() {
    add_options_page('Chidon Calendar Settings', 'Chidon Calendar', 'manage_options', 'chidon_calendar', 'chidoncalendar_options_page');
}
add_action('admin_menu', 'chidoncalendar_register_options_page');

function chidoncalendar_options_page() {
    ?>
<div>
    <?php screen_icon(); ?>
    <h2>Chidon Calendar Settings</h2>
    <form method="post" action="options.php">
        <?php settings_fields('chidoncalendar_options_group'); ?>
        <br>
        <h3>Registration Dates</h3>
        <table>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_registration-en">Registration Date - Secular</label></th>
                <td><input type="text" id="chidoncalendar_registration-en" name="chidoncalendar_registration-en" value="<?php echo get_option('chidoncalendar_registration-en'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_registration-hb">Registration Date - Hebrew</label></th>
                <td><input type="text" id="chidoncalendar_registration-hb" name="chidoncalendar_registration-hb" value="<?php echo get_option('chidoncalendar_registration-hb'); ?>" /></td>
            </tr>
        </table>
        <br>
        <h3>Test 1</h3>
        <table>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_test1-en">Date - Secular</label></th>
                <td><input type="text" id="chidoncalendar_test1-en" name="chidoncalendar_test1-en" value="<?php echo get_option('chidoncalendar_test1-en'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_test1-hb">Date - Hebrew</label></th>
                <td><input type="text" id="chidoncalendar_test1-hb" name="chidoncalendar_test1-hb" value="<?php echo get_option('chidoncalendar_test1-hb'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_test1-grade4">4th Grade</label></th>
                <td><input type="text" id="chidoncalendar_test1-grade4" name="chidoncalendar_test1-grade4" value="<?php echo get_option('chidoncalendar_test1-grade4'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_test1-grade5">5th Grade</label></th>
                <td><input type="text" id="chidoncalendar_test1-grade5" name="chidoncalendar_test1-grade5" value="<?php echo get_option('chidoncalendar_test1-grade5'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_test1-grade6">6th Grade</label></th>
                <td><input type="text" id="chidoncalendar_test1-grade6" name="chidoncalendar_test1-grade6" value="<?php echo get_option('chidoncalendar_test1-grade6'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_test1-grade7">7th Grade</label></th>
                <td><input type="text" id="chidoncalendar_test1-grade7" name="chidoncalendar_test1-grade7" value="<?php echo get_option('chidoncalendar_test1-grade7'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_test1-grade8">8th Grade</label></th>
                <td><input type="text" id="chidoncalendar_test1-grade8" name="chidoncalendar_test1-grade8" value="<?php echo get_option('chidoncalendar_test1-grade8'); ?>" /></td>
            </tr>
        </table>
        <br>
        <h3>Test 2</h3>
        <table>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_test2-en">Date - Secular</label></th>
                <td><input type="text" id="chidoncalendar_test2-en" name="chidoncalendar_test2-en" value="<?php echo get_option('chidoncalendar_test2-en'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_test2-hb">Date - Hebrew</label></th>
                <td><input type="text" id="chidoncalendar_test2-hb" name="chidoncalendar_test2-hb" value="<?php echo get_option('chidoncalendar_test2-hb'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_test2-grade4">4th Grade</label></th>
                <td><input type="text" id="chidoncalendar_test2-grade4" name="chidoncalendar_test2-grade4" value="<?php echo get_option('chidoncalendar_test2-grade4'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_test2-grade5">5th Grade</label></th>
                <td><input type="text" id="chidoncalendar_test2-grade5" name="chidoncalendar_test2-grade5" value="<?php echo get_option('chidoncalendar_test2-grade5'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_test2-grade6">6th Grade</label></th>
                <td><input type="text" id="chidoncalendar_test2-grade6" name="chidoncalendar_test2-grade6" value="<?php echo get_option('chidoncalendar_test2-grade6'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_test2-grade7">7th Grade</label></th>
                <td><input type="text" id="chidoncalendar_test2-grade7" name="chidoncalendar_test2-grade7" value="<?php echo get_option('chidoncalendar_test2-grade7'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_test2-grade8">8th Grade</label></th>
                <td><input type="text" id="chidoncalendar_test2-grade8" name="chidoncalendar_test2-grade8" value="<?php echo get_option('chidoncalendar_test2-grade8'); ?>" /></td>
            </tr>
        </table>
        <br>
        <h3>Test 3</h3>
        <table>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_test3-en">Date - Secular</label></th>
                <td><input type="text" id="chidoncalendar_test3-en" name="chidoncalendar_test3-en" value="<?php echo get_option('chidoncalendar_test3-en'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_test3-hb">Date - Hebrew</label></th>
                <td><input type="text" id="chidoncalendar_test3-hb" name="chidoncalendar_test3-hb" value="<?php echo get_option('chidoncalendar_test3-hb'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_test3-grade4">4th Grade</label></th>
                <td><input type="text" id="chidoncalendar_test3-grade4" name="chidoncalendar_test3-grade4" value="<?php echo get_option('chidoncalendar_test3-grade4'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_test3-grade5">5th Grade</label></th>
                <td><input type="text" id="chidoncalendar_test3-grade5" name="chidoncalendar_test3-grade5" value="<?php echo get_option('chidoncalendar_test3-grade5'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_test3-grade6">6th Grade</label></th>
                <td><input type="text" id="chidoncalendar_test3-grade6" name="chidoncalendar_test3-grade6" value="<?php echo get_option('chidoncalendar_test3-grade6'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_test3-grade7">7th Grade</label></th>
                <td><input type="text" id="chidoncalendar_test3-grade7" name="chidoncalendar_test3-grade7" value="<?php echo get_option('chidoncalendar_test3-grade7'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_test3-grade8">8th Grade</label></th>
                <td><input type="text" id="chidoncalendar_test3-grade8" name="chidoncalendar_test3-grade8" value="<?php echo get_option('chidoncalendar_test3-grade8'); ?>" /></td>
            </tr>
        </table>
        <br>
        <h3>Shabbaton</h3>
        <table>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_shabbaton-girls-en">Girls Date - Secular</label></th>
                <td><input type="text" id="chidoncalendar_shabbaton-girls-en" name="chidoncalendar_shabbaton-girls-en" value="<?php echo get_option('chidoncalendar_shabbaton-girls-en'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_shabbaton-girls-hb">Girls Date - Hebrew</label></th>
                <td><input type="text" id="chidoncalendar_shabbaton-girls-hb" name="chidoncalendar_shabbaton-girls-hb" value="<?php echo get_option('chidoncalendar_shabbaton-girls-hb'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_shabbaton-boys-en">Boys Date - Secular</label></th>
                <td><input type="text" id="chidoncalendar_shabbaton-boys-en" name="chidoncalendar_shabbaton-boys-en" value="<?php echo get_option('chidoncalendar_shabbaton-boys-en'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_shabbaton-boys-hb">Boys Date - Hebrew</label></th>
                <td><input type="text" id="chidoncalendar_shabbaton-boys-hb" name="chidoncalendar_shabbaton-boys-hb" value="<?php echo get_option('chidoncalendar_shabbaton-boys-hb'); ?>" /></td>
            </tr>
        </table>
        <br>
        <h3>Game Show & Award Ceremony</h3>
        <table>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_ceremony-girls-en">Girls Date - Secular</label></th>
                <td><input type="text" id="chidoncalendar_ceremony-girls-en" name="chidoncalendar_ceremony-girls-en" value="<?php echo get_option('chidoncalendar_ceremony-girls-en'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_ceremony-girls-hb">Girls Date - Hebrew</label></th>
                <td><input type="text" id="chidoncalendar_ceremony-girls-hb" name="chidoncalendar_ceremony-girls-hb" value="<?php echo get_option('chidoncalendar_ceremony-girls-hb'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_ceremony-boys-en">Boys Date - Secular</label></th>
                <td><input type="text" id="chidoncalendar_ceremony-boys-en" name="chidoncalendar_ceremony-boys-en" value="<?php echo get_option('chidoncalendar_ceremony-boys-en'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="chidoncalendar_ceremony-boys-hb">Boys Date - Hebrew</label></th>
                <td><input type="text" id="chidoncalendar_ceremony-boys-hb" name="chidoncalendar_ceremony-boys-hb" value="<?php echo get_option('chidoncalendar_ceremony-boys-hb'); ?>" /></td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>
    <?php
}



function chidon_calander_shortcodes( $atts ) { 
    $a = shortcode_atts( array(
        'type' => null
    ), $atts );

    $data = '';

    switch($a['type']) {
        case 'registration':
            $data = '<div class="link-box registration chidon-calendar">';
            $date_en = get_option('chidoncalendar_registration-en');
            $date_hb = get_option('chidoncalendar_registration-hb');
            $data .= '<div class="title"><h6><img src="'.plugin_dir_url( __FILE__ ).'images/registration.png">Registration</h6></div>';
            $data .= '<div class="info"><p class="date">'.$date_en.' &bull; <span class="hebrew">'.$date_hb.'</span></div>';
            break;
        case 'test1':
            $data = '<div class="link-box test chidon-calendar">';
            $date_en = get_option('chidoncalendar_test1-en');
            $date_hb = get_option('chidoncalendar_test1-hb');
            $data .= '<div class="title"><img src="'.plugin_dir_url( __FILE__ ).'images/test.png"><h6>Test 1</h6></div>';
            $data .= '<div class="info"><p class="date hebrew">'.$date_hb.'</p><p class="date">'.$date_en.'</p></div>';
            $data .= '<div class="units">';
            $data .= '<p><span class="grade">4th Grade</span>: '.get_option('chidoncalendar_test1-grade4').'</p>';
            $data .= '<p><span class="grade">5th Grade</span>: '.get_option('chidoncalendar_test1-grade5').'</p>';
            $data .= '<p><span class="grade">6th Grade</span>: '.get_option('chidoncalendar_test1-grade6').'</p>';
            $data .= '<p><span class="grade">7th Grade</span>: '.get_option('chidoncalendar_test1-grade7').'</p>';
            $data .= '<p><span class="grade">8th Grade</span>: '.get_option('chidoncalendar_test1-grade8').'</p>';
            $data .= '</div>';
            break;
        case 'test2':
            $data = '<div class="link-box chidon-calendar test">';
            $date_en = get_option('chidoncalendar_test2-en');
            $date_hb = get_option('chidoncalendar_test2-hb');
            $data .= '<div class="title"><img src="'.plugin_dir_url( __FILE__ ).'images/test.png"><h6>Test 2</h6></div>';
            $data .= '<div class="info"><p class="date hebrew">'.$date_hb.'</p><p class="date">'.$date_en.'</p></div>';
            $data .= '<div class="units">';
            $data .= '<p><span class="grade">4th Grade</span>: '.get_option('chidoncalendar_test2-grade4').'</p>';
            $data .= '<p><span class="grade">5th Grade</span>: '.get_option('chidoncalendar_test2-grade5').'</p>';
            $data .= '<p><span class="grade">6th Grade</span>: '.get_option('chidoncalendar_test2-grade6').'</p>';
            $data .= '<p><span class="grade">7th Grade</span>: '.get_option('chidoncalendar_test2-grade7').'</p>';
            $data .= '<p><span class="grade">8th Grade</span>: '.get_option('chidoncalendar_test2-grade8').'</p>';
            $data .= '</div>';
            break;
        case 'test3':
            $data = '<div class="link-box chidon-calendar test">';
            $date_en = get_option('chidoncalendar_test3-en');
            $date_hb = get_option('chidoncalendar_test3-hb');
            $data .= '<div class="title"><img src="'.plugin_dir_url( __FILE__ ).'images/test.png"><h6>Test 3</h6></div>';
            $data .= '<div class="info"><p class="date hebrew">'.$date_hb.'</p><p class="date">'.$date_en.'</p></div>';
            $data .= '<div class="units">';
            $data .= '<p><span class="grade">4th Grade</span>: '.get_option('chidoncalendar_test3-grade4').'</p>';
            $data .= '<p><span class="grade">5th Grade</span>: '.get_option('chidoncalendar_test3-grade5').'</p>';
            $data .= '<p><span class="grade">6th Grade</span>: '.get_option('chidoncalendar_test3-grade6').'</p>';
            $data .= '<p><span class="grade">7th Grade</span>: '.get_option('chidoncalendar_test3-grade7').'</p>';
            $data .= '<p><span class="grade">8th Grade</span>: '.get_option('chidoncalendar_test3-grade8').'</p>';
            $data .= '</div>';
            break;
        case 'shabbaton':
            $data = '<div class="link-box chidon-calendar shabbaton">';
            $data .= '<div class="title"><h6>Shabbaton</h6></div>';
            $data .= '<div class="info">';
            $data .= '<div class="girls"><p class="header secondary-red"><img src="'.plugin_dir_url( __FILE__ ).'images/girl.png" /> Girls</p><p class="date secondary-red">'.get_option('chidoncalendar_shabbaton-girls-en').' &bull; <span class="hebrew">'.get_option('chidoncalendar_shabbaton-girls-hb').'</span></p></div>';
            $data .= '<div class="boys"><p class="header secondary-green"><img src="'.plugin_dir_url( __FILE__ ).'images/boy.png" /> Boys</p><p class="secondary-green date">'.get_option('chidoncalendar_shabbaton-boys-en').' &bull; <span class="hebrew">'.get_option('chidoncalendar_shabbaton-boys-hb').'</span></p></div>';
            $data .= '</div>';
            break;
        case 'ceremony':
            $data = '<div class="link-box chidon-calendar ceremony">';
            $data .= '<div class="title"><h6>Game Show &amp; Award Ceremony</h6></div>';
            $data .= '<div class="info">';
            $data .= '<div class="girls"><p class="header secondary-red"><img src="'.plugin_dir_url( __FILE__ ).'images/girl.png" /> Girls</p><p class="date secondary-red">'.get_option('chidoncalendar_ceremony-girls-en').' &bull; <span class="hebrew">'.get_option('chidoncalendar_ceremony-girls-hb').'</span></p></div>';
            $data .= '<div class="boys"><p class="header secondary-green"><img src="'.plugin_dir_url( __FILE__ ).'images/boy.png" /> Boys</p><p class="secondary-green date">'.get_option('chidoncalendar_ceremony-boys-en').' &bull; <span class="hebrew">'.get_option('chidoncalendar_ceremony-boys-hb').'</span></p></div>';
            $data .= '</div>';
            break;
    }
    $data .= '</div>';
    return $data; 
 } 
add_shortcode('calendar','chidon_calander_shortcodes');
