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
    private $data;

    private function __construct($id, $username, $verify) // add type token, lesser access; perm.json needed; auth parameter
    {
        parent::__construct(
            json_encode(
                [
                    "id" => $id,
                    "username" => $username,
                    "verify" => $verify
                ]
            ),
            $this->getKey(),
            128,
            AES::M_CBC
        );
    }

    public static function createLogin($id, $username) {

    }

    public static function createToken() {

    }

    private function getKey() {
        $m = new ConfigManager();
        return $m->getConfig("api")["server"];
    }

    public function getIV()
    {
        return parent::getIV();
    }

    public function sign($id, $username) {
        $verify = bin2hex(openssl_random_pseudo_bytes(32));
        $status = $this->sql->update("users", ["verify" => $verify], ["user_id" => $id]);

        if ($status === false) {
            return false;
        }

        $aes = new AESBody($id, $username, $verify);
        return ["secret" => base64_encode($aes->encrypt()), "iv" => base64_encode($aes->getIV())];
    }
}