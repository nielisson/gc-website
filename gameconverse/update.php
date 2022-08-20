<?php

$response = [
	"response" => "400",
	"message" => "Bad request"
];

/*if (isset($_GET))
	$_POST = $_GET;*/

// Check if all POST attributes are set
if (!isset($_POST["username"], $_POST["password"], $_POST["email"], $_POST["full_name"], $_POST["country"], $_POST["genre"]))
	exit(json_encode($response));

include "init.php";

// Create registration variables
$username = $_POST["username"];
$password = $_POST["password"];
$email = $_POST["email"];
$full_name = $_POST["full_name"];
$country = intval($_POST["country"]);
$genre = intval($_POST["genre"]);
$response = [
	"response" => "401",
	"message" => "A valid username has to contain at least 6 characters and cannot have any symbols except for '.', '_' and '-'"
];

// Check values before proceeding
if (!ValidateUsername($username))
	exit(json_encode($response));

$response["message"] = "A valid password has to contain at least 8 characters. It must include a number, one upper and one lower letter.";

if (!ValidatePassword($password))
	exit(json_encode($response));

$response["message"] = "A valid Email is required";

if (!ValidateEmail($email))
	exit(json_encode($response));

$response["message"] = "A valid Full Name is required";

if (!ValidateName($full_name))
	exit(json_encode($response));

$response["message"] = "A valid Country is required";
$countries = CountriesList();

if ($country < 0 || $country >= count($countries))
	exit(json_encode($response));

$response["message"] = "A valid Favorite Game Genre is required";
$genres = GameGenresList();

if ($genre < 0 || $genre >= count($genres))
	exit(json_encode($response));

// Santize values to prevent errors
$username = SanitizeText($_POST["username"]);
$password = EncryptPassword($_POST["password"]);
$email = SanitizeEmail($_POST["email"]);
$full_name = SanitizeText($_POST["full_name"]);
// Increase country and genre indexes by one to match the ones in the database
$country++;
$genre++;

// Check if the user exists already or not
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

// Insert user into database
$time = (new DateTime())->format("Y-m-d H:i:s");
$sql = "INSERT INTO `users`(`username`, `password`, `email`, `full_name`, `country`, `fav_game_genre`, `membership_time`) VALUES ('$username', '$password', '$email', '$full_name', $country, $genre, '$time')";
$query = $conn->query($sql);
$response = [
	"response" => "500",
	"message" => "Error while connecting to our databases",
	"error" => $conn->error
];

if (!$query)
	exit(json_encode($response));

// Check if user has been inserted
$sql = "SELECT * FROM `users` WHERE `username` = '$username' AND `membership_time` = '$time'";
$query = $conn->query($sql);
$response["query"] = $sql;
$response["error"] = $conn->error;

if (!$query || $query->num_rows < 1)
	exit(json_encode($response));

// Retrieve user data from database
// Retrieve latest email code for the newly created user
$user = $query->fetch_assoc();
$user_id = $user["id"];
$code = GetLatestUserEmailCode($user_id, true, "verification");
$response["error"] = $conn->error;

unset($response["query"]);

if ($code === false)
	exit(json_encode($response));

// Send verification mail to user
require "mailer.php";

$response = [
	"response" => "501",
	"message" => "Sending the activation code mail has failed"
];

if (!SendVerificationMail($email, $full_name, $code))
{
	// In case of failure delete the user & its own email code from the database
	$conn->query("DELETE FROM `email_codes` WHERE `user_id` = $user_id");
	$conn->query("DELETE FROM `users` WHERE `id` = $user_id");

	exit(json_encode($response));
}

$response = [
	"response" => "200",
	"message" => "Success"
];

exit(json_encode($response));
