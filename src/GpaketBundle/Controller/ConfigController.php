<?php

namespace GpaketBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use GpaketBundle\Entity\Config;
use GpaketBundle\Form\ConfigType;

/**
 * Config controller.
 *
 */
class ConfigController extends Controller
{
    /**
     * Lists all Config entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $configs = $em->getRepository('GpaketBundle:Config')->findAll();

        return $this->render('config/index.html.twig', array(
            'configs' => $configs,
        ));
    }

    /**
     * Creates a new Config entity.
     *
     */
    public function newAction(Request $request)
    {
        $config = new Config();
        $form = $this->createForm(new ConfigType(), $config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($config);
            $em->flush();

            return $this->redirectToRoute('config_show', array('id' => $config->getId()));
        }

        return $this->render('config/new.html.twig', array(
            'config' => $config,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Config entity.
     *
     */
    public function showAction(Config $config)
    {
        $deleteForm = $this->createDeleteForm($config);

        return $this->render('config/show.html.twig', array(
            'config' => $config,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Config entity.
     *
     */
    public function editAction(Request $request, Config $config)
    {
        $deleteForm = $this->createDeleteForm($config);
        $editForm = $this->createForm(new ConfigType(), $config);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($config);
            $em->flush();

            return $this->redirectToRoute('config_edit', array('id' => $config->getId()));
        }

        return $this->render('config/edit.html.twig', array(
            'config' => $config,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Config entity.
     *
     */
    public function deleteAction(Request $request, Config $config)
    {
        $form = $this->createDeleteForm($config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($config);
            $em->flush();
        }

        return $this->redirectToRoute('config_index');
    }

    /**
     * Creates a form to delete a Config entity.
     *
     * @param Config $config The Config entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Config $config)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('config_delete', array('id' => $config->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
