<?php
/*
 * Plugin Name: Truncate RSS
 * Plugin URI: http://github.com/BryanH/Truncate-RSS
 * Description: Truncate/(Dis)Allow Comments/(Dis)Allow editing permalink for blog posts that come from RSS feeds.
 * Version: 0.90
 * Author: HBJitney, LLC
 * Author URI: http://hbjitney.com/
 * License: GPL3
 * License: GPLv3

 Copyright 2010 Houston Chronicle, Inc.

  Truncate RSS is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  Truncate RSS is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
define('META_LENGTH', 'trss_length');
define('META_KEYWORDS', 'trss_meta_keywords');
define('META_COMMENTS', 'trss_comments');
define('META_KEYWORDS', 'trss_meta_keywords');
define('META_EDIT_PERMALINK', 'trss_edit_permalink');
define('DEFAULT_META_LENGTH', '75');
define('DEFAULT_META_COMMENTS', '0');
define('DEFAULT_META_KEYWORDS', 'syndication_permalink');
define('DEFAULT_EDIT_PERMALINK', '0');
/* get ready for any scripts/styles */
add_action('wp_print_styles', 'enqueue_my_styles');
function enqueue_my_styles() {
	list ($css_file, $css_url) = get_css_location('trss-admin.css');
	wp_enqueue_style('tradmin-css', $css_url);
//		wp_die(__('SNN jIM!'));
}

/* Option menu - set truncation */
add_action('admin_menu', 'trss_plugin_menu');
function trss_plugin_menu() {
	add_options_page(__('Truncate RSS Options', 'menu-trss'), __('Truncate RSS', 'menu-trss'), 'manage_options', 'truncatersssettings', 'trss_options');
}
/*
 * Retrieves the value of the key from a form's values
 * Updates the datastore with that key:value
 * Parameter: 		$key - form and datastore key (must be identical)
 * Returns: 		value passed so the intermediate variable can be updated
 */
function trss_update_from_post($key) {
	//	new WP_Error($this, __("key: [" . $key . "]"));
	if (true == empty ($key)) {
		wp_die(__('Invalid key passed to trss_update_from_post'));
	}
	update_option($key, $_POST[$key]);
	return $_POST[$key];
}
/*
 * Retrieves value from datastore. If nothing returned,
 * then it returns the default
 * (equivalent to $foo = $bar || $default)
 * Parameters:	$key - datastore key
 *				$default - default value
 * Returns: either datastore's value, or default if former is empty
 */
function trss_get_value_or_default($key, $default) {
	$the_data = get_option($key);
	if (true == empty ($the_data)) {
		$the_data = $default;
	}
	return $the_data;
}
/*
 * Option screen
 */
function trss_options() {
	if (!current_user_can('manage_options')) {
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}
	/* variables for the field and option names */
	$opt_max_words = trss_get_value_or_default(META_LENGTH, DEFAULT_META_LENGTH);
	$opt_allow_comments = trss_get_value_or_default(META_COMMENTS, DEFAULT_META_COMMENTS);
	$opt_meta_words = trss_get_value_or_default(META_KEYWORDS, DEFAULT_META_KEYWORDS);
	$opt_edit_permalink = trss_get_value_or_default(META_EDIT_PERMALINK, DEFAULT_META_KEYWORDS);
	$hidden_field_name = 'mt_submit_hidden';
	//$data_max_words = META_LENGTH;
	if (isset ($_POST[$hidden_field_name]) && 'Y' == $_POST[$hidden_field_name]) {
		check_admin_referer( 'truncate_rss-admin_settings' );
		$opt_max_words = trss_update_from_post(META_LENGTH);
		$opt_allow_comments = trss_update_from_post(META_COMMENTS);
		$opt_meta_words = trss_update_from_post(META_KEYWORDS);
		$opt_edit_permalink = trss_update_from_post(META_EDIT_PERMALINK);
?>
<div class="updated"><p><strong><?php _e('settings saved', 'menu-trss' ); ?></strong></p></div>
<?php

	}
?>
<div class="wrap">
<h2><?php _e( "'Truncate RSS' Settings", 'menu_trss' ); ?></h2>
<p><?php

	_e("Instructions: Pick the number of words you wish to limit RSS posts on index pages. The entry itself will have the full post, unless you've configured your importer to link back to the original source.");
	echo ' ';
	_e("The Metatag field is used to match to the metadata in RSS posts. You probably don't need to change the default setting.");
?></p><div class="input-aligned"> <form name="form1" method="post" action="#">
<div class="spacer">&nbsp;</div><input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y" /><?php
if( function_exists( 'wp_nonce_field' ) ) {
  wp_nonce_field('truncate_rss-admin_settings' );
}
?>
	<div class="input-line"><span class="prompt"><?php _e("Metatag to filter on:", 'menu-trss'); ?></span>
<span class="input"><input type="text" name="<?php echo META_KEYWORDS; ?>" value="<?php echo $opt_meta_words; ?>" /></span>
</div>
<div class="input-line"><span class="prompt"><?php _e("Maximum number of words to display:", 'menu-trss' ); ?></span>
<span class="input"><input type="text" name="<?php echo META_LENGTH; ?>" value="<?php echo $opt_max_words; ?>" size="3" maxlength="2" /></span>
</div>
<div class="input-line"><span class="prompt"><?php _e("Allow permalink editing:", 'menu-trss' ); ?></span>
<span class="input"><input type="checkbox" name="<?php echo META_EDIT_PERMALINK; ?>"<? if( false == empty($opt_edit_permalink) ) { ?> checked="checked"<?php } ?> /></span>
</div>
<div class="input-line"><span class="prompt"><?php _e("Allow comments:", 'menu-trss' ); ?></span>
<span class="input"><input type="checkbox" name="<?php echo META_COMMENTS; ?>"<? if( false == empty($opt_allow_comments) ) { ?> checked="checked"<?php } ?> /></span>
</div>
<div class="input-line"><span class="prompt">&nbsp;</span>
<span class="input"><input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" /></span>
</div>
<div class="spacer">&nbsp;</div>
</form>
</div>
<?php

}
/*
 * Obtains the url and file location of a given CSS
 * Parameter:	css filename (assumes it lives in the 'stylesheets' directory under the plugin)
 * Output: 		file, url to stylesheet.
 * Use:			list($cssfile, $cssurl) = get_css_location('somecss.css');
 */
function get_css_location($css) {
	$admin_css = '/' . str_replace(basename(__FILE__), "", plugin_basename(__FILE__)) . 'stylesheets/' . $css;
	$css_location = array (
		WP_PLUGIN_DIR . $admin_css,
		WP_PLUGIN_URL . $admin_css
	);
	return $css_location;
}
/*add_action('admin_head', 'trss_format_options');
function trss_format_options() {
	if (function_exists('wp_enqueue_style')) {
		list ($css_file, $css_url) = get_css_location('trss-admin.css');
		if (file_exists($css_file)) {
			// TODO: figure out why register/enque style doesn't work; it is poor
			//       programming to use a direct insert.
			//
			//			wp_register_style('tradmin-css', $css_url, array (
			//				'global'
			//			), "", "all");
			//			wp_enqueue_style('tradmin-css');
			print '<!-- ' . __('Truncate RSS', 'trss_format_options') . "-->\n";
			print '<link type="text/css" rel="stylesheet" href="' . $css_url . '"' . " />\n";
		} else {
			wp_die(__("Can't find '" . $css_file . "'"));
		}
		// Hide permalink edit
		print "<!-- *****************\n";
		print "option value: [" . get_option(META_EDIT_PERMALINK) . "]\n";
		print "allow permalink? [" . ('on' == get_option(META_EDIT_PERMALINK)) . "]\n";
		print "-->\n";
		$can_edit = get_option(META_EDIT_PERMALINK);
		if (false == empty ($can_edit)) {
			list ($css_file, $css_url) = get_css_location('trss-admin-permalink-edit.css');
			if (file_exists($css_file)) {
				// TODO: figure out why register/enque style doesn't work; it is poor
				//       programming to use a direct insert.
				echo '<!-- ' . __('Truncate RSS', 'trss_format_options') . "-->\n";
				echo '<link type="text/css" rel="stylesheet" href="' . $css_url . '"' . " />\n";
			} else {
				wp_die(__("Can't find '" . $css_file . "'"));
			}
		}
	} else {
		wp_die(__("Can't enqueue stylesheets"));
	}
}
*/
add_filter("comments_open", 'trss_disable_comments');
add_filter("pings_open", 'trss_disable_comments');
/*
 * Disable comments on matching posts, if option is set
 */
function trss_disable_comments($open) {
	$opt_allow_comments = get_option(META_COMMENTS);
	if (true == $opt_allow_comments) {
		return $open;
	} else {
		$opt_meta_words = get_option(META_KEYWORDS);
		if (true == empty ($opt_meta_words)) { // exception
			Throw new Exception("RSS Truncation values are not set in the 'Settings' menu on the admin screen.");
		}
		$is_syndication_post = (get_post_meta(get_the_ID(), $opt_meta_words, true)) ? true : false;
		if ($is_syndication_post) {
			return false;
		} else {
			return $open;
		}
	}
}
/*
 * from http://beerpla.net/2010/01/13/wordpress-plugin-development-how-to-include-css-and-javascript-conditionally-and-only-when-needed-by-the-posts/
 * This allows full access to the posts before the head is rendered
  */
//add_filter('the_posts', 'conditionally_add_scripts_and_styles');
//function conditionally_add_scripts_and_styles($posts) {
//	if (empty ($posts))
//		return $posts;
//	$shortcode_found = false; // use this flag to see if styles and scripts need to be enqueued
//	foreach ($posts as $post) {
//		if (stripos($post->post_content, '[code]')) {
//			$shortcode_found = true; // bingo!
//			break;
//		}
//	}
//	if ($shortcode_found) {
//		// enqueue here
//		wp_enqueue_style('my-style', '/style.css');
//		wp_enqueue_script('my-script', '/script.js');
//	}
//	return $posts;
//}
//add_filter('get_comment', 'trss_handle_comments');
/*
 * Apply comment-hiding stylesheet if:
 * a) We're on a single sheet, and
 * b) The option to hide has been set
 */
/*function trss_handle_comments($comment) {
	if (is_single(get_the_ID())) {
		$opt_meta_words = get_option(META_KEYWORDS);
		if (true == empty ($opt_meta_words)) { // exception
			Throw new Exception("RSS Truncation values are not set in the 'Settings' menu on the admin screen.");
		}
		$is_syndication_post = (get_post_meta(get_the_ID(), $opt_meta_words, true)) ? true : false;
		if ($is_syndication_post) {
			$comment = '';
		} else {  leave it as is
		}
	}
	return $comment;
}*/
add_filter('the_content', 'trss_truncate');
/*
 * Truncate the post's comment if the meta matches the META_KEYWORD(s) and
 * it is not a single post. Otherwise pass it through unchanged..
 */
function trss_truncate($content) {
	if (!is_single(get_the_ID())) {
		$opt_meta_words = get_option(META_KEYWORDS);
		if (true == empty ($opt_meta_words)) { // exception
			Throw new Exception("RSS Truncation values are not set in the 'Settings' menu on the admin screen.");
		}
		$is_syndication_post = (get_post_meta(get_the_ID(), $opt_meta_words, true)) ? true : false;
		if ($is_syndication_post) {
			$opt_max_words = get_option(META_LENGTH);
			if (true == empty ($opt_max_words)) { // exception
				Throw new Exception("RSS Truncation values are not set in the 'Settings' menu on the admin screen.");
			}
			$content = x_words_from_post($opt_max_words, $content, true) . ' <a href="' . get_page_link(get_the_ID()) . '">More <span>&raquo;</span></a>';
		} else { /* leave it as is */
		}
	}
	return $content;
}
add_action('admin_head', 'trss_hide_slug');
/*
 * Hide the slug edit box.
 */
function trss_hide_slug() {
	wp_enqueue_style('trss-admin', plugins_url('stylesheets', __FILE__), false, '1.0', 'all');
	//	echo "\n<!-- " . __('Truncate RSS', 'trss_truncate') . " -->\n<style type='text/css'>\n/*<![CDATA[*/\n";
	//	echo "\n/*]]>*/\n</style>\n";
}
/*********************************************************************************
 * From Alan's template code
 */
function shorten_string($string, $wordsreturned) {
	$retval = $string; //Just in case of a problem
	$array = explode(" ", $string);
	if (count($array) <= $wordsreturned) { /*  Already short enough, return the whole thing*/
		$retval = $string;
	} else { /*  Need to chop of some words*/
		array_splice($array, $wordsreturned);
		$retval = implode(" ", $array);
		$retval = $retval . '...';
	}
	return $retval;
}
function x_words_from_post($num, $content = '', $already_filtered = false) { /*global post;*/
	if (!$content)
		$content = get_the_content('');
	if (!$already_filtered)
		$content = apply_filters('the_content', $content);
	$content = str_replace(']]>', ']]&gt;', $content);
	$content = str_replace('&lt;![CDATA[', '', $content); // get rid of bogus CDATA on imported posts?
	$content = str_replace(']]&gt;', '', $content); // get rid of bogus CDATA on imported posts?
	$content = str_replace('&apos;', '&#39;', $content); // IE doesn't understand &apos
	$content = preg_replace('#<p class="wp-caption-text">(.+)</p>#i', '', $content); // remove photo captions
	/*$content = preg_replace('#<script(.+)</script>#i', '', $content);*/ // make sure no scripts are in there
	/*$content = preg_replace("#<style(.+)</style>#i", '', $content);*/ // make sure no styles are in there
	//$content = preg_replace('#[(.+)]#i', '', $content); // remove wordpress smart tags (and some babies)
	$content = preg_replace('/<(style|script).*?<\/\1>/xmsi', '', $content); // removes both script and style
	$content = strip_tags($content, '');
	$content = shorten_string($content, $num); //echo $content;
	return $content;
}
?>
