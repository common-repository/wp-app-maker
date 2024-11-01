<?php
if ( !defined('SITE_URL') )
	define( 'SITE_URL', get_bloginfo('url'));
if ( !defined('WP_CONTENT_URL') )
	define( 'WP_CONTENT_URL', SITE_URL . '/wp-content');
if ( ! defined( 'WP_PLUGIN_URL' ) )
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
define('WP2A_PLUGIN_URL', WP_PLUGIN_URL . '/wp-app-maker');
define('WPAM_PLUGIN_URL', WP_PLUGIN_URL . '/wp-app-maker');

function wpam_upload_dir( $pathdata ) {
	$subdir = '/wp-app-maker'.$pathdata['subdir'];
 	$pathdata['path'] = str_replace($pathdata['subdir'], $subdir, $pathdata['path']);
 	$pathdata['url'] = str_replace($pathdata['subdir'], $subdir, $pathdata['url']);
	$pathdata['subdir'] = str_replace($pathdata['subdir'], $subdir, $pathdata['subdir']);
	return $pathdata;
}
?>