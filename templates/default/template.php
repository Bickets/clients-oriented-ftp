<?php
/*
Template name:
Default
*/

$ld = 'cftp_template'; // specify the language domain for this template
include_once(ROOT_DIR.'/templates/common.php'); // include the required functions for every template

$window_title = __('File downloads','cftp_template');

include_once(ROOT_DIR.'/header.php'); // include the required functions for every template
?>

<div id="wrapper">

	<script type="text/javascript">
		$(document).ready(function()
			{
				$("#files_list")
					.tablesorter( {
						sortList: [[0,1]], widgets: ['zebra'], headers: {
							4: { sorter: false },
							5: { sorter: false },
							6: { sorter: false }
						}
				})
				.tablesorterPager({container: $("#pager")})
			}
		);
	</script>

	<div id="left_column">
		<div id="current_logo">
			<img src="<?php echo $this_template; ?>/timthumb.php?src=<?php echo BASE_URI; ?>img/custom/logo/<?php echo LOGO_FILENAME; ?>&amp;w=250" alt="" />
		</div>
	</div>

	<div id="right_column">
	
		<?php
			$count = mysql_num_rows($template_files_sql);
			if (!$count) {
				_e('There are no files.','cftp_template');
			}
			else {
		?>

			<table id="files_list" class="tablesorter">
			<thead>
				<tr>
					<th><?php _e('Uploaded date','cftp_template'); ?></th>
					<th><?php _e('Name','cftp_template'); ?></th>
					<th><?php _e('Description','cftp_template'); ?></th>
					<th><?php _e('Size','cftp_template'); ?></th>
					<th><?php _e('Image preview','cftp_template'); ?></th>
					<th><?php _e('Download','cftp_template'); ?></th>
				</tr>
			</thead>
			<tbody>
			
			<?php
				while($row = mysql_fetch_array($template_files_sql)) {
			?>
			
				<tr>
					<td><?php echo date(TIMEFORMAT_USE,$row['timestamp']); ?></td>
					<td><strong><?php echo htmlentities($row['filename']); ?></strong></td>
					<td><?php echo htmlentities($row['description']); ?></td>
					<td><?php $this_file = filesize(UPLOADED_FILES_FOLDER.$row['url']); echo format_file_size($this_file); ?></td>
					<td>
						<?php
							$pathinfo = pathinfo($row['url']);
							$extension = strtolower($pathinfo['extension']);
							if (
								$extension == "gif" ||
								$extension == "jpg" ||
								$extension == "pjpeg" ||
								$extension == "jpeg" ||
								$extension == "png"
							) {
						?>
							<img src="<?php echo $this_template; ?>/timthumb.php?src=<?php echo BASE_URI.UPLOADED_FILES_URL; echo $row['url']; ?>&amp;w=<?php echo THUMBS_MAX_WIDTH; ?>" class="thumbnail" alt="" />
						<?php } ?>
					</td>
					<td>
						<a href="<?php echo BASE_URI; ?>process.php?do=download&amp;client=<?php echo $this_user; ?>&amp;file=<?php echo $row['url']; ?>" target="_blank" class="button button_blue">
							<?php _e('Download','cftp_template'); ?>
						</a>
					</td>
				</tr>
			
			<?php
				}
			}
			?>
	
				</tbody>
			</table>

<?php if ($count > 10) { ?>
	<div id="pager" class="pager">
		<form>
			<input type="button" class="first pag_btn" value="<?php _e('First','cftp_template'); ?>" />
			<input type="button" class="prev pag_btn" value="<?php _e('Prev.','cftp_template'); ?>" />
			<span><strong><?php _e('Page','cftp_template'); ?></strong>:</span>
			<input type="text" class="pagedisplay" disabled="disabled" />
			<input type="button" class="next pag_btn" value="<?php _e('Next','cftp_template'); ?>" />
			<input type="button" class="last pag_btn" value="<?php _e('Last','cftp_template'); ?>" />
			<span><strong><?php _e('Show','cftp_template'); ?></strong>:</span>
			<select class="pagesize">
				<option selected="selected" value="10">10</option>
				<option value="20">20</option>
				<option value="30">30</option>
				<option value="40">40</option>
			</select>
		</form>
	</div>
<?php } else {?>
	<div id="pager">
		<form>
			<input type="hidden" value="<?php echo $count; ?>" class="pagesize" />
		</form>
	</div>
<?php } ?>

	</div> <!-- right_column -->


</div> <!-- wrapper -->

<?php default_footer_info(); ?>

</body>
</html>
<?php $database->Close(); ?>