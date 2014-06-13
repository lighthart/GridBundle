<?php

namespace Lighthart\GridBundle\Grid;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Query;

class Column {

    private $entity; // from the query
    private $value; // from the query

    public function __construct( $entity, $value ='id') {
        $this->entity = $entity;
        $this->value  = $value;

    }

    public function setEntity( $entity ) {
        $this->entity = $entity;
        return $this;
    }

    public function getEntity(){
        return $this->entity;
    }

    public function setValue( $value ) {
        $this->value = $value;
        return $this;
    }

    public function getValue(){
        return $this->value;
    }

}
