<?php
/**
 * This file contains basic WP Image Preloader functions, you can include it into your theme functions.php file.
 * @package WP_Image_Preloader
 * @version 1.0
 * @author Adrian Silimon ( http://adrian.silimon.eu/ )
 * 
 */

//images to preload queue
$WPIMGP_Queue = array();

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
 * Echoes/Returns the javascript code for image preloading
 * @param bool $echo [optional] default TRUE
 */
function wpimgpreloader_preload_js($echo=TRUE){
	global $WPIMGP_Queue;
	
	$js_imagesList = wpimgpreloader_js_string_from_array($WPIMGP_Queue);
	
	$script = '<script type="text/javascript">jQuery.preLoadImages(' . $js_imagesList . ');</script>';
	
	if ($echo) echo   $script;
	else 	   return $script;
}

?>