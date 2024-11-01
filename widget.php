<?php
/* Add our function to the widgets_init hook. */
add_action( 'widgets_init', 'wpam_load_widgets' );

/* Function that registers our widget. */
function wpam_load_widgets() {
	register_widget( 'Wpam_Widget' );
}

class Wpam_Widget extends WP_Widget {
	function Wpam_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'wpam', 'description' => 'WP App Maker Download' );

		/* Widget control settings. */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'wpam-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'wpam-widget', 'WP App Maker Widget', $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );

		/* User-selected settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$link_type = $instance['link_type'];
		$custom_link = $instance['custom_link'];
		$info = $instance['info'];
		$qrsize = is_numeric($instance['qr_size']) ? $instance['qr_size'] : '200';

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Title of widget (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;
		
		echo "<div style='text-align:center;padding:10px;'>";
		$error = false;
		$options = get_option('wpam-options');

		if ($link_type == 'default'){
			$app_id = $options['public_id'];
			$download_link='http://services.wpappmaker.com/getapp/'.$app_id.'/android-app.apk';
			$qrcode = '<img width="'.$qrsize.'" height="'.$qrsize.'" alt="" src="http://chart.apis.google.com/chart?chs=200x200&amp;cht=qr&amp;chl='.urlencode($download_link).'">';
		}else if (isset($custom_link)){
			$download_link=$custom_link;
			$qrcode = '<img width="'.$qrsize.'" height="'.$qrsize.'" alt="" src="http://chart.apis.google.com/chart?chs=200x200&amp;cht=qr&amp;chl='.urlencode($custom_link).'">';
		}else{
			$qrcode = 'Unable to generate the QR code. Please review your configuration.';
			$error = true;
		}
		echo $qrcode;

		if (!$error){
			$link = '<br/><a href="'.$download_link.'">Direct Download</a><br/>';
		}

		echo $link;
		echo '</div>';
		echo $info;

		/* After widget (defined by themes). */
		echo $after_widget;
	}
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['link_type'] = strip_tags( $new_instance['link_type'] );
		$instance['custom_link'] = $new_instance['custom_link'];
		$instance['info'] = $new_instance['info'];
		$instance['qr_size'] = $new_instance['qr_size'];

		return $instance;
	}

	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => 'Download now our Android App', 'qr_size' =>'250', 'link_type' => 'default', 'custom_link' => 'http://', 
					'info' => 'Just take a photo of the QR code by using an app like <a href="http://market.android.com/details?id=com.google.zxing.client.android">Barcode Scanner</a> or visit this website with your smartphone and click on the "Direct Download" link.<center><small>Powered By <a alt="Android App Generator Plugin for Wordpress" target="_blank" href="http://wpappmaker.com">WP App Maker</a></small></center>' );
		$instance = wp_parse_args( (array) $instance, $defaults ); 
		
		?>
			

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:80%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'qr_size' ); ?>">QR Code height/width (px):</label>
			<input id="<?php echo $this->get_field_id( 'qr_size' ); ?>" name="<?php echo $this->get_field_name( 'qr_size' ); ?>" value="<?php echo $instance['qr_size']; ?>" size="5" />
		</p>
		<p>
			<input id="<?php echo $this->get_field_id( 'link_type' ); ?>1" name="<?php echo $this->get_field_name( 'link_type' ); ?>" <?php checked( $instance['link_type'], 'default' ); ?> value="default" type="radio" />
			<label for="<?php echo $this->get_field_id( 'link_type' ); ?>1">Use WP App Maker Download Link</label><br><small>Please note that in order to install the app from this non-Market link the device needs to <a target="_blank" href="http://services.wpappmaker.com/docs/howto.html">enable the "Unknown Sources" option</a></small>
		</p>
		<p>
			<input id="<?php echo $this->get_field_id( 'link_type' ); ?>2" name="<?php echo $this->get_field_name( 'link_type' ); ?>" <?php checked( $instance['link_type'], 'custom' ); ?> value="custom" type="radio" />
			<label for="<?php echo $this->get_field_id( 'link_type' ); ?>2">Use the following custom Download link (i.e. Android Market Download link)</label>:<br/>
			<input id="<?php echo $this->get_field_id( 'custom_link' ); ?>" name="<?php echo $this->get_field_name( 'custom_link' ); ?>" value="<?php echo $instance['custom_link']; ?>" style="width:80%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'info' ); ?>">Additional info (HTML is allowed):</label>
			<textarea rows="6" style="width:100%;" id="<?php echo $this->get_field_id( 'info' ); ?>" name="<?php echo $this->get_field_name( 'info' ); ?>"><?php echo htmlspecialchars($instance['info']); ?></textarea>
			
		</p>
<?php /*
		<p>
			<label for="<?php echo $this->get_field_id( 'sex' ); ?>">Sex:</label>
			<select id="<?php echo $this->get_field_id( 'sex' ); ?>" name="<?php echo $this->get_field_name( 'sex' ); ?>" class="widefat" style="width:100%;">
				<option <?php if ( 'male' == $instance['format'] ) echo 'selected="selected"'; ?>>male</option>
				<option <?php if ( 'female' == $instance['format'] ) echo 'selected="selected"'; ?>>female</option>
			</select>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['show_sex'], true ); ?> id="<?php echo $this->get_field_id( 'show_sex' ); ?>" name="<?php echo $this->get_field_name( 'show_sex' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_sex' ); ?>">Display sex publicly?</label>
		</p><?php
*/
	}

}
?>