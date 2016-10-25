<?php
/**
 * Created by PhpStorm.
 * User: gsrij
 * Date: 14/09/2016
 * Time: 10:44
 */

namespace RoomManager\Module;


use RoomManager\Core\Http\HttpResponse;
use RoomManager\Core\Http\Request;
use RoomManager\Core\Http\Response;
use RoomManager\Core\Security\IProtection;
use RoomManager\Core\SQL;
use RoomManager\Core\Utility\BuildRules;
use RoomManager\Core\Utility\JSONBuilder;

class ShowUser implements HttpResponse, IProtection
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
        $q = $request->getQuery();

        $statement = [
            "user_id" => $this->auth["data"]->user_id,
            "offset" => isset($q["offset"]) ? $q["offset"] : 0,
            "limit" => isset($q["limit"]) ? $q["limit"] : 15,
        ];

        $filter = ["%d", "%d", "%d"];

        $sqlStr = <<<EOT
        SELECT DISTINCT r.*, u.username,
        o.size AS "room_size", o.name AS "room_name", o.description AS "room_description",
        l.name AS "loc_name", l.location_id
        FROM reservations r 
        INNER JOIN rooms o USING (room_id)
        INNER JOIN locations l USING (location_id)

        LEFT JOIN invites i USING (res_id)
        INNER JOIN users u
        WHERE (i.user_id = u.user_id AND i.state != 0 OR r.user_id = u.user_id) AND u.user_id = :user_id AND r.state != 0
        ORDER BY date DESC, start_time DESC LIMIT :limit OFFSET :offset
EOT;

        $prepare = $this->sql->prepare($statement, $filter);
        $data = $this->sql->select2(
            $sqlStr,
            $filter,
            $prepare
        );

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

    public function doPost(Request $request, Response $response)
    {
        $response->setCode(400);
        return;
    }

}