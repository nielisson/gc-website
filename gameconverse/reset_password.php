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
		}
		else if (GetLatestUserEmailCode($user["id"], false, "password_reset"))
		{
			if (!isset($_POST["new_password"]))
				goto skip_code_delete;

			$new_password = $_POST["new_password"];

			if (!ValidatePassword($new_password))
			{
				$response = [
					"response" => "401",
					"message" => "A valid password has to contain at least 8 characters. It must include a number, one upper and one lower letter."
				];

				exit(json_encode($response));
			}

			$new_password = EncryptPassword($new_password);
			$sql =	"UPDATE `users` SET `password` = '$new_password' WHERE `email` = '$email'";
			$query = $conn->query($sql);
			$response["query"] = $sql;
			$response["error"] = $conn->error;
	
			if (!$query)
				exit(json_encode($response));
		}

		$sql =	"DELETE FROM `email_codes` WHERE `user_id` = $user[id]";
		$query = $conn->query($sql);
		$response["query"] = $sql;
		$response["error"] = $conn->error;

		if (!$query)
			exit(json_encode($response));
		
	skip_code_delete:
	}
	else
	{
		require "mailer.php";

		$response = [
			"response" => "501",
			"message" => "Sending the activation code mail has failed"
		];

		if (!SendPasswordResetMail($email, $user["nickname"], $user_code))
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
