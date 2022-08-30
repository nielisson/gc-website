<?php

/*if (isset($_GET))
	$_POST = $_GET;*/

include "init.php";

if (!isset($_POST) || empty($_POST))
	exit(json_encode(QuestsList()));

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
		if (!isset($_POST["name"], $_POST["description"], $_POST["period"], $_POST["start_at"], $_POST["end_at"], $_POST["target"], $_POST["target_game_id"], $_POST["target_value"], $_POST["target_social_media"], $_POST["reward"], $_POST["reward_item_id"], $_POST["reward_amount"]))
			exit(json_encode($response));

		$name = strip_tags($_POST["name"]);
		$description = strip_tags($_POST["description"]);
		$period = $_POST["period"];
		$start_at = empty($_POST["start_at"]) ? "NULL" : "'$_POST[start_at]'";
		$end_at = empty($_POST["end_at"]) ? "NULL" : "'$_POST[end_at]'";
		$target = $_POST["target"];
		$target_game_id = intval($_POST["target_game_id"]) < 1 ? "NULL" : $_POST["target_game_id"];
		$target_value = $_POST["target_value"];
		$target_social_media = empty($_POST["target_social_media"]) || strtoupper($_POST["target_social_media"]) === "NONE" ? "NULL" : "'$_POST[target_social_media]'";
		$reward = $_POST["reward"];
		$reward_item_id = intval($_POST["reward_item_id"]) < 1 ? "NULL" : $_POST["reward_item_id"];
		$reward_amount = intval($_POST["reward_amount"]) < 1 ? "NULL" : $_POST["reward_amount"];
		$response = [
			"sql" => "SELECT * FROM `quests` WHERE `name` = '$name' OR `name` LIKE '$name'",
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
			$response["message"] = "A quest named '$name' already exists in the database";

			break;
		}
		
		$response["sql"] = "INSERT INTO `quests`
		(
			`name`,
			`description`,
			`period`,
			`start_at`,
			`end_at`,
			`target`,
			`target_game_id`,
			`target_value`,
			`reward`,
			`reward_item_id`,
			`reward_amount`
		)
		VALUES
		(
			'$name',
			'$description',
			'$period',
			$start_at,
			$end_at,
			'$target',
			$target_game_id,
			$target_value,
			'$reward',
			$reward_item_id,
			$reward_amount
		)";

		if (!$conn->query($response["sql"]))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;
		}

		$response["sql"] = "SELECT * FROM `quests` WHERE `name` = '$name' OR `name` LIKE '$name'";

		if (!($query = $conn->query($response["sql"])))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;

			break;
		}
		else if ($query->num_rows < 1)
		{
			$response["response"] = "401";
			$response["message"] = "The quest has not been added for some unknown reason";

			break;
		}

		$response["quest"] = $query->fetch_assoc();

		break;
		
	case "UPDATE":
		if (!isset($_POST["id"], $_POST["name"], $_POST["description"], $_POST["period"], $_POST["start_at"], $_POST["end_at"], $_POST["target"], $_POST["target_game_id"], $_POST["target_value"], $_POST["target_social_media"], $_POST["reward"], $_POST["reward_item_id"], $_POST["reward_amount"]))
			exit(json_encode($response));

		$id = $_POST["id"];
		$name = strip_tags($_POST["name"]);
		$description = strip_tags($_POST["description"]);
		$period = $_POST["period"];
		$start_at = empty($_POST["start_at"]) ? "NULL" : "'$_POST[start_at]'";
		$end_at = empty($_POST["end_at"]) ? "NULL" : "'$_POST[end_at]'";
		$target = $_POST["target"];
		$target_game_id = intval($_POST["target_game_id"]) < 1 ? "NULL" : $_POST["target_game_id"];
		$target_value = $_POST["target_value"];
		$target_social_media = empty($_POST["target_social_media"]) || strtoupper($_POST["target_social_media"]) === "NONE" ? "NULL" : "'$_POST[target_social_media]'";
		$reward = $_POST["reward"];
		$reward_item_id = intval($_POST["reward_item_id"]) < 1 ? "NULL" : $_POST["reward_item_id"];
		$reward_amount = intval($_POST["reward_amount"]) < 1 ? "NULL" : $_POST["reward_amount"];
		$response = [
			"sql" => "SELECT * FROM `quests` WHERE `id` = $id",
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
			$response["message"] = "A quest with the ID of '$id' does not exist in the database or has been removed";

			break;
		}
		
		$response["sql"] = "UPDATE `quests` SET
			`name` = '$name',
			`description` = '$description',
			`period` = '$period',
			`start_at` = $start_at,
			`end_at` = $end_at,
			`target` = '$target',
			`target_game_id` = $target_game_id,
			`target_value` = $target_value,
			`reward` = '$reward',
			`reward_item_id` = $reward_item_id,
			`reward_amount` = $reward_amount
		WHERE `id` = $id";

		if (!$conn->query($response["sql"]))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;

			break;
		}

		$response["sql"] = "SELECT * FROM `quests` WHERE `id` = $id";

		if (!($query = $conn->query($response["sql"])))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;

			break;
		}
		else if ($query->num_rows < 1)
		{
			$response["response"] = "404";
			$response["message"] = "The quest cannot be found for some reason";

			break;
		}

		$response["quest"] = $query->fetch_assoc();

		break;

	case "DELETE":
		if (!isset($_POST["id"]))
			exit(json_encode($response));

		$id = $_POST["id"];
		$response = [
			"sql" => "SELECT * FROM `quests` WHERE `id` = $id",
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
			$response["message"] = "A quest with the ID of '$id' does not exist in the database or has been removed already";

			break;
		}
		
		$response["sql"] = "DELETE FROM `quests` WHERE `id` = $id";

		if (!$conn->query($response["sql"]))
		{
			$response["response"] = "500";
			$response["message"] = $conn->error;
		}

		break;
}

echo json_encode($response);
