<?php
/**
 * Created by PhpStorm.
 * User: André
 * Date: 10-9-2016
 * Time: 22:13
 */

namespace RoomManager\Module;


use RoomManager\Core\Http\HttpResponse;
use RoomManager\Core\Http\Request;
use RoomManager\Core\Http\Response;
use RoomManager\Core\SQL;

class ShowRooms implements HttpResponse
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
        $sqlStr = "SELECT * FROM Rooms WHERE location_id = :location_id AND name = :name ";

        if (!isset($q["location"])) {
            $response->setCode(400);
            return;
        }

        $statement = [
            "location_id" => $q["location"],
            "name" => isset($q["search"]) ? $q["search"] : "",
        ];

        if (isset($q["size"])) {
            $statement["size"] = $q["size"];
            $sqlStr .= "AND size = :size";
        }

        $filter = ["%d", "%s", "%d"];

        $prepare = $this->sql->prepare($statement, $filter);
        $prepare["search"] = "%" . $prepare["search"] . "%";
        if (isset($q["size"])) {
            $prepare["size"] = "%" . $prepare["size"] . "%";
        }

        $response->setBody($data = $this->sql->select2($sqlStr,
            $filter,
            $prepare
        ));
    }

    public function doPost(Request $request, Response $response)
    {
        $response->setCode(400);
        return;
    }
}