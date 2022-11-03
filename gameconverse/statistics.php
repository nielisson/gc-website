<?php

/*if (isset($_GET))
	$_POST = $_GET;*/

include "init.php";

$response = [
	"response" => "400",
	"message" => "Bad request",
	"result" => null
];

if (!isset($_GET["identifier"]))
	exit(json_encode($response));

$identifier = $_GET["identifier"];
$response = [
	"response" => "403",
	"message" => "Username is not valid",
	"result" => null
];

if (!ValidateUsername($identifier) && !ValidateEmail($identifier))
	exit(json_encode($response));

$identifier = SanitizeText($identifier);
$response = [
	"response" => "500",
	"message" => "We've had some internal errors!",
	"query" => "SELECT * FROM `users` WHERE `username` = '$identifier' OR `email` = '$identifier'",
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

$last_login = (new DateTime("now", new DateTimeZone("UTC")))->format("Y-m-d H:i:s");
$response = [
	"response" => "500",
	"message" => "We've had some internal errors!",
	"query" => "UPDATE `users` SET `last_login` = '$last_login' WHERE `id` = $user[id]",
	"result" => null
];
$query = $conn->query($response["query"]);
$response["error"] = $conn->error;

if (!$query)
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

$response = [
	"response" => "500",
	"message" => "We've had some internal errors!",
	"query" => "SELECT * FROM `users_games` WHERE `user_id` = $user[id]",
	"result" => null
];
$query = $conn->query($response["query"]);
$response["error"] = $conn->error;

if (!$query)
	exit(json_encode($response));

$user_games = [];

while ($row = $query->fetch_assoc())
	$user_games[] = $row;

$games = GamesList();
$bought_games = [];

foreach ($user_games as $user_game)
{
	$game_id = intval($user_game["game_id"]);
	$game = null;

	foreach ($games as $g)
		if (intval($g["id"]) === $game_id)
		{
			$game = $g;

			break;
		}

	if ($game === null)
		continue;

	if (!empty($game["price"]) && intval($game["price"]) > 0)
	{
		$bought_games[] = $game["id"];

		continue;
	}
}

if (isset($_POST["new_action"], $_POST["coins"], $_POST["xp"], $_POST["tickets"]))
{
	$action_type = $_POST["new_action"];
	$item_id = isset($_POST["item_id"]) ? $_POST["item_id"] : null;
	$game_id = isset($_POST["game_id"]) ? $_POST["game_id"] : null;
	$coins = $_POST["coins"];
	$xp = $_POST["xp"];
	$tickets = $_POST["tickets"];

	$response = [
		"response" => "500",
		"message" => "We've had some internal errors!",
		"query" => "INSERT INTO `users_actions`
		(
			`user_id`,
			`type_id`," .
			($item_id ? "`item_id`," : "")  .
			($game_id ? "`game_id`," : "")
			. "`coins`,
			`xp`,
			`tickets`
		) VALUES (
			$user[id],
			$action_type," .
			($item_id ? "$item_id," : "") .
			($game_id ? "$game_id," : "")
			. "$coins,
			$xp,
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
$username = $user["username"];
$nickname = $user["nickname"];
$country = $user["country"];
$fav_game_genre = $user["fav_game_genre"];
$coins = 0;
$xp = 0;
$impact = 0;
$tickets = 0;
$impact_negation = 0;

foreach ($actions as $action)
{
	$coins += intval($action["coins"]);
	$xp += intval($action["xp"]);
	$impact += max(intval($action["coins"]), 0);
	$tickets += intval($action["tickets"]);
	$impact_negation += intval($action["impact_negation"]);
}

$impact = round($impact / 250.0) - $impact_negation;
$all_actions = AllActionsList();
$global_impact = 0;
$impact_negation = 0;

foreach ($all_actions as $action)
{
	$global_impact += max(intval($action["coins"]), 0);
	$impact_negation += intval($action["impact_negation"]);
}

$global_impact = max(round($global_impact / 250.0) - $impact_negation, 0);
$response = [
	"response" => "200",
	"message" => "Success",
	"result" => [
		"username" => $username,
		"nickname" => $nickname,
		"country" => $country,
		"fav_game_genre" => $fav_game_genre,
		"coins" => "$coins",
		"xp" => "$xp",
		"impact" => "$impact",
		"global_impact" => "$global_impact",
		"tickets" => "$tickets",
		"bought_items" => $bought_items,
		"bought_games" => $bought_games
	]
];

exit(json_encode($response));
