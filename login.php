<?php

$response = [
	"response" => "400",
	"message" => "Bad request"
];

/*if (isset($_GET))
	$_POST = $_GET;*/

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
$code_types = ["verification", "password_reset"];
$code_iterator = 0;
$user_code = null;

while ($user_code === null && $code_iterator < count($code_types))
{
	$code_type = $code_types[$code_iterator];
	$user_code = GetLatestUserEmailCode($user["id"], false, $new_code, $code_type);

	$code_iterator++;
}

unset($code_types, $code_iterator);

$response = [
	"response" => "500",
	"message" => "We've had some errors while connecting to the database",
	"error" => $conn->error
];

if ($user_code === false)
	exit(json_encode($response));

if ($user_code !== null && isset($_POST["code"]))
{
	$code = $_POST["code"];
	$response = [
		"response" => "402",
		"message" => ucwords(str_replace("_", " ", $code_type)) . " code is incorrect"
	];

	if ($code !== $user_code)
		exit(json_encode($response));

	$sql = "DELETE FROM `email_codes` WHERE `user_id` = $user[id] WHERE `type` = '$code_type'";
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
			"message" => "Sending the activation code mail has failed"
		];

		require "mailer.php";

		switch ($code_type)
		{
			case "password_reset":
				if (!SendPasswordResetMail($user["email"], $user["full_name"], $user_code))
					exit(json_encode($response));

				break;

			default:
				if (!SendVerificationMail($user["email"], $user["full_name"], $user_code))
					exit(json_encode($response));

				break;
		}
	}

	$response = [
		"response" => "402",
		"message" => "Please verify your identity! We've sent an activation code to your mail inbox."
	];

	exit(json_encode($response));
}

$response = [
	"response" => "200",
	"message" => "Success"
];

exit(json_encode($response));
