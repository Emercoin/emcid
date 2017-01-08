<?php

namespace Emercoin\OAuthBundle\Security\Providers;

use Emercoin\OAuthBundle\Entity\User;
use Emercoin\OAuthBundle\Helper\InfoCardRequest;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Security\UserProvider;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Emercoin\OAuthBundle\Helper\StorageRequest as EmercoinRequest;

class CertificateProvider extends UserProvider
{
    protected $userManager;

    /** @var Request */
    protected $request;

    protected $server;

    public function __construct(UserManagerInterface $userManager, RequestStack $requestStack, Logger $logger, $dsn)
    {
        parent::__construct($userManager);
        $this->userManager = $userManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->logger = $logger;
        $this->dsn = $dsn;
        if (!$this->request) {
            return;
        }
        $this->server = $this->request->server;
    }

    protected function getSSLSerial()
    {
        if (!$this->request) {
            return null;
        }

        if (!$this->server->has('SSL_CLIENT_CERT')) {
            throw new AccessDeniedHttpException('No certificate presented, or missing flag +ExportCertData');
        }

        if (!$this->server->has('SSL_CLIENT_M_SERIAL')) {
            throw new AccessDeniedHttpException('This certificate has no serial number');
        }

        $serial = str_pad(strtolower($this->server->get('SSL_CLIENT_M_SERIAL')), 16, 0, STR_PAD_LEFT);

        if ($serial[0] == '0') {
            throw new AccessDeniedHttpException('Wrong serial number - must not start from zero');
        }

        try {
            $request = new EmercoinRequest('name_show', 'ssl:'.$serial, $this->dsn);
        } catch (\ErrorException $e) {
            throw new AccessDeniedHttpException('Error while connecting to Emercoin API');
        }

        if ($request->getData()['expires_in'] <= 0) {
            throw new AccessDeniedHttpException('NVS record expired, and is not trustable');
        }

        // Compute certificate fingerprint, using algo, defined in the NVS value
        list($algo, $emercoin_fingerprint) = explode('=', $request->getData()['value']);
        $certificate_fingerprint = hash(
            $algo,
            base64_decode(
                preg_replace(
                    '/\-+BEGIN CERTIFICATE\-+|-+END CERTIFICATE\-+|\n|\r/',
                    '',
                    $this->server->get('SSL_CLIENT_CERT')
                )
            )
        );

        if ($emercoin_fingerprint !== $certificate_fingerprint) {
            throw new AccessDeniedHttpException('False certificate provided');
        }

        return $serial;
    }

    public function loadUserByUsername($username)
    {
        $serial = $this->getSSLSerial();
        if (!$serial) {
            throw new UsernameNotFoundException('No user with such certificate SN');
        }

        $infocardRequest = new InfoCardRequest('name_show', $this->server->get('SSL_CLIENT_S_DN_UID'), $this->dsn, $this->server);

        $user = $this->userManager->findUserBy(['serial' => $serial]);

        if (!$user) {
            /** @var User $user */
            $user = $this->userManager->createUser();
            if (strlen($this->server->get('SSL_CLIENT_S_DN_Email')) > 0) {
                $user->setEmail($this->server->get('SSL_CLIENT_S_DN_Email'));
                $user->setEmailCanonical($this->server->get('SSL_CLIENT_S_DN_Email'));
                $user->setUsername($this->server->get('SSL_CLIENT_S_DN_Email'));
                $user->setUsernameCanonical($this->server->get('SSL_CLIENT_S_DN_Email'));
            } else {
                $login = uniqid();
                $user->setEmail($login.'@localhost.com');
                $user->setEmailCanonical($login.'@localhost.com');
                $user->setUsername($login.'@localhost.com');
                $user->setUsernameCanonical($login.'@localhost.com');
            }

            $password = hash('sha256', uniqid("pass", true));
            $user->setPassword($password);
            $user->setEnabled(true);
            $user->setSerial($serial);
        }

        $infocard = array();
        foreach ($this->server as $key => $value) {
            if (preg_match('/^(SSL_CLIENT(?!_CERT.*)|REMOTE_ADDR|HTTP_USER_AGENT).*/im', $key)) {
                $infocard[$key] = $value;
            }
        }

        $infocard['infocard'] = $infocardRequest->getInfocard();
        $user->setInfocard(json_encode($infocard, JSON_UNESCAPED_UNICODE));

        return $user;
    }
}
