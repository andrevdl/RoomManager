<?php
/**
 * Created by PhpStorm.
 * User: André
 * Date: 21-9-2016
 * Time: 21:31
 */

namespace RoomManager\Core\Module;


use Firebase\JWT\JWT;
use RoomManager\Core\Config\ConfigManager;
use RoomManager\Core\Http\HttpResponse;
use RoomManager\Core\Http\Request;
use RoomManager\Core\Http\Response;
use RoomManager\Core\Security\IAuth;
use RoomManager\Core\SQL;

class Token implements HttpResponse
{
    /**
     * @var SQL
     */
    private $sql;

    public function init(SQL $SQL, array $auth)
    {
        $this->sql = $SQL;
    }


    public function doGet(Request $request, Response $response)
    {
        $response->setCode(400);
        return;
    }

    public function doPost(Request $request, Response $response)
    {
        $m = new ConfigManager();
        $authTypes = $m->getConfig("auth");

        $q = $request->getQuery();

        if (empty($q["type"]) || empty($_POST["key"])) {
            $response->setCode(400);
            return;
        }

        if (!in_array($q["type"], array_keys($authTypes))) {
            $response->setCode(400);
            $response->setBody(array_keys($authTypes));
            return;
        }

        $c = $authTypes[$q["type"]];

        if (class_exists($c)) {
            $obj = new $c;
            if ($obj instanceof IAuth) {
                $package = $this->createPackage($obj, $q["type"]);
                if (!$package ) {
                    $response->setCode(401);
                    return;
                }

                $response->setBody(["jwt" => $package]);
            } else {
                $response->setCode(501);
            }
        } else {
            $response->setCode(501);
        }
    }

    private function createPackage(IAuth $auth, $type) {
        $prepare = $this->sql->prepare([$_POST["key"]]);
        $private = $this->sql->variable("SELECT private FROM api WHERE share = ?", $prepare);

        if (!$private) {
            return false;
        }

        // checking precondition
        foreach ($auth->requiredFields() as $field) {
            if (!isset($_POST[$field])) {
                return false;
            }
        }

        $time = time();

        $body = [
            'iat' => $time, // Issued at: time when the token was generated
            'jti' => base64_encode(mcrypt_create_iv(32)), // Json Token Id: an unique identifier for the token
            'iss' => "SERVER NAME", // Issuer
            'nbf' => $time, // Not before
            'exp' => $time + 3600, // Expire (1 hour)
            'data' => [],
            'at' => $type
        ];

        if ($auth->create($body['data'], $this->sql) === false) {
            return false;
        }

        $jwt = JWT::encode(
            $body,
            $private
        );

        return $jwt;
    }
}