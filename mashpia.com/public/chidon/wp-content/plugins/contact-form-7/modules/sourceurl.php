<?php 
wpcf7_add_shortcode('sourceurl', 'wpcf7_sourceurl_shortcode_handler', true);

function wpcf7_sourceurl_shortcode_handler($tag) {
	if (!is_array($tag)) return '';

	$name = $tag['name'];
	if (empty($name)) return '';

	$html = '<input type="text" name="' . $name . '" value="http://' . $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"] . '" />';
	return $html;
}
