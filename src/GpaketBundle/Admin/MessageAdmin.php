<?php

namespace GpaketBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class MessageAdmin extends AbstractAdmin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('message_id')
            ->add('date')
            ->add('text')
	        ->add('chat.title')
	        ->add('chat.username')
	        ->add('from.first_name')
	        ->add('from.last_name')
	        ->add('from.username')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('message_id')
            ->add('date')
            ->add('text')
            ->add('chat.title')
            ->add('chat.username')
            ->add('from.first_name')
            ->add('from.last_name')
            ->add('from.username')
            ->add('_action', null, array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array(),
                )
            ))
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('message_id')
            ->add('date')
            ->add('text')
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('message_id')
            ->add('date')
            ->add('text')
        ;
    }
}
