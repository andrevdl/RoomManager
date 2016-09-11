<?php

namespace RoomManager\Core\Http;

use RoomManager\Core\Config\ConfigManager;
use RoomManager\Core\SQL;

class Http
{
    private $request;

    private $response;
    
    private $handler;

    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();

        $m = new ConfigManager();
        
        $urls = $m->getConfig("urls");

        $c = "";
        foreach ($urls as $url => $class) {
            if ($this->request->getPath() == $url) {
                $c = $class;
                break;
            }
        }

        if (empty($c)) {
            $this->response->setCode(404);
            $this->response->send();
        } elseif (class_exists($c)) {
            $obj = new $c;
            if ($obj instanceof HttpResponse) {
                $this->handler = $obj;
            }
        } else {
            $this->response->setCode(501);
            $this->response->send();
        }
    }

    public function execute(SQL $SQL) {
        $this->handler->init($SQL);
        
        $resp = new Response();
        $resp->setCode(200);
        
        switch ($_SERVER['REQUEST_METHOD']) {
            case "GET":
                 $this->handler->doGet($this->request, $resp);
                break;
            default:
                // error
                break;
        }
        
        $resp->send();
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}