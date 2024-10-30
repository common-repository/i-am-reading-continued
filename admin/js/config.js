/**
 * Checks which data source is selected and
 * displays extended options for Amazon.
 * 
 * @return void
 */
function checkSource() {
	
	if ( jQuery('#data_source').attr('selectedIndex') == 0 ) {
		
		jQuery('#amazon').show();
		
	} else {
		
		jQuery('#amazon').hide();
	}
}

/**
 * Used to check the status of a given checkbox and show / hide
 * another content element (fieldsets, for example).
 * 
 * @param  string  ID of the checkbox used for decision
 * @param  string  ID of the content element to show / hide
 * @return void
 */
function checkExtendedOptions( checked_element, target ) {

	if ( jQuery('#'+checked_element).attr('checked') == true ) {
		
		jQuery('#'+target).show();
		jQuery('#widget_title').focus();
		
	} else {
		
		jQuery('#'+target).hide();
		jQuery('#widget_title').focus();
	}
}

/**
 * New function to check if the default theme or another one
 * is selected. Decides to show more display options for
 * the default theme, for compatability.
 * 
 * Will be used in an upcoming plugin version.
 * 
 * @return void
 */
function checkTheme() {

	var selected_theme = jQuery('#display_theme').val();

	var data = {
		action: 'iar_get_theme',
		theme: selected_theme
	};

	jQuery.post('admin-ajax.php', data, function(response) {

		jQuery('#theme-demo').html(response);
	});

	if ( jQuery('#display_theme').attr('selectedIndex') == 0 ) {
		
		jQuery('#widget_details').show();
		jQuery('#widget_settings').show();
		
	} else {
		
		jQuery('#widget_details').hide();
		jQuery('#widget_settings').hide();
	}
}

/**
 * Reloads the preview if changing to default theme
 *
 * @return void
 */
function reloadPreview() {

	// update just set vars for recognizing changed fields
	var display_update = getDisplayForm();

	// update widget title
	jQuery('#theme_preview .widgettitle').html(display_update['widget_title']);

	if ( display_update['widget_alignment'] == 'left' ) {

		jQuery('#iar_widget_box').css('float', 'none');
		jQuery('#iar_widget_box').css('width', '100%');
		jQuery('#iar_widget_box').css('text-align', 'left');
		jQuery('#iar_widget_table_top').css('margin', '0px');

	} else if ( display_update['widget_alignment'] == 'center' ) {

		jQuery('#iar_widget_box').css('float', 'none');
		jQuery('#iar_widget_box').css('width', '100%');
		jQuery('#iar_widget_box').css('text-align', 'center');
		jQuery('#iar_widget_table_top').css('margin', '0px auto');

	} else if ( display_update['widget_alignment'] == 'right' ) {

		jQuery('#iar_widget_box').css('width', 'auto');
		jQuery('#iar_widget_box').css('float', 'right');
		jQuery('#iar_widget_box').css('text-align', 'left');
		jQuery('#iar_widget_table_top').css('margin', '0px');
	}

	// update displayed items
	if ( display_update['widget_items']['cover'] == false ) {
		jQuery('#iar_cover_img').hide();
	} else {
		jQuery('#iar_cover_img').show();
	}

	if ( display_update['widget_items']['progress'] == false ) {
		jQuery('#iar_progressbar_graph').hide();
		jQuery('#iar_progressbar_pages').hide();
	} else {
		jQuery('#iar_progressbar_graph').show();
		jQuery('#iar_progressbar_pages').show();
	}

	if ( display_update['widget_items']['title'] == false ) {
		jQuery('#iar_book_title').hide();
	} else {
		jQuery('#iar_book_title').show();
	}

	// update cover Image
	if ( display_update['cover_size'] == 'small' ) {

		jQuery('#iar_cover_img').attr('src', jQuery('#cover_img_small').html());
		jQuery('#iar_cover_img').css('max-width', '50px');

	} else if ( display_update['cover_size'] == 'medium' ) {

		jQuery('#iar_cover_img').attr('src', jQuery('#cover_img_medium').html());
		jQuery('#iar_cover_img').css('max-width', '115px');

	} else if ( display_update['cover_size'] == 'large' ) {

		jQuery('#iar_cover_img').attr('src', jQuery('#cover_img_large').html());
		jQuery('#iar_cover_img').css('max-width', '215px');
	}

	// update book title
	jQuery('#iar_book_title').css('font-family', display_update['book_title_font_family']);
	jQuery('#iar_book_title').css('font-size', display_update['book_title_font_size']+'px');
	jQuery('#iar_book_title').css('color', display_update['book_title_font_color']);

		// update progressbar
	jQuery('#iar_progressbar').css('background', display_update['progress_color_back']);
	jQuery('#iar_progressbar_inner').css('background', display_update['progress_color_front']);

	if ( display_update['progress_color_border'] == '' ) {
		jQuery('#iar_progressbar').css('border', 'none');
	} else {
		jQuery('#iar_progressbar').css('border', '1px solid '+display_update['progress_color_border']);
	}

	jQuery('#iar_progressbar_text').css('font-family', display_update['progress_font_family']);
	jQuery('#iar_progressbar_text').css('font-size', display_update['progress_font_size']+'px');
	jQuery('#iar_progressbar_text').css('color', display_update['progress_font_color']);

	// update temp var for recognizing changed fields
	display_current = getDisplayForm();
}

/**
 * Checks all form fields for changes when called by different events
 * like entering text in input fields or changing the selected option
 * of a selectbox.
 * 
 * Depending on the form fields which have changed the preview in the
 * upper-right corner (I am reading - Display) will be updated.
 * 
 * @return void
 */
function updatePreview() {
	
	// update just set vars for recognizing changed fields
	var display_update = getDisplayForm();
	
	// update widget title
	if ( display_update['widget_title'] != display_current['widget_title'] ) {
		
		jQuery('#theme_preview .widgettitle').html(display_update['widget_title']);
	}
	
	// update widget alignment
	if ( display_update['widget_alignment'] != display_current['widget_alignment'] ) {
		
		if ( display_update['widget_alignment'] == 'left' ) {
			
			jQuery('#iar_widget_box').css('float', 'none');
			jQuery('#iar_widget_box').css('width', '100%');
			jQuery('#iar_widget_box').css('text-align', 'left');
			jQuery('#iar_widget_table_top').css('margin', '0px');
			
		} else if ( display_update['widget_alignment'] == 'center' ) {
			
			jQuery('#iar_widget_box').css('float', 'none');
			jQuery('#iar_widget_box').css('width', '100%');
			jQuery('#iar_widget_box').css('text-align', 'center');
			jQuery('#iar_widget_table_top').css('margin', '0px auto');
			
		} else if ( display_update['widget_alignment'] == 'right' ) {
			
			jQuery('#iar_widget_box').css('width', 'auto');
			jQuery('#iar_widget_box').css('float', 'right');
			jQuery('#iar_widget_box').css('text-align', 'left');
			jQuery('#iar_widget_table_top').css('margin', '0px');
		}
	}
	
	// update displayed items
	if (   (display_update['widget_items']['cover'] != display_current['widget_items']['cover'])
	    || (display_update['widget_items']['title'] != display_current['widget_items']['title'])
	    || (display_update['widget_items']['progress'] != display_current['widget_items']['progress']) ) {
		
		if ( display_update['widget_items']['cover'] == false ) {
			jQuery('#iar_cover_img').hide();
		} else {
			jQuery('#iar_cover_img').show();
		}
		
		if ( display_update['widget_items']['progress'] == false ) {
			jQuery('#iar_progressbar_graph').hide();
			jQuery('#iar_progressbar_pages').hide();
		} else {
			jQuery('#iar_progressbar_graph').show();
			jQuery('#iar_progressbar_pages').show();
		}
		
		if ( display_update['widget_items']['title'] == false ) {
			jQuery('#iar_book_title').hide();
		} else {
			jQuery('#iar_book_title').show();
		}
	}
	
	// update cover Image
	if ( display_update['cover_size'] != display_current['cover_size'] ) {
		
		if ( display_update['cover_size'] == 'small' ) {
			
			jQuery('#iar_cover_img').attr('src', jQuery('#cover_img_small').html());
			jQuery('#iar_cover_img').css('max-width', '50px');
			
		} else if ( display_update['cover_size'] == 'medium' ) {
			
			jQuery('#iar_cover_img').attr('src', jQuery('#cover_img_medium').html());
			jQuery('#iar_cover_img').css('max-width', '115px');
			
		} else if ( display_update['cover_size'] == 'large' ) {
			
			jQuery('#iar_cover_img').attr('src', jQuery('#cover_img_large').html());
			jQuery('#iar_cover_img').css('max-width', '215px');
		}
	}
	
	// update book title
	if ( display_update['book_title_font_family'] != display_current['book_title_font_family'] ) { jQuery('#iar_book_title').css('font-family', display_update['book_title_font_family']); }
	if ( display_update['book_title_font_size'] != display_current['book_title_font_size'] ) { jQuery('#iar_book_title').css('font-size', display_update['book_title_font_size']+'px'); }
	if ( display_update['book_title_font_color'] != display_current['book_title_font_color'] ) { jQuery('#iar_book_title').css('color', display_update['book_title_font_color']); }
	
		// update progressbar
	if ( display_update['progress_color_back'] != display_current['progress_color_back'] ) { jQuery('#iar_progressbar').css('background', display_update['progress_color_back']); }
	if ( display_update['progress_color_front'] != display_current['progress_color_front'] ) { jQuery('#iar_progressbar_inner').css('background', display_update['progress_color_front']); }
	
	if ( display_update['progress_color_border'] != display_current['progress_color_border'] ) {
		
		if ( display_update['progress_color_border'] == '' ) {
			jQuery('#iar_progressbar').css('border', 'none');
		} else {
			jQuery('#iar_progressbar').css('border', '1px solid '+display_update['progress_color_border']);
		}
	}
	
	if ( display_update['progress_font_family'] != display_current['progress_font_family'] ) { jQuery('#iar_progressbar_text').css('font-family', display_update['progress_font_family']); }
	if ( display_update['progress_font_size'] != display_current['progress_font_size'] ) { jQuery('#iar_progressbar_text').css('font-size', display_update['progress_font_size']+'px'); }
	if ( display_update['progress_font_color'] != display_current['progress_font_color'] ) { jQuery('#iar_progressbar_text').css('color', display_update['progress_font_color']); }
	
	// update temp var for recognizing changed fields
	display_current = getDisplayForm();
}

/**
 * Stores the form data of the 'I am reading - Display' page
 * and is used to check which fields have been modified
 * on update.
 * 
 * @return void
 */
function getDisplayForm() {
	
	var form_data = new Array();
	
	form_data['widget_title']     = jQuery('#widget_title').val();
	form_data['widget_alignment'] = jQuery('#display_alignment').val();
	
	form_data['widget_items']             = new Array();
	form_data['widget_items']['cover']    = jQuery('#display_cover_image').attr('checked');
	form_data['widget_items']['title']    = jQuery('#display_book_title').attr('checked');
	form_data['widget_items']['progress'] = jQuery('#display_progressbar').attr('checked');
	
	form_data['cover_size']               = jQuery('#display_cover_image_size').val();
	
	form_data['book_title_font_family']   = jQuery('#display_book_title_font_family').val();
	form_data['book_title_font_size']     = jQuery('#display_book_title_font_size').val();
	form_data['book_title_font_color']    = jQuery('#dbtc_input').val();
	
	form_data['progress_color_back']      = jQuery('#dpbcb_input').val();
	form_data['progress_color_front']     = jQuery('#dpbcf_input').val();
	form_data['progress_color_border']    = jQuery('#dpbcbo_input').val();
	form_data['progress_font_family']     = jQuery('#display_progressbar_font_family').val();
	form_data['progress_font_size']       = jQuery('#display_progressbar_font_size').val();
	form_data['progress_font_color']      = jQuery('#dpbfc_input').val();
	
	return form_data;
}