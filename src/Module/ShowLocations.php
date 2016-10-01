<?php
/**
 * Created by PhpStorm.
 * User: André
 * Date: 11-9-2016
 * Time: 19:41
 */

namespace RoomManager\Module;


use RoomManager\Core\Http\HttpResponse;
use RoomManager\Core\Http\Request;
use RoomManager\Core\Http\Response;
use RoomManager\Core\Security\IProtection;
use RoomManager\Core\SQL;

class ShowLocations implements HttpResponse, IProtection
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
        $response->setBody($this->sql->select("SELECT location_id, name FROM locations"));
    }

    public function doPost(Request $request, Response $response)
    {
        $response->setCode(400);
        return;
    }
}