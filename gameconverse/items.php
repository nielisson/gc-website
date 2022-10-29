<?php

/*if (isset($_GET))
	$_POST = $_GET;*/

include "init.php";

if (!isset($_POST) || empty($_POST))
	exit(json_encode(ItemsList()));

$response = [
	"response" => "400",
	"message" => "Bad Request. A valid request is required",
	"query" => "None"
];

if (!isset($_POST["request"]) || $_POST["request"] !== "BUY" && $_POST["request"] !== "INSERT" && $_POST["request"] !== "UPDATE" && $_POST["request"] !== "DELETE")
	exit(json_encode($response));

$response["message"] = "Bad Request. Some request fields are missing";

switch ($_POST["request"])
{
	case "BUY":
		if (!isset($_POST["username"], $_POST["item_id"]))
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

		$item_id = $_POST["item_id"];
		$response["query"] = "INSERT INTO `users_items`
		(
			`user_id`,
			`item_id`
		)
		VALUES
		(
			$user[id],
			$item_id
		)";

		if (!($query = $conn->query($response["query"])))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;

			break;
		}

		break;

	case "INSERT":
		if (!isset($_POST["name"], $_POST["type_id"], $_POST["sprite_path"], $_POST["icon_path"], $_POST["dependencies"], $_POST["dependency_alternative"], $_POST["price"]))
			exit(json_encode($response));

		$name = addslashes(strip_tags($_POST["name"]));
		$response = [
			"query" => "SELECT * FROM `items` WHERE `name` = '$name' OR `name` LIKE '$name'",
			"response" => "200",
			"message" => "Success"
		];

		if (!($query = $conn->query($response["query"])))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;

			break;
		}
		else if ($query->num_rows > 0)
		{
			$response["response"] = "403";
			$response["message"] = "A item named '$name' already exists in the database";

			break;
		}
		
		$type_id = $_POST["type_id"];
		$sprite_path = $_POST["sprite_path"];
		$sprite_path = empty($sprite_path) ? "NULL" : "'$sprite_path'";
		$icon_path = $_POST["icon_path"];
		$icon_path = empty($icon_path) ? "NULL" : "'$icon_path'";
		$dependencies = $_POST["dependencies"];
		$dependencies = empty($dependencies) ? "NULL" : "'$dependencies'";
		$dependency_alternative = $_POST["dependency_alternative"];
		$dependency_alternative = empty($dependency_alternative) ? "NULL" : $dependency_alternative;
		$price = $_POST["price"];
		$response["query"] = "INSERT INTO `items`(
			`name`,
			`type_id`,
			`sprite_path`,
			`icon_path`,
			`dependencies`,
			`price`
		) VALUES (
			'$name',
			$type_id,
			$sprite_path,
			$icon_path,
			$dependencies,
			$price
		)";

		if (!$conn->query($response["query"]))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;
		}

		$response["query"] = "SELECT * FROM `items` WHERE `name` = '$name' OR `name` LIKE '$name'";

		if (!($query = $conn->query($response["query"])))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;

			break;
		}
		else if ($query->num_rows < 1)
		{
			$response["response"] = "401";
			$response["message"] = "The item has not been added for some unknown reason";

			break;
		}

		$response["item"] = $query->fetch_assoc();

		break;
		
	case "UPDATE":
		if (!isset($_POST["id"], $_POST["name"], $_POST["type_id"], $_POST["sprite_path"], $_POST["icon_path"], $_POST["dependencies"], $_POST["dependency_alternative"], $_POST["price"]))
			exit(json_encode($response));

		$id = $_POST["id"];
		$name = addslashes(strip_tags($_POST["name"]));
		$response = [
			"query" => "SELECT * FROM `items` WHERE `id` = $id",
			"response" => "200",
			"message" => "Success"
		];

		if (!($query = $conn->query($response["query"])))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;

			break;
		}
		else if ($query->num_rows < 1)
		{
			$response["response"] = "403";
			$response["message"] = "A item with the ID of '$id' does not exist in the database or has been removed";

			break;
		}
		
		$type_id = $_POST["type_id"];
		$sprite_path = $_POST["sprite_path"];
		$sprite_path = empty($sprite_path) ? "NULL" : "'$sprite_path'";
		$icon_path = $_POST["icon_path"];
		$icon_path = empty($icon_path) ? "NULL" : "'$icon_path'";
		$dependencies = $_POST["dependencies"];
		$dependencies = empty($dependencies) ? "NULL" : "'$dependencies'";
		$dependency_alternative = $_POST["dependency_alternative"];
		$dependency_alternative = empty($dependency_alternative) ? "NULL" : $dependency_alternative;
		$price = $_POST["price"];
		$response["query"] = "UPDATE `items` SET
			`name` = '$name',
			`type_id` = $type_id,
			`sprite_path` = $sprite_path,
			`icon_path` = $icon_path,
			`dependencies` = $dependencies,
			`dependency_alternative` = $dependency_alternative,
			`price` = $price
		WHERE `id` = $id";

		if (!$conn->query($response["query"]))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;

			break;
		}

		$response["query"] = "SELECT * FROM `items` WHERE `id` = $id";

		if (!($query = $conn->query($response["query"])))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;

			break;
		}
		else if ($query->num_rows < 1)
		{
			$response["response"] = "404";
			$response["message"] = "The item cannot be found for some reason";

			break;
		}

		$response["item"] = $query->fetch_assoc();

		break;

	case "DELETE":
		if (!isset($_POST["id"]))
			exit(json_encode($response));

		$id = $_POST["id"];
		$response = [
			"query" => "SELECT * FROM `items` WHERE `id` = $id",
			"response" => "200",
			"message" => "Success"
		];

		if (!($query = $conn->query($response["query"])))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;

			break;
		}
		else if ($query->num_rows < 1)
		{
			$response["response"] = "403";
			$response["message"] = "A item with the ID of '$id' does not exist in the database or has been removed already";

			break;
		}
		
		$response["sql"] = "UPDATE `users_actions` SET `item_id` = NULL WHERE `item_id` = $id";

		if (!$conn->query($response["sql"]))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;
		}
		
		$response["sql"] = "DELETE FROM `quests` WHERE `reward_item_id` = $id";

		if (!$conn->query($response["sql"]))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;
		}
		
		$response["sql"] = "DELETE FROM `users_items` WHERE `item_id` = $id";

		if (!$conn->query($response["sql"]))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;
		}
		
		$response["query"] = "DELETE FROM `items` WHERE `id` = $id";

		if (!$conn->query($response["query"]))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;
		}

		break;
}

echo json_encode($response);
