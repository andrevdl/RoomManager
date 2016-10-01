<?php
/**
 * Created by PhpStorm.
 * User: André
 * Date: 1-10-2016
 * Time: 23:03
 */

namespace RoomManager\Core\Module\Auth;


use RoomManager\Core\Security\IAuth;
use RoomManager\Core\SQL;

class Token implements IAuth
{
    public function requiredFields()
    {
        return [];
    }

    public function create(array &$body, SQL $SQL)
    {
        return true;
    }
}