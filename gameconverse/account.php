<?php

include "init.php";

$response = [
	"response" => "400",
	"message" => "Bad request"
];

if (!isset($_POST["request"], $_POST["identifier"]))
	exit(json_encode($_POST));

$identifier = strip_tags($_POST["identifier"]);

switch ($_POST["request"])
{
	case "PROFILE":
		if (!isset($_POST["username"], $_POST["nickname"], $_POST["country"], $_POST["fav_game_genre"]))
			exit(json_encode($response));

		$username = strip_tags($_POST["username"]);
		$nickname = strip_tags($_POST["nickname"]);
		$country = intval($_POST["country"]);
		$fav_game_genre = intval($_POST["fav_game_genre"]);
		$countries = CountriesList();

		if ($country < 1 || $country > count($countries))
			exit(json_encode($response));

		$genres = GameGenresList();

		if ($fav_game_genre < 1 || $fav_game_genre > count($genres))
			exit(json_encode($response));

		$response = [
			"response" => "403",
			"message" => "A valid username is required"
		];

		if (!ValidateUsername($username))
			exit(json_encode($response));

		$response = [
			"response" => "403",
			"message" => "A valid nickname is required"
		];

		if (!ValidateName($nickname))
			exit(json_encode($response));

		$response = [
			"response" => "500",
			"message" => "We've had some internal errors!",
			"query" => "UPDATE `users` SET
				`username` = '$username',
				`nickname` = '$nickname',
				`country` = $country,
				`fav_game_genre` = $fav_game_genre
			WHERE `username` = '$identifier' OR `email` = '$identifier'"
		];
		$query = $conn->query($response["query"]);
		$response["error"] = $conn->error;

		if (!$query)
			exit(json_encode($response));

		$response = [
			"response" => "200",
			"message" => "Success"
		];

		break;

	case "PASSWORD":
		if (!isset($_POST["old_password"], $_POST["new_password"]))
			exit(json_encode($response));

		$old_password = $_POST["old_password"];
		$response = [
			"response" => "404",
			"message" => "User not found",
			"query" => "SELECT * FROM `users` WHERE `username` = '$identifier' OR `email` = '$identifier'"
		];
		$query = $conn->query($response["query"]);
		$response["error"] = $conn->error;

		if (!$query || !($user = $query->fetch_assoc()))
			exit(json_encode($response));

		$response = [
			"response" => "403",
			"message" => "Old password is invalid or incorrect."
		];

		if (!password_verify($old_password, $user["password"]))
			exit(json_encode($response));

		$response = [
			"response" => "403",
			"message" => "A valid password has to contain at least 8 characters. It must include a number, one upper and one lower letter."
		];

		if (!ValidatePassword($_POST["new_password"]))
			exit(json_encode($response));

		$new_password = EncryptPassword($_POST["new_password"]);
		$response = [
			"response" => "500",
			"message" => "We've had some internal errors!",
			"query" => "UPDATE `users` SET
				`password` = '$new_password'
			WHERE `username` = '$identifier' OR `email` = '$identifier'"
		];
		$query = $conn->query($response["query"]);
		$response["error"] = $conn->error;

		if (!$query)
			exit(json_encode($response));

		$response = [
			"response" => "200",
			"message" => "Success"
		];

		break;
}

exit(json_encode($response));
