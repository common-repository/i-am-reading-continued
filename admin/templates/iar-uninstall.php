<div class="wrap form">

	<h2>I am reading &raquo; <?php _e('Uninstall', IAR_TEXTDOMAIN); ?></h2>
	
	<?php if ( $error_code > 0 ) : ?>
			
	<!-- error messages -->
	<div id="message" class="error">
		
		<?php if ( $error_code == IAR_ERROR_NOT_INSTALLED ) : ?>
			
			<p><strong><?php _e('The database tables have been deleted before !', IAR_TEXTDOMAIN); ?></strong></p>
			<p><?php _e('There´s nothing to do here.', IAR_TEXTDOMAIN); ?></p>
			
		<?php endif; ?>
		
	</div>
	
	<?php elseif ( $action_confirmed === false ) : ?>
		
		<div id="message" class="error">
			<p><strong><?php _e('Please read the following information, before clicking the button:', IAR_TEXTDOMAIN); ?></strong></p>
			<p><?php _e('This feature should only be used if you really want to deactivate this plugin and remove all of it\'s stored information from the database.', IAR_TEXTDOMAIN); ?></p>
			<p><?php echo sprintf(__('Please use the %sPlugins%s page for %s normal %s deactivation without losing the plugin\'s setup (Amazon Keys, current book, ...).', IAR_TEXTDOMAIN),'<a href="plugins.php">', '</a>', '<i>', '</i>'); ?></p>
		</div>
		
		<p><strong><?php _e('By clicking the button you agree to', IAR_TEXTDOMAIN); ?></strong></p>
		<ol>
			<li><?php _e('delete all database tables created by this plugin.', IAR_TEXTDOMAIN); ?></li>
			<li><?php echo sprintf(__('deactivate the plugin and jump to %sPlugins%s page.', IAR_TEXTDOMAIN), '<a href="plugins.php">', '</a>'); ?></li>
		</ol>
		
		<p class="spacer"></p>
		
		<form name="uninstform" action="admin.php?page=iar_uninstall" method="post">
			<p class="submit" style="margin-top: 0px; padding: 0px;">
				<input type="submit" name="confirmed" value="<?php _e('Uninstall plugin', IAR_TEXTDOMAIN); ?>">
			</p>
		</form>
		
	<?php else: ?>
		
		<div id="message" class="error">
			<p><?php echo sprintf(__('%s Plugin\'s database tables deleted. %s Redirecting to plugin deactivation ...', IAR_TEXTDOMAIN), '<strong>', '</strong>'); ?></p>
		</div>

		<?php $deactivate_url = html_entity_decode(wp_nonce_url('plugins.php?action=deactivate&plugin=i-am-reading-continued/i-am-reading.php&plugin_status=all&paged=1', 'deactivate-plugin_i-am-reading-continued/i-am-reading.php')); ?>

		<script type="text/javascript">
			function deactivate() {

				document.location = '<?php echo $deactivate_url; ?>';
			}
			
			window.setTimeout('deactivate()', 2000);
		</script>
		
	<?php endif; ?>
	
</div>