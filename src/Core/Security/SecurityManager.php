<?php
/**
 * Created by PhpStorm.
 * User: André
 * Date: 14-9-2016
 * Time: 22:44
 */

namespace RoomManager\Core\Security;


use RoomManager\Core\Config\ConfigManager;
use RoomManager\Core\SQL;

class SecurityManager
{
    /**
     * @var SQL
     */
    private $sql;

    public function __construct(SQL $SQL)
    {
        $this->sql = $SQL;
    }

    public function login($username, $password) {
        if (!isset($_POST["secret"]) || !isset($_POST["IV"])) {
            return false;
        }

        $p = $this->valid();

        $prepare = $this->sql->prepare([$username, $password]);
        $result = $this->sql->row("SELECT * FROM users WHERE username = ? AND password = ?", $prepare);

        if (!$result) {
            return false;
        }

        // create lease
        $verify = hash("sha256", $result["username"] . bin2hex(openssl_random_pseudo_bytes(32)));
        $status = $this->sql->update("users", ["verify" => $verify], ["user_id" => $result["user_id"]]);

        if ($status === false) {
            return false;
        }

        //create send package
        $aes = new AESBody($result["user_id"], $verify);

        return ["secret" => $aes->encrypt(), "IV" => base64_encode($aes->getIV())] ;
    }

    public function valid() {
        if (!isset($_POST["secret"]) || !isset($_POST["IV"])) {
            return false;
        }



        $m = new ConfigManager();
        $aes = new AES($_POST["secret"], $m->getConfig("api")["client"]);

        // todo: check time


    }

    private function updateLease() {

    }

    public function check() {

    }

    public function logout() {

    }

    public static function generateAPIKey() {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }
}