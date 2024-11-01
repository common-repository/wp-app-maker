<?php 
$wp_root = dirname(__FILE__) .'/../../../';
if(file_exists($wp_root . 'wp-load.php')) {
	require_once($wp_root . "wp-load.php");
} else if(file_exists($wp_root . 'wp-config.php')) {
	require_once($wp_root . "wp-config.php");
} else {
	exit;
}
require_once(dirname(__FILE__) .'/common.php');
require_once( ABSPATH . 'wp-admin/includes/file.php' );

if ( !is_user_logged_in() )die(); 

global $wpdb, $wpam;
$wpam_options = get_option('wpam-options');

if ( isset($_POST['action']) && $_POST['action'] == 'restore' ) {
	//ajax reset request
	$url = WPAM_PLUGIN_URL.'/images/splash.png';
	$wpam->update_option('splash_screen_url',$url);
	//$wpam->update_option('splash_screen_file', $dest_file);
	die();
}else if ( isset($_POST['save']) ) {

	if ( empty($errors ) ) {
		$time = current_time('mysql');
		$overrides = array('test_form'=>false);
			
		add_filter('upload_dir', 'wpam_upload_dir');
		
		$file = wp_handle_upload($_FILES['wpam_upload'], $overrides, $time);
		
		remove_filter('upload_dir', 'wpam_upload_dir');
		
		if ( !isset($file['error']) ) {
			$filename = $file['file'];
		} 
		else $errors = '<div class="error">'.$file['error'].'</div>';
		

		if ( empty($errors ) ) {
			$image_extensions_allowed = array('jpg', 'jpeg', 'png');
			$ext = strtolower(substr(strrchr($filename, "."), 1));
			if(!in_array($ext, $image_extensions_allowed)){
				$exts = implode(', ',$image_extensions_allowed);
				$errors .= '<div class="error">You must upload a file with one of the following extensions: '.$exts.'</div>';
			}
		} 

		if ( !empty($errors ) ) {
			// No File Was uploaded
			if ( empty( $_POST['wpam_filename'] ) && !isset($_FILES['wpam_upload']) ) $errors = '<div class="error">'.__('No file selected',"wp-app-maker").'</div>';
			elseif (!empty($_POST['wpam_filename'])) $errors = '';
		}

		if ( empty($errors ) ) {
			
			$upload_dir = wpam_upload_dir(wp_upload_dir());
			$dest_file = $upload_dir['basedir'].'/wp-app-maker/splash.png';
			@unlink($dest_file);

			$ext = strtolower(substr(strrchr($filename, "."), 1));
			if ($ext == 'jpg' || $ext == 'jpeg'){
				$im = @imagecreatefromjpeg($filename);	
				imagepng($im,$dest_file,1);
			}else {
				copy($filename, $dest_file);
			}

			$url = $upload_dir['baseurl'].'/wp-app-maker/splash.png';
			$wpam->update_option('splash_screen_url',$url);
			//$wpam->update_option('splash_screen_file', $dest_file);

				
		}else echo $errors;
	}
}
?>
<html>
<head>
<style>
html {
    background-color: #F9F9F9;
}
.form-table th, .form-wrap label {
    color: #222222;
    text-shadow: 0 1px 0 #FFFFFF;
}
.form-table th {
    text-align: left;
}

.form-table th, .form-wrap label {
    font-weight: normal;
    text-shadow: 0 1px 0 #FFFFFF;
}

.form-table td {
    font-size: 11px;
    line-height: 20px;
    margin-bottom: 9px;
    padding: 8px 10px;
}
body, td, th, textarea, input, select {
    font-family: sans-serif,"Lucida Grande",Verdana,Arial,"Bitstream Vera Sans";
    font-size: 12px;
}
h3 {
    display: block;
    font-size: 1.17em;
    font-weight: bold;
    margin: 1.5em 0;
}
</style>
</head>
<body>

<?php 
if ( empty($errors ) && isset($_POST['save']) ) {
	echo '<div id="message" class="updated fade"><p>'.
	__('New Splash Screen successfully saved: it will be updated after rebooting the app twice.',"wp-app-maker").
	'</p></div>';
}
?>
<div class="wrap">
<h3><?php _e('Add New Splash Screen',"wp-app-maker"); ?></h3>
		<form enctype="multipart/form-data" action="<?php echo WP2A_PLUGIN_URL.'/upload.php'?>" method="post" class="form-table"> 
            <table class="optiontable niceblue" cellpadding="0" cellspacing="0"> 
				<tr valign="top">
						<th scope="row">
							<?php _e('Select an image...',"wp-app-maker"); ?> <small><?php _e('(suggested size 480px x 800px)',"wp-app-maker"); ?></small>
						</th> 
						<td>
							<div style="width:310px;">
								<div style="float:left;">
									<h3 style="margin:0 0 0.5em"><?php _e('Upload File',"wp-app-maker"); ?></h3>
									<input type="file" name="wpam_upload" style="width:200px; margin:1px;" />
								</div>
								<div style="clear:both"></div>
							</div>
						</td>
                </tr> 
				</tbody>
			</table>
			<hr />

            <p class="submit"><input type="submit" class="btn button-primary" name="save" style="padding:5px 30px 5px 30px;" value="<?php _e('Upload &amp; save',"wp-app-maker"); ?>" /></p>
			<input type="hidden" name="postDate" value="<?php echo date_i18n(__('Y-m-d H:i:s',"wp-app-maker")) ;?>" />
									
		</form>
	</div>

</div>
</body>
</html>
