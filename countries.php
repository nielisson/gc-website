<?php

include "connect.php";
require "utilities.php";

echo json_encode(CountriesList(), JSON_PRETTY_PRINT);
