<?php

namespace Lighthart\GridBundle\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CellType extends AbstractType
{
    private $class;
    private $field;
    private $id;
    private $em;
    private $user;

    public function __construct(
        $em,
        $tokenStorage
    ) {
        $this->em = $em;
        if ($tokenStorage->getToken()) {
            $this->user = $tokenStorage->getToken()->getUser();
        } else {
            $this->user = null;
        }
    }

    public function buildForm(
        FormBuilderInterface $builder,
        array                $options
    ) {
        if (isset($options['class'])) {
            $this->class = $options['class'];
        } else {
            $this->class = null;
        }

        if (isset($options['field'])) {
            $this->field = $options['field'];
        } else {
            $this->field = null;
        }

        if (isset($options['entity_id'])) {
            $this->id = $options['entity_id'];
        } else {
            $this->id = null;
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {}
    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'cell';
    }
}
