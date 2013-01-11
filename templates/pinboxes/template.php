<?php
/*
Template name:
PinBoxes

Inspired by the awesome desing of Pinterest!
*/
$ld = 'pinboxes_template'; // specify the language domain for this template
include_once(ROOT_DIR.'/templates/common.php'); // include the required functions for every template

$window_title = __('Available files','pinboxes_template');
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php echo $user_full_name.' | '.$window_title; ?> | <?php echo SYSTEM_NAME; ?></title>
		<link rel="stylesheet" media="all" type="text/css" href="<?php echo $this_template; ?>main.css" />
		<link rel="shortcut icon" href="<?php echo BASE_URI; ?>/favicon.ico" />
		<link href='http://fonts.googleapis.com/css?family=Metrophobic' rel='stylesheet' type='text/css'>
		
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js" type="text/javascript"></script>
		<script type="text/javascript" src="<?php echo $this_template; ?>/js/jquery.masonry.min.js"></script>
		
		<script type="text/javascript">
			$(document).ready(function()
				{
					var $container = $('.photo_list');
					$container.imagesLoaded(function(){
					  $container.masonry({
						itemSelector : '.photo'
					  });
					});
				}
			);
		</script>
	</head>
	
	<body>
		<div id="header">
			<?php if (file_exists(ROOT_DIR.'/img/custom/logo/'.LOGO_FILENAME)) { ?>
				<div id="current_logo">
					<img src="<?php echo $this_template; ?>/timthumb.php?src=<?php echo BASE_URI; ?>img/custom/logo/<?php echo LOGO_FILENAME; ?>&amp;w=300" alt="" />
				</div>
			<?php } ?>
		</div>
		
		<div id="menu">
			<p class="welcome">
				<?php _e('Welcome','pinboxes_template'); ?>, <?php echo $user_full_name; ?>
			</p>
			<ul>
				<li><a href="<?php echo BASE_URI; ?>upload-from-computer.php" target="_self"><?php _e('Upload files', 'cftp_admin'); ?></a></li><li>
				<a href="<?php echo BASE_URI; ?>process.php?do=logout" target="_self"><?php _e('Logout', 'cftp_admin'); ?></a></li>
			</ul>
		</div>
			
		<div id="content">
		
			<div class="wrapper">
		
		<?php
			$count = mysql_num_rows($template_files_sql);
			if (!$count) {
		?>
				<div class="no_files">
					<?php
						_e('There are no files.','pinboxes_template');
					?>
				</div>
		<?php
			}
			else {
		?>
				<div class="photo_list">
				<?php
					while($row = mysql_fetch_array($template_files_sql)) {
				?>
						<div class="photo">
							<div class="photo_int">
								<?php
									/**
									 * Generate the thumbnail if the file is an image.
									 */
									$pathinfo = pathinfo($row['url']);
									$extension = strtolower($pathinfo['extension']);
									$img_formats = array('gif','jpg','pjpeg','jpeg','png');
									if (in_array($extension,$img_formats)) {
								?>
										<div class="img_prev">
											<a href="<?php echo BASE_URI; ?>process.php?do=download&amp;client=<?php echo $this_user; ?>&amp;file=<?php echo $row['url']; ?>" target="_blank">
												<img src="<?php echo $this_template; ?>/timthumb.php?src=<?php echo BASE_URI.UPLOADED_FILES_URL; echo $row['url']; ?>&amp;w=250" alt="<?php echo htmlentities($row['filename']); ?>" />
											</a>
										</div>
								<?php
								} else {
								?>
										<div class="ext_prev">
											<a href="<?php echo BASE_URI; ?>process.php?do=download&amp;client=<?php echo $this_user; ?>&amp;file=<?php echo $row['url']; ?>" target="_blank">
												<h6><?php echo $extension; ?></h6>
											</a>
										</div>
								<?php
								}
								?>
							</div>
							<div class="img_data">
								<h2><?php echo htmlentities($row['filename']); ?></h2>
								<div class="photo_info">
									<?php echo $row['description']; ?>
									<p class="file_size">
										<?php _e('File size:','pinboxes_template'); ?> <strong><?php $this_file = filesize(UPLOADED_FILES_FOLDER.$row['url']); echo format_file_size($this_file); ?></strong>
									</p>
								</div>
								<div class="download_link">
									<a href="<?php echo BASE_URI; ?>process.php?do=download&amp;client=<?php echo $this_user; ?>&amp;file=<?php echo $row['url']; ?>" target="_blank" class="button button_gray">
										<?php _e('Download','pinboxes_template'); ?>
									</a>
								</div>
							</div>
						</div>
				<?php
					}
					?>
				</div>
			<?php
			}
			?>
		
			</div>
	
			<?php default_footer_info(); ?>
	
		</div>
	
	</body>
</html>
<?php $database->Close(); ?>