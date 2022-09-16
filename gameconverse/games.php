<?php

/*if (isset($_GET))
	$_POST = $_GET;*/

include "init.php";

if (!isset($_POST) || empty($_POST))
	exit(json_encode(GamesList()));

$response = [
	"response" => "400",
	"message" => "Bad Request. A valid request is required",
	"sql" => "None"
];

if (!isset($_POST["request"]) || $_POST["request"] !== "BUY" && $_POST["request"] !== "INSERT" && $_POST["request"] !== "UPDATE" && $_POST["request"] !== "DELETE")
	exit(json_encode($response));

$response["message"] = "Bad Request. Some request fields are missing";

switch ($_POST["request"])
{
	case "BUY":
		if (!isset($_POST["username"], $_POST["game_id"]))
			exit(json_encode($response));

		$username = $_POST["username"];
		$response = [
			"query" => "SELECT * FROM `users` WHERE `username` = '$username' OR `email` = '$username'",
			"response" => "200",
			"message" => "Success"
		];

		if (!($query = $conn->query($response["query"])))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;

			break;
		}
		else if (!($user = $query->fetch_assoc()))
		{
			$response["response"] = "404";
			$response["message"] = "Username doesn't exist";

			break;
		}

		$game_id = $_POST["game_id"];
		$response["query"] = "INSERT INTO `users_games`
		(
			`user_id`,
			`game_id`
		)
		VALUES
		(
			$user[id],
			$game_id
		)";

		if (!($query = $conn->query($response["query"])))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;

			break;
		}

		break;

	case "INSERT":
		if (!isset($_POST["name"], $_POST["description"], $_POST["type_id"], $_POST["genre_id"], $_POST["icon_path"], $_POST["price"], $_POST["scene_path"]))
			exit(json_encode($response));

		$name = addslashes(strip_tags($_POST["name"]));
		$description = empty($_POST["description"]) ? "NULL" : "'" . addslashes(strip_tags($_POST["description"])) . "'";
		$type_id = $_POST["type_id"];
		$genre_id = $_POST["genre_id"];
		$icon_path = $type_id < 2 ? "NULL" : "'$_POST[icon_path]'";
		$price = $type_id < 2 ? "NULL" : $_POST["price"];
		$scene_path = strip_tags($_POST["scene_path"]);
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
		
		$response["sql"] = "INSERT INTO `games` (`name`, `description`, `type_id`, `genre_id`, `icon_path`, `price`, `scene_path`) VALUES ('$name', $description, $type_id, $genre_id, $icon_path, $price, '$scene_path')";

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
		if (!isset($_POST["id"], $_POST["name"], $_POST["description"], $_POST["type_id"], $_POST["genre_id"], $_POST["icon_path"], $_POST["price"], $_POST["scene_path"]))
			exit(json_encode($response));

		$id = $_POST["id"];
		$name = addslashes(strip_tags($_POST["name"]));
		$description = empty($_POST["description"]) ? "NULL" : "'" . addslashes(strip_tags($_POST["description"])) . "'";
		$type_id = $_POST["type_id"];
		$genre_id = $_POST["genre_id"];
		$icon_path = empty($_POST["icon_path"]) ? "NULL" : "'$_POST[icon_path]'";
		$price = $type_id < 2 ? "NULL" : $_POST["price"];
		$scene_path = strip_tags($_POST["scene_path"]);
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
			`description` = $description,
			`type_id` = $type_id,
			`genre_id` = $genre_id,
			`icon_path` = $icon_path,
			`price` = $price,
			`scene_path` = '$scene_path'
		WHERE `id` = $id";

		if (!$conn->query($response["sql"]))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;

			break;
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
