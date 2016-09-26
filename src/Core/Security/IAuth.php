<?php
/**
 * Created by PhpStorm.
 * User: andre
 * Date: 26-9-2016
 * Time: 16:30
 */

namespace RoomManager\Core\Security;


interface IAuth
{
    function requiredFields();

    function create();

    function verify();
}