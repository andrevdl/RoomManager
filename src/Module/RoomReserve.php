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
use RoomManager\Core\SQL;

class RoomReserve implements HttpResponse
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
        $vars = [
            "user",
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
                "user_id" => $_POST["user"],
                "date" => $_POST["date"],
                "start_time" => $_POST["start"],
                "end_time" => $_POST["end"],
                "description" => $_POST["description"],
                "state" => 1
            ],
            ["%d", "%d", "%s", "%s", "%s", "%s", "%d"]
        );

        //Add reservation parameter to request
        $request->addQuery("reservation", $this->sql->lastInsertId());

        if ($status === false) {
            $response->setCode(400);
            return;
        }

        if (isset($_POST["ids"])) {
            $c = new ReservationInvite();
            $c->init($this->sql);
            $c->doPost($request, $response);
        }
    }
}