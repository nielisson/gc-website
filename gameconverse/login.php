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

$code_types = ["verification"/*, "password_reset"*/];
$code_iterator = 0;
$user_code = null;

while ($user_code === null && $code_iterator < count($code_types))
{
	$code_type = $code_types[$code_iterator];
	$user_code = GetLatestUserEmailCode($user["id"], false, $code_type);

	$code_iterator++;
}

unset($code_types, $code_iterator);

$response = [
	"response" => "500",
	"message" => "We've had some errors while connecting to the database",
	"error" => $conn->error,
	"user_code" => $user_code
];

if ($user_code === false)
	exit(json_encode($response));

if ($user_code !== null && isset($_POST["code"]) && $code_type === "verification")
{
	$code = $_POST["code"];
	$response = [
		"response" => "402",
		"message" => ucwords(str_replace("_", " ", $code_type)) . " code is incorrect",
		"user_code" => $response["user_code"]
	];

	if ($code !== $user_code)
		exit(json_encode($response));

	$games = GamesList();
	
	foreach ($games as $game)
	{
		if (intval($game["type_id"]) !== 1)
			continue;

		$sql = "SELECT * FROM `users_games` WHERE `user_id` = $user[id] AND `game_id` = $game[id]";
		$query = $conn->query($sql);
		$response["error"] = $conn->error;
		$response["query"] = $sql;

		if (!$query)
			exit(json_encode($response));

		if ($query->num_rows > 0)
			continue;

		$sql = "INSERT INTO `users_games`(`user_id`, `game_id`) VALUES ($user[id], $game[id])";
		$query = $conn->query($sql);
		$response["error"] = $conn->error;
		$response["query"] = $sql;

		if (!$query)
			exit(json_encode($response));
	}

	unset($games);

	$sql = "DELETE FROM `email_codes` WHERE `user_id` = $user[id] AND `type` = '$code_type'";
	$query = $conn->query($sql);
	$response = [
		"response" => "500",
		"message" => "We've had some errors while connecting to the database",
		"query" => $sql,
		"error" => $conn->error,
		"user_code" => $response["user_code"]
	];

	if (!$query)
		exit(json_encode($response));
}
else if ($user_code !== null)
{
	$response = [
		"response" => "501",
		"message" => "Sending the activation code mail has failed",
		"user_code" => $response["user_code"]
	];

	require "mailer.php";

	switch ($code_type)
	{
		case "password_reset":
			if (!SendPasswordResetMail($user["email"], $user["nickname"], $user_code))
				exit(json_encode($response));

			break;

		default:
			if (!SendVerificationMail($user["email"], $user["nickname"], $user_code))
				exit(json_encode($response));

			break;
	}

	$response = [
		"response" => "402",
		"message" => "Please verify your identity! We've sent an activation code to your mail inbox.",
		"user_code" => $response["user_code"]
	];

	exit(json_encode($response));
}

$response = [
	"response" => "200",
	"message" => "Success",
	"email" => $user["email"]
];

exit(json_encode($response));
