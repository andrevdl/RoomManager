<?php
/**
 * Created by PhpStorm.
 * User: André
 * Date: 25-9-2016
 * Time: 23:17
 */

namespace RoomManager\Core\Utility;


class JSONBuilder
{
    public static function bundleDataArray(array &$jsonArray, array $merge, $name)
    {
        foreach ($jsonArray as &$item) {
            self::bundleData($item, $merge, $name);
        }
    }

    public static function bundleData(array &$json, array $merge, $name)
    {
        $bundle = [];
        foreach ($merge as $value) {
            $bundle[$value] = $json[$value];
            unset($json[$value]);
        }

        $json[$name] = $bundle;
    }

    public static function parseBooleanArray(array &$jsonArray, $key) {
        foreach ($jsonArray as &$item) {
            self::parseBoolean($item, $key);
        }
    }

    public static function parseBoolean(array &$json, $key) {
        $json[$key] = $json[$key] ? true : false;
    }
}