<?php
/**
 * Created by PhpStorm.
 * User: André
 * Date: 21-9-2016
 * Time: 22:06
 */

namespace RoomManager\Module;


use RoomManager\Core\Http\HttpResponse;
use RoomManager\Core\Http\Request;
use RoomManager\Core\Http\Response;
use RoomManager\Core\Security\IProtection;
use RoomManager\Core\SQL;
use RoomManager\Core\Utility\JSONBuilder;

class ShowInvites implements HttpResponse, IProtection
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

        if (!isset($q["reservation"])) {
            $response->setCode(400);
            return;
        }

        $data = $this->sql->select("SELECT res_id, user_id, username, state FROM invites INNER JOIN users USING (user_id) WHERE res_id = ?", [$q["reservation"]]);
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