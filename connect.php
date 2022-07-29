<?php

// Connection parameters
$servername = "localhost";
$is_localhost = str_contains("localhost/", $_SERVER['HTTP_HOST']);

// Check if localhost
if ($is_localhost)
{
	$username = "root";
	$password = "";
	$dbname = "game_converse";
}
else
{
	$username = "id19295659_root";
	$password = "&X_8sAoxrW\/Iz6q";
	$dbname = "id19295659_gc";
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if (isset($conn->connect_error))
	exit("Connection failed: " . $conn->connect_error);
