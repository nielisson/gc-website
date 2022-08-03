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
	$username = "gameygmg_root";
	$password = "GcGc2022-";
	$dbname = "gameygmg_main";
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if (isset($conn->connect_error))
	exit("Connection failed: " . $conn->connect_error);
