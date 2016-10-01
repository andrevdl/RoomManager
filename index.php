<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "src/Core/Psr4Autoloader.php";
$psr4 = new \RoomManager\Core\Psr4Autoloader();
$psr4->register();

$vendor = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR;

$psr4->addNamespace("RoomManager", $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "src");
$psr4->addNamespace("Firebase\\JWT", $vendor . "php-jwt" . DIRECTORY_SEPARATOR . "src");

new \RoomManager\Core\Bootloader();
