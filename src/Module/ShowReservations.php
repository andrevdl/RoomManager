<?php
/**
 * Created by PhpStorm.
 * User: André
 * Date: 11-9-2016
 * Time: 23:13
 */

namespace RoomManager\Module;


use RoomManager\Core\Http\HttpResponse;
use RoomManager\Core\Http\Request;
use RoomManager\Core\Http\Response;
use RoomManager\Core\SQL;

class ShowReservations implements HttpResponse
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
        $q = $request->getQuery();

        if (!isset($q["room"])) {
            $response->setCode(400);
            return;
        }

        $room = $this->sql->prepare(["room_id" => $q["room"]], ["%d"]);
        $data = $this->sql->select("SELECT * FROM Reservations WHERE room_id = ?", $room);

        $response->setBody($data);
    }
}