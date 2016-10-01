<?php
/**
 * Created by PhpStorm.
 * User: andre
 * Date: 26-9-2016
 * Time: 16:30
 */

namespace RoomManager\Core\Module\Auth;


use RoomManager\Core\Security\IAuth;
use RoomManager\Core\SQL;

class Login implements IAuth
{
    public function requiredFields()
    {
        return ["username", "password"];
    }

    public function create(array &$body, SQL $SQL)
    {
        $prepare = $SQL->prepare([$_POST["username"], $_POST["password"]]);
        $data = $SQL->row("SELECT user_id, username FROM users WHERE username = ? AND password = ?", $prepare);

        if (empty($data)) {
            return false;
        }

        $body = $data;
        return true;
    }
}