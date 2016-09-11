<?php
/**
 * Created by PhpStorm.
 * User: André
 * Date: 11-9-2016
 * Time: 21:35
 */

namespace RoomManager\Core\Http;


class Response
{
    /**
     * @var integer
     */
    protected $code;

    /**
     * @var mixed
     */
    protected $body;
    
    public function send() {
        http_response_code($this->code);
        header('Content-type: application/json');
        $response = [
            "status" => $this->code,
            "data" => $this->body
        ];

        die(json_encode($response));
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }
    
    
}