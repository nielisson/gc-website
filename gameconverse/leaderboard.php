<?php

include "init.php";

$response = [
	"response" => "400",
	"message" => "Bad request"
];

if (!isset($_GET["username"], $_GET["limit"]))
	exit(json_encode($response));

$limit = intval($_GET["limit"]);

if (is_nan($limit))
	exit(json_encode($response));

$username = $_GET["username"];
$response = [
	"response" => "404",
	"message" => "User not found",
	"query" => "SELECT * FROM `users` WHERE `username` = '$username' OR `email` = '$username'"
];
$query = $conn->query($response["query"]);
$response["error"] = $conn->error;

if (!$query || !($user = $query->fetch_assoc()))
	exit(json_encode($response));

$response = [
	"response" => "500",
	"message" => "We've had an internal error!",
	"query" => "SELECT * FROM `users` WHERE `username` != '$username' AND `email` != '$username' LIMIT $limit"
];
$query = $conn->query($response["query"]);
$response["error"] = $conn->error;

if (!$query)
	exit(json_encode($response));

$users = [$user];
$today = (new DateTime())->format("Y-m-d H:i:s");

while ($user = $query->fetch_assoc())
	$users[] = $user;

for ($i = 0; $i < count($users); $i++)
{
	$user = $users[$i];
	$user["impact"] = 0;
	$user_actions = ActionsList(intval($user["id"]));

	foreach ($user_actions as $action)
		if (($impact = intval($action["coins"])) > 0)
			$user["impact"] += intval($impact);

	$user["is_player"] = $i === 0;
	$user["impact"] = strval(round($user["impact"] / 250.0));
	$users[$i] = $user;
}
	
$response = [
	"response" => "200",
	"message" => "Success",
	"users" => $users
];

exit(json_encode($response));
