<?php

namespace Emercoin\OAuthBundle\Controller;

use FOS\OAuthServerBundle\Controller\AuthorizeController as BaseController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AuthorizeController extends BaseController
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    function authorizeAction(Request $request)
    {
        try {
            $response = parent::authorizeAction($request);

            return $response;
        } catch (AccessDeniedException $e) {
            if ($request->query->has('redirect_uri')) {
                $uri = $request->query->get('redirect_uri');

                $request = Request::create(
                    $uri,
                    'GET',
                    array(
                        'error' => 'access_denied',
                        'error_description' => $e->getMessage(),
                    )
                );

                return $this->redirect($request->getUri());
            }
            throw new AccessDeniedHttpException($e->getMessage());
        }
    }

    function redirect($url, $status = 302)
    {
        return new RedirectResponse($url, $status);
    }
}