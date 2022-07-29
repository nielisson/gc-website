<?php

function StringStartsWith($string, $match)
{
	$len = strlen($match);

	return substr($string, 0, $len) === $match;
}
function StringEndsWith($string, $match)
{
	$length = strlen($match);

	if ($length === 0)
		return true;
		
	return substr($string, -$length) === $match;
}
function StringContains($string, $match)
{
	return strpos($string, $match) !== false;
}
function CharIsNumber($char)
{
	return $char === '0' || $char === '1' || $char === '2' || $char === '3' || $char === '4'
		|| $char === '5' || $char === '6' || $char === '7' || $char === '8' || $char === '9';
}
function CharIsUpperLetter($char)
{
	return $char === 'A' || $char === 'B' || $char === 'C' || $char === 'D' || $char === 'E'
		|| $char === 'F' || $char === 'G' || $char === 'H' || $char === 'I' || $char === 'J'
		|| $char === 'K' || $char === 'L' || $char === 'M' || $char === 'N' || $char === 'O'
		|| $char === 'P' || $char === 'Q' || $char === 'R' || $char === 'S' || $char === 'T'
		|| $char === 'U' || $char === 'V' || $char === 'W' || $char === 'X' || $char === 'Y'
		|| $char === 'Z';
}
function CharIsLowerLetter($char)
{
	return $char === 'a' || $char === 'b' || $char === 'c' || $char === 'd' || $char === 'e'
		|| $char === 'f' || $char === 'g' || $char === 'h' || $char === 'i' || $char === 'j'
		|| $char === 'k' || $char === 'l' || $char === 'm' || $char === 'n' || $char === 'o'
		|| $char === 'p' || $char === 'q' || $char === 'r' || $char === 's' || $char === 't'
		|| $char === 'u' || $char === 'v' || $char === 'w' || $char === 'x' || $char === 'y'
		|| $char === 'z';
}
function CharIsLetter($char)
{
	return CharIsUpperLetter(strtoupper($char)) || preg_match("/[^\x20-\x7e]/", $char);
}
function CharIsSymbol($char)
{
	return !CharIsNumber($char) && !CharIsLetter($char);
}
function SanitizeText($text)
{
	return filter_var($text, FILTER_SANITIZE_SPECIAL_CHARS);
}
function SanitizeEmail($email)
{
	return filter_var($email, FILTER_SANITIZE_EMAIL);
}
function SanitizeURL($url)
{
	return filter_var($url, FILTER_SANITIZE_URL);
}
function EncryptPassword($password)
{
	$hash_options = [
		"memory_cost" => 1 << 17,
		"time_cost" => 4,
		"threads" => 1
	];
	$hash = password_hash($password, PASSWORD_ARGON2ID, $hash_options);
	$max_attempts = 5;
	$attempt = 0;

	while (password_needs_rehash($hash, PASSWORD_ARGON2I, $hash_options))
	{
		$hash = password_hash($password, PASSWORD_ARGON2I, $hash_options);

		$attempt++;

		if ($attempt >= $max_attempts)
			break;
	}
	
	return $hash;
}
function ValidateUsername($username)
{
	$count = strlen($username);

	if ($count < 6 || $count > 64)
		return false;

	for ($i = 0; $i < $count; $i++)
		if (CharIsSymbol($username[$i]) && $username[$i] !== '_' && $username[$i] !== '-' && $username[$i] !== '.')
			return false;

	return true;
}
function ValidateName($name)
{
	$count = strlen($name);

	if ($count < 2 || $count > 64)
		return false;

	for ($i = 0; $i < $count; $i++)
		if (CharIsNumber($name[$i]) || CharIsSymbol($name[$i]) && $name[$i] !== ' ' && $name[$i] !== '.' && $name[$i] !== '-')
			return false;

	return true;
}
function ValidatePassword($password)
{
	$count = strlen($password);

	if ($count < 8 || $count > 64)
		return false;

	$has_lower_letters = false;
	$has_upper_letters = false;
	$has_numbers = false;

	for ($i = 0; $i < $count; $i++)
	{
		$has_lower_letters = $has_lower_letters || CharIsLowerLetter($password[$i]);
		$has_upper_letters = $has_upper_letters || CharIsUpperLetter($password[$i]);
		$has_numbers = $has_numbers || CharIsNumber($password[$i]);
	}

	return $has_lower_letters && $has_upper_letters && $has_numbers;
}
function ValidateEmail($email)
{
	global $base_url;

	$count = strlen($email);

	if ($count < 4 || $count > 64)
		return false;

	for ($i = 0; $i < $count; $i++)
	{
		if (CharIsSymbol($email[$i]) && ($email[$i] !== '.' && $email[$i] !== '_' && $email[$i] !== '-' && $email[$i] !== '+' && $email[$i] !== '@' || $i < 1 || $i >= $count - 1))
			return false;

		if (CharIsNumber($email[$i]))
		{
			if ($i < 1)
				return false;
			else if ($email[$i - 1] === "@")
				return false;
		}
	}

	$atIndex = strpos($email, '@');
	$atLastIndex = strrpos($email, '@');
	$dotLastIndex = strrpos($email, '.');

	if ($atIndex !== false && $dotLastIndex !== false && ($atIndex < 1 || $atLastIndex !== $atIndex || $atLastIndex >= $dotLastIndex - 1))
		return false;

	$emailDomain = explode("@", $email);
	$emailDomain = strtolower($emailDomain[count($emailDomain) - 1]);
	$emailDomainName = explode(".", $emailDomain)[0];
	$blacklistedDomains = DisposableEmailDomains();

	foreach ($blacklistedDomains as $blacklistedDomain)
		if ($emailDomain === $blacklistedDomain || StringContains($emailDomainName, $blacklistedDomain))
			return false;

	return checkdnsrr($emailDomain);
}
function ValidateCountryCode($country_code)
{
	if (strlen($country_code) !== 2)
		return false;

	return true;
}
function ValidatePostalCode($postal_code)
{
	$count = strlen($postal_code);

	if ($count < 3 || $count > 10)
		return false;

	for ($i = 0; $i < $count; $i++)
		if (CharIsSymbol($postal_code[$i]) && $postal_code[$i] !== '.' && $postal_code[$i] !== '-')
			return false;

	return true;
}
function ValidateAddressLine($address)
{
	$count = strlen($address);

	if ($count < 10)
		return false;

	for ($i = 0; $i < $count; $i++)
		if (CharIsSymbol($address[$i]) && $address[$i] !== ' ' && $address[$i] !== '.' && $address[$i] !== '-' && $address[$i] !== ',')
			return false;

	return true;
}
function ValidatePhoneNumber($number, $country_code)
{
	return ValidatePhoneNumberData(PhoneNumberData($number, $country_code));
}
function ValidatePhoneNumberData($data)
{
	return !empty($data) && (isset($data["error"]) ? $data["error"]["code"] !== 210 && $data["error"]["code"] !== 211 && $data["error"]["code"] !== 310 : isset($data["valid"]) && $data["valid"]);
}
function ValidateURL($url)
{
	return filter_var($url, FILTER_VALIDATE_URL);
}
function ValidateMacAddress($mac)
{
	return filter_var($mac, FILTER_VALIDATE_MAC);
}
function ValidateIpAddress($ip)
{
	return filter_var($ip, FILTER_VALIDATE_IP);
}
function RandomString($length, $upper_chars = true, $lower_chars = true, $numbers = true, $symbols = true)
{
	$chars = $upper_chars ? "ABCDEFGHIJKLMNOPQRSTUVWXYZ" : "";
	$chars .= $lower_chars ? "abcdefghijklmnopqrstuvwxyz" : "";
	$chars .= $numbers ? "0123456789" : "";
	$chars .= $symbols ? "~!@%^&*()_-+=[]{}/:;,.?|" : "";
	$chars_length = strlen($chars);
	$result = "";

	while ($chars_length > 0 && (empty($result) || StringContains($result, "&#")))
		for ($i = 0; $i < $length; $i++)
		{
			$index = rand(0, $chars_length - 1);
			$result .= $chars[$index];
		}

	return $result;
}
function GamesList()
{
	global $conn;

	$query = $conn->query("SELECT * FROM `games` ORDER BY `id`");
	$array = [];

	while ($query && $row = $query->fetch_assoc())
		$array[] = $row;

	return $array;
}
function GameGenresList()
{
	global $conn;

	$query = $conn->query("SELECT * FROM `game_genres` ORDER BY `name`");
	$array = [];
	
	while ($query && $row = $query->fetch_assoc())
		$array[] = $row["name"];

	return $array;
}
function GameTypesList()
{
	global $conn;

	$query = $conn->query("SELECT * FROM `game_types` ORDER BY `name`");
	$array = [];
	
	while ($query && $row = $query->fetch_assoc())
		$array[] = $row["name"];

	return $array;
}
function CountriesList()
{
	global $conn;

	$query = $conn->query("SELECT * FROM `countries`");
	$array = [];

	while ($row = $query->fetch_assoc())
		$array[] = $row;

	return $array;
}
function DisposableEmailDomains()
{
	global $conn;

	$query = $conn->query("SELECT * FROM `disposable_email_domains` ORDER BY `domain`");
	$array = [];

	while ($row = $query->fetch_assoc())
		$array[] = $row["domain"];

	return $array;
}
