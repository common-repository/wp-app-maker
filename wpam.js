jQuery(document).ready(function() {
	initCP = function(){
				jQuery('#post_text_colorpickerField').change(function() {
					jQuery('#post_text_colorSelector div').css('backgroundColor', jQuery('#post_text_colorpickerField').val());
				});
				jQuery('#title_colorpickerField').change(function() {
					jQuery('#title_colorSelector div').css('backgroundColor', jQuery('#title_colorpickerField').val());
				});
				jQuery('#post_bg_colorpickerField').change(function() {
					jQuery('#post_bg_colorSelector div').css('backgroundColor', jQuery('#post_bg_colorpickerField').val());
				});
				jQuery('#header_text_colorpickerField').change(function() {
					jQuery('#header_text_colorSelector div').css('backgroundColor', jQuery('#header_text_colorpickerField').val());
				});
				jQuery('#header_search_colorpickerField').change(function() {
					jQuery('#header_search_colorSelector div').css('backgroundColor', jQuery('#header_search_colorpickerField').val());
				});
				jQuery('#header_search_text_colorpickerField').change(function() {
					jQuery('#header_search_text_colorSelector div').css('backgroundColor', jQuery('#header_search_text_colorpickerField').val());
				});
				jQuery('#header_bg_colorpickerField').change(function() {
					jQuery('#header_bg_colorSelector div').css('backgroundColor', jQuery('#header_bg_colorpickerField').val());
				});

				jQuery('#post_text_colorSelector').ColorPicker({
					color: '#00ff00',
					onShow: function (colpkr) {
						jQuery(colpkr).fadeIn(500);
						return false;
					},
					onHide: function (colpkr) {
						jQuery(colpkr).fadeOut(500);
						return false;
					},
					onSubmit: function(hsb, hex, rgb, el) {
						jQuery('#post_text_colorpickerField').val('#'+hex);
						jQuery(el).ColorPickerHide();
					},
					onChange: function (hsb, hex, rgb) {
						jQuery('#post_text_colorpickerField').val('#'+hex);
						jQuery('#post_text_colorSelector div').css('backgroundColor', '#' + hex);
					}
				});


				jQuery('#title_colorSelector').ColorPicker({
					color: '#00ff00',
					onShow: function (colpkr) {
						jQuery(colpkr).fadeIn(500);
						return false;
					},
					onHide: function (colpkr) {
						jQuery(colpkr).fadeOut(500);
						return false;
					},
					onSubmit: function(hsb, hex, rgb, el) {
						jQuery('#title_colorpickerField').val('#'+hex);
						jQuery(el).ColorPickerHide();
					},
					onChange: function (hsb, hex, rgb) {
						jQuery('#title_colorpickerField').val('#'+hex);
						jQuery('#title_colorSelector div').css('backgroundColor', '#' + hex);
					}
				});

				jQuery('#post_bg_colorSelector').ColorPicker({
					color: '#00ff00',
					onShow: function (colpkr) {
						jQuery(colpkr).fadeIn(500);
						return false;
					},
					onHide: function (colpkr) {
						jQuery(colpkr).fadeOut(500);
						return false;
					},
					onSubmit: function(hsb, hex, rgb, el) {
						jQuery('#post_bg_colorpickerField').val('#'+hex);
						jQuery(el).ColorPickerHide();
					},
					onChange: function (hsb, hex, rgb) {
						jQuery('#post_bg_colorpickerField').val('#'+hex);
						jQuery('#post_bg_colorSelector div').css('backgroundColor', '#' + hex);
					}
				});

				jQuery('#header_text_colorSelector').ColorPicker({
					color: '#00ff00',
					onShow: function (colpkr) {
						jQuery(colpkr).fadeIn(500);
						return false;
					},
					onHide: function (colpkr) {
						jQuery(colpkr).fadeOut(500);
						return false;
					},
					onSubmit: function(hsb, hex, rgb, el) {
						jQuery('#header_text_colorpickerField').val('#'+hex);
						jQuery(el).ColorPickerHide();
					},
					onChange: function (hsb, hex, rgb) {
						jQuery('#header_text_colorpickerField').val('#'+hex);
						jQuery('#header_text_colorSelector div').css('backgroundColor', '#' + hex);
					}
				});

				jQuery('#header_search_colorSelector').ColorPicker({
					color: '#00ff00',
					onShow: function (colpkr) {
						jQuery(colpkr).fadeIn(500);
						return false;
					},
					onHide: function (colpkr) {
						jQuery(colpkr).fadeOut(500);
						return false;
					},
					onSubmit: function(hsb, hex, rgb, el) {
						jQuery('#header_search_colorpickerField').val('#'+hex);
						jQuery(el).ColorPickerHide();
					},
					onChange: function (hsb, hex, rgb) {
						jQuery('#header_search_colorSelector div').css('backgroundColor', '#' + hex);
						jQuery('#header_search_colorpickerField').val('#'+hex);
					}
				});

				jQuery('#header_search_text_colorSelector').ColorPicker({
					color: '#00ff00',
					onShow: function (colpkr) {
						jQuery(colpkr).fadeIn(500);
						return false;
					},
					onHide: function (colpkr) {
						jQuery(colpkr).fadeOut(500);
						return false;
					},
					onSubmit: function(hsb, hex, rgb, el) {
						jQuery('#header_search_text_colorpickerField').val('#'+hex);
						jQuery(el).ColorPickerHide();
					},
					onChange: function (hsb, hex, rgb) {
						jQuery('#header_search_text_colorSelector div').css('backgroundColor', '#' + hex);
						jQuery('#header_search_text_colorpickerField').val('#'+hex);
					}
				});

				jQuery('#header_bg_colorSelector').ColorPicker({
					color: '#00ff00',
					onShow: function (colpkr) {
						jQuery(colpkr).fadeIn(500);
						return false;
					},
					onHide: function (colpkr) {
						jQuery(colpkr).fadeOut(500);
						return false;
					},
					onSubmit: function(hsb, hex, rgb, el) {
						jQuery('#header_bg_colorpickerField').val('#'+hex);
						jQuery(el).ColorPickerHide();
					},
					onChange: function (hsb, hex, rgb) {
						jQuery('#header_bg_colorpickerField').val('#'+hex);
						jQuery('#header_bg_colorSelector div').css('backgroundColor', '#' + hex);
					}
				});

				d = new Date();

				jQuery("#launcher_icon_build").fancybox({
				'width'				: '95%',
				'height'			: '95%',
				'autoScale'			: false,
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'type'				: 'iframe',
				'onClosed':function(){jQuery('#launcher_icon_img').attr('src',jQuery('#launcher_icon_img').attr('src')+'&'+d.getTime());ajax_poll();}
				});
	
				jQuery("#launcher_icon_upload").fancybox({
				'width'				: 450,
				'height'			: 280,
				'autoScale'			: false,
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'type'				: 'iframe',
				'onClosed':function(){jQuery('#launcher_icon_img').attr('src',jQuery('#launcher_icon_img').attr('src')+'&'+d.getTime());ajax_poll();}
				});
		
	
				jQuery("#launcher_buy_pro").fancybox({
				'width'				: 980,
				'height'			: '95%',
				'autoScale'			: false,
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'type'				: 'iframe',
				'onClosed':function(){parent.location.reload(true);}
				});

				jQuery("#terms").fancybox({
				'width'				: 980,
				'height'			: '95%',
				'autoScale'			: false,
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'type'				: 'iframe'
				});

				jQuery(".help").fancybox({
				'width'				: 700,
				'height'			: 400,
				'autoScale'			: false,
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'type'				: 'iframe'
				});

				jQuery(".compare").fancybox({
					'width'				: 750,
					'height'			: 720,
					'autoScale'			: false,
					'transitionIn'		: 'none',
					'transitionOut'		: 'none',
					'type'				: 'iframe'
					});


	};

});