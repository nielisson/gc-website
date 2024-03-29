<?php

function ActionsList(int $user_id)
{
	global $conn;

	$query = $conn->query("SELECT * FROM `users_actions` WHERE `user_id` = $user_id");
	$array = [];

	while ($query && $row = $query->fetch_assoc())
		$array[] = $row;

	return $array;
}
function AllActionsList()
{
	global $conn;

	$query = $conn->query("SELECT * FROM `users_actions`");
	$array = [];

	while ($query && $row = $query->fetch_assoc())
		$array[] = $row;

	return $array;
}
function GamesList()
{
	global $conn;

	$query = $conn->query("SELECT * FROM `games` ORDER BY `id`");
	$array = [];

	while ($query && $row = $query->fetch_assoc())
		$array[] = $row;

	return $array;
}
function GameGenresList()
{
	global $conn;

	$query = $conn->query("SELECT * FROM `game_genres` ORDER BY `name`");
	$array = [];
	
	while ($query && $row = $query->fetch_assoc())
		$array[] = $row["name"];

	return $array;
}
function GameTypesList()
{
	global $conn;

	$query = $conn->query("SELECT * FROM `game_types` ORDER BY `name`");
	$array = [];
	
	while ($query && $row = $query->fetch_assoc())
		$array[] = $row["name"];

	return $array;
}
function ItemsList()
{
	global $conn;

	$query = $conn->query("SELECT * FROM `items` ORDER BY `id`");
	$array = [];

	while ($query && $row = $query->fetch_assoc())
		$array[] = $row;

	return $array;
}
function ItemTypesList()
{
	global $conn;

	$query = $conn->query("SELECT * FROM `item_types` ORDER BY `name`");
	$array = [];
	
	while ($query && $row = $query->fetch_assoc())
		$array[] = $row["name"];

	return $array;
}
function CountriesList()
{
	global $conn;

	$query = $conn->query("SELECT * FROM `countries`");
	$array = [];

	while ($row = $query->fetch_assoc())
		$array[] = $row;

	return $array;
}
function QuestsList()
{
	global $conn;

	$query = $conn->query("SELECT * FROM `quests` ORDER BY `id`");
	$array = [];

	while ($query && $row = $query->fetch_assoc())
		$array[] = $row;

	return $array;
}
function DisposableEmailDomains()
{
	global $conn;

	$query = $conn->query("SELECT * FROM `disposable_email_domains` ORDER BY `domain`");
	$array = [];

	while ($row = $query->fetch_assoc())
		$array[] = $row["domain"];

	return $array;
}
function GetLatestUserEmailCode(int $user_id, bool $regenerate, string $type)
{
	global $conn;

	$sql = "SELECT * FROM `email_codes` WHERE `user_id` = $user_id AND `type` = '$type' ORDER BY `time` DESC";
	$query = $conn->query($sql);

	if (!$query)
		return false;

	if (!$regenerate && $query->num_rows < 1)
		return null;

	$code = $query->num_rows > 0 ? $query->fetch_assoc() : [ "time" => "1970-01-01 00:00:00" ];
	$time = (new DateTime())->format("Y-m-d H:i:s");
	$code["time"] = (new DateTime($code["time"]))->add(new DateInterval("PT5M"))->format("Y-m-d H:i:s");

	if ($time >= $code["time"])
	{
		$sql = "DELETE FROM `email_codes` WHERE `user_id` = $user_id AND `type` = '$type'";
		$query = $conn->query($sql);
	
		if (!$query)
			return false;

		$code = RandomString(4, false, false, true, false);
		$sql = "INSERT INTO `email_codes` (`user_id`, `code`, `time`, `type`) VALUES ($user_id, '$code', '$time', '$type')";
		$query = $conn->query($sql);

		if (!$query)
			return false;

		return $code;
	}

	return $code["code"];
}
