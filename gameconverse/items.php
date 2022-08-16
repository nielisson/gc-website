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

if (!isset($_POST["request"]) || $_POST["request"] !== "INSERT" && $_POST["request"] !== "UPDATE" && $_POST["request"] !== "DELETE")
	exit(json_encode($response));

$response["message"] = "Bad Request. Some request fields are missing";

switch ($_POST["request"])
{
	case "INSERT":
		if (!isset($_POST["name"], $_POST["icon_path"], $_POST["price"]))
			exit(json_encode($response));

		$name = strip_tags($_POST["name"]);
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
		
		$price = $_POST["price"];
		$icon_path = $_POST["icon_path"];
		$icon_path = empty($icon_path) ? "NULL" : "'$icon_path'";
		$response["query"] = "INSERT INTO `items`(`name`, `icon_path`, `price`) VALUES ('$name', $icon_path, $price)";

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
		if (!isset($_POST["id"], $_POST["name"], $_POST["icon_path"], $_POST["price"]))
			exit(json_encode($response));

		$id = $_POST["id"];
		$name = strip_tags($_POST["name"]);
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
		
		$price = $_POST["price"];
		$icon_path = $_POST["icon_path"];
		$icon_path = empty($icon_path) ? "NULL" : "'$icon_path'";
		$response["query"] = "UPDATE `items` SET
			`name` = '$name',
			`icon_path` = $icon_path,
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
		
		$response["query"] = "DELETE FROM `items` WHERE `id` = $id";

		if (!$conn->query($response["query"]))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;
		}

		break;
}

echo json_encode($response);