<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Root path of the installation.
 */
define("ROOT", __DIR__);

/**
 * URL Root of the installation.
 * Default empty.
 * When the installation is placed in a sub folder,
 * place here the path from the main to the sub folder.
 * Always start with a "/".
 *
 * Value muss be regular expression escaped.
 */
define("URL_ROOT", "\/RoomManager");

require_once "src/Core/Psr4Autoloader.php";
$psr4 = new \RoomManager\Core\Psr4Autoloader();
$psr4->register();

$vendor = ROOT . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR;

$psr4->addNamespace("RoomManager", ROOT . DIRECTORY_SEPARATOR . "src");
$psr4->addNamespace("Firebase\\JWT", $vendor . "php-jwt" . DIRECTORY_SEPARATOR . "src");

new \RoomManager\Core\Bootloader();
