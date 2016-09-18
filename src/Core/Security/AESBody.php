<?php
/**
 * Created by PhpStorm.
 * User: André
 * Date: 15-9-2016
 * Time: 00:05
 */

namespace RoomManager\Core\Security;


use RoomManager\Core\Config\ConfigManager;

class AESBody extends AES
{
    public function __construct($id, $username, $verify) // add type token, lesser access; perm.json needed; auth parameter
    {
        parent::__construct(
            json_encode(
                [
                    "id" => $id,
                    "username" => $username,
                    "verify" => $verify,
                    "time" => time()
                ]
            ),
            $this->getKey(),
            256,
            AES::M_CBC
        );
    }

    private function getKey() {
        $m = new ConfigManager();
        return $m->getConfig("api")["server"];
    }

    public function getIV()
    {
        return parent::getIV();
    }
}