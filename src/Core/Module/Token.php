<?php
/**
 * Created by PhpStorm.
 * User: André
 * Date: 21-9-2016
 * Time: 21:31
 */

namespace RoomManager\Core\Module;


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

    public function init(SQL $SQL)
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

        if (!empty($q["type"])) {
            $response->setCode(400);
        }

        if (!in_array($q["type"], array_keys($authTypes))) {
            $response->setCode(400);
            return;
        }

        $c = $authTypes[$q["type"]];

        if (class_exists($c)) {
            $obj = new $c;
            if ($obj instanceof IAuth) {
                $this->validate($obj, $request); // do something from the output
            } else {
                $response->setCode(501);
            }
        } else {
            $response->setCode(501);
        }
    }

    private function validate(IAuth $auth, Request $request) {
        $prepare = $this->sql->prepare([$_POST["key"]]);
        $status = $this->sql->variable("SELECT COUNT(share) FROM api WHERE share = ?", $prepare);

        if ($status != 1) {
            return false;
        }

        // create container

        // add auth type container part

        return false;
    }
}