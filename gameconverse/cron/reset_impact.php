<?php

include "../init.php";

$response = [
	"response" => "500",
	"message" => "We've had some internal errors!",
	"query" => "SELECT * FROM `users`"
];
$query = $conn->query($response["query"]);
$response["error"] = $conn->error;

if (!$query)
	exit(json_encode($response));

while ($user = $query->fetch_assoc())
{
	$actions = ActionsList(intval($user["id"]));
	$impact = 0;
	$impact_negation = 0;

	foreach ($actions as $action)
	{
		$impact += max(intval($action["coins"]), 0);
		$impact_negation += intval($action["impact_negation"]);
	}

	$impact = round($impact / 250.0) - $impact_negation;

	if ($impact == 0)
		continue;

	$response["query"] = "INSERT INTO `users_actions` (`user_id`, `type_id`, `impact_negation`) VALUES ($user[id], 3, $impact)";
	$query2 = $conn->query($response["query"]);
	$response["error"] = $conn->error;

	if (!$query2)
		exit(json_encode($response));
}
