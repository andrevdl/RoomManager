<?php
/**
 * Created by PhpStorm.
 * User: André
 * Date: 10-9-2016
 * Time: 22:59
 */

namespace RoomManager\Core\Http;


class Request
{
    private $query = [];

    private $path = "/";

    public function __construct()
    {
        $url = preg_replace("/^" . URL_ROOT .  "/", "", $_SERVER['REQUEST_URI']);
        $info = parse_url($url);

        $this->path = $info['path'];
        
        if (isset($info['query'])) {
            parse_str($info['query'], $this->query);
        }
    }

    /**
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    public function addQuery($key, $name) {
        $this->query[$key] = $name;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}