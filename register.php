<?php

$response = [
	"response" => "400",
	"message" => "Bad request"
];

if (!isset($_POST["username"], $_POST["password"], $_POST["email"], $_POST["fullname"], $_POST["country"], $_POST["genre"]))
	exit(json_encode($response));

include "connect.php";
require "utilities.php";

$username = $_POST["username"];
$password = $_POST["password"];
$email = $_POST["email"];
$fullname = $_POST["fullname"];
$country = intval($_POST["country"]);
$genre = intval($_POST["genre"]);
$response = [
	"response" => "401",
	"message" => "A valid username has to contain at least 6 characters and cannot have any symbols except for '.', '_' and '-'"
];

if (!ValidateUsername($username))
	exit(json_encode($response));

$response["message"] = "A valid password has to contain at least 8 characters. It must include a number, one upper and one lower letter.";

if (!ValidatePassword($password))
	exit(json_encode($response));

$response["message"] = "A valid Email is required";

if (!ValidateEmail($email))
	exit(json_encode($response));

$response["message"] = "A valid Full Name is required";

if (!ValidateName($fullname))
	exit(json_encode($response));

$response["message"] = "A valid Country is required";
$countries = CountriesList();

if ($country < 0 || $country >= count($countries))
	exit(json_encode($response));

$response["message"] = "A valid Favorite Game Genre is required";
$genres = GameGenresList();

if ($genre < 0 || $genre >= count($genres))
	exit(json_encode($response));

$username = SanitizeText($_POST["username"]);
$password = EncryptPassword($_POST["password"]);
$email = SanitizeEmail($_POST["email"]);
$fullname = SanitizeText($_POST["fullname"]);
$country++;
$genre++;

$sql = "SELECT * FROM `users` WHERE `username` = '$username' OR `email` = '$email'";
$query = $conn->query($sql);
$response = [
	"response" => "500",
	"message" => "Error while connecting to our databases",
	"query" => $sql,
	"error" => $conn->error
];

if (!$query)
	exit(json_encode($response));

$response = [
	"response" => "403",
	"message" => "The used Username or Email is already in use!"
];

if ($query->num_rows > 0)
	exit(json_encode($response));

/*
require "mailer.php";

Send Email

if email not sent
	exit a 501 response code
*/

$time = (new DateTime())->format("Y-m-d H:i:s");
$sql = "INSERT INTO `users`(`username`, `password`, `email`, `fullname`, `country`, `fav_game_genre`, `membership_time`) VALUES ('$username', '$password', '$email', '$fullname', $country, $genre, '$time')";
$query = $conn->query($sql);
$response = [
	"response" => "500",
	"message" => "Error while connecting to our databases",
	"query" => $sql,
	"error" => $conn->error
];

if (!$query)
	exit(json_encode($response));

$response = [
	"response" => "200",
	"message" => "Success"
];

exit(json_encode($response));
