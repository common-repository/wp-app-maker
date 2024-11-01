<?php

if ( !defined('WP_CONTENT_URL') )
    define( 'WP_CONTENT_URL', get_option('blogurl') . '/wp-content');
if ( ! defined( 'WP_PLUGIN_URL' ) )
      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
define('DEBUG', false);

class WpPlugin {

	protected $namespace, $options_name, $name, $readable_name, $top_level_admin_page, $top_level_admin_page_name, $version;
	protected $default_options = array();
	protected $config_tables = array();
	
	function __construct($plugin_namespace, $default_options, $config_tables, $plugin_name, $readable_name, $version, $file) {
       $this->name = $plugin_name;
       $this->namespace = $plugin_namespace;
       $this->options_name = $this->namespace . "-options";
       $this->default_options = $default_options;
       $this->readable_name = $readable_name;
       $this->top_level_admin_page_name = "settings_page";
       $this->version = $version;
       $this->config_tables = $config_tables;
       
       add_option($this->options_name,$this->default_options);
       register_activation_hook($file, array(&$this,'install'));
       register_deactivation_hook($file, array(&$this,'uninstall'));
	}
   	
   	function WpPlugin($plugin_namespace, $default_options, $config_tables, $plugin_name, $readable_name, $version, $file){
   		self::__construct($plugin_namespace, $default_options, $config_tables, $plugin_name, $readable_name, $version, $file);
   	}
   	
   	function uninstall($dummy=true){
   		self::debug(__CLASS__.' :: uninstall');
   	}
   	
   	function install($dummy=true){
   		self::debug(__CLASS__.' :: install');
   		global $wpdb;
   		if (is_array($this->config_tables) && count($this->config_tables) > 0){
   			foreach ($this->config_tables as $config_table_name => $config_table_lines){
   				$table_name = $wpdb->prefix . $config_table_name;
   				$sql = "CREATE TABLE $table_name (";
   				foreach($config_table_lines as $config_table_line){
   					$sql .= $config_table_line . " ";
   				}
   				$sql .= ");";
   				self::debug(__CLASS__.":: install :: $sql");
   				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			    dbDelta($sql);
   			}
   		}
   	}
   	
   	function init(){
		$options = self::get_options();
		if (count($options) != count($this->default_options)){
			$merged_options = array_merge((array)$this->default_options, (array)$options);
			update_option($this->options_name, $merged_options);
		}
		
		
   	}

	function debug($msg) {
		if (DEBUG){
		    $today = date("Y-m-d H:i:s ");
		    $myFile = dirname(__file__) . "/debug.log";
		    $fh = fopen($myFile, 'a') or die("Can't open debug file. Please manually create the 'debug.log' file ");
		    $ua_simple = preg_replace("/(.*)\s\(.*/","\\1",$_SERVER['HTTP_USER_AGENT']);
		    fwrite($fh, $today . " [from: ".$_SERVER['REMOTE_ADDR']."|$ua_simple] - " . $msg . "\n");
		    fclose($fh);
		}	
	}
	
	function get_options(){
		return get_option($this->options_name);
	}
	
	function get_option($key){
		$options = get_option($this->options_name);
		return $options[$key];
	}
	
	function update_option($key,$value){
		$options = get_option($this->options_name);
		$options[$key] = $value;
		update_option($this->options_name, $options);
	}
	
	function create_top_level_admin_page(){
   		self::debug('create_top_level_admin_page');
		$this->top_level_admin_page = add_menu_page($this->readable_name, $this->readable_name, 
				'administrator', __FILE__, array(&$this, $this->top_level_admin_page_name),plugins_url('/images/icon.png', __FILE__));
		//register settings
		add_action('admin_init', array(&$this, 'my_register_settings') ); 
		add_action('admin_print_scripts-'.$this->top_level_admin_page, array(&$this, 'add_admin_js'));
		add_action('admin_head-'.$this->top_level_admin_page, array(&$this, 'add_admin_head_code'));
		add_action('admin_print_styles-'.$this->top_level_admin_page, array(&$this, 'add_admin_styles'));
	}
	
	function add_admin_js($dummy=true){
	}
	function add_admin_head_code($dummy=true){
	}
	function add_admin_styles($dummy=true){
	}
	
	function my_register_settings() {
		self::debug('my_register_settings');
		//register our settings
		register_setting( $this->namespace.'-settings-group', $this->options_name, array(&$this,'options_validate') );
	}
	
	//abstract
	function options_validate($options, $dummy=0){ 
		self::debug('parent options_validate');
		return $options;
	}
	
	function is_file_writable($filename) {
		if(!is_writable($filename)) {
			if(!@chmod($filename, 0666)) {
				$pathtofilename = dirname($filename);
				if(!is_writable($pathtofilename)) {
					if(!@chmod($pathtoffilename, 0666)) {
						return false;
					}
				}
			}
		}
		return true;
	}

	function settings_page_head($message, $show_first_box=true, $show_second_box=true){
		//self::debug(__CLASSNAME__.'settings_page_head');
		$options = self::get_options();
		$author_name = "WP App Maker";
		$author_url = "http://wpappmaker.com";
		?>		
<div class="wrap">
	<h2><?php echo $this->readable_name . " " . $this->version?></h2>
	
	<div style="padding-bottom:10px;margin-top:5px;margin-bottom:10px;">
	by <strong><a target="_blank" href="<?php echo $author_url?>"><?php echo $author_name?></a></strong>	
	</div>
<!--
	<div style="width: 832px;">
		<div style="float: left; background-color: white; padding: 10px; margin-right: 15px; border: 1px solid rgb(221, 221, 221);">
			<div style="width: 350px; height: 80px;">
				<em><?php echo $message?></a></strong></em>
			</div>
		</div>
	</div>
-->
	<div style="clear:both;"></div>
  	<?php      
  	if (strlen($options['messages']) > 0){
  		echo '<div class="error fade" style="background-color:red;color:white;"><p>' . $options['messages'] .'</p></div>';
  	}
  	?>

		<form id="submit-form" method="post" action="options.php">
    	<?php 
    	settings_fields( $this->namespace.'-settings-group' ); 	
    	

    
	}
	
	function settings_page_footer(){
		//self::debug(__CLASSNAME__.'settings_page_footer');
	?>
		</form>
	</div>
	<?php	
	}
	
	function get_wp_content_url(){
		return WP_CONTENT_URL;
	}
	
	function get_wp_plugins_url(){
		return WP_PLUGIN_URL;
	}
	
	function get_plugin_url(){
		return WP_PLUGIN_URL . '/' . $this->name;
	}

}
?>