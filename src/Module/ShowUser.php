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
use RoomManager\Core\SQL;
use RoomManager\Core\Utility\JSONBuilder;

class ShowUser implements HttpResponse
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

        if (!isset($q["user"])) {
            $response->setCode(400);
            return;
        }

        $statement = [
            "user_id" => $q["user"],
            "offset" => isset($q["offset"]) ? $q["offset"] : 0,
            "limit" => isset($q["limit"]) ? $q["limit"] : 15,
        ];

        $filter = ["%d", "%d", "%d"];

        $sqlStr = <<<EOT
        SELECT r.*, u.username FROM reservations r 
        INNER JOIN invites i
        INNER JOIN users u 
        ON u.user_id = r.user_id
        WHERE r.user_id = :user_id 
        OR i.user_id = :user_id
        AND i.state != 0
        AND r.state != 0
        ORDER BY date DESC, start_time DESC LIMIT :limit OFFSET :offset
EOT;

        $prepare = $this->sql->prepare($statement, $filter);
        $data = $this->sql->select2(
            $sqlStr,
            $filter,
            $prepare
        );

        JSONBuilder::bundleDataArray($data, ["user_id", "username"], "user");
        JSONBuilder::parseBooleanArray($data, "state");

        $response->setBody($data);
    }

    public function doPost(Request $request, Response $response)
    {
        $response->setCode(400);
        return;
    }

}