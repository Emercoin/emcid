<?php

namespace Emercoin\OAuthBundle\Helper;

class StorageRequest
{
    protected $data;

    protected $error;

    function __construct($method, $params = [], $dsn)
    {
        if (!(is_array($params) || is_string($params))) {
            throw new \InvalidArgumentException(sprintf("Invalid parameter type. Must be array or string. Type %s given.", gettype($params)));
        }
        $connect = $dsn;
        $params = is_string($params) ? [$params] : $params;
        // Prepares the request
        $request = json_encode(
            [
                'method' => $method,
                'params' => $params,
            ],
            JSON_UNESCAPED_UNICODE
        );
        // Prepare and performs the HTTP POST
        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => join(
                    "\r\n",
                    [
                        'Content-Type: application/json; charset=utf-8',
                        'Accept-Charset: utf-8;q=0.7,*;q=0.7',
                    ]
                ),
                'content' => $request,
                'ignore_errors' => true,
                'timeout' => 10
            ],
            'ssl' => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ],
        ];

        $response = @file_get_contents($connect, false, stream_context_create($opts));

        if (!$response) {
            throw new \ErrorException('Something went wrong. Please wait for a while and try again.');
        }


        $rc = json_decode($response, true);

        $this->error = $rc['error'];

        if (!is_null($this->error)) {
            throw new \ErrorException('EmercoinResponse error: '.$this->error['message']);
        }

        $this->data = $rc['result'];
    }

    function getData()
    {
        return $this->data;
    }
}