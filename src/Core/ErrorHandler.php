<?php
/**
 * Created by PhpStorm.
 * User: André
 * Date: 11-9-2016
 * Time: 16:59
 */

namespace RoomManager\Core;


use RoomManager\Core\Http\Response;

class ErrorHandler
{
    /**
     * @var Response
     */
    private $response;

    /**
     * ErrorHandler constructor.
     * @param Response $response
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function database($message, $critical, $hide) {
        if ($hide) {
            // log

            $this->response->setCode(500);
        } else {
            $this->response->setBody($message);
            $this->response->setCode(500);
        }

        $this->response->send();
    }
}