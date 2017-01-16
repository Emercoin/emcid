<?php

namespace Emercoin\OAuthBundle\Helper;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class InfoCardRequest extends StorageRequest
{
    /** @var \Symfony\Component\HttpFoundation\ServerBag */
    protected $server;

    /** @var string */
    protected $key;

    /** @var string */
    protected $service;

    /** @var string */
    protected $passwd;

    /** @var array */
    protected $infocard = [];

    /**
     * @param $method
     * @param array $params
     * @param $dsn
     * @param $server
     * @throws \ErrorException
     */
    function __construct($method, $params = [], $dsn, $server)
    {
        list($service, $key, $passwd) =
            explode(':', preg_replace('/[^0-9A-Za-z_:]/', '', $params));

        $this->key = $key;
        $this->service = $service;
        $this->passwd = $passwd;
        $this->server = $server;
        try {
            $this->preValidate();
            parent::__construct($method, ['info:'.$key, 'base64'], $dsn);
            $this->afterValidate();
            $this->deriveInfocard();
        } catch (AccessDeniedHttpException $e) {}
    }

    /**
     * @return void
     *
     * @throws AccessDeniedHttpException
     */
    protected function preValidate()
    {
        if ($this->service != "info") {
            throw new AccessDeniedHttpException(sprintf("Unsupported InfoCard service type: %s", $this->service));
        }

        if (!isset($this->passwd)) {
            throw new AccessDeniedHttpException('Wrong InfoCard link format - missing password');
        }
    }

    /**
     * @return void
     *
     * @throws AccessDeniedHttpException
     */
    protected function afterValidate()
    {
        if ($this->getData()['expires_in'] <= 0) {
            throw new AccessDeniedHttpException('NVS record expired, and is not trustable');
        }
    }

    /**
     * @return void
     */
    protected function deriveInfocard()
    {
        $cached_path = uniqid('infocard_');

        $fh = popen("openssl aes-256-cbc -d -pass pass:$this->passwd | zcat > /tmp/$cached_path", "wb");
        fwrite($fh, base64_decode($this->getData()['value']));
        pclose($fh);

        $fh = fopen("/tmp/$cached_path", 'r');
        $k = '';
        $tpr = '_hash_'.getmypid().'_';
        while (($buffer = fgets($fh, 4096)) !== false) {
            preg_match('/^(\S+)?(\s+)(.+)?/', $buffer, $matches);
            if (isset($matches[1]) && !empty($matches[1])) {
                $k = $matches[1];
            }
            $v = "";
            if (isset($matches[3])) {
                $v = preg_replace('/\\\#/', $tpr, $matches[3]);
                $v = preg_replace('/\s*\#.*/', '', $v);
                $v = preg_replace("/$tpr/", '#', $v);
            }
            if (!empty($k) && !empty($v)) {
                $this->infocard[$k] = $v;
            }
        }
        fclose($fh);
        unlink("/tmp/$cached_path");
    }

    /**
     * @return array
     */
    function getInfocard()
    {
        return $this->infocard;
    }
}