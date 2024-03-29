<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require "phpmailer/src/Exception.php";
require "phpmailer/src/PHPMailer.php";
require "phpmailer/src/SMTP.php";

$verification_mail_subject = "Account Activation";
$password_reset_mail_subject = "Password Reset";
$sender_name_long = "Games Converse Team";
$sender_name_short = "Games Converse";

function SendVerificationMail(string $to, string $name, string $code)
{
	global $verification_mail_subject;
	global $sender_name_long;

	$body = file_get_contents("mailer/email_verification.html");
	$body = str_replace("%01%", $name, $body);
	$body = str_replace("%02%", $code, $body);
	$message = "<b>Hi, $name!</b><br />";
	$message .= "<br />";
	$message .= "We've received your request to join our Game Converse hub!<br />";
	$message .= "Your account activation code is: <b>$code</b><br />";
	$message .= "<br />";
	$message .= "Best Regards,<br />";
	$message .= "The $sender_name_long.<br />";
	$message .= "<br />";
	$message .= "<br />";
	$message .= "<br />";

	return SendMail($to, $name, $verification_mail_subject, $body, $message, "noreply@gamesconverse.space", true);
}
function SendPasswordResetMail(string $to, string $name, string $code)
{
	global $password_reset_mail_subject;
	global $sender_name_long;

	$body = file_get_contents("mailer/password_reset.html");
	$body = str_replace("%01%", $name, $body);
	$body = str_replace("%02%", $code, $body);
	$message = "<b>Hi, $name!</b><br />";
	$message .= "<br />";
	$message .= "We've received your request to reset your account password!<br />";
	$message .= "Your account activation code is: <b>$code</b><br />";
	$message .= "<br />";
	$message .= "If you think there's a problem, please contact us at support@gamesconverse.space<br />";
	$message .= "<br />";
	$message .= "Best Regards,<br />";
	$message .= "The $sender_name_long.<br />";
	$message .= "<br />";
	$message .= "<br />";
	$message .= "<br />";

	return SendMail($to, $name, $password_reset_mail_subject, $body, $message, "noreply@gamesconverse.space", true);
}
function SendMail(string $to, string $name, string $subject, string $body, string $message, string $from, bool $is_html, ?string $reply_to = null)
{
	global $is_localhost;
	global $sender_name_short;

	if ($is_localhost)
		return true;

	if (empty($reply_to))
		$reply_to = $from;

	// PHPMailer SMTP Configurations 
	$mailer_host = "mail.gamesconverse.space";
	$mailer_username = $from;
	$mailer_password = "Gcgc2022*";
	$mailer_secure = $is_localhost ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
	$mailer_port = $is_localhost ? 587 : 465;

	try
	{
		$mail				= new PHPMailer(false);
		$mail->isSMTP();											// Send using SMTP
		$mail->SMTPDebug	= SMTP::DEBUG_OFF;						// Enable verbose debug output
		$mail->Host			= $mailer_host;							// Set the SMTP server to send through
		$mail->SMTPAuth		= true;									// Enable SMTP authentication
		$mail->Username		= $mailer_username;						// SMTP username
		$mail->Password		= $mailer_password;						// SMTP password
		$mail->SMTPSecure	= $mailer_secure;						// Enable TLS encryption `PHPMailer::ENCRYPTION_STARTTLS`; `PHPMailer::ENCRYPTION_SMTPS` is encouraged
		$mail->Port			= $mailer_port;							// Port 587 for TLS; 465 for SSL
		//Recipients
		$mail->Sender		= $from;
		$mail->setFrom($from, $sender_name_short);
		$mail->addReplyTo($reply_to, $sender_name_short);
		$mail->addAddress($to, $name);								// Add a recipient
		//Content
		$mail->isHTML($is_html);									// Set email format to HTML
		$mail->Subject		= $subject;
		$mail->Body			= $body;
		$mail->AltBody		= $message;
		//$mail->addAttachment('images/example.png');
		// SMTP Options
		$mail->SMTPOptions	= [
			"ssl" => [
				"verify_peer" => false,
				"verify_peer_name" => false,
				"allow_self_signed" => true
			]
		];

		//Send Mail
		return $mail->send();
	}
	catch (Exception $e)
	{
		//echo "error";
		//echo "Mail could not be sent. Error: {$mail->ErrorInfo}";
	}

	return false;
}
