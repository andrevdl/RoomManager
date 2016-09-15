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
use RoomManager\Core\SQL;

class InviteRevoke implements HttpResponse
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
        //TODO ask andere how revoke works state 0?
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
                "Invites",
                [
                    "res_id" => $_POST["reservation"],
                    "user_id" => $id
                ],
                ["%d", "%d"]
            );
            if ($status === false) {
                $response->setCode(400);
                return;
            }
        }
    }
}