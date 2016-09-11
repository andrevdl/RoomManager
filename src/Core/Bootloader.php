<?php
/**
 * Created by PhpStorm.
 * User: André
 * Date: 10-9-2016
 * Time: 22:16
 */

namespace RoomManager\Core;


use RoomManager\Core\Config\ConfigManager;
use RoomManager\Core\Http\Http;
use RoomManager\Core\Utility\Path;

class Bootloader
{
    private $errorHandler;

    private $sql;

    public function __construct()
    {
        Path::init();

        $http = new Http();
        $this->errorHandler = new ErrorHandler($http->getResponse());
        
        $m = new ConfigManager();
        $this->setupSQL($m);

        
        $http->execute($this->sql);
    }

    private function setupSQL(ConfigManager $configManager) {
        SQL::setErrorCallback(array($this->errorHandler, "database"));

        $config = $configManager->getConfig("sql");
        $this->sql = new SQL($config['host'], $config['database'], $config['user'], $config['password']);
    }
}