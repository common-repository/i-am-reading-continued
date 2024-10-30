<div class="wrap form">
	
	<div style="float: left; margin-right: 40px;">
		
		<h2>I am reading &raquo; <?php _e("Book Search", IAR_TEXTDOMAIN); ?></h2>
		
		<?php if ( $error_code > 0 ) : ?>
			
			<!-- error messages -->
			<div id="message" class="error">
				<p>
					
					<?php if ( $error_code == IAR_ERROR_MISSING_KEYS ) : ?>
						
						<?php _e('<strong>Missing Amazon Key(s)</strong>: Please set up your AWS account details <a href="admin.php?page=iar_amazon">here</a>.', IAR_TEXTDOMAIN); ?>
						
					<?php elseif ( $error_code == IAR_ERROR_WRONG_ISBN ) : ?>
						
						<?php echo sprintf(__('<strong>Unknown ISBN</strong>: No books found with ISBN %1$s.', IAR_TEXTDOMAIN), $book_isbn); ?>
						
					<?php elseif ( $error_code == IAR_ERROR_NO_KEYWORDS ) : ?>
						
						<?php _e('<strong>Missing keywords</strong>: Please enter a book title or author.', IAR_TEXTDOMAIN); ?>
						
					<?php elseif ( $error_code == IAR_ERROR_NO_MATCHES ) : ?>
						
						<?php _e('<strong>Nothing found</strong>: There\'s no book with given author / title.', IAR_TEXTDOMAIN); ?>
						
					<?php endif; ?>
					
				</p>
			</div>
			
		<?php elseif ( ($form_send === true) && ($query_type === 'set_book') ) : ?>
			
			<!-- success message -->
			<div id="message" class="updated fade">
				<p>
					<?php _e('<strong>New book set</strong>.', IAR_TEXTDOMAIN); ?>
				</p>
			</div>
			
		<?php endif; ?>
		
		<?php if ( ($query_type !== 'search_keywords') && ($error_code != IAR_ERROR_MISSING_KEYS) ) : ?>
		
		<!-- Find new book by ISBN -->
		<form name="searchform_isbn" method="post" action="admin.php?page=iar_search">
			
		<fieldset>
			<legend><?php _e('Find new book by ISBN', IAR_TEXTDOMAIN); ?></legend>
			
			<!-- Book ISBN -->
			<p>
				<label for="book_isbn"><?php _e('Book ISBN', IAR_TEXTDOMAIN); ?>:</label>
				<input type="text" class="text" name="book_isbn" id="book_isbn" value="<?php echo $book_isbn; ?>">
			</p>
			
		</fieldset>
		
		<p class="submit">
			<input type="hidden" name="query" value="search_isbn">
			<input type="submit" name="form_send" value="<?php _e('search for ISBN', IAR_TEXTDOMAIN) ?>" />
		</p>
		
		</form>
		
		<?php endif; ?>
		
		<?php if ( ($query_type !== 'search_isbn') && ($error_code != IAR_ERROR_MISSING_KEYS) ) : ?>
		
		<!-- Find new book by title / author -->
		<form name="searchform_info" method="post" action="admin.php?page=iar_search">
			
		<fieldset>
			<legend><?php _e('Find new book by Title / Author', IAR_TEXTDOMAIN); ?></legend>
			<!-- Book Title -->
			<p>
				<label for="book_title"><?php _e('Book Title', IAR_TEXTDOMAIN); ?>:</label>
				<input type="text" class="text" name="book_title" id="book_title" value="<?php print $book_title; ?>">
			</p>
			
			<!-- Book Author -->
			<p>
				<label for="book_author"><?php _e('Book Author', IAR_TEXTDOMAIN); ?>:</label>
				<input type="text" class="text" name="book_author" id="book_author" value="<?php print $book_author; ?>">
			</p>
		</fieldset>
		
		<p class="submit">
			<input type="hidden" name="query" value="search_keywords">
			<input type="submit" name="form_send" value="<?php _e('search for title / author', IAR_TEXTDOMAIN) ?>" />
		</p>
		
		</form>
		
		<?php endif; ?>
		
		<?php if($book === false): ?>
		<script type="text/javascript">
			document.searchform_isbn.book_isbn.focus();
		</script>
		<?php endif; ?>
		
		<?php if ( ($form_send === true) && ($query_type !== 'set_book') && ($error_code === 0) ) : ?>
			
			<?php if ( (int)$book_search['item_pages'] > 1 ) : ?>
			<div class="tablenav">
				<div class="tablenav-pages">
					<?php echo $pagination->show(); ?>
				</div>
			</div>
			<?php endif; ?>
			
			<?php foreach ( $book_search as $book ) : if ( is_array($book) ) : ?>
			<form name="current_form_<?php echo $book['isbn']; ?>" action="admin.php?page=iar_current" method="post">
			<fieldset>
				<?php if ( strlen($book['title']) > 60 ) : ?>
					<legend><?php echo substr($book['title'], 0, 57); ?>...</legend>
				<?php else: ?>
					<legend><?php echo $book['title']; ?></legend>
				<?php endif; ?>
				
				<div style="float: left; width: 125px; margin-left: 10px; ">
					<?php if ( $book['images']['medium']['URL'] != '' ) : ?>
						<a href="<?php echo $book['book_link']; ?>" target="_blank"><img style="margin-top: 5px; max-width: 115px;" src="<?php echo $book['images']['medium']['URL']; ?>"></a>
					<?php else: ?>
						<a href="<?php echo $book['book_link']; ?>" target="_blank"><img style="margin-top: 5px; max-width: 115px;" src="<?php print IAR_PLUGIN_URL; ?>admin/images/no-book-cover.gif" alt=""></a>
					<?php endif; ?>
				</div>
				
				<div style="float: left; width: 355px;">
					<p>
						<label><strong><?php _e('Authors', IAR_TEXTDOMAIN); ?>:</strong></label>
						<?php if ( $book['authors_str'] != '' ) : ?>
							<?php echo $book['authors_str']; ?>
						<?php else: ?>
							<?php _e('unknown', IAR_TEXTDOMAIN); ?>
						<?php endif; ?>
					</p>
					
					<?php if ( $book['binding'] != '' ) : ?>
					<p>
						<label><strong><?php _e('Binding', IAR_TEXTDOMAIN); ?>:</strong></label><?php echo $book['binding']; ?>
					</p>
					<?php endif; ?>
					
					<?php if ( $book['public_date'] != '' ) : ?>
					<p>
						<label><strong><?php _e('Published', IAR_TEXTDOMAIN); ?>:</strong></label><?php echo date_i18n('d. F Y', strtotime($book['public_date'])); ?>
					</p>
					<?php endif; ?>
					
					<?php if ( $book['pages_total'] != '' ) : ?>
					<p>
						<label><strong><?php _e('Pages', IAR_TEXTDOMAIN); ?>:</strong></label><?php echo $book['pages_total']; ?>
					</p>
					<?php endif; ?>
					
					<p class="submit" style="margin-left: 10px; margin-top: 10px;">
						<input type="hidden" name="query" value="set_book">
						<input type="hidden" name="book_asin" value="<?php echo $book['asin']; ?>"> 
						
						<?php if ( $book['pages_total'] > 0 ) : ?>
							<input type="submit" name="form_send" value="<?php _e('set as current book', IAR_TEXTDOMAIN) ?>" />
						<?php endif; ?>
					</p>
				</div>
				
				<div class="clear"></div>
				
			</fieldset>
			</form>
			<?php endif; endforeach; ?>
			
			<?php if ( (int)$book_search['item_pages'] > 1 ) : ?>
			<div class="tablenav">
				<div class="tablenav-pages">
					<?php echo $pagination->show(); ?>
				</div>
			</div>
			<?php endif; ?>
			
		<?php endif; ?>
		
	</div>
	
</div>