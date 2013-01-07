<?php
/**
 * Class that handles all the e-mails that the system can send.
 *
 * Currently there are e-mails defined for the following actions:
 * - A new file has been uploaded.
 * - A new client has been created.
 * - A new system user has been created.
 *
 * @package		ProjectSend
 * @subpackage	Classes
 */

/**
 * Call the file that has the markup for the header and footer
 * of the e-mails.
 */
include_once(ROOT_DIR.'/includes/email-template.php');

/** Define the messages texts */

/** Strings for the "New file uploaded" BY A SUSTEM USER e-mail */
$email_strings_file_by_user = array(
									'subject' => __('New file uploaded for you','cftp_admin'),
									'body' => __('A new file has been uploaded for you to download.','cftp_admin'),
									'body2' => __("If you don't want to be notified about new files, please contact the uploader.",'cftp_admin'),
									'body3' => __('You can access a list of all your files','cftp_admin'),
									'body4' => __('by logging in here','cftp_admin')
								);

/** Strings for the "New file uploaded" BY A CLIENT e-mail */
$email_strings_file_by_client = array(
									'subject' => __('New file uploaded by a client.','cftp_admin'),
									'body' => __('A new file has been uploaded by the client','cftp_admin'),
									'body2' => __("You can manage this client's account and the corresponding files",'cftp_admin'),
									'body3' => __('by logging in here','cftp_admin')
								);


/** Strings for the "New client created" e-mail */
$email_strings_new_client = array(
									'subject' => __('Welcome to ProjectSend','cftp_admin'),
									'body' => __('A new account was created for you. From now on, you can access the files that have been uploaded under your account using the following credentials:','cftp_admin'),
									'body2' => __('You can log in following this link','cftp_admin'),
									'body3' => __('Please contact the administrator if you need further assistance.','cftp_admin'),
									'label_user' => __('Your username','cftp_admin'),
									'label_pass' => __('Your password','cftp_admin')
								);

/**
 * Strings for the "New client" e-mail to the admin
 * on self registration.
 */
$email_strings_new_client_self = array(
									'subject' => __('A new client has registered.','cftp_admin'),
									'body' => __('A new account was created using the self registration form on your site. Registration information:','cftp_admin'),
									'body2' => __('Please log in to activate it.','cftp_admin'),
									'body3' => __('Remember, your new client will not be able to log in until an administrator has approved the account.','cftp_admin'),
									'label_name' => __('Full name','cftp_admin'),
									'label_user' => __('Username','cftp_admin')
								);

/** Strings for the "New system user created" e-mail */
$email_strings_new_user = array(
									'subject' => __('Welcome to ProjectSend','cftp_admin'),
									'body' => __('A new account was created for you. From now on, you can access the system administrator using the following credentials:','cftp_admin'),
									'body2' => __('Access the system panel here','cftp_admin'),
									'body3' => __('Thank you for using ProjectSend.','cftp_admin'),
									'label_user' => __('Your username','cftp_admin'),
									'label_pass' => __('Your password','cftp_admin')
								);


class PSend_Email
{

	var $email_headers = '';
	
	/**
	 * The body of the e-mails is gotten from the html templates
	 * found on the /emails folder.
	 */
	function email_prepare_body($filename)
	{
		global $email_template_header;
		global $email_template_footer;

		$this->get_body = 'emails/'.$filename;
		$this->make_body = $email_template_header;
		$this->make_body .= file_get_contents($this->get_body);
		$this->make_body .= $email_template_footer;
		return $this->make_body;
	}

	/**
	 * Prepare the headers using the information obtained on sys.options.php
	 * (main admin e-mail, the title for this ProjectSend installation,
	 * and the character encoding values).
	 */
	function email_set_headers()
	{
		$this->email_headers = 'From: '.THIS_INSTALL_SET_TITLE.' <'.ADMIN_EMAIL_ADDRESS.'>' . "\n";
		$this->email_headers .= 'Return-Path:<'.ADMIN_EMAIL_ADDRESS.'>' . "\r\n";
		$this->email_headers .= 'MIME-Version: 1.0' . "\n";
		$this->email_headers .= 'Content-type: text/html; charset='.EMAIL_ENCODING."\r\n";
		$this->email_headers .= "Sensitivity: Private\n";
		return $this->email_headers;
	}

	/**
	 * Prepare the body for the "New File by user" e-mail and replace the
	 * tags with the strings values set at the top of this file and the
	 * link to the log in page.
	 */
	function email_new_file_by_user()
	{
		global $email_strings_file_by_user;
		$this->email_body = $this->email_prepare_body('new-file-for-client.html');
		$this->email_body = str_replace(
									array('%SUBJECT%','%BODY1%','%BODY2%','%BODY3%','%BODY4%','%LINK%'),
									array(
										$email_strings_file_by_user['subject'],
										$email_strings_file_by_user['body'],
										$email_strings_file_by_user['body2'],
										$email_strings_file_by_user['body3'],
										$email_strings_file_by_user['body4'],
										BASE_URI
									),
									$this->email_body
								);
		return array(
					'subject' => $email_strings_file_by_user['subject'],
					'body' => $this->email_body
				);
	}

	/**
	 * Prepare the body for the "New File by client" e-mail and replace the
	 * tags with the strings values set at the top of this file and the
	 * link to the log in page.
	 */
	function email_new_file_by_client($client_id)
	{
		global $email_strings_file_by_client;
		$this->client_info = get_client_by_id($client_id);
		$this->email_body = $this->email_prepare_body('new-file-by-client.html');
		$this->email_body = str_replace(
									array('%SUBJECT%','%BODY1%','%BODY2%','%BODY3%','%LINK%'),
									array(
											$email_strings_file_by_client['subject'],
											$email_strings_file_by_client['body'].' <strong>'.$this->client_info['name'].'</strong> ('.$this->client_info['username'].')',
											$email_strings_file_by_client['body2'],
											$email_strings_file_by_client['body3'],
											BASE_URI
									),
									$this->email_body
								);
		return array(
					'subject' => $email_strings_file_by_client['subject'].' '.$this->client_info['name'],
					'body' => $this->email_body
				);
	}

	/**
	 * Prepare the body for the "New Client" e-mail.
	 * The new username and password are also sent.
	 */
	function email_new_client($username,$password)
	{
		global $email_strings_new_client;
		$this->email_body = $this->email_prepare_body('new-client.html');
		$this->email_body = str_replace(
									array('%SUBJECT%','%BODY1%','%BODY2%','%BODY3%','%LBLUSER%','%LBLPASS%','%USERNAME%','%PASSWORD%','%URI%'),
									array(
										$email_strings_new_client['subject'],
										$email_strings_new_client['body'],
										$email_strings_new_client['body2'],
										$email_strings_new_client['body3'],
										$email_strings_new_client['label_user'],
										$email_strings_new_client['label_pass'],
										$username,$password,BASE_URI
										),
									$this->email_body
								);
		return array(
					'subject' => $email_strings_new_client['subject'],
					'body' => $this->email_body
				);
	}

	/**
	 * Prepare the body for the "New Client" self registration e-mail.
	 * The name of the client and username are also sent.
	 */
	function email_new_client_self($username,$fullname)
	{
		global $email_strings_new_client_self;
		$this->email_body = $this->email_prepare_body('new-client-self.html');
		$this->email_body = str_replace(
									array('%SUBJECT%','%BODY1%','%BODY2%','%BODY3%','%LBLNAME%','%LBLUSER%','%FULLNAME%','%USERNAME%','%URI%'),
									array(
										$email_strings_new_client_self['subject'],
										$email_strings_new_client_self['body'],
										$email_strings_new_client_self['body2'],
										$email_strings_new_client_self['body3'],
										$email_strings_new_client_self['label_name'],
										$email_strings_new_client_self['label_user'],
										$fullname,$username,BASE_URI
										),
									$this->email_body
								);
		return array(
					'subject' => $email_strings_new_client_self['subject'],
					'body' => $this->email_body
				);
	}

	/**
	 * Prepare the body for the "New User" e-mail.
	 * The new username and password are also sent.
	 */
	function email_new_user($username,$password)
	{
		global $email_strings_new_user;
		$this->email_body = $this->email_prepare_body('new-user.html');
		$this->email_body = str_replace(
									array('%SUBJECT%','%BODY1%','%BODY2%','%BODY3%','%LBLUSER%','%LBLPASS%','%USERNAME%','%PASSWORD%','%URI%'),
									array(
										$email_strings_new_user['subject'],
										$email_strings_new_user['body'],
										$email_strings_new_user['body2'],
										$email_strings_new_user['body3'],
										$email_strings_new_user['label_user'],
										$email_strings_new_user['label_pass'],
										$username,
										$password,
										BASE_URI
									),
									$this->email_body
								);
		return array(
					'subject' => $email_strings_new_user['subject'],
					'body' => $this->email_body
				);
	}

	/**
	 * Finally, try to send the e-mail and return a status, where
	 * 1 = Message sent OK
	 * 2 = Error sending the e-mail
	 *
	 * Returns custom values instead of a boolean value to allow more
	 * codes in the future, on new validations and functions.
	 */
	function psend_send_email($type,$address,$username = '',$password = '',$client_id = '',$name = '')
	{
		$this->headers = $this->email_set_headers();
		switch($type) {
			case 'new_file_by_user':
				$this->mail_info = $this->email_new_file_by_user();
			break;
			case 'new_file_by_client':
				$this->mail_info = $this->email_new_file_by_client($client_id);
			break;
			case 'new_client':
				$this->mail_info = $this->email_new_client($username,$password);
			break;
			case 'new_client_self':
				$this->mail_info = $this->email_new_client_self($username,$name);
			break;
			case 'new_user':
				$this->mail_info = $this->email_new_user($username,$password);
			break;
		}
		$this->subject = $this->mail_info['subject'];
		$this->body = $this->mail_info['body'];
		if(@mail($address,$this->subject,$this->body,$this->headers)) {
			return 1;
		}
		else {
			return 2;
		}
	}

}

?>