<?php
$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/plugins/settings.php";
include_once($path);
global $db_url;
ini_set('memory_limit', '-1');
ini_set('max_execution_time', 300);
$private_url = parse_url($db_url['genelist']);
mysql_connect($private_url['host'], $private_url['user'], $private_url['pass']) or die(mysql_error());
mysql_select_db(str_replace('/', '', $private_url['path'])) or die(mysql_error());
?>
