<?php

namespace Emercoin\OAuthBundle\Form;

use Emercoin\OAuthBundle\Entity\Client;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ClientType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $options['user'];

        $builder
            ->add(
                'name',
                null,
                array(
                    'label' => 'App Name',
                )
            )
            ->add(
                'redirect_uris',
                CollectionType::class,
                array(
                    'label' => 'Redirect URIs',
                    'entry_type' => UrlType::class,
                    'entry_options' => array(
                        'label' => false,
                        'default_protocol' => 'https',
                        'constraints' => array(
                            new Assert\Url(array('protocols' => array('http', 'https'))),
                        ),
                    ),
                    'prototype' => true,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'delete_empty' => true,
                    'by_reference' => false,
                )
            )->addEventListener(
                FormEvents::POST_SUBMIT,
                function (FormEvent $event) use ($user) {
                    $form = $event->getForm();
                    /** @var Client $data */
                    $data = $form->getData();
                    $data->setUser($user);
                }
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Emercoin\OAuthBundle\Entity\Client',
                'user' => null,
                'attr' => array('novalidate' => 'novalidate'),
            )
        );
        $resolver->setAllowedTypes('user', array('Symfony\Component\Security\Core\User\AdvancedUserInterface'));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'emercoin_client';
    }
}
