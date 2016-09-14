<?php
/**
 * Created by PhpStorm.
 * User: André
 * Date: 12-9-2016
 * Time: 00:46
 */

namespace RoomManager\Module;


use RoomManager\Core\Http\HttpResponse;
use RoomManager\Core\Http\Request;
use RoomManager\Core\Http\Response;
use RoomManager\Core\SQL;

class ShowUsers implements HttpResponse
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

        $statement = [
            "username" => $q["search"],
        ];

        $filter = ["%s"];
        $prepare = $this->sql->prepare($statement, $filter);
        $prepare["username"] = "%" . $prepare["username"] . "%";

        $response->setBody($this->sql->select2("SELECT user_id, username FROM users WHERE username LIKE :username",
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