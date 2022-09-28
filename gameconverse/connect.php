<?php

// Connection parameters
$is_localhost = strpos("localhost/", $_SERVER['HTTP_HOST']) !== false;
$servername = "localhost";

// Check if localhost
if ($is_localhost)
{
	$username = "root";
	$password = "";
	$dbname = "game_converse";
}
else
{
	$username = "gamehagx_root";
	$password = "Gcgc2022*";
	$dbname = "gamehagx_gc";
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if (isset($conn->connect_error))
	exit("Connection failed: " . $conn->connect_error);
