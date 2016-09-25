<?php
/**
 * Created by PhpStorm.
 * User: André
 * Date: 19-9-2016
 * Time: 22:24
 */

namespace RoomManager\Core\Module;


use RoomManager\Core\Http\HttpResponse;
use RoomManager\Core\Http\Request;
use RoomManager\Core\Http\Response;
use RoomManager\Core\SQL;

class Login implements HttpResponse
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
        $response->setCode(400);
        return;
    }

    // add token support; code part is bugging
    public function doPost(Request $request, Response $response)
    {

        // temp data is filled
        $prepare = $this->sql->prepare(["andre", "qwerty"]);
        $result = $this->sql->row("SELECT user_id, username FROM users WHERE username = ? AND password = ?", $prepare);

        if (!$result) {
            $response->setCode(400);
            return;
        }

        $result = array_merge($result, $this->sign($result["user_id"], $result["username"]));
        $response->setBody($result);
    }
}