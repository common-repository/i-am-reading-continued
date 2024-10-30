<div class="wrap form">
	
	<form name="configform" action="admin.php?page=iar_amazon" method="post">
		
		<h2>I am reading &raquo; Amazon API</h2>
		
		<?php if ( $error_code > 0 ) : ?>
				
			<!-- error messages -->
			<div id="message" class="error">
				<p>
					
					<?php if ( $error_code == IAR_ERROR_NO_ACCESS_KEY_ID ) : ?>
						
						<?php _e('<strong>Missing Access Key ID</strong>: Sign up for a free <a href="https://aws-portal.amazon.com/gp/aws/developer/registration/index.html" target="_blank">Amazon Web Services</a> account to use this plugin.', IAR_TEXTDOMAIN); ?>
						
					<?php elseif ( $error_code == IAR_ERROR_WRONG_ACCESS_KEY_ID ) : ?>
						
						<?php _e('<strong>Wrong Access Key ID</strong>: Check your ID at <a href="https://aws-portal.amazon.com/gp/aws/developer/account/index.html?action=access-key" target="_blank">Amazon Web Services</a> and try again.', IAR_TEXTDOMAIN); ?>
						
					<?php elseif ( $error_code == IAR_ERROR_NO_SECRET_ACCESS_KEY ) : ?>
						
						<?php _e('<strong>Missing Secret Access Key</strong>: Check your Key at <a href="https://aws-portal.amazon.com/gp/aws/developer/account/index.html?action=access-key" target="_blank">Amazon Web Services</a> and try again.', IAR_TEXTDOMAIN); ?>
						
					<?php elseif ( $error_code == IAR_ERROR_WRONG_SECRET_ACCESS_KEY ) : ?>
						
						<?php _e('<strong>Wrong Secret Access Key</strong>: Check your Key at <a href="https://aws-portal.amazon.com/gp/aws/developer/account/index.html?action=access-key" target="_blank">Amazon Web Services</a> and try again.', IAR_TEXTDOMAIN); ?>
						
					<?php endif; ?>
					
				</p>
			</div>
			
		<?php elseif ( $form_send === true ) : ?>
			
			<!-- success message -->
			<div id="message" class="updated fade">
				<p>
					<?php _e('<strong>Configuration saved</strong>.', IAR_TEXTDOMAIN); ?>
				</p>
			</div>
			
		<?php endif; ?>
		
		<!-- Amazon Config -->
		<fieldset id="amazon">
			<legend>Amazon API</legend>
			
			<!-- Country Code -->
			<p>
				<label for="aws_country_code"><?php _e('Amazon Marketplace', IAR_TEXTDOMAIN); ?>:</label>
				<select name="aws_country_code" id="aws_country_code">
					<?php
						$country_codes = array(__('Canada', IAR_TEXTDOMAIN).' (Amazon.ca)' => 'ca',
						                           __('France', IAR_TEXTDOMAIN).' (Amazon.fr)' => 'fr',
						                           __('Germany', IAR_TEXTDOMAIN).' (Amazon.de)' => 'de',
						                           __('Japan', IAR_TEXTDOMAIN).' (Amazon.jp)' => 'jp',
						                           __('United States', IAR_TEXTDOMAIN).' (Amazon.us)' => 'us',
						                           __('United Kingdom', IAR_TEXTDOMAIN).' (Amazon.uk)' => 'uk');
						
						ksort($country_codes);
						
						foreach ( $country_codes as $this_name => $this_code ) {
					?>
						<option value="<?php print $this_code; ?>" <?php print($prefill['aws_country_code']==$this_code)?'selected="selected"':'' ?>><?php print $this_name; ?></option>
					<?php } ?>
				</select>
				<img class="info-icon" title="header=[<?php _e('Amazon Marketplace', IAR_TEXTDOMAIN); ?>] body=[<?php _e('Choose your marketplace to ensure the books you are searching for can be found.<br /> Currently there aren\'t all english books in the german XML service, although they are shown at amazon.de', IAR_TEXTDOMAIN); ?>]" src="<?php print IAR_PLUGIN_URL; ?>admin/images/icon-help.png" alt="info" title="" />
			</p>
			
			<!-- Access Key ID -->
			<p>
				<label for="aws_access_key_id"><?php _e('Access Key ID', IAR_TEXTDOMAIN); ?>:</label> <input type="text" class="text" name="aws_access_key_id" id="aws_access_key_id" value="<?php print $prefill['aws_access_key_id']; ?>">
				<img class="info-icon" title="header=[<?php _e('Access Key ID', IAR_TEXTDOMAIN); ?>] body=[<?php _e('You need to get an Access Key ID for <b>free</b>.<br />The plugin can\'t get the book information from Amazon otherwise.', IAR_TEXTDOMAIN); ?>]" src="<?php print IAR_PLUGIN_URL; ?>admin/images/icon-help.png" alt="info" title="" />
				<a href="https://aws-portal.amazon.com/gp/aws/developer/account/index.html?action=access-key" target="_blank"><img class="info-icon" title="Get a new one or check your Access Key ID" src="<?php print IAR_PLUGIN_URL; ?>admin/images/icon-link.png" alt="link to aws" title="" /></a>
			</p>
			
			<!-- Secret Key -->
			<p>
				<label for="aws_secret_key"><?php _e('Secret Access Key', IAR_TEXTDOMAIN); ?>:</label> <input type="text" class="text" name="aws_secret_key" id="aws_secret_key" value="<?php print $prefill['aws_secret_key']; ?>">
				<img class="info-icon" title="header=[<?php _e('Secret Access Key', IAR_TEXTDOMAIN); ?>] body=[<?php _e('The secret key is needed for Amazon Webservices since August 2009.<br />You can find it in your AWS Account Details.', IAR_TEXTDOMAIN); ?>]" src="<?php print IAR_PLUGIN_URL; ?>admin/images/icon-help.png" alt="info" title="" />
				<a href="https://aws-portal.amazon.com/gp/aws/developer/account/index.html?action=access-key" target="_blank"><img class="info-icon" title="Get a new one or check your Secret Key" src="<?php print IAR_PLUGIN_URL; ?>admin/images/icon-link.png" alt="link to aws" title="" /></a>
			</p>
			
			<!-- Partner ID -->
			<p>
				<label for="aws_associates_id"><?php _e('Amazon Partner ID', IAR_TEXTDOMAIN); ?>:</label> <input type="text" class="text" name="aws_associates_id" id="aws_associates_id" value="<?php print $prefill['aws_associates_id']; ?>">
				<img class="info-icon" title="header=[<?php _e('Amazon Partner ID', IAR_TEXTDOMAIN); ?>] body=[<?php _e('You can get a fee when books get bought by users who used a link with your ID.', IAR_TEXTDOMAIN); ?>]" src="<?php print IAR_PLUGIN_URL; ?>admin/images/icon-help.png" alt="info" title="" />
			</p>
			
			<!-- Partner Link -->
			<p>
				<label for="#"><?php _e('Amazon Partner Link', IAR_TEXTDOMAIN); ?>:</label>
				<input type="radio"  name="display_amazon_link" id="display_amazon_link_yes" value="1" <?php if ( $prefill['display_amazon_link'] == 1 ) { print 'checked'; } ?> /> <label class="inline" for="display_amazon_link_yes"><?php _e('yes', IAR_TEXTDOMAIN); ?></label> &nbsp;
				<input type="radio"  name="display_amazon_link" id="display_amazon_link_no" value="0" <?php if ( $prefill['display_amazon_link'] == 0 ) { print 'checked'; } ?> /> <label class="inline" for="display_amazon_link_no"><?php _e('no', IAR_TEXTDOMAIN); ?></label>
				&nbsp; <img class="info-icon" title="header=[<?php _e('Amazon Partner Link', IAR_TEXTDOMAIN); ?>] body=[<?php _e('You should choose \'yes\' if you\'d like to get a fee by using your Amazon Partner ID.', IAR_TEXTDOMAIN); ?>]" src="<?php print IAR_PLUGIN_URL; ?>admin/images/icon-help.png" alt="info" title="" />
			</p>
		</fieldset>
		
		<p class="submit">
			<input type="submit" name="form_send" value="<?php _e('save settings', IAR_TEXTDOMAIN) ?>" />
		</p>
		
	</form>
	
</div>