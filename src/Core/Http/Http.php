<?php

namespace RoomManager\Core\Http;

use RoomManager\Core\Config\ConfigManager;
use RoomManager\Core\Security\AES;
use RoomManager\Core\Security\SecurityManager;
use RoomManager\Core\SQL;

class Http
{
    private $request;

    private $response;
    
    private $handler;

    private $authHandler;

    private $config;

    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();

        $this->config = new ConfigManager();
        
        $urls = $this->config->getConfig("urls");
        $urls_sec = $this->config->getConfig("urls-sec");

        $urls = array_merge($urls, $urls_sec);
        if (!in_array($this->request->getPath(), $urls_sec)) { // failure
//            $this->authHandler = new SecurityManager();
        }

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
            } else {
                $this->response->setCode(501);
                $this->response->send();
            }
        } else {
            $this->response->setCode(501);
            $this->response->send();
        }
    }

    public function execute(SQL $SQL) {
        $resp = new Response();

        $this->handler->init($SQL);
        $resp->setCode(200);

        $this->auth($SQL, $resp);
        
        switch ($_SERVER['REQUEST_METHOD']) {
            case "GET":
                 $this->handler->doGet($this->request, $resp);
                break;
            case "POST":
                $this->handler->doPost($this->request, $resp);
                break;
            default:
                $resp->setCode(405);
                break;
        }
        
        $resp->send();
    }

    private function auth(SQL $SQL, Response $resp) {
        if (!is_null($this->authHandler)) {
            $this->authHandler->init($SQL);
            $this->authHandler->auth($this->request, $resp);
        }
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}