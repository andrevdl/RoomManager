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

        $statement = [
            "room_id" => $q["room"],
            "offset" => isset($q["offset"]) ? $q["offset"] : 0,
            "limit" => isset($q["limit"]) ? $q["limit"] : 15,
        ];

        $filter = ["%d", "%d", "%d"];

        $prepare = $this->sql->prepare($statement, $filter);
        $data = $this->sql->select2("SELECT * FROM Reservations WHERE room_id = :room_id ORDER BY date DESC, start_time DESC LIMIT :limit OFFSET :offset", $filter, $prepare);

        $response->setBody($data);
    }

    public function doPost(Request $request, Response $response)
    {
        $response->setCode(400);
        return;
    }
}