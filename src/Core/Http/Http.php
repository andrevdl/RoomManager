<?php

namespace RoomManager\Core\Http;

use RoomManager\Core\Config\ConfigManager;
use RoomManager\Core\Security\AES;
use RoomManager\Core\SQL;

class Http
{
    private $request;

    private $response;
    
    private $handler;

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
            }
        } else {
            $this->response->setCode(501);
            $this->response->send();
        }
    }

    public function execute(SQL $SQL) {
        $resp = new Response();

        // security
        $c = $this->config->getConfig("api");
        $q = $this->request->getQuery();
        if (isset($q["data"])) {
            $aes = new AES($q["data"], $c["client"], 256, AES::M_ECB);
            $message = $aes->decrypt();
        }

        $this->handler->init($SQL);
        $resp->setCode(200);
        
        switch ($_SERVER['REQUEST_METHOD']) {
            case "GET":
                 $this->handler->doGet($this->request, $resp);
                break;
            case "POST":
                $this->handler->doPost($this->request, $resp);
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