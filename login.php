<?php

$response = [
	"response" => "400",
	"message" => "Bad Request"
];

if(!isset($_POST["username"]) || !isset($_POST["password"]))
	exit(json_encode($response));

include "init.php";

$username = ValidateEmail($_POST["username"]) ? SanitizeEmail($_POST["username"]) : SanitizeText($_POST["username"]);
$password = $_POST["password"];
$response = [
	"response" => "403",
	"message" => "Username or Password are invalid"
];

if (!ValidateUsername($username) && !ValidateEmail($username) || !ValidatePassword($password))
	exit(json_encode($response));

$query = $conn->query("SELECT * FROM `users` WHERE `username` = '$username' OR `email` = '$username'");
$response = [
	"response" => "404",
	"message" => "Username or Password are incorrect"
];

if (!$query || $query->num_rows < 1)
	exit(json_encode($response));

$user = $query->fetch_assoc();

if(!password_verify($password, $user["password"]))
	exit(json_encode($response));

$new_code = false;
$user_code = GetLatestUserEmailCode($user["id"], false, $new_code);
$response = [
	"response" => "500",
	"message" => "We've had some errors while connecting to the database",
	"error" => $conn->error
];

if ($user_code === false)
	exit(json_encode($response));

if (isset($_POST["code"]))
{
	$code = $_POST["code"];
	$response = [
		"response" => "402",
		"message" => "Verification code is incorrect"
	];

	if ($code !== $user_code)
		exit(json_encode($response));

	$sql = "DELETE FROM `email_codes` WHERE `user_id` = $user[id]";
	$query = $conn->query($sql);
	$response = [
		"response" => "500",
		"message" => "We've had some errors while connecting to the database",
		"query" => $sql,
		"error" => $conn->error
	];

	if (!$query)
		exit(json_encode($response));
}
else if ($user_code !== null)
{
	if ($new_code)
	{
		$response = [
			"response" => "501",
			"message" => "Sending the confirmation code mail has failed"
		];

		require "mailer.php";

		if (!SendVerificationMail($user["email"], $user["full_name"], $user_code))
			exit(json_encode($response));
	}

	$response = [
		"response" => "402",
		"message" => "Please verify your account! We've sent an activation code to your mail inbox."
	];

	exit(json_encode($response));
}

$response = [
	"response" => "200",
	"message" => "Success"
];

exit(json_encode($response));
