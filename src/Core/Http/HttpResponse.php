<?php
/**
 * Created by PhpStorm.
 * User: André
 * Date: 10-9-2016
 * Time: 22:15
 */

namespace RoomManager\Core\Http;


use RoomManager\Core\SQL;

interface HttpResponse
{
    public function init(SQL $SQL);

    public function doGet(Request $request, Response $response);
    
    public function doPost(Request $request, Response $response);
}