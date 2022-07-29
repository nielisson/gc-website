<?php

$response = [
	"response" => "400",
	"message" => "Bad request"
];

/*if (isset($_GET))
	$_POST = $_GET;*/

if (!isset($_POST["email"]))
	exit(json_encode($response));

include "init.php";

$email = $_POST["email"];
$response = [
	"response" => "403",
	"message" => "Please enter a valid email address"
];

if (!ValidateEmail($email))
	exit(json_encode($response));

$email = SanitizeEmail($email);
$sql = "SELECT * FROM `users` WHERE `email` = '$email'";
$query = $conn->query($sql);
$response = [
	"response" => "500",
	"message" => "We've had some errors while connecting to the database",
	"query" => $sql,
	"error" => $conn->error
];

if (!$query)
	exit(json_encode($response));

if ($query->num_rows > 0)
{
	$user = $query->fetch_assoc();
	$new_code = false;

	if (GetLatestUserEmailCode($user["id"], false, $new_code, "verification"))
	{
		$sql = "DELETE FROM `email_codes` WHERE `user_id` = $user[id] AND `type` = 'verification'";
		$query = $conn->query($sql);
		$response["query"] = $sql;
		$response["error"] = $conn->error;

		if (!$query)
			exit(json_encode($response));
	}

	$code = GetLatestUserEmailCode($user["id"], true, $new_code, "password_reset");
	$response["error"] = $conn->error;

	unset($response["query"]);

	if ($code === false)
		exit(json_encode($response));

	require "mailer.php";

	$response = [
		"response" => "501",
		"message" => "Sending the activation code mail has failed"
	];

	if (!SendPasswordResetMail($email, $user["full_name"], $code))
		exit(json_encode($response));
}

$response = [
	"response" => "200",
	"message" => "Success"
];

exit(json_encode($response));
