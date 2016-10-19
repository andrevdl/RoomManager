<?php
/**
 * Created by PhpStorm.
 * User: andre
 * Date: 14-9-2016
 * Time: 11:32
 */

namespace RoomManager\Module;


use RoomManager\Core\Http\HttpResponse;
use RoomManager\Core\Http\Request;
use RoomManager\Core\Http\Response;
use RoomManager\Core\Security\IProtection;
use RoomManager\Core\SQL;

class RoomReserve implements HttpResponse, IProtection
{
    /**
     * @var SQL
     */
    private $sql;

    private $auth;

    public function init(SQL $SQL, array $auth)
    {
        $this->sql = $SQL;
        $this->auth = $auth;
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
            "name",
            "room",
            "date",
            "start",
            "end",
            "description"
        ];

        foreach ($vars as $var) {
            if (!isset($_POST[$var])) {
                $response->setCode(400);
                return;
            }
        }

        $status = $this->sql->insert(
            "reservations",
            [
                "room_id" => $_POST["room"],
                "user_id" => $this->auth["data"]->user_id,
                "name" => $_POST["name"],
                "date" => $_POST["date"],
                "start_time" => $_POST["start"],
                "end_time" => $_POST["end"],
                "description" => $_POST["description"],
                "state" => 1
            ],
            ["%d", "%d", "%s", "%s", "%s", "%s", "%s", "%d"]
        );

        if ($status === false) {
            $response->setCode(400);
            return;
        }

        if (isset($_POST["ids"])) {
            //Add reservation parameter to request
            $_POST["reservation"] = $this->sql->lastInsertId();

            $c = new ReservationInvite();
            $c->init($this->sql, $this->auth);
            $c->doPost($request, $response);
        }
    }
}