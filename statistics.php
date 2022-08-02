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

if (!ValidateUsername($username))
	exit(json_encode($response));

$username = SanitizeText($username);
$response = [
	"response" => "404",
	"message" => "Username not found",
	"query" => "SELECT * FROM `users` WHERE `username` = '$username'",
	"result" => null
];
$query = $conn->query($response["query"]);
$response["error"] = $conn->error;

if (!$query)
	exit(json_encode($response));

$response = [
	"response" => "200",
	"message" => "Success",
	"result" => [
		"impact" => "0",
		"coins" => "0",
		"level" => "1"
	]
];

exit(json_encode($response));
