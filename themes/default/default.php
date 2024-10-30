<?php
/*
Theme Name:  Default
Theme URI:   http://i-am-reading.ginchen.de
Description: Default theme with all available options
Version:     1.0
Author:      Dominik Hanke
Author URI:  http://www.bitte-nicht-lesen.de/
*/
?>
<style type="text/css">
	#iar_widget_box {
		<?php if ( $this->config['display_alignment'] == 'right' ) : ?>float: right;<?php else: ?>
			float: none;
		<?php endif; ?>
		<?php if ( $this->config['display_alignment'] == 'center' ) : ?>text-align: center;<?php endif; ?>
	}
	
	#iar_widget_table_top {
		<?php if ( $this->config['display_alignment'] == 'center' ) : ?>margin: 0px auto;<?php endif; ?>
	}
	
	#iar_cover_img_cell { padding-bottom: 3px; }
	#iar_cover_img {
		<?php if ($this->config['display_cover_image'] == 0): ?>display: none; <?php endif; ?>
		max-width:
		<?php if ( $this->config['display_cover_image_size'] == 'small' ) : ?>50px;
		<?php elseif ( $this->config['display_cover_image_size'] == 'medium' ) : ?>115px;
		<?php elseif ( $this->config['display_cover_image_size'] == 'large' ) : ?>215px;<?php endif; ?>
	}
	
	#iar_progressbar {
		height:           5px;
		<?php if ( $this->config['display_progressbar_color_border'] != '' ) : ?>;border: 1px solid <?php print $this->config['display_progressbar_color_border']; ?>;<?php endif; ?>
		background-color: <?php print $this->config['display_progressbar_color_back']; ?>;
		font:             1px Arial,Verdana,sans-serif;
	}
	
	#iar_progressbar_graph { <?php if ($this->config['display_progressbar'] == 0): ?>display: none; <?php endif; ?> }
	#iar_progressbar_pages { <?php if ($this->config['display_progressbar'] == 0): ?>display: none; <?php endif; ?> }
	
	#iar_progressbar_inner {
		position:         relative;
		height:           5px;
		width:            <?php if ( $this->config['pages_read'] >= $this->book_data['pages_total'] ) { print '100'; } else { print round(($this->config['pages_read']/$this->book_data['pages_total'])*100); } ?>%;
		background-color: <?php print $this->config['display_progressbar_color_front']; ?>;
		font:             1px Arial,Verdana,sans-serif;
		float:            left;
	}
	
	#iar_progressbar_text {
		padding-top: 4px;
		font:        <?php print $this->config['display_progressbar_font_size']; ?>px <?php print $this->config['display_progressbar_font_family']; ?>;
		color:       <?php print $this->config['display_progressbar_font_color']; ?>;
		text-align:  left;
		max-width:   200px;
	}
	
	#iar_widget_bottom { padding-top: 5px; }
	
	#iar_book_title {
		<?php if ($this->config['display_book_title'] == 0): ?>display: none; <?php endif; ?>
		font:  <?php print $this->config['display_book_title_font_size']; ?>px <?php print $this->config['display_book_title_font_family']; ?>;
		color: <?php print $this->config['display_book_title_font_color']; ?>;
	}
</style>

<div id="iar_widget_box">
	
	<?php if ( ($this->config['display_cover_image'] == 1) || ($this->config['display_progressbar'] == 1) || (is_admin()) ) : ?>
	<table id="iar_widget_table_top" cellspacing="0" cellpadding="0">
		<?php if ( ($this->config['display_cover_image'] == 1) || (is_admin()) ) : ?>
		<tr>
			<td id="iar_cover_img_cell">
				<?php if ( $this->config['display_amazon_link'] == 1 ): ?>
					<a href="<?php print $this->book_data['book_link']; ?>" target="_blank"><img id="iar_cover_img" src="<?php print $this->book_data['images'][$this->config['display_cover_image_size']]['URL']; ?>" style="border: none;" alt="<?php print $this->book_data['title']; ?>" title="<?php print $this->book_data['title']; ?>"></a><br />
				<?php else: ?>
					<img id="iar_cover_img" src="<?php print $this->book_data['images'][$this->config['display_cover_image_size']]['URL']; ?>" style="border: none;" alt="<?php print $this->book_data['title']; ?>" title="<?php print $this->book_data['title']; ?>" /><br />
				<?php endif; ?>
			</td>
		</tr>
		<?php endif; ?>
		<?php if ( ($this->config['display_progressbar'] == 1) || (is_admin()) ) : ?>
		<tr id="iar_progressbar_graph">
			<td id="iar_progressbar">
				<div id="iar_progressbar_inner"></div>
			</td>
		</tr>
		<tr id="iar_progressbar_pages">
			<td>
				<div id="iar_progressbar_text">
					<?php echo $this->config['pages_read']; ?> / <?php print $this->book_data['pages_total']; ?> <?php _e('Pages', IAR_TEXTDOMAIN); ?>
				</div>
			</td>
		</tr>
		<?php endif; ?>
	</table>
	<?php endif; ?>
	
	<?php if ( ($this->config['display_book_title'] == 1) || (is_admin()) ) : ?>
	<div id="iar_widget_bottom">
		<span id="iar_book_title"><?php print $this->book_data['title']; ?></span>
	</div>
	<?php endif; ?>
	
</div>
<div style="clear:both;"></div>

<?php if ( is_admin() ) : ?>
	<span style="display: none;" id="cover_img_small"><?php print $this->book_data['images']['small']['URL']; ?></span>
	<span style="display: none;" id="cover_img_medium"><?php print $this->book_data['images']['medium']['URL']; ?></span>
	<span style="display: none;" id="cover_img_large"><?php print $this->book_data['images']['large']['URL']; ?></span>
<?php endif; ?>