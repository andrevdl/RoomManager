<?php
/**
 * Created by PhpStorm.
 * User: André
 * Date: 14-9-2016
 * Time: 22:44
 */

namespace RoomManager\Core\Security;


use RoomManager\Core\Config\ConfigManager;
use RoomManager\Core\Http\HttpResponse;
use RoomManager\Core\Http\Request;
use RoomManager\Core\Http\Response;
use RoomManager\Core\SQL;

class SecurityManager implements HttpResponse
{
    /**
     * @var SQL
     */
    private $sql;

    private $username;

    private $password;

    public function init(SQL $SQL)
    {
        $this->sql = $SQL;
    }

    public function doGet(Request $request, Response $response)
    {
        $data = $this->valid($request);
        if ($data === false) {
            $response->setCode(401);
            $response->send();
        }

        if ($data !== true) {
            $response->setBody($response);
        }
    }

    public function doPost(Request $request, Response $response)
    {
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
        if (!isset($_POST["secret"]) || !isset($_POST["IV"])) {
            return false;
        }

        $m = new ConfigManager();
        $aes = new AES($_POST["secret"], $m->getConfig("api")["client"], 256, AES::M_CBC);
        $aes->setIV($_POST["IV"]);

        $data = json_encode($aes->decrypt(), true);

        // check body
        $vars = [
            "id",
            "verify",
            "endpoint",
            "time"
        ];

        foreach ($vars as $var) {
            if (!isset($data[$var])) {
                return false;
            }
        }

        // todo: check time
//        $data["time"]

        if ($request->getPath() != $data["endpoint"]) {
            return false;
        }

        if ("/login" != $data["endpoint"]) {
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
            return true;
        } else {
            if ($data["id"] == true) {
                return false;
            }

            $prepare = $this->sql->prepare([$this->username, $this->password]);
            $result = $this->sql->row("SELECT * FROM users WHERE username = ? AND password = ?", $prepare);

            if (!$result) {
                return false;
            }

            // create aes response
            return $this->sign($result["user_id"], $result["username"]);
        }
    }

    private function sign($id, $username) {
        $verify = hash("sha256", $username . bin2hex(openssl_random_pseudo_bytes(32)));
        $status = $this->sql->update("users", ["verify" => $verify], ["user_id" => $id]);

        if ($status === false) {
            return false;
        }

        $aes = new AESBody($id, $username, $verify);
        return ["secret" => $aes->encrypt(), "IV" => base64_encode($aes->getIV())];
    }

    public static function generateAPIKey() {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }
}