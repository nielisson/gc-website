<?php

function GamesList()
{
	$query = $conn->query("SELECT * FROM `games` ORDER BY `id`");
	$array = [];

	while ($query && $row = $query->fetch_assoc())
		$array[] = $row;

	return $array;
}
function GameGenresList()
{
	$query = $conn->query("SELECT * FROM `game_genres` ORDER BY `name`");
	$array = [];
	
	while ($query && $row = $query->fetch_assoc())
		$array[] = $row["name"];

	return $array;
}
function GameTypesList()
{
	$query = $conn->query("SELECT * FROM `game_types` ORDER BY `name`");
	$array = [];
	
	while ($query && $row = $query->fetch_assoc())
		$array[] = $row["name"];

	return $array;
}
