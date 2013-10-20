<?php
/**
 * Show the form to reset the password.
 *
 * @package		ProjectSend
 *
 */
$allowed_levels = array(9,8,7,0);
require_once('sys.includes.php');

$page_title = __('Lost password','cftp_admin');

include('header-unlogged.php');

	/** The form was submitted */
	if ($_POST) {
		/**
		 * Clean the posted form values.
		 */
		$reset_password_email = encode_html($_POST['reset_password_email']);
	}
	?>

		<h2><?php echo $page_title; ?></h2>

		<div class="container">
			<div class="row">
				<div class="span4 offset4 white-box">
					<div class="white-box-interior">
						<?php
								/**
								 * If the form was submited with errors, show them here.
								 */
								$valid_me->list_errors();
				
								if (isset($new_response)) {
									/**
									 * Show the ok or error message for the email notification.
									 */
									switch ($new_response['email']) {
										case 1:
											$msg = __('An e-mail notification with login information was sent to the specified address.','cftp_admin');
											echo system_message('ok',$msg);
										break;
										case 0:
											$msg = __("E-mail notification couldn't be sent.",'cftp_admin');
											echo system_message('error',$msg);
										break;
									}
								}
								else {
									/**
									 * If not $new_response is set, it means we are just entering for the first time.
									 * Include the form.
									 */
						?>
									<script type="text/javascript">
										$(document).ready(function() {
											$("form").submit(function() {
												clean_form(this);
									
													is_complete(this.add_client_form_name,'<?php echo $validation_no_name; ?>');
									
												// show the errors or continue if everything is ok
												if (show_form_errors() == false) { return false; }
											});
										});
									</script>
									
									<form action="reset-password.php" name="resetpassword" method="post" role="form">
										<fieldset>
											<label class="control-label" for="reset_password_email"><?php _e('E-mail','cftp_admin'); ?></label>
											<input type="text" name="reset_password_email" id="reset_password_email" class="span4" placeholder="<?php _e("Please enter your account's e-mail",'cftp_admin'); ?>" />

											<div class="form_submit_li">
												<button type="submit" name="submit" id="button_login" class="button button_blue button_submit"><?php _e('Continue','cftp_admin'); ?></button>
											</div>
										</fieldset>
									</form>
						<?php
								}
						?>
						<div class="login_form_links">
							<p><a href="<?php echo BASE_URI; ?>" target="_self"><?php _e('Go back to the homepage.','cftp_admin'); ?></a></p>
						</div>

					</div>
				</div>
			</div>
		</div> <!-- container -->
	</div> <!-- main (from header) -->

	<?php default_footer_info(false); ?>

</body>
</html>
<?php
	$database->Close();
	ob_end_flush();
?>