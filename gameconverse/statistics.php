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
	"response" => "500",
	"message" => "We've had some internal errors!",
	"query" => "SELECT * FROM `users` WHERE `username` = '$username' OR `email` = '$username'",
	"result" => null
];
$query = $conn->query($response["query"]);
$response["error"] = $conn->error;

if (!$query)
	exit(json_encode($response));

$response = [
	"response" => "404",
	"message" => "Username not found",
	"result" => null
];

if (!($user = $query->fetch_assoc()))
	exit(json_encode($response));

$response = [
	"response" => "500",
	"message" => "We've had some internal errors!",
	"query" => "SELECT * FROM `users_items` WHERE `user_id` = $user[id]",
	"result" => null
];
$query = $conn->query($response["query"]);
$response["error"] = $conn->error;

if (!$query)
	exit(json_encode($response));

$user_items = [];

while ($row = $query->fetch_assoc())
	$user_items[] = $row;

$items = ItemsList();
$bought_items = [];

foreach ($user_items as $user_item)
{
	$item_id = intval($user_item["item_id"]);
	$item = null;

	foreach ($items as $i)
		if (intval($i["id"]) === $item_id)
		{
			$item = $i;

			break;
		}

	if (!$item)
		continue;

	if (intval($item["price"]) > 0)
	{
		$bought_items[] = $item["id"];

		continue;
	}
}

if (isset($_POST["new_action"], $_POST["coins"], $_POST["xp"], $_POST["impact"], $_POST["tickets"]))
{
	$action_type = $_POST["new_action"];
	$item_id = isset($_POST["item_id"]) ? $_POST["item_id"] : null;
	$coins = $_POST["coins"];
	$xp = $_POST["xp"];
	$impact = $_POST["impact"];
	$tickets = $_POST["tickets"];

	$response = [
		"response" => "500",
		"message" => "We've had some internal errors!",
		"query" => "INSERT INTO `users_actions`
		(
			`user_id`,
			`type_id`," .
			($item_id ? "`item_id`," : "")
			. "`coins`,
			`xp`,
			`impact`,
			`tickets`
		) VALUES (
			$user[id],
			$action_type," .
			($item_id ? "$item_id," : "")
			. "$coins,
			$xp,
			$impact,
			$tickets	
		)",
		"result" => null
	];
	$query = $conn->query($response["query"]);
	$response["error"] = $conn->error;
	
	if (!$query)
		exit(json_encode($response));
}

$actions = ActionsList(intval($user["id"]));
$nickname = $user["nickname"];
$coins = 0;
$xp = 0;
$impact = 0;
$tickets = 0;

foreach ($actions as $action)
{
	$coins += intval($action["coins"]);
	$xp += intval($action["xp"]);
	$impact += intval($action["impact"]);
	$tickets += intval($action["tickets"]);
}

$response = [
	"response" => "200",
	"message" => "Success",
	"result" => [
		"nickname" => $nickname,
		"coins" => "$coins",
		"xp" => "$xp",
		"impact" => "$impact",
		"tickets" => "$tickets",
		"bought_items" => $bought_items
	]
];

exit(json_encode($response));
