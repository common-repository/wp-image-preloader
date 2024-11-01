<?php
/**
 * @package WP_Image_Preloader
 * @version 1.0
 * @author Adrian Silimon ( http://adrian.silimon.eu/ )
 */
/*
Plugin Name: WP Image Preloader
Plugin URI: http://adrian.silimon.eu/wp-image-preloader/
Description: This plugin preloads images using Javascript. It uses an image preloader for jQuery (thanks to Matt Farina, http://www.mattfarina.com/). You can use it to preload hover images for menus, slideshows etc. 
Author: Adrian &#350;ilimon
Version: 1.0
Author URI: http://adrian.silimon.eu/
License: GPL2

	Copyright 2010  Adrian Silimon  (email: adrian.silimon@yahoo.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//the default shortcode used by plugin
define('WP_IMG_PRELOADER_SHORTCODE'		  , "[preload=IMAGE_URL]");

//shortcode regular expresstion to match in posts contents
define('WP_IMG_PRELOADER_SHORTCODE_REGEXP', "/\[preload=(.*)\]/");

//wp-image-preloader plugin textdomain
define('WP_IMG_PRELOADER_TEXTDOMAIN'	  , "wp-img-preloader");


//images to preload strack
$WPIMGP_Queue = array();

function wpimgpreloader_activate(){
	
	if ( NULL == get_option( 'wpimgpreloader_installed', NULL ) ){ //not installed		
		add_option('wpimgpreloader_installed'			, 'yes', FALSE, 'no');
		add_option('wpimgpreloader_queue'				, '', FALSE, 'no');
		add_option('wpimgpreloader_shortcode_activate'	, 'no', FALSE, 'no');
	}
		
}

function wpimgpreloader_deactivate(){
			
	delete_option('wpimgpreloader_installed');
	delete_option('wpimgpreloader_queue');
	delete_option('wpimgpreloader_shortcode_activate');
		
}

function wpimgpreloader_plugin_url () {
	
	$_relative = dirname(__FILE__);
	$_relative = explode(DIRECTORY_SEPARATOR, $_relative);
	$_relative = $_relative[count($_relative) -1];
	
	return WP_PLUGIN_URL . '/' . $_relative; 
}

function wpimgpreloader_config () {
	
	$plugin_url = wpimgpreloader_plugin_url ();
	
	if (count($_POST)){
		$wp_img_queue 			   = trim($_POST['queue']); 
		$wp_img_activate_shortcode = $_POST['shortcode_activate'] == 'yes' ? 'yes' : 'no'; 
		
		$updated1 = update_option('wpimgpreloader_queue'			 , $wp_img_queue);
		$updated2 = update_option('wpimgpreloader_shortcode_activate', $wp_img_activate_shortcode);
		
		$msg = '<div class="updated" style="background-color: lightYellow;"><p><strong>Options updated.</strong></p></div>';
	}
	
	$sh_active = array();
	$sh_state  = get_option('wpimgpreloader_shortcode_activate' , 'no');
	
	$sh_active[$sh_state] = 'checked="checked"';
	$queue	   			  = get_option('wpimgpreloader_queue', "");
	
	echo '<div class="wrap">
			<div id="icon-wpimgpreloader" style="background:url(\''.$plugin_url.'/icons/wp-image-preloader.png\') no-repeat left top" class="icon32"><br /></div>
			<h2>WP Image Preloader Options</h2>
	
			<div id="re-wp-wrap">
				' . $msg . '
				<form method="post" target="_self">
					<table class="form-table">
						<tr valign="top">
							<th scope="row"><label for="last_rotated">' . __("Activate shortcode", WP_IMG_PRELOADER_TEXTDOMAIN) . ':</label></th>
							<td>
								<input type="radio" name="shortcode_activate" id="shortcode_activate" value="yes" ' . $sh_active['yes'] . ' /> Yes
								&nbsp;&nbsp;&nbsp;
								<input type="radio" name="shortcode_activate" id="shortcode_activate" value="no" ' . $sh_active['no'] . ' /> No
								<br/>
								' . __("* Check Yes if you want to use shortcodes in your posts or pages to enqueue images to preload. Check no to deactivate.", WP_IMG_PRELOADER_TEXTDOMAIN) . '
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="shortcode">' . __("Shortcode", WP_IMG_PRELOADER_TEXTDOMAIN) . ':</label></th>
							<td>
								<strong>' . WP_IMG_PRELOADER_SHORTCODE . '</strong><br/>
								' . __("* Example:", WP_IMG_PRELOADER_TEXTDOMAIN) . ' [preload=http://domain.tld/bigimage.png]
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="queue">' . __("Default Stack", WP_IMG_PRELOADER_TEXTDOMAIN) . ':</label></th>
							<td>
								<textarea name="queue" id="queue" cols="80" rows="10">' . $queue . '</textarea><br/>
								' . __("Put here a default list of images to preload. One image url per line. These will be preloaded on every page of your blog.", WP_IMG_PRELOADER_TEXTDOMAIN) . '
								<br/>
								' . __("You can use absolute or relative urls to ", WP_IMG_PRELOADER_TEXTDOMAIN) . get_bloginfo('url') . '
							</td>
						</tr>
					</table>
					<p class="submit" align="center">
						<input type="submit" name="Submit" class="button-primary" value="' . __("Save Options", WP_IMG_PRELOADER_TEXTDOMAIN) . '">
					</p>
					
					<p align="center">
						Stuck? Find out more: 
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<a href="http://adrian.silimon.eu/wp-image-preloader/#respond" target="_blank">Ask for help.</a>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<a href="http://adrian.silimon.eu/wp-image-preloader/#how-to-use" target="_blank">How to use.</a>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<a href="http://adrian.silimon.eu/wp-image-preloader/#documentation" target="_blank">Plugin documentation.</a>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					</p>
				</form>
			</div>
		</div>			
	';
}

/**
 * Transforms about any url to an absolute one
 * @param string $url
 */
function wpimgpreloader_parse_url($url){
	
	//sanitize url
	$url = trim($url);
	$url = trim($url, "\n");
	$url = trim($url, "\r");
	$url = trim($url, "\t");
	
	if (strpos($url, 'http') === FALSE){ //does not starts with http
		$url = trim($url, "/");
		$url = (get_bloginfo('url') . "/" . $url);
	}
	
	return $url;
}

function wpimgpreloader_build_default_stack(){
	global $WPIMGP_Queue;
	
	$default_stack = get_option('wpimgpreloader_queue', NULL);
	$stack 		   = @explode("\n", $default_stack);
	
	
	if (count($stack)) foreach ($stack as $url){
		if (strlen($url) > 0) $WPIMGP_Queue[] =  wpimgpreloader_parse_url($url);
	}
}

function wpimgpreloader_parse_stack_from_content($content=""){
	global $post;
	global $WPIMGP_Queue;
	
	$urls  = array();
	
	if (empty($content))
		$content = $post->contents;
		
	$matches = array();
	@preg_match_all(WP_IMG_PRELOADER_SHORTCODE_REGEXP, $content, $matches);
		
	$matches = $matches[0]; 
		
	if (count($matches)) foreach ($matches as $m){
		//now let's find the url
		$url = str_ireplace('[', '', $m);
		$url = str_ireplace(']', '', $url);
		$url = str_ireplace('preload=', '', $url); 
			
		$url= trim($url);
		$url = wpimgpreloader_parse_url($url);
			
		$WPIMGP_Queue[] = $url;
		
		//replace the match in the content
		$content = str_replace($m, '', $content);
	}
	
	return $content;
}

function wpimgpreloader_js_string_from_array($array, $derrive=false, $value=false){
		
	if (count($array)){
		$string = "";
		
		if($derrive){
			foreach ($array as $values) {
				if (strlen($string) > 0){
					$string.= (",\"" . stripslashes($values[$value]) . "\"" );
				}
				else {
					$string.=("\"" . stripslashes($values[$value]) . "\"");
				}
			}
		}
		else{
			foreach ($array as $val) {
				if (strlen($string) > 0){
					$string.= (",\"" . stripslashes($val) . "\"" );
				}
				else {
					$string.=("\"" . stripslashes($val) . "\"");
				}
			}
		}
	}
		
	return $string;
}

/**
 * Enqueue a single image or an array of images to preload
 * @param mixed $images
 */
function wpimgpreloader_enqueue($images=NULL){
	global $WPIMGP_Queue;
	
	$queue = array();
	
	if (is_array($images)) foreach ($images as $url)
		$queue[] = wpimgpreloader_parse_url($url);
	
	$WPIMGP_Queue = array_merge($WPIMGP_Queue, $queue);
}

/**
 * Echoes the javascript code for image preloading
 */
function wpimgpreloader_preload_js(){
	global $WPIMGP_Queue;
	
	$js_imagesList = wpimgpreloader_js_string_from_array($WPIMGP_Queue);
	
	$script = '<script type="text/javascript">jQuery.preLoadImages(' . $js_imagesList . ');</script>';
	
	echo $script;
}


function wpimgpreloader_init(){
	//---- queue plugin js --------------//
	
	$plugin_url = wpimgpreloader_plugin_url();
	
	if (!is_admin()){
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery.imagepreloader', $plugin_url . '/js/jquery.imagepreloader.js');
	}
}

function wpimgpreloader_admin_menu(){
	$plugin_url = wpimgpreloader_plugin_url ();
	
	add_submenu_page( 'plugins.php', 'WP Image Preloader', 'WP Image Preloader', 'edit_posts', 'wp-image-preloader.php', 'wpimgpreloader_config' );
}

if ( is_admin() ) {
	add_action( 'admin_notices', 'wpimgpreloader_activate' );
	add_action( 'admin_menu'   , 'wpimgpreloader_admin_menu' );
	
	register_deactivation_hook( __FILE__, 'wpimgpreloader_deactivate' );
}
else{
	add_action('init', 'wpimgpreloader_init');
	
	add_action('wp_head', 'wpimgpreloader_build_default_stack');
	
	if ('yes' == get_option('wpimgpreloader_shortcode_activate' , 'no'))
		add_filter('the_content', 'wpimgpreloader_parse_stack_from_content', 99, 1);
	
	add_action('wp_footer', 'wpimgpreloader_preload_js');
}

?>