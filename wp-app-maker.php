<?php 
/*
Plugin Name: WP App Maker
Plugin Script: wp-app-maker.php
Plugin URI: http://wpappmaker.com/
Description: WP App Maker will easily make you able to generate a native Android app for your blog with a lot of advanced features like offline navigation, full text search, categories and colors customization!
Version: 1.0.16.4
Author: WP App Maker
Author URI: http://wpappmaker.com

*/

/*
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
Online: http://www.gnu.org/licenses/gpl.txt
*/
require_once(dirname(__FILE__) .'/wpplugin.php');
require_once(dirname(__FILE__) .'/common.php');

define ('WPAM_SERVICES_URL', 'http://services.wpappmaker.com');
define ('WPAM_PLIMUS_URL', 'https://www.plimus.com/jsp/buynow.jsp?contractId=3054062');
define ('WPAM_VERSION','1.0.16.4');

class WPAppMaker extends WpPlugin {
	
	protected $app_status, $show_news;
	
	function __construct() {
		$categories=get_categories(); 
		$default_terms_ids = array();
		foreach ($categories as $category)
			$default_terms_ids[] = $category->term_id;

		$default_options = array(
			'level' => 'FREE',
			'is_registered' => false,
			'title_color' => '#BE1576',
			'text_color' => '#4A4A4A',
			'bg_color' => '#FFFFFF',
			'header_bg_color' => '#000000',
			'header_text_color' => '#FFFFFF',
			'header_search_color' => '#E8E8E8',
			'header_search_text_color' => '#000000',
			'action'=>'',
			'app_name_hidden'=>'',
			'adwhirl_uid'=>'',
			'analytics_uid'=>'',
			'splash_screen_url'=> WPAM_PLUGIN_URL.'/images/splash.png' ,
			'categories_enabled'=>$default_terms_ids,
			'categories_name'=>array(),
			'category_featured'=> -1,
			'version'=>WPAM_VERSION
	
		);
		

		parent::__construct('wpam', $default_options, $config_tables, 'wp-app-maker', 'WP App Maker', WPAM_VERSION, __FILE__);

		if(defined('WP_ADMIN')) {
			add_action('admin_init', array(&$this, 'admin_init') ); 
			add_action('admin_menu', array(&$this, 'create_top_level_admin_page'));
		}

		add_action( 'init', array(&$this, 'init') );
		add_filter('query_vars', array(&$this, 'add_query_vars'));
		add_action('parse_request', array(&$this, 'parse_request'));
		
	}
	
	function install(){
		parent::install();
		parent::update_option('version', WPAM_VERSION);
		$options = parent::get_options();
		if (isset($options['secret_key'])){
			$data = array('uid'=>$options['secret_key'],'act'=>'Y','ver'=>WPAM_VERSION);
			$res = self::do_http_get(WPAM_SERVICES_URL.'/notify.php', $data);
		}
		
	}
	
	function uninstall(){
		parent::uninstall();
		$options = parent::get_options();
		$data = array('uid'=>$options['secret_key'],'act'=>'N','ver'=>WPAM_VERSION);
		$res = self::do_http_get(WPAM_SERVICES_URL.'/notify.php', $data);
	}

	function add_query_vars($public_query_vars) {
		$public_query_vars[] = $this->namespace.'_p';
		$public_query_vars[] = $this->namespace.'_w';
		$public_query_vars[] = $this->namespace.'_d';
		return $public_query_vars;
	}

	function parse_request($wp) {
		global $wp_version, $post;
		$options = parent::get_options();
	    if (array_key_exists($this->namespace.'_p', $wp->query_vars)) {
			//add_shortcode( 'nggallery', array(&$this, 'show_gallery') );
			
			$width = 450;
			if (isset($wp->query_vars[$this->namespace.'_w'])){
				$width = $wp->query_vars[$this->namespace.'_w'];
			}
			$GLOBALS['wpam_width'] = $width;

			$app_name = trim($options['app_name']);
			
			//prevent empty app name
			if (strlen($app_name) == 0) 
				$app_name = get_bloginfo();
				
			///////////////////////////////////
			// The Loop
			echo "<datasource>";
			echo "<blogtitle><![CDATA[";
			echo $app_name;
			echo "]]></blogtitle>\n";
			echo "<locale>".get_locale()."</locale>\n";
			echo "<version>".WPAM_VERSION."</version>\n";
			echo "<date>".date('Y-m-d H:i:s', time())."</date>\n";
			echo "<headerbgcolor>".$options['header_bg_color']."</headerbgcolor>\n";
			echo "<headertxcolor>".$options['header_text_color']."</headertxcolor>\n";
			echo "<headersearchcolor>".$options['header_search_color']."</headersearchcolor>\n";
			echo "<headersearchtextcolor>".$options['header_search_text_color']."</headersearchtextcolor>\n";
			echo "<titlecolor>".$options['title_color']."</titlecolor>\n";
			echo "<textcolor>".$options['text_color']."</textcolor>\n";
			echo "<bgcolor>".$options['bg_color']."</bgcolor>\n";
			echo "<analyticsuid>".$options['analytics_uid']."</analyticsuid>\n";
			echo "<adwhirluid>".$options['adwhirl_uid']."</adwhirluid>\n";
			echo "<splashurl>".$options['splash_screen_url']."</splashurl>\n";
			
			$category_featured = $options['category_featured'];
			
			echo "<featureditems>\n";
			if ($category_featured > 0){
				$args = array(
					'post_type' => 'post',
					'post_status' => 'publish',
					'category' => $category_featured,
					'numberposts' => 3
				);
				$featured_posts = get_posts($args);
				foreach( $featured_posts as $post ) {	
					setup_postdata($post);
					//$wpam_query = new WP_Query( $args );
					//while ( $wpam_query->have_posts() ) : $wpam_query->the_post();
					if ( version_compare( $wp_version, '2.9', '>=' ) ) {
						$featured_image_url = $this->get_featured_image_url(get_the_ID());
                    } else {
                        $featured_image_url = get_post_meta(get_the_ID(), 'app-maker-featured', true);
               		}
					if ($featured_image_url == '') continue;
					$wpam_featured_post_ids[] = get_the_ID();
					echo "<featureditem>\n";
					echo "<fi-id>";
					echo get_the_ID();
					echo "</fi-id>";
					echo "<fi-text><![CDATA[";
					the_title();
					echo "]]></fi-text>\n";
					echo "<fi-image><![CDATA[";
					echo $featured_image_url;
					echo "]]></fi-image>";
					echo "<fi-pubdate>";
			  		echo mysql2date( 'D, d M Y H:i:s +0000', get_post_time( 'Y-m-d H:i:s', true ), false ); 
			  		echo "</fi-pubdate>\n";
					echo "</featureditem>\n";
				}
				//wp_reset_query();
			}			
			echo "</featureditems>\n";
			
			
			$categories = $options['categories_enabled'];
			$categories_names = $options['categories_name'];
			
			echo "<categories>\n";
			foreach ($categories as $key=>$cat_id){
				$cat_name = $categories_names[$key];
				if (strlen($cat_name) == 0)
					$cat_name = get_cat_name($cat_id);
				echo "<category>".$cat_id."=".$cat_name ."</category>\n";
			}
			echo "</categories>\n";
			
			echo "<items>\n";
			global $more;    // Declare global $more (before the loop).
			
			//custom tags not compatible with the mobile content
			$tags_to_strip = array('[ecards]');
			
			add_filter( 'posts_where', array(&$this, 'ds_filter_where') );
			$args = array(
				'post_type' => 'post',
				'post_status' => 'publish',
				'category__in' => $categories,
			);
			if ($wp->query_vars[$this->namespace.'_p'] == 1){
				$args['posts_per_page'] = 10;
				$args['offset'] = 0;
			}else{
				$args['posts_per_page'] = 40;
				$args['offset'] = 10;
			}
			
			$posts = get_posts($args);
			
			//featured posts always downloaded
			if (is_array($featured_posts))
				$posts = array_merge($posts, $featured_posts);
				
			foreach( $posts as $post ) {	
				setup_postdata($post);
				$more = 1; 
				$content = get_the_content();
				$images = $this->get_images_for_gallery($content);
				//strip incompatible shotcodes
				foreach ($tags_to_strip as $tag_to_strip)      
					$content = str_replace($tag_to_strip, '', $content);
				//apply filters 
				$content = apply_filters('the_content',  $content);
				//strip unallowed tags
				$content = $this->ds_filter($width, $content);
				echo "<item>\n";
				echo "<id>";
				echo get_the_ID();
				echo "</id>";
				echo "<url>";
				echo get_permalink( get_the_ID() );
				echo "</url>";
				echo "<pubdate>";
			  	echo mysql2date( 'D, d M Y H:i:s +0000', get_post_time( 'Y-m-d H:i:s', true ), false ); 
			  	echo "</pubdate>\n";
				echo "<thumb><![CDATA[";
				//echo WPAM_PLUGIN_URL . '/timthumb.php?src=' . $this->ds_get_thumb($content) . '&w=100';
				echo WPAM_PLUGIN_URL . '/timthumb.php?src=' . $this->ds_get_thumb($content) . '&w=100';
				echo "]]></thumb>\n";
				echo "<category-ids>,";
				$categories = get_the_category(get_the_ID());
				
				foreach ($categories as $category)
					echo $category->term_id.",";
				
				echo "</category-ids>\n";
				echo "<title><![CDATA[";
				the_title();
				echo "]]></title>\n";
				echo "<excerpt><![CDATA[";
				$ex = strip_tags($content);
				$ex = substr($ex,0,strrpos(substr($ex,0,100),' '));
				echo $ex.'...'; 
				echo "]]></excerpt>\n";
				echo "<text><![CDATA["; 
				echo '<style>pre {overflow: auto;width: 100%;} p {text-align:justify;line-height: 115%;}  .ngg-gallery-thumbnail {float: left;margin-right: 5px;text-align: center;}.ngg-galleryoverview {clear: both;display: block !important;margin-top: 10px;overflow: hidden;width: 100%;}</style>';
				echo $this->fix_images($content);
				echo "]]></text>\n";
	    		echo "<imgs><![CDATA[";

	    		
				foreach ($images as $image){
					echo WPAM_PLUGIN_URL."/timthumb.php?w=".$GLOBALS['wpam_width']."&src=".urlencode($image).",";					
				}
			    
	    		echo "]]></imgs>\n";
			    echo "</item>\n";
			}
			echo "</items>";
			echo "</datasource>";

			die();
	    }
	}
	
	function get_featured_image_url($post_id){
		$url = '';
		if (has_post_thumbnail( $post_id ) ){
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'single-post-thumbnail' ); 
			$url = WPAM_PLUGIN_URL . '/timthumb.php?w='.$GLOBALS['wpam_width'].'&src=' . urlencode($image[0]);
		}
		return $url;				
	}
	
	function get_images_for_gallery($content){
		$images = array();

		$args = array(
		   'post_type' => 'attachment',
		   'numberposts' => -1,
		   'post_status' => null,
		   'post_parent' => get_the_ID()
		);
		
		$attachments = get_posts( $args );
		if ( $attachments ) {
	        foreach ( $attachments as $attachment ) {
				$image_attributes = wp_get_attachment_image_src( $attachment->ID, 'full' );
	           $images[] = $image_attributes[0];
	        }
	    }
	    
	    //get nggallery images
	    global $nggdb;
	    if ($nggdb){
	    	$pattern = get_shortcode_regex();
	    	if (preg_match_all( '/'. $pattern .'/s', $content, $matches) && 
	    			array_key_exists( 2, $matches ) && in_array( 'nggallery', $matches[2] )){
	    		//print_r($matches);
	    		$p = 0;
				foreach($matches[2] as $sc_name){
					if ($sc_name == 'nggallery'){
						$gallery_id = trim(preg_replace('/id=([0-9]*)/','\\1',$matches[3][$p]));
						if (is_numeric($gallery_id)){
					    	$gallery = $nggdb->get_gallery($gallery_id, 'sortorder', 'ASC', true, 0, 0);
					    	foreach($gallery as $elem) {
					    		$images[] = $elem->imageURL;
					    	}
						}
					}
					$p++;
				}	    				
	    	}
	    }
	    return $images;		
	}
	
	function clean_tags($content){
		$tags_to_remove = array('form','script','fb:like','fb:send','style','g:plusone');
		
		foreach($tags_to_remove as $tag_to_remove){
			$expr = '/(<'.$tag_to_remove.'[^>]*>.*?<\/'.$tag_to_remove.'>)/ims'; 
			$content = preg_replace($expr, '', $content); 	
		}
		return $content;
	}
	
	function ds_filter($w, $content){
		$content = $this->clean_tags($content);
		
		//$content = preg_replace('/width="[^"]+"/i', 'width="'.$GLOBALS['wpam_width'].'"', $content);
		//$content = strip_tags($content, '<textarea><input><small><script><style><em><code><pre><p><b><img><iframe><a><strong><b><h1><h2><h3><h4><br><div><ul><li><ol>');
		
		$content = strip_tags($content, '<textarea><input><small><em><code><pre><p><b><img><iframe><a><strong><b><h1><h2><h3><h4><br><div><ul><li><ol>');
		$content = preg_replace('@(["\']{1})//@','\\1http://',$content);
		return $content;
	}
	
	function fix_images($content){
		//nextgen gallery clean: this could be done better 
		//$content = preg_replace('@<img([^>]*)src="([^"]+)/thumbs/thumbs_([^"]+)"([^>]*)>@Uims','<img\\1src="\\2/\\3"\\4>',$content);
		
		//wp gallery images
		//$content = preg_replace('@<img([^>]*)src="([^"]+)\-[0-9]+x[0-9]+(\.[a-zA-Z]{3})"([^>]*)>@Uims','<img\\1src="\\2\\3"\\4>',$content);
		
		$content = preg_replace_callback('@<img([^>]*)src="([^"]+)"([^>]*)>@Uims', 
			create_function(
	            '$matches',
	            'if (false !== stripos($matches[2], SITE_URL)) return "<img src=\"".WPAM_PLUGIN_URL . "/timthumb.php?w=".$GLOBALS[\'wpam_width\']."&src=".urlencode($matches[2])."\">"; else return $matches[0];'
	        ),$content );
		$content = preg_replace_callback('@<a([^>]*)href="([^"]+\.(jpg|png))"([^>]*)>@Uims', 
			create_function(
	            '$matches',
	            'if (false !== stripos($matches[2], SITE_URL)) return "<a href=\"".WPAM_PLUGIN_URL . "/timthumb.php?w=".$GLOBALS[\'wpam_width\']."&src=".urlencode($matches[2])."\">"; else return $matches[0];'
	        ),$content );
	        return $content;
	}
	
	function ds_get_thumb($content){
		$matches = array();
		preg_match('@<img[^>]*src="('.SITE_URL.'[^"]+)"[^>]*>@Uims', $content, $matches);
		if (count($matches) > 0)
			$src = urlencode($matches[1]);
		else
			$src = urlencode(WPAM_PLUGIN_URL . "/images/stub.png");
		return $src;
	}
	
	function ds_filter_where( $where='' ) {
		
		
		if (isset($_GET[$this->namespace.'_d'])){
			$date = $_GET[$this->namespace.'_d'];
			$where .= " AND (post_date >= '" . $date . "' OR post_modified >= '" . $date . "')";
		}
		return $where;
	}

	function init(){
		//self::debug('init');
		parent::init();
	}

	function admin_init(){
		if (strpos($_GET['page'], 'wp-app-maker/') === 0) {
			self::debug('admin_init:');
			$options = parent::get_options();
			self::update_server($options);
				//let's retrieve the services status
			$data = array('uid'=>$options['secret_key']);
			$status = self::do_http_get(WPAM_SERVICES_URL.'/service_status.php', $data);
			list ($this->app_status,$this->news_show) = explode('|',$status);
			if ($options['is_registered'] && ($this->app_status == 'NR' || $this->app_status == 'FI') ){
				//inconsistency detected: reset client status
				$options['is_registered'] = false;
				parent::update_option('is_registered', false);
			}			
		}
	}
	
	function add_admin_head_code(){
		$options = parent::get_options();
		
		$upload_dir = wpam_upload_dir(wp_upload_dir());
		$url_splash = $upload_dir['baseurl'].'/wp-app-maker/splash.png';
	?>
	<script>
		jQuery(document).ready(function() {
				jQuery('#edit-profile-section').hide();
				initCP();

				/////////////////////////


				jQuery("#splash_screen_upload").fancybox({
				'width'				: 450,
				'height'			: 250,
				'autoScale'			: false,
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'type'				: 'iframe',
				'onClosed':function(){
					jQuery('#splash_screen_img').attr('src','<?php echo $url_splash;?>'+'?'+d.getTime());
					jQuery('#splash_screen_img_hidden').attr('value','<?php echo $url_splash;?>');
				}
				});

				jQuery("#splash_screen_restore").click(function(){
					jQuery.post('<?php echo parent::get_plugin_url()?>/upload.php', {type: 'ajax', action: 'restore'}, function(data) {
  						jQuery('#splash_screen_img').attr('src','<?php echo WPAM_PLUGIN_URL.'/images/splash.png'?>');
  						jQuery('#splash_screen_img_hidden').attr('value','<?php echo WPAM_PLUGIN_URL.'/images/splash.png'?>');
					});
				});

				jQuery("input[rel='save-options']").click(function(){
               		jQuery("input[name='<?php echo $this->options_name?>[action]']").val('save-options');
					jQuery('#submit-form').submit(); 
        		});

				jQuery("input[rel='save-profile']").click(function(){
					if (jQuery("#terms_check").is(':checked')){
	                	jQuery("input[name='<?php echo $this->options_name?>[action]']").val('save-profile');
						jQuery('#submit-form').submit(); 
					}else{
						alert('You must read and accept the terms of service before registering.');
					}
            	});

				jQuery("input[rel='update-profile']").click(function(){
                	jQuery("input[name='<?php echo $this->options_name?>[action]']").val('update-profile');
					jQuery('#submit-form').submit(); 
            	});

				jQuery("a[rel='show-edit-profile']").click(function(){
					jQuery('#show-profile-section').hide();
					jQuery('#edit-profile-section').show();
            	});

				jQuery("a[rel='hide-edit-profile']").click(function(){
					jQuery('#show-profile-section').show();
					jQuery('#edit-profile-section').hide();
            	});

				
				jQuery('#app_status_ko').hide();
				jQuery('#app_status_ok').hide();

				var html_value = '';
				ajax_poll = function () {
					if ('<?php echo $options['secret_key'] ?>'.length == 0){
			    		jQuery('#pro_upgrade').hide();
			    		html_value = '<p><center><img width="100" src="<?php echo parent::get_plugin_url()?>/images/maintenance.png"/><br><strong>App not yet available.</strong><p>Just register and configure your options in order to instantly get your app!</p></center></p>';
						if (html_value != jQuery('#app_status_ko').html())
					    	jQuery('#app_status_ko').html(html_value);
				    	jQuery('#app_status_ok').hide();
				    	jQuery('#app_status_ko').show();						    	
			    		setTimeout(function() { ajax_poll(); }, 10000);
						return;
					}
					jQuery.ajax({
					    url: "<?php echo WPAM_SERVICES_URL?>/app_status.php?uid=<?php echo $options['secret_key'] ?>&jsoncall=?", 
					    dataType: 'json',
					    cache: false,
					    error: function(data) {
						    	jQuery('#app_status_ok').hide();
						    	jQuery('#app_status_ko').show();
					    },
					    success: function(data) {
						    
					    	if (data.error_code == 0 && data.status == 'OK' && data.build_needed == 'N'){
					    		html_value = '';
					    		jQuery('#app_status_ko').html(html_value);
						    	jQuery('#app_status_ko').hide();
						    	jQuery('#app_status_ok').show(); 
						    	jQuery('#build_time').html('<small>Last Update Time: ' + data.last_build_time+'</small>');

					    	}else if (data.error_code == 2 || data.build_needed == 'Y'){
					    		html_value = '<p>The WP App Maker server automatically builds and signs your app when needed; it usually performs the build action in a few minutes.</p><center><img src="<?php echo parent::get_plugin_url()?>/images/loading.gif" /><br>Building. Please wait...</center>';
								if (html_value != jQuery('#app_status_ko').html())
					    			jQuery('#app_status_ko').html(html_value);
						    	jQuery('#app_status_ok').hide();
						    	jQuery('#app_status_ko').show();
						    	setTimeout(function() { ajax_poll(); }, 10000);
					    		
					    	}else{					    		
					    		jQuery('#pro_upgrade').hide();
					    		html_value = '<p><center><img width="100" src="<?php echo parent::get_plugin_url()?>/images/maintenance.png"/><br><strong>App not yet available.</strong><p>Just register and configure your options in order to instantly get your app!</p></center></p>';
								if (html_value != jQuery('#app_status_ko').html())
							    	jQuery('#app_status_ko').html(html_value);
						    	jQuery('#app_status_ok').hide();
						    	jQuery('#app_status_ko').show();						    	
					    		setTimeout(function() { ajax_poll(); }, 10000);
						    }
					    	
					    }//,
					    //contentType: 'application/json'
					 });
				}
				ajax_poll();


		
		});
		
	</script>
	<?php
	}
	
	function add_admin_js(){
		wp_enqueue_script('jquery');  
    	wp_enqueue_script('wpa_colorpicker',	parent::get_plugin_url().'/colorpicker/js/colorpicker.js', array('jquery'),  mt_rand()  );    
		wp_enqueue_script('wpa_wheel',			parent::get_plugin_url().'/fancybox/jquery.mousewheel-3.0.4.pack.js', array('jquery'),  mt_rand()  );    
    	wp_enqueue_script('wpa_fancy',			parent::get_plugin_url().'/fancybox/jquery.fancybox-1.3.4.pack.js', array('jquery'),  mt_rand()  );    
		wp_enqueue_script('wpa_admin',			parent::get_plugin_url().'/wpam.js', array('jquery','wpa_colorpicker'),  mt_rand()  );   


	}  
	 
	function add_admin_styles() { 
		wp_enqueue_style('colorpicker_style',	parent::get_plugin_url().'/colorpicker/css/colorpicker.css', array(), mt_rand(), 'all' );
		wp_enqueue_style('wpa_style',			parent::get_plugin_url().'/style.css', array(), mt_rand(), 'all' );
    	wp_enqueue_style('wpa_fancy',			parent::get_plugin_url().'/fancybox/jquery.fancybox-1.3.4.css', array(), mt_rand(), 'all' );
    
	}
	
	function is_local_blog(){
		$patterns = array('http://localhost','http://192.168','http://127.0');
		foreach($patterns as $pattern){
			if (substr(SITE_URL,0,strlen($pattern)) == $pattern) return true;
		}
		return false;
	}
	
	function settings_page(){
		$options = parent::get_options();

		//let's retrieve the services status
		$data = array('uid'=>$options['secret_key']);

		parent::settings_page_head('',false,false);

		if ($this->is_local_blog()){
			$message = "<strong>Warning:</strong> This websites doesn't seem to be online. The app cannot work if the website is unreachable on Internet.";
			echo '<div class="error fade" style="background-color:red;color:white;"><p>' . $message .'</p></div>';
			$this->app_status = 'OL';//let's put the app offline
		}else if ($this->app_status == 'OL'){
			$message = "<strong>Warning:</strong> Build service temporarily offline. Please be patient and try again later.";
			echo '<div class="error fade" style="background-color:red;color:white;"><p>' . $message .'</p></div>';
		}
		
		?>
		<input type="hidden" name="<?php echo $this->options_name?>[action]" value="a"/>
		<input type="hidden" name="<?php echo $this->options_name?>[app_name_hidden]" value="<?php echo $options['app_name']?>"/>
		
		<div class="postbox-container" style="width:65%;padding-right:10px;">
			
			<div class="metabox-holder">	
				<div class="meta-box-sortables ui-sortable">
					<div class="postbox">
						<h3 style="cursor:default;" class="handle"><span>Your account</span></h3>
						<div class="inside" style="margin:10px;">				
						<?php 
						if (!$options['is_registered']){

							$current_user = wp_get_current_user();	
							$first_name = $current_user->user_firstname;						
							$last_name = $current_user->user_lastname;						
							$email = $current_user->user_email;						
							?>
							<p>Registration is needed only for enabling the app building service.</p>
							<table class="form-table" width="100%">
					        <tr valign="top">
					        	<th scope="row"><strong>First Name:</th>
					           <td><input type="text" name="<?php echo $this->options_name?>[first_name]" value="<?php echo $first_name?>" maxlenght="64"></td>
					        </tr>
					        <tr valign="top">
					        	<th scope="row"><strong>Last Name:</th>
					           <td><input type="text" name="<?php echo $this->options_name?>[last_name]" value="<?php echo $last_name?>" maxlenght="64"></td>
					        </tr>
					        <tr valign="top">
					        	<th scope="row"><strong>Email address:</th>
					           <td><input type="text" name="<?php echo $this->options_name?>[email]" value="<?php echo $email?>" maxlenght="64"></td>
					        </tr>
					        <tr valign="top">
					        	<th scope="row"><strong>Terms of service:</th>
					           <td><input type="checkbox" id="terms_check" > I have read and accept the <a id="terms" href="<?php echo WPAM_SERVICES_URL?>/docs/terms_of_service.html">terms of service</a></td>
					        </tr>
					        
					        
					        </table>
					        <?php if ($this->app_status != 'OL'){?>
							<p class="submit" style="margin:0; padding-top:.5em; padding-left:10px;">
							<input rel="save-profile" type="button" class="button-primary" name="save-profile" value="<?php _e('Register') ?>" />
							</p>
							<?php }?>
						<?php }else{
							//registered users
							

							?>
							<div id="show-profile-section">
								<p>Welcome <?php echo $options['first_name']?> <?php echo $options['last_name']?><br/>
								you're currently registered for the <strong><?php echo $options['level']?></strong> version of the mobile app</p>

								<table class="form-table" width="100%">
						        <tr valign="top">
						        	<th scope="row"><strong>Application Id:</th>
						           <td><?php echo $options['secret_key'] ?></td>
						        </tr>
						        </table>
						        <?php if ($this->app_status != 'OL'){?>
								<a rel="show-edit-profile" class="button-primary" href="#" >Edit profile</a>
								<?php }?>
							</div>

							<div id="edit-profile-section" >
								<table class="form-table" width="100%">
						        <tr valign="top">
						        	<th scope="row"><strong>First Name:</th>
						           <td><input type="text" name="<?php echo $this->options_name?>[first_name]" value="<?php echo $options['first_name']?>" maxlenght="64"></td>
						        </tr>
						        <tr valign="top">
						        	<th scope="row"><strong>Last Name:</th>
						           <td><input type="text" name="<?php echo $this->options_name?>[last_name]" value="<?php echo $options['last_name']?>" maxlenght="64"></td>
						        </tr>
						        <tr valign="top">
						        	<th scope="row"><strong>Email address:</th>
						           <td><input type="text" name="<?php echo $this->options_name?>[email]" value="<?php echo $options['email']?>" maxlenght="64"></td>
						        </tr>
						        
						        
						        </table>
								<?php if ($this->app_status != 'OL'){?>
								<p class="submit" style="margin:0; padding-top:.5em; padding-left:10px;">
									<input rel="update-profile" type="button" class="button-primary" name="update-profile" value="<?php _e('Update') ?>" />
									<a rel="hide-edit-profile"  class="button-primary" href="#" >Cancel</a>
								</p>
								<?php }?>
							</div>

						<?php }?>
						</div>
					</div>
					<?php if ($options['is_registered']){

						if (!isset($options['app_name'])){
							$options['app_name'] = get_bloginfo();	
						}			
					?>
					<div class="postbox">
						<h3  style="cursor:default;" class="handle"><span>App Configuration</span></h3>
						<div class="inside"  style="padding:10px; padding-top:0;">	
							<h4>Core properties</h4>		
							<table class="form-table" width="100%">
					        <tr valign="top">
					        	<th scope="row">App name *:</th>
					           <td>
					           <input type="text" name="<?php echo $this->options_name?>[app_name]" value="<?php echo $options['app_name']?>" maxlenght="64">
					           </td>
					        </tr>
					        <tr valign="top">
					        	<th scope="row">Launcher Icon *:</th>
					           <td> 
					           <table>
					           <tr>
					           <td style="vertical-align:top;padding:0px;">
					           <img id="launcher_icon_img" src="<?php echo WPAM_SERVICES_URL?>/launcher_icon.php?uid=<?php echo $options['secret_key'] ?>" />
					           </td>
					           <td>
					           
					           <?php if ($this->app_status != 'OL'){ ?>
						            <a class="button" id="launcher_icon_build" href="<?php echo parent::get_plugin_url()?>/asset-studio/icons-launcher.php?uid=<?php echo $options['secret_key'] ?>">Generate</a>
						           	&nbsp;...OR just 
						           	<a class="button" id="launcher_icon_upload" href="<?php echo WPAM_SERVICES_URL?>/upload.php?uid=<?php echo $options['secret_key'] ?>">manually upload</a> it:<br/><br/>
						       <?php }?> 
					           </td>
					           </tr>
					           </table>
					           	
								</td>
					        </tr>
					        <tr valign="top">
					        	<td colspan="2" >* Indicates properties which needs the app to be rebuilt and reinstalled. All the other properties just require a restart of the app from your device.</td>
					        </tr>
					        </table>

							<h4>Categories customization</h4>		
							<table class="form-table" width="100%">
					        <tr valign="top">
					        	<th scope="row">Featured category:<br><small>(a featured image is needed for each post)</small></th>
								<td><?php 
								$select_args = array(
									'selected'=>$options['category_featured'],
									'show_option_none'=>'--None--');
								wp_dropdown_categories($select_args);
								?>
								<input type="hidden" id="<?php echo $this->options_name?>[category_featured]" name="<?php echo $this->options_name?>[category_featured]" value="<?php echo $options['category_featured']?>"/></td>
							</tr>
					        <tr valign="top">
					        	<th scope="row">Categories to publish:</th>
					           	<td>
								<table>
									<tr><th><strong>Blog Category</strong></th><th><strong>Mobile Name (not mandatory)</strong></th>
									</tr>
								<?php 
									$categories=get_categories();   
									$i=0;
									foreach($categories as $category) {    
										 echo "<tr><td><input type='checkbox' " . (isset($options['categories_enabled'][$i])?"checked":"") . " name='" . $this->options_name . "[categories_enabled][".$i."]' value='$category->term_id' />";    
										 echo $category->cat_name;
    									 echo '</td><td>';
										 echo "<input type='text' name='" . $this->options_name . "[categories_name][" . $i . "]' value='" . $options['categories_name'][$i] . "' />";    
    									 echo '</td></tr>';   
										$i++ ;
									}
								?>
								</table>
					           </td>					        
								</tr>
					        </table>
							<script type="text/javascript"><!--
							    var dropdown = document.getElementById("cat");
							    function onCatChange() {
									cat_id = dropdown.options[dropdown.selectedIndex].value;
									hidden_field = document.getElementById("<?php echo $this->options_name?>[category_featured]");
									hidden_field.value = cat_id;
							    }
							    dropdown.onchange = onCatChange;
							--></script>

							<h4>Colors and Layout</h4>			
							<table class="form-table" width="100%">

					        <tr valign="top">
					        	<th scope="row">Splash Screen Image:</th>
					           <td> 
					           <table>
					           <tr>
					           <td style="vertical-align:top;padding:0px;">
					           <img width="50px;" id="splash_screen_img" src="<?php echo $options['splash_screen_url']?>" />
							   <input id="splash_screen_img_hidden" type="hidden" name="<?php echo $this->options_name?>[splash_screen_url]" value="<?php echo $options['splash_screen_url'] ?>"/>
					           </td>
					           <td>
					           
					           <?php if ($this->app_status != 'OL'){ ?>
						           	<a class="button" id="splash_screen_upload" href="<?php echo parent::get_plugin_url()?>/upload.php">upload</a>
						           	<a class="button" id="splash_screen_restore" href="#">restore default</a>
						       <?php }?> 
					           </td>
					           </tr>
					           </table>
					           	
								</td>
					        </tr>

					        <tr valign="top">
					        	<th scope="row">Post colors settings:</th>
					            <td style="vertical-align:top;padding-top:0px;">
						           	<table cellpadding=0 cellspacing=0>
									<tr>
									<td>
	 									
							           	<table cellpadding=0 cellspacing=0>
										<tr><td colspan=2>Title</td></tr>
							           	<tr>
										<td style="padding:0px;">
								           	<input id="title_colorpickerField" type="text" name="<?php echo $this->options_name?>[title_color]" 
								           			value="<?php echo $options['title_color'] ?>" size="9" maxlenght="7">
						           		</td>
										<td style="padding:0px;">
								           	<div id="title_customWidget">
				        		     			<div id="title_colorSelector"><div style="background-color: <?php echo $options['title_color'] ?>"></div></div>
				    			            </div>
			    			            </td>
			    			            </tr>
			    			            </table>
									</td>
									<td style="width:10px;"></td>
									<td>									
										
							           	<table cellpadding=0 cellspacing=0>
										<tr><td colspan=2>Text</td></tr>

							           	<tr>
										<td style="padding:0px;">
								           	<input id="post_text_colorpickerField" type="text" name="<?php echo $this->options_name?>[text_color]" 
							           			value="<?php echo $options['text_color'] ?>" size="9" maxlenght="7">
						           		</td>
										<td style="padding:0px;">
								           	<div id="post_text_customWidget">
				        		     			<div id="post_text_colorSelector"><div style="background-color: <?php echo $options['text_color'] ?>"></div></div>
				    			            </div>
			    			            </td>
			    			            </tr>
			    			            </table>
									</td>
									<td style="width:10px;"></td>
									<td>									
										
							           	<table cellpadding=0 cellspacing=0>
										<tr><td colspan=2>Background</td></tr>

							           	<tr>
										<td style="padding:0px;">
								           	<input id="post_bg_colorpickerField" type="text" name="<?php echo $this->options_name?>[bg_color]" 
							           			value="<?php echo $options['bg_color'] ?>" size="9" maxlenght="7">
						           		</td>
										<td style="padding:0px;">
								           	<div id="post_bg_customWidget">
				        		     			<div id="post_bg_colorSelector"><div style="background-color: <?php echo $options['bg_color'] ?>"></div></div>
				    			            </div>
			    			            </td>
			    			            </tr>
			    			            </table>
									</td>
									</tr>
									</table>
					           </td>
					        </tr>

					        <tr valign="top">
					        	<th scope="row">Header colors settings:</th>
					            <td style="vertical-align:top;padding-top:0px;">
						           	<table cellpadding=0 cellspacing=0>
									<tr>
									<td>									
							           	<table cellpadding=0 cellspacing=0>
										<tr>
											<td colspan=2>Text</td>
										</tr>
							           	<tr>
											<td style="padding:0px;">
								           	<input id="header_text_colorpickerField" type="text" name="<?php echo $this->options_name?>[header_text_color]" 
							           			value="<?php echo $options['header_text_color'] ?>" size="9" maxlenght="7">
							           		</td>
											<td style="padding:0px;">
								           	<div id="header_text_customWidget">
				        		     			<div id="header_text_colorSelector"><div style="background-color: <?php echo $options['header_text_color'] ?>"></div></div>
					    			            <!--<div id="header_text_colorpickerHolder"></div>-->
				    			            </div>
				    			            </td>
			    			            </tr>
			    			            </table>
									</td>
									<td style="width:10px;"></td>
									<td>
	 									
							           	<table cellpadding="0" cellspacing="0">
										<tr>
											<td colspan="2">Background</td>
										</tr>
							           	<tr>
											<td style="padding:0px;">
								           	<input id="header_bg_colorpickerField" type="text" name="<?php echo $this->options_name?>[header_bg_color]" 
							           			value="<?php echo $options['header_bg_color'] ?>" size="9" maxlenght="7">
							           		</td>
											<td style="padding:0px;">
								           	<div id="header_bg_customWidget">
				        		     			<div id="header_bg_colorSelector"><div style="background-color: <?php echo $options['header_bg_color'] ?>"></div></div>
					    			            <!--<div id="header_bg_colorpickerHolder"></div>-->
				    			            </div>
				    			            </td>
			    			            </tr>
			    			            </table>
									</td>
									</tr>
									</table>
					           </td>
					        </tr>

					        <tr valign="top">
					        	<th scope="row">Search bar colors settings:</th>
					            <td style="vertical-align:top;padding-top:0px;">
						           	<table cellpadding=0 cellspacing=0>
									<tr>
									<td>									
							           	<table cellpadding=0 cellspacing=0>
										<tr>
											<td colspan=2>Text</td>
										</tr>
							           	<tr>
											<td style="padding:0px;">
								           	<input id="header_search_text_colorpickerField" type="text" name="<?php echo $this->options_name?>[header_search_text_color]" 
							           			value="<?php echo $options['header_search_text_color'] ?>" size="9" maxlenght="7">
							           		</td>
											<td style="padding:0px;">
								           	<div id="header_search_text_customWidget">
				        		     			<div id="header_search_text_colorSelector"><div style="background-color: <?php echo $options['header_search_text_color'] ?>"></div></div>
				    			            </div>
				    			            </td>
			    			            </tr>
			    			            </table>
									</td>
									<td style="width:10px;"></td>
									<td>									
							           	<table cellpadding=0 cellspacing=0>
										<tr>
											<td colspan=2>Background</td>
										</tr>
							           	<tr>
											<td style="padding:0px;">
								           	<input id="header_search_colorpickerField" type="text" name="<?php echo $this->options_name?>[header_search_color]" 
							           			value="<?php echo $options['header_search_color'] ?>" size="9" maxlenght="7">
							           		</td>
											<td style="padding:0px;">
								           	<div id="header_search_customWidget">
				        		     			<div id="header_search_colorSelector"><div style="background-color: <?php echo $options['header_search_color'] ?>"></div></div>
				    			            </div>
				    			            </td>
			    			            </tr>
			    			            </table>
									</td>
									</tr>
									</table>
					           </td>
					        </tr>

					        </table>

							<h4>Tracking</h4>
							<table class="form-table" width="100%">

					        <tr valign="top">
					        	<th scope="row">Google Analytics tracking id (leave empty if you don't want to enable tracking): 								</th>
					           <td>
								<?php if ($options['level'] == 'PRO'){?>
					           <input type="text" name="<?php echo $this->options_name?>[analytics_uid]" value="<?php echo $options['analytics_uid']?>" size="32" maxlenght="64">
								<br/><small>You must first create a free account at <a target="_blank"  href="http://www.google.com/analytics">www.google.com/analytics</a> and create a new web property in that account using a fake but descriptive website URL 
									(e.g. http://mymobileapp.mywebsite.com). Once you create the property (UA-xxxxx-yy), just paste it here.</small>
					           	<?php }else{?>	
								<em>Feature only available on PRO version</em>
								<?php }?>
					           </td>
					        </tr>
					           </td>
					        </tr>
 							</table>

							<h4>Monetize</h4>
							<table class="form-table" width="100%">

					        <tr valign="top">
					        	<th scope="row">AdWhirl SDK Key (leave empty if you don't want to show your ads):</th>
					           <td>
								<?php if ($options['level'] == 'PRO'){?>
					           <input type="text" name="<?php echo $this->options_name?>[adwhirl_uid]" value="<?php echo $options['adwhirl_uid']?>" size="42" maxlenght="64">
								<br/><small> <a target="_blank" href="http://www.adwhirl.com/">AdWhirl</a> enables you to serve ads in your app from any number of ad networks as well as your own house ads. By using multiple networks, you can 
								determine which perform best for you and optimize accordingly to maximize your revenue and fill all your inventory.</small>
					           	<?php }else{?>	
								<em>Feature only available on PRO version</em>
								<?php }?>
								</td>
					        </tr>
					           </td>
					        </tr>
 							</table>

					        <?php if ($this->app_status != 'OL'){?>
							<p class="submit" style="margin:0; padding-top:.5em; padding-left:10px;">
							<input  rel="save-options" type="button" class="button-primary" name="save-options" value="<?php _e('Save') ?>" />
							</p>
							<?php }?>
						</div>
					</div>
					<?php } ?>
					
				</div>
			</div>
		</div>

		<div class="postbox-container" style="width:32%;">
			<div class="metabox-holder">	
				<div class="meta-box-sortables ui-sortable">

					<?php if ($options['level'] != 'PRO'){?>

					<div id="pro_upgrade" class="postbox">
						<h3  style="cursor:default;" class="handle"><span>Upgrade to PRO version</span></h3>
						<div class="inside" style="text-align:center;padding:10px; padding-top:0;">
							<h4>Do you want to get an ads free version or to monetize and track your app?</h4>
							<small>See the <a class="compare" href="<?php echo WPAM_SERVICES_URL?>/product_compare.php">product comparison table</a> for more details.</small>
							<br/><br/>
							<p style="">
								<strong><a id="launcher_buy_pro" href="<?php echo WPAM_PLIMUS_URL.'&custom1='.$options['secret_key'];?>">Upgrade now to the PRO version</a></strong>
								<br/>(only $9.95/Month)<br><br>
								<small>Secure payment gateway provided by <a target="_blank" href="http://www.plimus.com">Plimus</a></small>
							</p>
						</div>
					</div>

					<?php } ?>

					<div class="postbox">
						<h3  style="cursor:default;" class="handle"><span>Your Android App</span></h3>
						<div class="inside" style="padding:10px; padding-top:0;">
							
							
							<div id="app_status_ko" style="display: none;"></div>
							<div id="app_status_ok" style="display: none;">
								<h4>Your App is available for downloading!</h4> 
								<p><small>Before downloading please make sure to <a class="help" href="<?php echo WPAM_SERVICES_URL?>/docs/howto.html">enable the "Unknown Sources" option</a> on your device in order to be able to install an App that isn't sourced through the Market.</small></p>
								<center>
								<strong>Direct download...</strong><br><br>
								<a title="Download" id="downloadbutton" href="<?php echo WPAM_SERVICES_URL?>/getapp/<?php echo $options['public_id'] ?>/android-app.apk"></a><br>
								<a title="Download" href="<?php echo WPAM_SERVICES_URL?>/getapp/<?php echo $options['public_id'] ?>/android-app.apk">Download!</a><br><br>
						    	<strong>...or just take a photo (a QR code reader like <a href="http://market.android.com/details?id=com.google.zxing.client.android">Barcode Scanner</a> is needed):</strong><br>
						    	<img src="http://chart.apis.google.com/chart?chs=200x200&cht=qr&chl=<?php echo urlencode(WPAM_SERVICES_URL.'/getapp/'.$options['public_id'].'/android-app.apk')?>" width="200" height="200" alt="" />

								<div id="build_time" style="margin-top:10px;"></div></center>
							</div>
						</div>
					</div>
					<?php if ($this->app_status != 'OL' && $this->news_show == 'EN'){?>
					<iframe width="100%" height="200" src="<?php echo WPAM_SERVICES_URL?>/additional_info.php"></iframe>
					<?php }?>
				</div>
			</div>
		</div>
<?php
		parent::settings_page_footer();
?>
<?php
	}
	
	function do_http_get($url,$data){
		$res = '';
		if(function_exists('curl_init') ) { // if cURL is available, use it...
			
			$data_url = http_build_query ($data);
			//foreach ($data AS $key=>$value) 
    		//	$post_url .= $key.'='.$value.'&'; 
			//$data_url = rtrim($post_url, '&'); 
			$ch = curl_init($url."?".$data_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$res = curl_exec($ch);
			self::debug('endpoint [CURL]: '.$url.' | get data:'.$data_url. ' res='.$res);
			curl_close($ch);
		
		}else{

			$data_url = http_build_query ($data);
	    	$data_len = strlen ($data_url);
			self::debug('endpoint [PHP]: '.$url.' | get data:'.$data_url);

			$params = array (
	    		'http'=>array (
	    			'method'=>'GET'
            ));
            $ctx = stream_context_create($params);	
            $url .= "?".$data_url;
			$fp = @fopen($url, 'rb', false, $ctx);
			if (!$fp) {
				self::debug("Problem calling GET $url, $php_errormsg");
			}else{
				$res = @stream_get_contents($fp);
	        	self::debug('endpoint: '.$url.' | response:' . $res);    											
			}
		}
		return $res;
	}
	
	function do_http_post($url,$data){
		$res = '';
		if(function_exists('curl_init') ) { // if cURL is available, use it...
			
			$data_url = http_build_query ($data);
			//foreach ($data AS $key=>$value) 
    		//	$post_url .= $key.'='.$value.'&'; 
			//$data_url = rtrim($post_url, '&'); 
			$ch = curl_init($url);
			curl_setopt($ch,CURLOPT_POST,true);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$data_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$res = curl_exec($ch);
			self::debug('endpoint [CURL]: '.$url.' | post data:'.$data_url. ' res='.$res);
			curl_close($ch);
		
		}else{

			$data_url = http_build_query ($data);
	    	$data_len = strlen ($data_url);
			self::debug('endpoint [PHP]: '.$url.' | post data:'.$data_url);

			$params = array (
	    		'http'=>array (
	    			'method'=>'POST', 
	    			'header'=>"Connection: close\r\nContent-Length: $data_len\r\n", 
	    			'content'=>$data_url
            ));
            $ctx = stream_context_create($params);	
			$fp = @fopen($url, 'rb', false, $ctx);
			if (!$fp) {
				self::debug("Problem calling [POST] $url, $php_errormsg");
			}else{
				$res = @stream_get_contents($fp);
	        	self::debug('endpoint: '.$url.' | response:' . $res);    											
			}
		}
		return $res;
	}
	
	function is_color($str){
		return preg_match('/#[0-9a-zA-Z]{6}/',$str);
	}

	function options_validate($options){ 
		self::debug('child options_validate => '.$options['action']);
		$old_options = parent::get_options();
		$options['messages'] = '';
		$options = array_merge( $old_options, $options);
	    $messages = '';
		
		//TBD
		if ($options['action']==''){
			return $old_options;
		}

		//profile data 
		if ($options['action']=='save-profile' || $options['action']=='update-profile'){
			if (!isset($options['first_name'])){ 
				$messages .= 'First Name is mandatory<br/>';
			}
			if (!isset($options['last_name'])){
				$messages .= 'Last Name is mandatory<br/>';
			}
			if (!isset($options['email'])){
				$messages .= 'Email is mandatory<br/>';
			}
			if (!is_email($options['email'])){
				$messages .= 'Please provide a valid email address<br/>';
			}

			
			if (strlen($messages) == 0){
				//all data validated
				$data = array(
					'u'=>SITE_URL,
					'fn'=>$options['first_name'],
					'ln'=>$options['last_name'],
					'em'=>$options['email'],
					'an'=>get_bloginfo(),
					'ver'=>WPAM_VERSION,
				);	
				if ($options['action']=='update-profile'){
					$data['uid'] = $options['secret_key'];
				}
				$res = self::do_http_post(WPAM_SERVICES_URL.'/register.php', $data);
				
				$res = json_decode($res, true);
			    if (true){
			    	if (isset($res['error_code']) && $res['error_code'] == '0'){
				    	$uid = $res['uid'];
				    	$level = $res['level'];
						$options['secret_key'] = $uid;
						$options['is_registered'] = true;
						$options['level'] = $level;
			    	} else {
			    		if (isset($res['message']))
			    			$err_msg = $res['message'];
			    		else		
				    		$err_msg = 'Server temporarily unavailable: please try again in a few minutes.';
			    	}
			    }	
			    $messages .= $err_msg;			    
			}

		}else{

			//save options	
			if (!isset($options['app_name']) || strlen($options['app_name'])==0){
				$messages .= 'App Name is mandatory<br/>';
			}
			if (!$this->is_color($options['title_color'])){
				$messages .= 'Post title color '.$options['title_color'].' is invalid <br/>';
			}
			if (!$this->is_color($options['text_color'])){
				$messages .= 'Post text color '.$options['text_color'].' is invalid <br/>';
			}
			if (!$this->is_color($options['bg_color'])){
				$messages .= 'Post background color '.$options['bg_color'].' is invalid <br/>';
			}
			if (!$this->is_color($options['header_bg_color'])){
				$messages .= 'Header background color '.$options['header_bg_color'].' is invalid <br/>';
			}
			if (!$this->is_color($options['header_text_color'])){
				$messages .= 'Header text color '.$options['header_text_color'].' is invalid <br/>';
			}
			
			if (!$this->is_color($options['header_search_text_color'])){
				$messages .= 'Search bar text color '.$options['header_search_text_color'].' is invalid <br/>';
			}
			if (!$this->is_color($options['header_search_color'])){
				$messages .= 'Search bar background color '.$options['header_search_color'].' is invalid <br/>';
			}
			if (count($options['categories_enabled'])==0){
				$messages .= 'Please enable one or more categories <br/>';
			}
			
			/*
			if (strlen($messages) == 0){
				//all data validated
			    //$messages .= self::update_server($options);			    
			}
			*/			
		}
		if (strlen($messages) > 0){
			$options = $old_options;
        	$options['messages'] = $messages;
	    }
		$options['action']='';
		return $options;
	}	

	function update_server($options){

		$err_msg='';
		$data = array(
			'url'=>SITE_URL,
			'uid'=>$options['secret_key'],
			'an'=>$options['app_name'],

		);	
		$res = self::do_http_post(WPAM_SERVICES_URL.'/update.php', $data);

		$res = json_decode($res, true);
	    if (true){
	    	if (isset($res['error_code']) && $res['error_code'] == '0'){
		    	$uid = $res['uid'];
		    	$id = $res['public_id'];
		    	$level = $res['level'];
				$options['secret_key'] = $uid;
				$options['public_id'] = $id;
				$options['is_registered'] = true;
				$options['level'] = $level;		
				update_option($this->options_name,$options);

				self::debug('update_server :: saved level ' . $level);
	    	} else {
	    		if (isset($res['message']))
	    			$err_msg = $res['message'];
	    		else		
		    		$err_msg = 'Server temporarily unavailable: please try again in a few minutes.';
	    	}
	    }	
		return $err_msg;
	}

}
require_once(dirname(__FILE__) .'/widget.php');

$wpam = new WPAppMaker();

?>