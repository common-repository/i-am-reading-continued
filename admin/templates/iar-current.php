<div class="wrap form">
	
	<div style="float: left; margin-right: 40px;">
		
		<h2>I am reading &raquo; <?php _e('Current Book', IAR_TEXTDOMAIN); ?></h2>
		
		<?php if ( $error_code > 0 ) : ?>
				
			<!-- error messages -->
			<div id="message" class="error">
				<p>
					
					<?php if ( $error_code == IAR_ERROR_NO_BOOK ) : ?>
						
						<?php _e('<strong>No book selected:</strong> Try to find one using the forms <a href="admin.php?page=iar_search">here</a> to get started.', IAR_TEXTDOMAIN); ?>
					
					<?php elseif ( $error_code == IAR_ERROR_NO_BOOK_TITLE ) : ?>
						
						<?php _e('<strong>Missing book title</strong>: Please enter a title and try again.', IAR_TEXTDOMAIN); ?>
						
					<?php elseif ( $error_code == IAR_ERROR_WRONG_PAGES_READ_VALUE ) : ?>
						
						<?php _e('<strong>Wrong current page value</strong>: Please enter a valid number.', IAR_TEXTDOMAIN); ?>
						
					<?php elseif ( $error_code == IAR_ERROR_HIGH_PAGES_READ_VALUE ) : ?>
						
						<?php _e('<strong>Wrong current page value</strong>: Please enter a number <= pages total.', IAR_TEXTDOMAIN); ?>
						
					<?php elseif ( $error_code == IAR_ERROR_NO_ASIN ) : ?>
						
						<?php _e('<strong>Missing ASIN</strong>: Can\'t read book information from Amazon.', IAR_TEXTDOMAIN); ?>
						
					<?php endif; ?>
					
				</p>
			</div>
			
		<?php elseif ( ($form_send === true) && ($error_code === 0) ) : ?>
			
			<!-- success message -->
			<div id="message" class="updated fade">
				<p>
					<?php if ( $query_type == 'update_current' ) : ?>
						<?php _e('<strong>Book settings saved</strong>.', IAR_TEXTDOMAIN); ?>
					<?php else: ?>
						<?php echo sprintf(__('<strong>New book \'%s\' set as current</strong>.', IAR_TEXTDOMAIN), $book['title']); ?>
					<?php endif; ?>
				</p>
			</div>
			
		<?php endif; ?>
		
		<?php if ( $error_code != IAR_ERROR_NO_BOOK ) : ?>
		
		<form name="currentform" method="post" action="admin.php?page=iar_current">
		
		<!-- Current Book -->
		<fieldset style="float: left;">
			<legend><?php _e('Currently Reading', IAR_TEXTDOMAIN); ?></legend>
			
			<!-- Book Title -->
			<p>
				<label for="book_title"><?php _e('Book Title', IAR_TEXTDOMAIN); ?>:</label>
				<input type="text" class="text" name="book_title" id="book_title" value="<?php print $book['title']; ?>">
			</p>
			
			<!-- Current Page -->
			<p>
				<label for="pages_read"><?php _e('Current Page', IAR_TEXTDOMAIN); ?>:</label>
				<input type="text" class="text" name="pages_read" id="pages_read" value="<?php print $book['pages_read']; ?>"> / <?php echo $book['pages_total']; ?>
			</p>
			
		</fieldset>
		
		<div style="float: left; margin: 10px 0px 0px 20px;">
			<a href="<?php echo $book['book_link']; ?>" target="_blank"><img class="cover-small" src="<?php echo $book['images']['small']['URL']; ?>" alt=""></a>
		</div>
		
		<div class="clear"></div>
		
		<p class="submit" style="margin-top: 0;">
			<input type="hidden" name="query" value="update_current">
			<input type="submit" name="form_send" value="<?php _e('save book info', IAR_TEXTDOMAIN) ?>" />
		</p>
		
		</form>
		
		<?php endif; ?>
		
	</div>
	
</div>