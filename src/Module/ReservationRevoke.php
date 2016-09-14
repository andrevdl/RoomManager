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

class ReservationRevoke implements HttpResponse
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
            "reservation",
        ];

        foreach ($vars as $var) {
            if (!isset($_POST[$var])) {
                $response->setCode(400);
                return;
            }
        }

        $res = $_POST["reservation"];

        $status = $this->sql->update("reservations", ["state" => 0], ["res_id" => $res], ["%d"], ["%d"]);

        if ($status === false) {
            $response->setCode(400);
            return;
        }

        $status = $this->sql->update("invites", ["state" => 0], ["res_id" => $res], ["%d"], ["%d"]);

        if ($status === false) {
            $response->setCode(400);
            return;
        }
    }
}