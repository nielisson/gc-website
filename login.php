<?php

$response = [
	"response" => "400",
	"message" => "Bad Request"
];

if(!isset($_POST["username"]) || !isset($_POST["password"]))
	exit(json_encode($response));

include "connect.php";
require "utilities.php";

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

$response = [
	"response" => "200",
	"message" => "Success"
];

exit(json_encode($response));
