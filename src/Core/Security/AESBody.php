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
    public function __construct($id, $verify)
    {
        parent::__construct(
            json_encode(
                [
                    "id" => $id,
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