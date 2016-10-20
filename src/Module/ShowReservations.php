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
use RoomManager\Core\Security\IProtection;
use RoomManager\Core\SQL;
use RoomManager\Core\Utility\BuildRules;
use RoomManager\Core\Utility\JSONBuilder;
use RoomManager\Module\Helper\JSONBuilderHelper;

class ShowReservations implements HttpResponse, IProtection
{
    const DATE = 0;

    const SET = 1;

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

        if (!isset($q["room"])) {
            $response->setCode(400);
            return;
        }

        $sqlStr = <<<EOT
        SELECT r.*, u.username, 
        o.size AS "room_size", o.name AS "room_name", o.description AS "room_description",
        l.name AS "loc_name", l.location_id
        FROM reservations r
        INNER JOIN users u USING (user_id)
        INNER JOIN rooms o USING (room_id)
        INNER JOIN locations l USING (location_id)
        WHERE r.room_id = :room_id AND state != 0
EOT;
        $statement = [
            "room_id" => $q["room"],
        ];
        $filter = ["%d"];

        switch ($this->check($q)) {
            case self::DATE:
                $sqlStr .= " AND date = :date";
                $statement["date"] = $q["date"];
                $filter[] =  "%s";
                break;
            case self::SET:
                $sqlStr .= " ORDER BY date DESC, start_time DESC LIMIT :limit OFFSET :offset";
                $statement["offset"] = isset($q["offset"]) ? $q["offset"] : 0;
                $statement["limit"] = isset($q["limit"]) ? $q["limit"] : 15;
                $filter = array_merge($filter, ["%d", "%d"]);
                break;
            default:
                $response->setCode(400);
                return;
        }

        $prepare = $this->sql->prepare($statement, $filter);
        $data = $this->sql->select2($sqlStr, $filter, $prepare);

        $rules = new BuildRules([
            "room" => [
                "room_id" => "room_id",
                "size" => "room_size",
                "name" => "room_name",
                "description" => "room_description",
                "location" => [
                    "location_id" => "location_id",
                    "name" => "loc_name"
                ]
            ],
            "user" => [
                "user_id" => "user_id",
                "username" => "username"
            ]
        ]);
        JSONBuilder::bundleDataAdvancedArray($data, $rules);
        JSONBuilder::parseBooleanArray($data, "state");

        $response->setBody($data);
    }

    /**
     * @param array $q (request)
     * @return int What kind of query it is
     */
    private function check(array $q)
    {
        if (!isset($q["room"])) {
            return -1;
        }
        elseif (isset($q["offset"]) && isset($q["limit"]) && !isset($q["date"])) {
            return self::SET;
        }
        elseif (isset($q["date"])) {
            return self::DATE;
        }
        return self::SET;
    }

    public function doPost(Request $request, Response $response)
    {
        $response->setCode(400);
        return;
    }
}