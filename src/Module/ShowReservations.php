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
    const DATE = 0;

    const SET = 1;

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
        $sqlStr = "SELECT * FROM Reservations WHERE room_id = :room_id";

        $statement = [
            "room_id" => $q["room"],
        ];
        $filter = ["%d"];


        switch ($this->check($q)) {
            case 0:
                $sqlStr .= "ORDER BY date DESC, start_time DESC LIMIT :limit OFFSET :offset";
                $statement["offset"] =isset($q["offset"]) ? $q["offset"] : 0;
                $statement["limit"] =isset($q["limit"]) ? $q["limit"] : 15;
                array_merge($filter, ["%d", "%d"]);
                break;
            case 1:
                $sqlStr .= "AND date = :date";
                $statement["date"] = sprintf("%s-%s-%s", $q["year"], $q["month"], $q["day"]);
                $filter[] =  "%s";
                break;
            default:
                $response->setCode(400);
                return;
        }

        $prepare = $this->sql->prepare($statement, $filter);
        $data = $this->sql->select2($sqlStr, $filter, $prepare);

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
        elseif (isset($q["offset"]) && isset($q["limit"]) && !isset($q["day"]) && !isset($q["month"]) && !isset($q["year"])) {
            return self::SET;
        }
        elseif (isset($q["day"]) && isset($q["month"]) && isset($q["year"])) {
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