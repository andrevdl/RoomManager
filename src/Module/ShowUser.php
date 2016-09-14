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

        $prepare = $this->sql->prepare($statement, $filter);
        $data = $this->sql->select2(
            "SELECT * FROM Reservations r INNER JOIN invites i WHERE r.user_id = :user_id OR i.user_id = :user_id ORDER BY date DESC, start_time DESC LIMIT :limit OFFSET :offset",
            $filter,
            $prepare
        );

        $response->setBody($data);
    }

    public function doPost(Request $request, Response $response)
    {
        $response->setCode(400);
        return;
    }

}