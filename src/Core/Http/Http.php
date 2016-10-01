<?php

namespace RoomManager\Core\Http;

use Firebase\JWT\JWT;
use RoomManager\Core\Config\ConfigManager;
use RoomManager\Core\Security\IProtection;
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

        // check auth
        $auth = $this->auth($SQL);
        if ($this->handler instanceof IProtection && !in_array($auth['type'], $this->handler->allowAuth())) {
            $resp->setCode(403);
            $resp->send();
        }

        $this->handler->init($SQL, $auth);
        $resp->setCode(200);

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

    private function auth(SQL $SQL) {
        if (isset($this->request->getQuery()['jwt']) && isset($this->request->getQuery()['key'])) {
            try {
                $prepare = $SQL->prepare([$this->request->getQuery()["key"]]);
                $private = $SQL->variable("SELECT private FROM api WHERE share = ?", $prepare);

                $token = JWT::decode($this->request->getQuery()['jwt'], $private, ["HS256"]);
                return ['type' => $token->at, 'data' => $token->data];
            } catch (\Exception $e){
                return ['type' => "", 'body' => []];
            }
        }

        return ['type' => "", 'body' => []];
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}