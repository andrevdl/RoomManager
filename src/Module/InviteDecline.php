<?php
/**
 * Created by PhpStorm.
 * User: andre
 * Date: 14-9-2016
 * Time: 15:25
 */

namespace RoomManager\Module;


use RoomManager\Core\Http\HttpResponse;
use RoomManager\Core\Http\Request;
use RoomManager\Core\Http\Response;
use RoomManager\Core\Security\IProtection;
use RoomManager\Core\SQL;

class InviteDecline implements HttpResponse, IProtection
{
    /**
     * @var SQL
     */
    private $sql;

    public function init(SQL $SQL, array $auth)
    {
        $this->sql = $SQL;
    }

    public function allowAuth()
    {
        return ["login"];
    }

    public function doGet(Request $request, Response $response)
    {
        $response->setCode(400);
        return;
    }

    public function doPost(Request $request, Response $response)
    {
        $vars = [
            "reservation",
            "ids"
        ];

        foreach ($vars as $var) {
            if (!isset($_POST[$var])) {
                $response->setCode(400);
                return;
            }
        }

        if (!is_array($_POST["ids"])) {
            $response->setCode(400);
            return;
        }

        foreach ($_POST["ids"] as $id) {
            $status = $this->sql->update(
                "invites",
                [
                    "res_id" => $_POST["reservation"],
                    "user_id" => $id,
                    "state" => 0
                ],
                ["%d", "%d", "%d"]
            );
            if ($status === false) {
                $response->setCode(400);
                return;
            }
        }
    }
}