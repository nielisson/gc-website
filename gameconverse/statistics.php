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

		break;
	}
}

if (isset($_POST["new_action"], $_POST["coins"], $_POST["xp"], $_POST["impact"], $_POST["tickets"]))
{
	$action_type = $_POST["new_action"];
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
			`type_id`,
			`coins`,
			`xp`,
			`impact`,
			`tickets`
		) VALUES (
			$user[id],
			$action_type,
			$coins,
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
$coins = 0;
$xp = 0;
$level = 0;
$impact = 0;
$tickets = 0;

foreach ($actions as $action)
{
	$coins += intval($action["coins"]);
	$xp += intval($action["xp"]);
	$impact += intval($action["impact"]);
	$tickets += intval($action["tickets"]);
}

if ($xp < 100)
	$level = 0;
else if ($xp < 300)
	$level = 1;
else if ($xp < 750)
	$level = 2;
else if ($xp < 1400)
	$level = 3;
else
{
	$level = 4;

	while ($xp > (100 * pow($level, 2)) - (50 * $level))
		$level++;
}

$response = [
	"response" => "200",
	"message" => "Success",
	"result" => [
		"coins" => "$coins",
		"xp" => "$xp",
		"level" => "$level",
		"impact" => "$impact",
		"tickets" => "$tickets",
		"bought_items" => $bought_items
	]
];

exit(json_encode($response));
