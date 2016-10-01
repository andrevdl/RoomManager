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
use RoomManager\Core\Security\IProtection;
use RoomManager\Core\SQL;
use RoomManager\Core\Utility\JSONBuilder;
use RoomManager\Module\Helper\JSONBuilderHelper;

class ShowRooms implements HttpResponse, IProtection
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
        return ["token", "login"];
    }

    public function doGet(Request $request, Response $response)
    {
        $q = $request->getQuery();

        if (!isset($q["location"])) {
            $response->setCode(400);
            return;
        }

        $sqlStr = "SELECT location_id, room_id, r.name, l.name AS 'location_name', description, size FROM Rooms r INNER JOIN locations l USING (location_id) WHERE location_id = :location_id";
        $filter = ["%d"];

        $statement = [
            "location_id" => $q["location"]
        ];

        if (isset($q["search"])) {
            $statement["search"] = $q["search"];
            $sqlStr .= " AND name LIKE :search";
            $filter[] = ["%s"];
        }

        if (isset($q["size"])) {
            $statement["size"] = $q["size"];
            $sqlStr .= " AND size >= :size";
            $filter[] = ["%d"];
        }

        $prepare = $this->sql->prepare($statement, $filter);

        if (isset($q["search"])) {
            $prepare["search"] = "%" . $prepare["search"] . "%";
        }

        $sqlStr .= "";

        $data = $this->sql->select2($sqlStr,
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