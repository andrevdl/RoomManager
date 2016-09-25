<?php
/**
 * Created by PhpStorm.
 * User: André
 * Date: 14-9-2016
 * Time: 22:44
 */

namespace RoomManager\Core\Security;


use RoomManager\Core\Config\ConfigManager;
use RoomManager\Core\Http\Request;
use RoomManager\Core\Http\Response;
use RoomManager\Core\SQL;

class SecurityManager
{
    /**
     * @var SQL
     */
    private $sql;

    public function init(SQL $SQL)
    {
        $this->sql = $SQL;
    }

    public function auth(Request $request, Response $response) {
        $data = $this->valid($request);

        if ($data === false) {
            $response->setCode(401);
            $response->send();
        }

        if ($data !== true) {
            $response->setBody($response);
        }
    }

    private function valid(Request $request) {
        $q = $request->getQuery();

        if (!isset($q["secret"]) || !isset($q["iv"])) {
            return false;
        }

        // timeout after 30 seconds
        if (microtime(true) - $_REQUEST["REQUEST_TIME_FLOAT"] > 30) {
            return false;
        }

        $m = new ConfigManager();
        $aes = new AES($q["secret"], $m->getConfig("api")["client"], 128, AES::M_CBC); // check failure
        $aes->setIV($q["iv"]);

        $data = json_encode($aes->decrypt(), true);

        // check body
        $vars = [
            "id",
            "verify",
            "endpoint",
            "type" // token, login
        ];

        foreach ($vars as $var) {
            if (!isset($data[$var])) {
                return false;
            }
        }

        // type must be token or login
        if (!in_array($data["type"], ["token", "login"])) {
            return false;
        }

        if ($request->getPath() != $data["endpoint"]) {
            return false;
        }

        if (!in_array($data["endpoint"], $m->getConfig("urls-sec"))) {
            $prepare = $this->sql->prepare($data["verify"], [$data["id"]]);
            $time = $this->sql->variable(
                "SELECT lease FROM Users WHERE verify = ? AND user_id = ?",
                $prepare
            );

            if ($time == false) {
                return false;
            }

            // check lease time
            // ...

            $this->sql->update("users", [], ["user_id" => $data["user_id"]], null, ["%d"]);
        }

        return true;
    }

    public static function generateAPIKey() {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }
}