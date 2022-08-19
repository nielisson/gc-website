<?php

/*if (isset($_GET))
	$_POST = $_GET;*/

include "init.php";

$response = [
	"response" => "400",
	"message" => "Bad request",
	"result" => null
];

if (!isset($_GET["username"]))
	exit(json_encode($response));

$username = $_GET["username"];
$response = [
	"response" => "403",
	"message" => "Username is not valid",
	"result" => null
];

if (!ValidateUsername($username) && !ValidateEmail($username))
	exit(json_encode($response));

$username = SanitizeText($username);
$response = [
	"response" => "404",
	"message" => "Username not found",
	"query" => "SELECT * FROM `users` WHERE `username` = '$username' OR `email` = '$username'",
	"result" => null
];
$query = $conn->query($response["query"]);
$response["error"] = $conn->error;

if (!$query)
	exit(json_encode($response));

$item_types = ItemTypesList();
$user = $query->fetch_assoc();

if (isset($_POST["sprites"]))
{
	$avatar = empty($_POST["sprites"]) ? "NULL" : "'$_POST[sprites]'";
	$response = [
		"response" => "500",
		"message" => "Could not save avatar sprites",
		"query" => "UPDATE `users` SET `avatar` = $avatar WHERE `username` = '$username' OR `email` = '$username'",
		"result" => null
	];
	$query = $conn->query($response["query"]);
	$response["error"] = $conn->error;
	
	if (!$query)
		exit(json_encode($response));

	$user["avatar"] = $_POST["sprites"];
}

$response = [
	"response" => "200",
	"message" => "Success",
	"result" => [
		"sprites" => $user["avatar"]
	]
];

exit(json_encode($response));
