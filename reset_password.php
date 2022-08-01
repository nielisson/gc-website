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
	$user_code = GetLatestUserEmailCode($user["id"], true, "password_reset");
	$response["error"] = $conn->error;

	unset($response["query"]);

	if ($user_code === false)
		exit(json_encode($response));

	if (isset($_POST["code"]) && $_POST["code"] === $user_code)
	{
		if (GetLatestUserEmailCode($user["id"], false, "verification"))
		{
			$games = GamesList();
		
			foreach ($games as $game)
			{
				if (intval($game["type_id"]) !== 1)
					continue;
		
				$sql = "INSERT INTO `users_games`(`user_id`, `game_id`) VALUES ($user[id], $game[id])";
				$query = $conn->query($sql);
				$response["error"] = $conn->error;
				$response["query"] = $sql;
		
				if (!$query)
					exit(json_encode($response));
			}

			unset($games);
		}

		$sql =	"DELETE FROM `email_codes` WHERE `user_id` = $user[id]";
		$query = $conn->query($sql);
		$response["query"] = $sql;
		$response["error"] = $conn->error;

		if (!$query)
			exit(json_encode($response));
	}
	else
	{
		require "mailer.php";

		$response = [
			"response" => "501",
			"message" => "Sending the activation code mail has failed"
		];

		if (!SendPasswordResetMail($email, $user["full_name"], $user_code))
			exit(json_encode($response));

		if (isset($_POST["code"]))
		{
			$response = [
				"response" => "402",
				"message" => "Please verify your identity! We've sent an activation code to your mail inbox."
			];
			
			exit(json_encode($response));
		}
	}
}

$response = [
	"response" => "200",
	"message" => "Success"
];

exit(json_encode($response));
