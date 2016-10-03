<?php
/**
 * Created by PhpStorm.
 * User: André
 * Date: 10-9-2016
 * Time: 23:01
 */

namespace RoomManager\Core\Utility;


class Path
{
    private static $root;

    public static function init() {
         self::$root = ROOT;
    }

    public static function getRes($path) {
        return self::$root . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR . $path;
    }
}