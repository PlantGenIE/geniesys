<?php
$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/plugins/settings.php";
include_once($path);
global $db_url;
$private_url = parse_url($db_url['genelist']);
mysqli_connect($private_url['host'], $private_url['user'], $private_url['pass']) or die(mysql_error());
mysqli_select_db(str_replace('/', '', $private_url['path'])) or die(mysql_error());
?>
