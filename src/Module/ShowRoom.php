<?php
/**
 * Created by PhpStorm.
 * User: gsrij
 * Date: 14/09/2016
 * Time: 11:04
 */

namespace RoomManager\Module;


use RoomManager\Core\Http\HttpResponse;
use RoomManager\Core\Http\Request;
use RoomManager\Core\Http\Response;
use RoomManager\Core\SQL;
use RoomManager\Core\Utility\JSONBuilder;
use RoomManager\Module\Helper\JSONBuilderHelper;

class ShowRoom implements HttpResponse
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
        //todo fix this
        $q = $request->getQuery();

        if (!isset($q["room"])) {
            $response->setCode(400);
            return;
        }

        $statement = [
            "room_id" => $q["room"],
        ];

        $filter = ["%d"];

        $prepare = $this->sql->prepare($statement, $filter);
        $data = $this->sql->select2(
            "SELECT location_id, room_id, r.name, l.name AS 'location_name', description, size FROM Rooms r INNER JOIN locations l USING (location_id) WHERE room_id = :room_id",
            $filter,
            $prepare
        );

        JSONBuilder::bundleDataArray($data, ["location_id", "location_name"], "location");

        //repair name
        foreach ($data as &$item) {
            $item["location"]["name"] = $item["location"]["location_name"];
            unset($item["location"]["location_name"]);
        }

        $response->setBody($data);
    }

    public function doPost(Request $request, Response $response)
    {
        $response->setCode(400);
        return;
    }
}