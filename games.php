<?php

include "connect.php";
include "utilities.php";

if (!isset($_POST) || empty($_POST))
	exit(json_encode(GamesList()));

switch ($_POST["request"])
{
	case "add":
		$name = $_POST["name"];
		$type_id = $_POST["type_id"];
		$genre_id = $_POST["genre_id"];
		$sql = "INSERT INTO `games` (`name`, `type_id`, `genre_id`) VALUES ('$name', $type_id, $genre_id)";
		$response = [
			"sql" => $sql
		];

		if ($query = $conn->query($sql))
		{
			$response["status"] = 200;
			$response["message"] = "Success";
		}
		else
		{
			$response["status"] = 400;
			$response["error"] = $conn->error;
		}

		break;

	case "remove":

		break;
}
