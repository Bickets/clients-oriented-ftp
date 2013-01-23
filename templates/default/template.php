<?php
/*
Template name:
Default
*/

$ld = 'cftp_template'; // specify the language domain for this template
include_once(ROOT_DIR.'/templates/common.php'); // include the required functions for every template

$window_title = __('File downloads','cftp_template');

$tablesorter = 1;
include_once(ROOT_DIR.'/header.php'); // include the required functions for every template

$count = count($my_files);
?>

<div id="wrapper">

	<script type="text/javascript">
		$(document).ready(function() {
			$("#files_list").tablesorter( {
				sortList: [[1,1]], widgets: ['zebra'], headers: {
					0: { sorter: false },
					5: { sorter: false },
					6: { sorter: false }
				}
			})
			.tablesorterPager({container: $("#pager")})

			$("#select_all").click(function(){
				var status = $(this).prop("checked");
				$("td>input:checkbox").prop("checked",status);
			});

			$("#view_reduced").click(function(){
				$(this).addClass('active_view_button');
				$("#view_full").removeClass('active_view_button');
				$(".extra").hide();
			});
			$("#view_full").click(function(){
				$(this).addClass('active_view_button');
				$("#view_reduced").removeClass('active_view_button');
				$(".extra").show();
			});

			$("#do_action").click(function() {
				var checks = $("td>input:checkbox").serializeArray(); 
				if (checks.length == 0) { 
					alert('<?php _e('Please select at least one file to proceed.','cftp_admin'); ?>');
					return false; 
				} 
				else {
					var action = $('#files_actions').val();
					if (action == 'zip') {

						var checkboxes = $.map($('input:checkbox:checked'), function(e,i) {
							if (e.value != '0') {
								return +e.value;
							}
						});

						$('.loading').stop(true, true).fadeIn();

						$.get('<?php echo BASE_URI; ?>process.php', { do:"zip_download", client:"<?php echo $this_user; ?>", files:checkboxes },
							function(data) {
								$('.loading_info').append("<iframe src='<?php echo BASE_URI; ?>process-zip-download.php?file="+data+"'></iframe>");
								$('#loading_close').stop(true, true).fadeIn();
							}
						);
					}
				return false;
				}
			});

			$("#loading_close").click(function() {
				$('.loading').stop(true, true).fadeOut();
				return false;
			});
		});
	</script>

	<div class="loading">
		<div class="loading_info">
			<img src="<?php echo BASE_URI; ?>/img/ajax-loader.gif" alt="Loading" />
			<div class="clear"></div>
			<p><?php _e('Please wait while your download is prepared.','cftp_admin'); ?></p>
			<p><?php _e('This operation could take a few minutes, depending on the size of the files.','cftp_admin'); ?></p>
			<a href="#" id="loading_close"><?php _e('Close this window','cftp_admin'); ?></a>
		</div>
	</div>

	<div id="left_column">
		<div id="current_logo">
			<img src="<?php echo $this_template; ?>/timthumb.php?src=<?php echo BASE_URI; ?>img/custom/logo/<?php echo LOGO_FILENAME; ?>&amp;w=250" alt="" />
		</div>
	</div>

	<div id="right_column">

		<div class="form_actions_left">
			<div class="form_actions_limit_results">
				<form action="" name="files_search" method="post" class="inline_form">
					<input type="text" name="search" id="search" value="<?php if(isset($_POST['search']) && !empty($_POST['search'])) { echo $_POST['search']; } ?>" class="txtfield form_actions_search_box" />
					<input type="submit" id="btn_proceed_search" value="<?php _e('Search','cftp_admin'); ?>" class="button_form" />
				</form>
			</div>
		</div>
	
		<form action="" name="files_list" method="post">
			<div class="form_actions_right">
				<div class="form_actions">
					<div class="form_actions_submit">
						<label><?php _e('Selected files actions','cftp_admin'); ?>:</label>
						<select name="files_actions" id="files_actions" class="txtfield">
							<option value="zip"><?php _e('Download zipped','cftp_admin'); ?></option>
						</select>
						<input type="submit" id="do_action" value="<?php _e('Proceed','cftp_admin'); ?>" class="button_form" />
					</div>
				</div>
			</div>
	
			<div class="right_clear"></div>

			<div class="form_actions_count">
				<p class="form_count_total"><?php _e('Showing','cftp_admin'); ?>: <span><?php echo $count; ?> <?php _e('files','cftp_admin'); ?></span></p>
				<ul id="table_view_modes">
					<li><a href="#" id="view_reduced"><?php _e('View reduced table','cftp_admin'); ?></a></li><li>
						<a href="#" id="view_full" class="active_view_button"><?php _e('View full table','cftp_admin'); ?></a></li>
				</ul>
			</div>

			<div class="right_clear"></div>

			<?php
				if (!$count) {
					if (isset($no_results_error)) {
						switch ($no_results_error) {
							case 'search':
								$no_results_message = __('Your search keywords returned no results.','cftp_admin');;
								break;
						}
					}
					else {
						$no_results_message = __('There are no files available.','cftp_template');;
					}
					echo system_message('error',$no_results_message);
				}
			?>
	
			<table id="files_list" class="tablesorter">
				<thead>
					<tr>
						<th class="td_checkbox">
							<input type="checkbox" name="select_all" id="select_all" value="0" />
						</th>
						<th><?php _e('Name','cftp_template'); ?></th>
						<th><?php _e('Description','cftp_template'); ?></th>
						<th><?php _e('Size','cftp_template'); ?></th>
						<th class="extra"><?php _e('Uploaded date','cftp_template'); ?></th>
						<th class="extra"><?php _e('Image preview','cftp_template'); ?></th>
						<th><?php _e('Download','cftp_template'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
						if ($count > 0) {
							foreach ($my_files as $file) {
					?>
								<tr>
									<td><input type="checkbox" name="files[]" value="<?php echo $file["id"]; ?>" /></td>
									<td><strong><?php echo htmlentities($file['name']); ?></strong></td>
									<td><?php echo htmlentities($file['description']); ?></td>
									<td><?php $this_file = filesize(UPLOADED_FILES_FOLDER.$file['url']); echo format_file_size($this_file); ?></td>
									<td class="extra"><?php echo date(TIMEFORMAT_USE,$file['timestamp']); ?></td>
									<td class="extra">
										<?php
											$pathinfo = pathinfo($file['url']);
											$extension = strtolower($pathinfo['extension']);
											if (
												$extension == "gif" ||
												$extension == "jpg" ||
												$extension == "pjpeg" ||
												$extension == "jpeg" ||
												$extension == "png"
											) {
										?>
											<img src="<?php echo $this_template; ?>/timthumb.php?src=<?php echo BASE_URI.UPLOADED_FILES_URL; echo $file['url']; ?>&amp;w=<?php echo THUMBS_MAX_WIDTH; ?>" class="thumbnail" alt="" />
										<?php } ?>
									</td>
									<td>
										<a href="<?php echo BASE_URI; ?>process.php?do=download&amp;client=<?php echo $this_user; ?>&amp;file=<?php echo $file['url']; ?>" target="_blank" class="button button_blue">
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
		</form>
	
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