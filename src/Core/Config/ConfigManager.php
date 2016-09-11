<?php
/**
 * Created by PhpStorm.
 * User: André
 * Date: 10-9-2016
 * Time: 23:28
 */

namespace RoomManager\Core\Config;


use RoomManager\Core\Utility\Path;

class ConfigManager
{
    private static $placeholders = [
        "%MODULE%" => "RoomManager\\Module"
    ];

    public function getConfig($name) {
        $file = Path::getRes("config" . DIRECTORY_SEPARATOR . $name . ".json");
        $data = file_get_contents($file);
        
        return $this->replacePlaceholders(json_decode($data, true));
    }

    private function replacePlaceholders($json) {
        $output = [];
        foreach ($json as $key => $item) {
            foreach (self::$placeholders as $placeholder => $value) {
                $output[$key] = str_replace($placeholder, $value, $item);
            }
        }
        return $output;
    }
}