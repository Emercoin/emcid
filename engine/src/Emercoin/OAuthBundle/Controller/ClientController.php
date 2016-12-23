<?php

namespace Emercoin\OAuthBundle\Controller;

use Emercoin\OAuthBundle\Entity\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Client controller.
 *
 * @Route("/client")
 */
class ClientController extends Controller
{
    /**
     * Lists all client entities.
     *
     * @Route("s", name="client_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $clients = $em->getRepository('EmercoinOAuthBundle:Client')->findBy(
            ['user' => $this->getUser()],
            ['id' => 'desc']
        );

        return array(
            'clients' => $clients,
        );
    }

    /**
     * Creates a new client entity.
     *
     * @Route("/new", name="client_new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request)
    {
        $client = new Client();
        $form = $this->createForm('Emercoin\OAuthBundle\Form\ClientType', $client, array('user' => $this->getUser()));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($client);
            $em->flush($client);

            return $this->redirectToRoute('client_index');
        }

        return array(
            'client' => $client,
            'form' => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing client entity.
     *
     * @Route("/{id}/edit", name="client_edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, $id)
    {
        $client = $this->getDoctrine()->getRepository('EmercoinOAuthBundle:Client')->findOneBy(
            ['user' => $this->getUser(), 'id' => $id]
        );
        if (!$client) {
            throw new NotFoundHttpException();
        }
        $deleteForm = $this->createDeleteForm($client);
        $editForm = $this->createForm(
            'Emercoin\OAuthBundle\Form\ClientType',
            $client,
            array('user' => $this->getUser())
        );
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('client_edit', array('id' => $client->getId()));
        }

        return array(
            'client' => $client,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a client entity.
     *
     * @Route("/{id}", name="client_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $client = $this->getDoctrine()->getRepository('EmercoinOAuthBundle:Client')->findOneBy(
            ['user' => $this->getUser(), 'id' => $id]
        );
        if (!$client) {
            throw new NotFoundHttpException();
        }

        $form = $this->createDeleteForm($client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($client);
            $em->flush($client);
        }

        return $this->redirectToRoute('client_index');
    }

    /**
     * Creates a form to delete a client entity.
     *
     * @param Client $client The client entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Client $client)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('client_delete', array('id' => $client->getId())))
            ->setMethod('DELETE')
            ->add('delete', SubmitType::class, array('attr' => array('onclick' => 'return confirm(\'Are you sure?\')')))
            ->getForm();
    }
}
