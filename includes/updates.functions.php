<?php
/**
 * Define the common functions used on the installer and updates.
 *
 * @package		ProjectSend
 * @subpackage	Functions
 */

/** Called on r346 */
function chmod_timthumb()
{
	global $updates_made;
	global $updates_errors;
	global $updates_error_messages;

	$chmods = 0;
	$timthumb_file = ROOT_DIR.'/includes/timthumb/timthumb.php';
	$cache_folder = ROOT_DIR.'/includes/timthumb/cache';
	$index_file = ROOT_DIR.'/includes/timthumb/cache/index.html';
	$touch_file = ROOT_DIR.'/includes/timthumb/cache/timthumb_cacheLastCleanTime.touch';
	if (@chmod($timthumb_file, 0755)) { $chmods++; }
	if (@chmod($cache_folder, 0755)) { $chmods++; }
	if (@chmod($index_file, 0755)) { $chmods++; }
	if (@chmod($touch_file, 0755)) { $chmods++; }

	if ($chmods > 0) {
		$updates_made++;
	}
	
	/** This message is mandatory */
	$updates_errors++;
	if ($updates_errors > 0) {
		$updates_error_messages[] = __("If images thumbnails aren't showing on your client's files lists (even your company logo there and on the branding page) please chmod the includes/timthumb/cache folder to 777 -try both in that order- and then do the same with the 'index.html' and 'timthumb_cacheLastCleanTime.touch' files inside that folder. Then try lowering each file to 644 and see if everything is still working.", 'cftp_admin');
	}
}

function chmod_emails()
{
	global $updates_made;
	global $updates_errors;
	global $updates_error_messages;

	$chmods = 0;
	$emails_folder = ROOT_DIR.'/emails';
	if (@chmod($emails_folder, 0777)) { $chmods++; } else { $updates_errors++; }

	$emails_files = glob($emails_folder."*", GLOB_NOSORT);

	foreach ($emails_files as $emails_file) {
		if(is_file($emails_file)) {
			if (@chmod($emails_file, 0777)) { $chmods++; } else { $updates_errors++; }
		}
	}

	if ($chmods > 0) {
		$updates_made++;
	}
	
	if ($updates_errors > 0) {
		$updates_error_messages[] = __("The chmod values of the emails folder and the html templates inside couldn't be set. If ProjectSend isn't sending notifications emails, please set them manually to 777.", 'cftp_admin');
	}
}
?>