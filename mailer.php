<?php

function SendVerificationMail(string $to, string $name, string $code)
{
	$message = "<b>Hi, $name!</b><br />";
	$message .= "<br />";
	$message .= "We've received your request to join our Game Converse hub!<br />";
	$message .= "Your account activation code is: <b>$code</b><br />";
	$message .= "<br />";
	$message .= "Best Regards,<br />";
	$message .= "The Game Converse team.<br />";
	$message .= "<br />";
	$message .= "<br />";
	$message .= "<br />";

	return SendMail($to, "Account Activation", $message, "noreply@gameconverse.com", true);
}
function SendMail(string $to, string $subject, string $message, string $from, bool $is_html, ?string $reply_to = null)
{
	global $is_localhost;

	if ($is_localhost)
		return true;

	if (empty($reply_to))
		$reply_to = $from;

	$mail_headers =	"From: $from\r\nReply-To: $reply_to\r\n" .
					($is_html ? "Content-type: text/html\r\n" : "") .
					"X-Mailer: PHP/" . phpversion();
	
	return mail($to, $subject, $message, $mail_headers);
}
