<?php

include "connect.php";
include "utilities.php";

if (!isset($_POST) || empty($_POST))
	exit(json_encode(GamesList()));

$response = [
	"response" => "400",
	"message" => "Bad Request. A valid request is required",
	"sql" => "None"
];

if (!isset($_POST["request"]) || $_POST["request"] !== "INSERT" && $_POST["request"] !== "UPDATE" && $_POST["request"] !== "DELETE")
	exit(json_encode($response));

$response["message"] = "Bad Request. Some request fields are missing";

switch ($_POST["request"])
{
	case "INSERT":
		if (!isset($_POST["name"], $_POST["type_id"], $_POST["genre_id"], $_POST["scene_guid"]))
			exit(json_encode($response));

		$name = strip_tags($_POST["name"]);
		$type_id = $_POST["type_id"];
		$genre_id = $_POST["genre_id"];
		$scene_guid = strip_tags($_POST["scene_guid"]);
		$response = [
			"sql" => "SELECT * FROM `games` WHERE `name` = '$name' OR `name` LIKE '$name'",
			"response" => "200",
			"message" => "Success"
		];

		if (!($query = $conn->query($response["sql"])))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;

			break;
		}
		else if ($query->num_rows > 0)
		{
			$response["response"] = "403";
			$response["message"] = "A game named '$name' already exists in the database";

			break;
		}
		
		$response["sql"] = "INSERT INTO `games` (`name`, `type_id`, `genre_id`, `scene_guid`) VALUES ('$name', $type_id, $genre_id, '$scene_guid')";

		if (!$conn->query($response["sql"]))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;
		}

		$response["sql"] = "SELECT * FROM `games` WHERE `name` = '$name' OR `name` LIKE '$name'";

		if (!($query = $conn->query($response["sql"])))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;

			break;
		}
		else if ($query->num_rows < 1)
		{
			$response["response"] = "401";
			$response["message"] = "The game has not been added for some unknown reason";

			break;
		}

		$response["game"] = $query->fetch_assoc();

		break;
		
	case "UPDATE":
		if (!isset($_POST["id"], $_POST["name"], $_POST["type_id"], $_POST["genre_id"], $_POST["scene_guid"]))
			exit(json_encode($response));

		$id = strip_tags($_POST["id"]);
		$name = strip_tags($_POST["name"]);
		$type_id = $_POST["type_id"];
		$genre_id = $_POST["genre_id"];
		$scene_guid = strip_tags($_POST["scene_guid"]);
		$response = [
			"sql" => "SELECT * FROM `games` WHERE `id` = $id",
			"response" => "200",
			"message" => "Success"
		];

		if (!($query = $conn->query($response["sql"])))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;

			break;
		}
		else if ($query->num_rows < 1)
		{
			$response["response"] = "403";
			$response["message"] = "A game with the ID of '$id' does not exist in the database or has been removed";

			break;
		}
		
		$response["sql"] = "UPDATE `games` SET
			`name` = '$name',
			`type_id` = $type_id,
			`genre_id` = $genre_id,
			`scene_guid` = '$scene_guid'
		";

		if (!$conn->query($response["sql"]))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;
		}

		$response["sql"] = "SELECT * FROM `games` WHERE `id` = $id";

		if (!($query = $conn->query($response["sql"])))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;

			break;
		}
		else if ($query->num_rows < 1)
		{
			$response["response"] = "404";
			$response["message"] = "The game cannot be found for some reason";

			break;
		}

		$response["game"] = $query->fetch_assoc();

		break;

	case "DELETE":
		if (!isset($_POST["id"]))
			exit(json_encode($response));

		$id = $_POST["id"];
		$response = [
			"sql" => "SELECT * FROM `games` WHERE `id` = $id",
			"response" => "200",
			"message" => "Success"
		];

		if (!($query = $conn->query($response["sql"])))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;

			break;
		}
		else if ($query->num_rows < 1)
		{
			$response["response"] = "403";
			$response["message"] = "A game with the ID of '$id' does not exist in the database or has been removed already";

			break;
		}
		
		$response["sql"] = "DELETE FROM `games` WHERE `id` = $id";

		if (!$conn->query($response["sql"]))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;
		}

		break;
}

echo json_encode($response);
