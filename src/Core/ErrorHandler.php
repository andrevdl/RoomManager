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
            // log output

//            if ($critical) {
//                die();
//            }

            $this->response->setCode(500);
        } elseif ($critical) {
            $this->response->setCode(500);
//            die($message);
        } else {
            $this->response->setCode(500);
//            echo($message);
        }

        $this->response->send();
    }
}