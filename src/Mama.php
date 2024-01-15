<?php

class Mama
{

    private $url;
    private $token;
    private $response;
    private $config;

    public function __construct()
    {
        $this->config = (new Config())->init();
        $cloudEnv = isset($_GET['cloudEnv']) && !empty($_GET['cloudEnv']) ? $_GET['cloudEnv'] : $this->config['cloudEnv'];
        $this->url = $this->config['mamaUrl'] . $cloudEnv;
        $this->token = $this->config['mamaToken'];
    }

    public function getResponse()
    {
        $authorizationHeader = "Authorization: Bearer $this->token";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($authorizationHeader));

        $jsonData = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error: ' . curl_error($ch);
        }

        curl_close($ch);

        $this->response = json_decode($jsonData, true);
        return $this;
    }

    public function withJson()
    {
        $results = json_encode($this->response);
        print($results);
        return $this;
    }

}
