<?php
/**
 * Created by PhpStorm.
 * User: andre
 * Date: 26-9-2016
 * Time: 16:30
 */

namespace RoomManager\Core\Security;


use RoomManager\Core\SQL;

interface IAuth
{
    public function requiredFields();

    public function create(array &$body, SQL $SQL);
}