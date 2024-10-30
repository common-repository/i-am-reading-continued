<script type="text/javascript">
	
	jQuery(document).ready(function() {
		
		/* register color picker click-handler */
		
		function test() { alert('test'); }
		
		// book title: font
		jQuery('#dbtc_demo').click(function(){ showColorGrid3('dbtc_input','dbtc_demo'); });
		
		// progressbar: background, foreground, border
		jQuery('#dpbcb_demo').click(function(){ showColorGrid3('dpbcb_input','dpbcb_demo'); });
		jQuery('#dpbcf_demo').click(function(){ showColorGrid3('dpbcf_input','dpbcf_demo'); });
		jQuery('#dpbcbo_demo').click(function(){ showColorGrid3('dpbcbo_input','dpbcbo_demo'); });
		jQuery('#dpbfc_demo').click(function(){ showColorGrid3('dpbfc_input','dpbfc_demo'); });
		
		// save form data to check for modified fields on update
		display_current = getDisplayForm();
	});
</script>

<div class="wrap form">
	
	<div style="float: left; margin-right: 40px;">
		
		<form name="displayform" method="post">
			
			<h2>I am reading &raquo; <?php _e('Display', IAR_TEXTDOMAIN); ?></h2>
			
			<?php if ( $book_data === false ): ?>
				<div id="message" class="error">
					<p>
						<?php _e('<strong>No book selected:</strong> Try to find one using the forms <a href="admin.php?page=iar_search">here</a> to get started.', IAR_TEXTDOMAIN); ?>
					</p>
				</div>
			<?php endif; ?>
			
			<?php if ( $error_code > 0 ) : ?>
					
				<!-- error messages -->
				<div id="message" class="error">
					<p>
						
						<?php if ( $error_code == IAR_ERROR_NO_WIDGET_TITLE ) : ?>
							
							<?php _e('<strong>Missing widget title</strong>: Please enter a title and try again.', IAR_TEXTDOMAIN); ?>
							
						<?php elseif ( $error_code == IAR_ERROR_NO_WIDGET_ITEMS ) : ?>
							
							<?php _e('<strong>Nothing to display</strong>: Set one ore more widget items to be displayed.', IAR_TEXTDOMAIN); ?>
							
						<?php endif; ?>
						
					</p>
				</div>
				
			<?php elseif ( $form_send === true ) : ?>
				
				<!-- success message -->
				<div id="message" class="updated fade">
					<p>
						<?php _e('<strong>Display settings saved</strong>.', IAR_TEXTDOMAIN); ?>
					</p>
				</div>
				
			<?php endif; ?>
			
			<?php if ( $book_data !== false ) : ?>
			<!-- Display -->
			<fieldset>
				<legend><?php _e('Widget', IAR_TEXTDOMAIN); ?></legend>
				
				<!-- Widget Title -->
				<p>
					<label for="widget_title"><?php _e('Widget Title', IAR_TEXTDOMAIN); ?>:</label>
					<input type="text" class="text" name="widget_title" id="widget_title" value="<?php print $prefill['widget_title']; ?>" <?php if ( $book_data !== false ): ?>onKeyUp="updatePreview();"<?php endif; ?>>
				</p>
				
				<?php /*
				<p>
					<?php
						$iAmReading = new iAmReading();
						$themes     = $iAmReading->getThemes();
					?>
					
					<label for="display_theme"><?php _e('Widget Theme', IAR_TEXTDOMAIN); ?>:</label>
					<select name="display_theme" id="display_theme" onChange="checkTheme();">
						<?php
							
							foreach ( $themes as $theme_id => $theme_data ) {
						?>
							<option value="<?php print $theme_id; ?>" <?php print($prefill['display_theme']==$theme_id)?'selected="selected"':'' ?>><?php print $theme_data['Name']; ?></option>
						<?php } ?>
					</select>
					<img class="info-icon" title="header=[<?php _e('Widget Theme', IAR_TEXTDOMAIN); ?>] body=[<?php _e('You can create and use themes since ver. 0.9.9 Most display options will disappear,<br /> when using a theme other than the default one.', IAR_TEXTDOMAIN); ?>]" src="<?php echo IAR_PLUGIN_URL; ?>admin/images/icon-help.png" alt="info" title="" />
				</p>

				<span id="widget_details" <?php if ( $prefill['display_theme'] != 'default' ): ?>style="display: none;"<?php endif; ?>>
				*/ ?>
					
				<input type="hidden" name="display_theme" value="default" />

				<span id="widget_details">
				<!-- Widget Alignment -->
				<p>
					<label for="display_alignment"><?php _e('Widget Alignment', IAR_TEXTDOMAIN); ?>:</label>
					<select name="display_alignment" id="display_alignment" onChange="updatePreview();">
						<?php
							$alignments = array('left' => __('alignment left', IAR_TEXTDOMAIN),
							                    'center' => __('alignment center', IAR_TEXTDOMAIN),
							                    'right' => __('alignment right', IAR_TEXTDOMAIN));
							
							foreach ( $alignments as $this_alignment => $this_name ) {
						?>
							<option value="<?php print $this_alignment; ?>" <?php print($prefill['display_alignment']==$this_alignment)?'selected="selected"':'' ?>><?php print $this_name; ?></option>
						<?php } ?>
					</select>
				</p>
				
				<!-- Items to display -->
				<p>
					<label for="display_items"><?php _e('Widget Items', IAR_TEXTDOMAIN); ?>:</label>
						<input type="checkbox" name="display_cover_image" id="display_cover_image" onClick="checkExtendedOptions('display_cover_image', 'display_cover');" onChange="updatePreview();" value="1" <?php print ($prefill['display_cover_image'] == 1) ? 'checked="checked"' : '' ?> /> <label class="inline" for="display_cover_image"><?php _e('book cover', IAR_TEXTDOMAIN); ?></label> &nbsp;
						<input type="checkbox" name="display_progressbar" id="display_progressbar" onClick="checkExtendedOptions('display_progressbar', 'display_progressbar_opt');" onChange="updatePreview();" value="1" <?php print ($prefill['display_progressbar'] == 1) ? 'checked="checked"' : '' ?> /> <label class="inline" for="display_progressbar"><?php _e('progress bar', IAR_TEXTDOMAIN); ?></label> &nbsp;
						<input type="checkbox" name="display_book_title"  id="display_book_title"  onClick="checkExtendedOptions('display_book_title',  'display_title');" onChange="updatePreview();" value="1" <?php print ($prefill['display_book_title'] == 1) ? 'checked="checked"' : '' ?> /> <label class="inline" for="display_book_title"><?php _e('book title', IAR_TEXTDOMAIN); ?></label>
				</p>
				
				</span>
				
			</fieldset>
			
			<span id="widget_settings" <?php if ( $prefill['display_theme'] != 'default' ): ?>style="display: none;"<?php endif; ?>>
			
			<!-- Display: Book Cover -->
			<fieldset id="display_cover" <?php if($prefill['display_cover_image'] == 0){ ?>style="display: none;"<?php } ?>>
				<legend><?php _e('Book Cover', IAR_TEXTDOMAIN); ?></legend>
					
					<p>
						<label for="display_cover_image_size"><?php _e('Image Size', IAR_TEXTDOMAIN); ?>:</label>
						<select name="display_cover_image_size" id="display_cover_image_size" onChange="updatePreview();">
							<?php
								$sizes = array('small' => __('size small', IAR_TEXTDOMAIN),
								               'medium' => __('size medium', IAR_TEXTDOMAIN),
								               'large' => __('size large', IAR_TEXTDOMAIN));
								
								foreach ( $sizes as $this_size => $this_name ) {
							?>
								<option value="<?php print $this_size; ?>" <?php print($prefill['display_cover_image_size']==$this_size)?'selected="selected"':'' ?>><?php print $this_name; ?> &nbsp;</option>
							<?php } ?>
						</select>
					</p>
					
			</fieldset>
			
			<!-- Display: Book Title -->
			<fieldset id="display_title" <?php if($prefill['display_book_title'] == 0){ ?>style="display: none;"<?php } ?>>
				<legend><?php _e('Book Title', IAR_TEXTDOMAIN); ?></legend>
				
				<!-- Font Family & Size -->
				<p>
					<label for="display_book_title_font_family"><?php _e('Font Family & Size', IAR_TEXTDOMAIN); ?>:</label>
					<select name="display_book_title_font_family" id="display_book_title_font_family" onChange="updatePreview();">
						<?php
							foreach ( $web_fonts as $this_font ) {
						?>
							<option value="<?php print $this_font; ?>" style="font-family:<?php print $this_font; ?>;" <?php print($prefill['display_book_title_font_family']==$this_font)?'selected="selected"':'' ?>><?php print $this_font;?></option>
						<?php } ?>
					</select>
					<select class="auto" name="display_book_title_font_size" id="display_book_title_font_size" onChange="updatePreview();">
						<?php
							$sizes = array(10,12,14,16,18,20,24,36);
							
							foreach ( $sizes as $this_size ) {
						?>
							<option value="<?php print $this_size; ?>" style="font-size:<?php print $this_size; ?>px;" <?php print($prefill['display_book_title_font_size']==$this_size)?'selected="selected"':'' ?>><?php print $this_size; ?></option>
						<?php } ?>
					</select>
				</p>
				
				<!-- Font Color -->
				<p>
					<label><?php _e('Font Color', IAR_TEXTDOMAIN); ?>:</label>
					<input type="text" class="text" name="display_book_title_font_color" id="dbtc_input" value="<?php print $prefill['display_book_title_font_color']; ?>" size="8" onChange="updatePreview();" readonly="readonly">
					<input id="dbtc_demo" class="button-highlighted" style="background-color:<?php print $prefill['display_book_title_font_color']; ?>;" type="text" class="text" size="4" title="<?php _e('click here to choose a color', IAR_TEXTDOMAIN); ?>"><br />
				</p>
				
			</fieldset>
			
			<!-- Display: Progressbar -->
			<fieldset id="display_progressbar_opt" <?php if($prefill['display_progressbar'] == 0){ ?>style="display: none;"<?php } ?>>
				<legend><?php _e('Progressbar', IAR_TEXTDOMAIN); ?></legend>
				
				<!-- Background Color -->
				<p>
					<label><?php _e('Background Color', IAR_TEXTDOMAIN); ?>:</label>
					<input type="text" class="text" name="display_progressbar_color_back" id="dpbcb_input" value="<?php print $prefill['display_progressbar_color_back']; ?>" size="8" onChange="updatePreview();" readonly="readonly">
					<input id="dpbcb_demo" class="button-highlighted" style="background-color:<?php print $prefill['display_progressbar_color_back']; ?>;" type="text" class="text" size="4" title="<?php _e('click here to choose a color', IAR_TEXTDOMAIN); ?>"><br />
				</p>
				
				<!-- Foreground Color -->
				<p>
					<label><?php _e('Foreground Color', IAR_TEXTDOMAIN); ?>:</label>
					<input type="text" class="text" name="display_progressbar_color_front" id="dpbcf_input" value="<?php print $prefill['display_progressbar_color_front']; ?>" size="8" onChange="updatePreview();" readonly="readonly">
					<input id="dpbcf_demo" class="button-highlighted" style="background-color:<?php print $prefill['display_progressbar_color_front']; ?>;" type="text" class="text" size="4" title="<?php _e('click here to choose a color', IAR_TEXTDOMAIN); ?>"><br />
				</p>
				
				<!-- Border Color -->
				<p>
					<label><?php _e('Border Color', IAR_TEXTDOMAIN); ?>:</label>
					<input type="text" class="text" name="display_progressbar_color_border" id="dpbcbo_input" value="<?php print $prefill['display_progressbar_color_border']; ?>" size="8" onChange="updatePreview();" readonly="readonly">
					<input id="dpbcbo_demo" class="button-highlighted" style="background-color:<?php print $prefill['display_progressbar_color_border']; ?>;" type="text" class="text" size="4" title="<?php _e('click here to choose a color', IAR_TEXTDOMAIN); ?>"><br />
				</p>
				
				<hr class="grey">
				
				<!-- Font Family & Size -->
				<p>
					<label for="display_progressbar_font_family"><?php _e('Font Family & Size', IAR_TEXTDOMAIN); ?>:</label>
					<select name="display_progressbar_font_family" id="display_progressbar_font_family"onChange="updatePreview();">
						<?php
							foreach ( $web_fonts as $this_font ) {
						?>
							<option value="<?php print $this_font; ?>" style="font-family:<?php print $this_font; ?>;" <?php print($prefill['display_progressbar_font_family']==$this_font)?'selected="selected"':'' ?>><?php print $this_font;?></option>
						<?php } ?>
					</select>
					<select class="auto" name="display_progressbar_font_size" id="display_progressbar_font_size"onChange="updatePreview();">
						<?php
							$sizes = array(10,12,14,16,18,20,24,36);
							
							foreach ( $sizes as $this_size ) {
						?>
							<option value="<?php print $this_size; ?>" style="font-size:<?php print $this_size; ?>px;" <?php print($prefill['display_progressbar_font_size']==$this_size)?'selected="selected"':'' ?>><?php print $this_size; ?></option>
						<?php } ?>
					</select>
				</p>
				
				<!-- Font Color -->
				<p>
					<label><?php _e('Font Color', IAR_TEXTDOMAIN); ?>:</label>
					<input type="text" class="text" name="display_progressbar_font_color" id="dpbfc_input" value="<?php print $prefill['display_progressbar_font_color']; ?>" size="8" onChange="updatePreview();" readonly="readonly">
					<input id="dpbfc_demo" class="button-highlighted" style="background-color:<?php print $prefill['display_progressbar_font_color']; ?>;" type="text" class="text" size="4" title="<?php _e('click here to choose a color', IAR_TEXTDOMAIN); ?>"><br />
				</p>
				
			</fieldset>
			
			</span><!-- /widget_settings -->
			
			<p class="submit">
				<input type="submit" name="form_send" value="<?php _e('save display settings', IAR_TEXTDOMAIN) ?>" />
			</p>
			
			<?php endif; ?>
			
		</form>
		
	</div>
	
	<?php if ( $book_data !== false ): ?>
	<!-- Preview Box -->
	<div id="theme_preview">
		<ul>
			<li id="i-am-reading" class="iar_sidebar_widget">
				<h2 class="widgettitle"><?php echo $prefill['widget_title']; ?></h2>
				<div id="theme-demo">
					<?php iar_print_html(); ?>
				</div>
			</li>
		</ul>
	</div>
	
	<div class="clear"></div>
	
	<div id="colorpicker301" class="colorpicker301"></div>
	<?php endif; ?>
	
</div>