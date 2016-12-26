<?php

namespace Emercoin\OAuthBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return $this->render('EmercoinOAuthBundle:Default:index.html.twig');
    }

    /**
     * @Route("/infocard/{token}", name="_infocard")
     * @Method("GET")
     *
     * @param Request $request
     * @param string $token
     *
     * @return JsonResponse
     */
    public function infocardAction(Request $request, $token)
    {
        $access_token = $this->getDoctrine()
            ->getRepository('EmercoinOAuthBundle:AccessToken')
            ->findOneBy(
                ['token' => $token]
            );

        if (!$access_token) {
            throw new NotFoundHttpException();
        }

        $infocard = $access_token->getUser()->getInfocard();

        return new JsonResponse(json_decode($infocard, true));
    }
}
