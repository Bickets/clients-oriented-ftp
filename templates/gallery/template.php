<?php
/*
Template name:
Gallery

Background modified from: http://www.artofadambetts.com/weblog/2008/05/black-leather-apple-desktop-background/
Delete icon: http://www.iconfinder.com/icondetails/37519/16/can_delete_trash_icon
*/

$ld = 'cftp_template_gallery'; // specify the language domain for this template
include_once(ROOT_DIR.'/templates/common.php'); // include the required functions for every template

$window_title = __('Gallery','cftp_template_gallery');

/**
 * Filter files by type, only save images.
*/
$img_formats = array('gif','jpg','pjpeg','jpeg','png');
foreach ($my_files as $file) {
	$pathinfo = pathinfo($file['url']);
	$extension = strtolower($pathinfo['extension']);
	if (in_array($extension,$img_formats)) {
		$img_files[] = $file;
	}
}
$count = count($img_files);
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $client_info['name'].' | '.$window_title; ?> | <?php echo SYSTEM_NAME; ?></title>
	<link rel="stylesheet" media="all" type="text/css" href="<?php echo $this_template; ?>main.css" />
	<link rel="shortcut icon" href="<?php echo BASE_URI; ?>/favicon.ico" />
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js" type="text/javascript"></script>
	<link href='http://fonts.googleapis.com/css?family=Sirin+Stencil' rel='stylesheet' type='text/css'>
</head>

<body>

<div id="header">
	<?php if (file_exists(ROOT_DIR.'/img/custom/logo/'.LOGO_FILENAME)) { ?>
		<div id="current_logo">
			<img src="<?php echo $this_template; ?>/timthumb.php?src=<?php echo BASE_URI; ?>img/custom/logo/<?php echo LOGO_FILENAME; ?>&amp;w=<?php echo LOGO_MAX_WIDTH; ?>" alt="" />
		</div>
	<?php } ?>

	<a href="<?php echo BASE_URI; ?>process.php?do=logout" target="_self" id="logout" class="header_button"><?php _e('Logout', 'cftp_admin'); ?></a>
	<a href="<?php echo BASE_URI; ?>upload-from-computer.php" target="_self" id="upload" class="header_button"><?php _e('Upload files', 'cftp_admin'); ?></a>
</div>
	
<div id="content">

	<div class="wrapper">

<?php
	if (!$count) {
		_e('There are no files.','cftp_template_gallery');
	}
	else {
?>
		<ul class="photo_list">
			<?php
				foreach ($img_files as $file) {
					$download_link = BASE_URI.
										'process.php?do=download
										&amp;client='.$this_user.'
										&amp;client_id='.$client_info['id'].'
										&amp;url='.$file['url'].'
										&amp;id='.$file['id'].'
										&amp;origin='.$file['origin'];
					if (!empty($file['group_id'])) {
						$download_link .= '&amp;group_id='.$file['group_id'];
					}
			?>
					<li>
						<h5><?php echo htmlentities($file['name']); ?></h5>
						<div class="img_prev">
							<a href="<?php echo BASE_URI; ?>process.php?do=download&amp;client=<?php echo $this_user; ?>&amp;file=<?php echo $row['url']; ?>" target="_blank">
								<img src="<?php echo $this_template; ?>/timthumb.php?src=<?php echo BASE_URI.UPLOADED_FILES_URL; echo $file['url']; ?>&amp;w=280&amp;h=215&amp;f=2" class="thumbnail" alt="" />
							</a>
						</div>
						<div class="img_data">
							<div class="download_link">
								<a href="<?php echo $download_link; ?>" target="_blank">
									<?php _e('Download original','cftp_template_gallery'); ?>
								</a>
							</div>
						</div>
					</li>
			<?php
				}
			?>
		</ul>
	<?php
	}
	?>

	</div>
</div>

<?php default_footer_info(); ?>

</body>
</html>
<?php $database->Close(); ?>