<?php
/**
 * Generates the zip file for multi downloads.
 *
 * @package		ProjectSend
 */
$allowed_levels = array(9,8,7,0);
require_once('sys.includes.php');
require_once('header.php');

$zip_file = tempnam("tmp", "zip");
$zip = new ZipArchive();
$zip->open($zip_file, ZipArchive::OVERWRITE);

$files_to_zip = explode(',',substr($_GET['file'], 0, -1));

$added_files = 0;

$current_level = get_current_user_level();
$current_username = get_current_user_username();

foreach ($files_to_zip as $file_to_zip) {
	/**
	 * If the file is being generated for a client, make sure
	 * that only files under his account can be added.
	 */
	if ($current_level == 0) {
		$sql = $database->query('SELECT * FROM tbl_files WHERE url="' . $file_to_zip .'"');
		$row = mysql_fetch_array($sql);
		if ($row['client_user'] == $current_username) {
			/** Do not add hidden files */
			if ($row['hidden'] == '0') {
				$allowed_to_zip[] = $file_to_zip;
			}
		}
	}
	else {
		$allowed_to_zip[] = $file_to_zip;
		$added_files++;
	}
}

/** Start adding the files to the zip */
foreach ($allowed_to_zip as $this_allowed_file) {
	$sql_sum = $database->query('UPDATE tbl_files SET download_count=download_count+1 WHERE url="' . $this_allowed_file .'"');
	$zip->addFile(UPLOADED_FILES_FOLDER.$this_allowed_file,$this_allowed_file);
	$added_files++;
}

$zip->close();
if ($added_files > 0) {
	if (file_exists($zip_file)) {
		$zip_file_name = 'download_files_'.generateRandomString();
		header('Content-Type: application/zip');
		header('Content-Length: ' . filesize($zip_file));
		header('Content-Disposition: attachment; filename="'.$zip_file_name.'"');
		readfile($zip_file);
		unlink($zip_file);
	}
}
?>